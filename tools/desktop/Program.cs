using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Drawing;
using System.IO;
using System.Net;
using System.Net.Sockets;
using System.Text;
using System.Threading;
using System.Threading.Tasks;
using System.Windows.Forms;
using Microsoft.Web.WebView2.Core;
using Microsoft.Web.WebView2.WinForms;

namespace De2027Desktop
{
    internal static class Program
    {
        [STAThread]
        private static void Main(string[] args)
        {
            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);
            Application.Run(new MainForm(args));
        }
    }

    internal sealed class MainForm : Form
    {
        private readonly string[] args;
        private WebView2 webView;
        private Label status;
        private Process server;
        private StreamWriter log;
        private string baseDir;
        private string appDir;
        private string php;
        private string ini;
        private string url;
        private bool screenshotMode;
        private string startPath = "";

        public MainForm(string[] args)
        {
            this.args = args;
            baseDir = AppDomain.CurrentDomain.BaseDirectory.TrimEnd('\\');
            screenshotMode = Array.IndexOf(args, "--screenshot") >= 0;

            foreach (var arg in args)
            {
                if (arg.StartsWith("--path="))
                {
                    startPath = arg.Substring("--path=".Length);
                }
            }

            Text = "ДЭ-2027 — платформа подготовки";
            Width = 1440;
            Height = 920;
            MinimumSize = new Size(1024, 700);
            StartPosition = FormStartPosition.CenterScreen;
            BackColor = Color.FromArgb(23, 25, 27);

            var iconPath = Path.Combine(baseDir, "icon.ico");
            if (File.Exists(iconPath))
            {
                try { Icon = new Icon(iconPath); } catch { }
            }

            status = new Label();
            status.Dock = DockStyle.Fill;
            status.TextAlign = ContentAlignment.MiddleCenter;
            status.ForeColor = Color.FromArgb(242, 239, 231);
            status.BackColor = Color.FromArgb(23, 25, 27);
            status.Font = new Font("Segoe UI", 14f, FontStyle.Regular);
            status.Text = "Запускаю платформу...\r\n\r\nПоднимаю PHP, базу SQLite и уроки.";

            webView = new WebView2();
            webView.Dock = DockStyle.Fill;
            webView.Visible = false;

            Controls.Add(webView);
            Controls.Add(status);
            status.BringToFront();

            Shown += OnShown;
            FormClosing += OnClosing;
        }

        private async void OnShown(object sender, EventArgs e)
        {
            try
            {
                url = await Task.Run(new Func<string>(StartServer));
                await InitWebView();
                status.Visible = false;
                webView.Visible = true;
                webView.Source = new Uri(startPath == "" ? url : url + startPath);
            }
            catch (Exception ex)
            {
                status.Text = "Не удалось запустить платформу.\r\n\r\n" + ex.Message
                    + "\r\n\r\nЛог: " + Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData), "DE-2027", "app", "storage", "logs", "desktop.log");
                MessageBox.Show(this, ex.Message, "ДЭ-2027", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
        }

        private async Task InitWebView()
        {
            var dataDir = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData), "DE-2027", "WebView2");
            Directory.CreateDirectory(dataDir);

            var env = await CoreWebView2Environment.CreateAsync(null, dataDir, null);
            await webView.EnsureCoreWebView2Async(env);

            webView.CoreWebView2.Settings.AreDevToolsEnabled = false;
            webView.CoreWebView2.Settings.AreDefaultContextMenusEnabled = false;
            webView.CoreWebView2.NewWindowRequested += OnNewWindow;
            webView.NavigationCompleted += OnNavigationCompleted;
        }

        private void OnNewWindow(object sender, CoreWebView2NewWindowRequestedEventArgs e)
        {
            e.Handled = true;
            try { Process.Start(e.Uri); } catch { }
        }

        private async void OnNavigationCompleted(object sender, CoreWebView2NavigationCompletedEventArgs e)
        {
            if (!screenshotMode || !e.IsSuccess)
            {
                return;
            }

            await Task.Delay(1800);

            try
            {
                var path = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData), "DE-2027", "screenshot.png");
                using (var stream = File.Create(path))
                {
                    await webView.CoreWebView2.CapturePreviewAsync(CoreWebView2CapturePreviewImageFormat.Png, stream);
                }
            }
            catch
            {
                // скриншот не критичен
            }

            Close();
        }

        private string StartServer()
        {
            php = Path.Combine(baseDir, "runtime", "php", "php.exe");

            if (!File.Exists(php))
            {
                throw new Exception("Не найден встроенный PHP: runtime\\php\\php.exe");
            }

            var sourceApp = Path.Combine(baseDir, "app");
            if (!Directory.Exists(sourceApp))
            {
                throw new Exception("Не найдена папка app.");
            }

            var root = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData), "DE-2027");
            appDir = Path.Combine(root, "app");

            var codeUpdated = SyncApp(sourceApp, appDir);

            foreach (var dir in new[] { "storage\\logs", "storage\\framework\\cache", "storage\\framework\\sessions", "storage\\framework\\views", "storage\\app\\public" })
            {
                Directory.CreateDirectory(Path.Combine(appDir, dir));
            }

            ini = PrepareIni(root, appDir);
            log = new StreamWriter(Path.Combine(appDir, "storage", "logs", "desktop.log"), true, Encoding.UTF8);
            log.AutoFlush = true;

            var port = FindPort(8100);
            if (port == 0)
            {
                throw new Exception("Не удалось найти свободный порт 8100–8120.");
            }

            var db = Path.Combine(appDir, "database", "database.sqlite");
            var marker = Path.Combine(appDir, "storage", "app", "installed");
            Directory.CreateDirectory(Path.GetDirectoryName(db));

            var env = new Dictionary<string, string>();
            env["APP_ENV"] = "production";
            env["APP_DEBUG"] = "false";
            env["APP_URL"] = "http://127.0.0.1:" + port;
            env["DB_CONNECTION"] = "sqlite";
            env["DB_DATABASE"] = db;
            env["SESSION_DRIVER"] = "database";
            env["CACHE_STORE"] = "file";
            env["QUEUE_CONNECTION"] = "sync";
            env["MAIL_MAILER"] = "log";
            env["PHPRC"] = ini;

            if (!File.Exists(db))
            {
                File.WriteAllBytes(db, new byte[0]);
                if (File.Exists(marker)) File.Delete(marker);
            }

            if (!File.Exists(marker))
            {
                RunPhp(env, "artisan migrate --seed --force");
                File.WriteAllText(marker, "installed");
            }
            else
            {
                RunPhp(env, "artisan migrate --force");
            }

            // Контент обновляем только вместе с новой версией кода,
            // чтобы не затирать правки уроков при каждом запуске.
            if (codeUpdated)
            {
                RunPhp(env, "artisan db:seed --class=CourseSeeder --force");
                RunPhp(env, "artisan db:seed --class=DrillSeeder --force");
                RunPhp(env, "artisan db:seed --class=ProjectVariantSeeder --force");
                RunPhp(env, "artisan db:seed --class=WatchVariantSeeder --force");
                RunPhp(env, "artisan db:seed --class=BuildStepSeeder --force");
                RunPhp(env, "artisan db:seed --class=CaseStudySeeder --force");
                RunPhp(env, "artisan db:seed --class=BookexchangeCaseSeeder --force");
                RunPhp(env, "artisan db:seed --class=RestaurantCaseSeeder --force");
                RunPhp(env, "artisan db:seed --class=PizzaCaseSeeder --force");
                RunPhp(env, "artisan db:seed --class=WatchCaseSeeder --force");
                RunPhp(env, "artisan db:seed --class=ChronicleSeeder --force");
            }

            var psi = new ProcessStartInfo();
            psi.FileName = php;
            psi.Arguments = "-c \"" + ini + "\" artisan serve --host=127.0.0.1 --port=" + port;
            psi.WorkingDirectory = appDir;
            psi.UseShellExecute = false;
            psi.CreateNoWindow = true;
            psi.RedirectStandardOutput = true;
            psi.RedirectStandardError = true;

            foreach (var pair in env)
            {
                psi.EnvironmentVariables[pair.Key] = pair.Value;
            }

            server = new Process();
            server.StartInfo = psi;
            server.OutputDataReceived += (s, e) => { if (e.Data != null) log.WriteLine("php: " + e.Data); };
            server.ErrorDataReceived += (s, e) => { if (e.Data != null) log.WriteLine("php err: " + e.Data); };
            server.Start();
            server.BeginOutputReadLine();
            server.BeginErrorReadLine();

            if (!WaitForServer(port))
            {
                throw new Exception("Сервер не ответил за 30 секунд. Лог: " + Path.Combine(appDir, "storage", "logs", "desktop.log"));
            }

            return "http://127.0.0.1:" + port;
        }

        private void RunPhp(Dictionary<string, string> env, string arguments)
        {
            var psi = new ProcessStartInfo();
            psi.FileName = php;
            psi.Arguments = "-c \"" + ini + "\" " + arguments;
            psi.WorkingDirectory = appDir;
            psi.UseShellExecute = false;
            psi.CreateNoWindow = true;
            psi.RedirectStandardOutput = true;
            psi.RedirectStandardError = true;

            foreach (var pair in env)
            {
                psi.EnvironmentVariables[pair.Key] = pair.Value;
            }

            using (var process = Process.Start(psi))
            {
                var output = process.StandardOutput.ReadToEnd();
                var error = process.StandardError.ReadToEnd();
                process.WaitForExit();

                if (process.ExitCode != 0)
                {
                    throw new Exception((output + "\r\n" + error).Trim());
                }
            }
        }

        private string PrepareIni(string root, string appDirPath)
        {
            var source = Path.Combine(baseDir, "runtime", "php", "php.ini");
            var target = Path.Combine(root, "php.local.ini");
            var ext = Path.Combine(baseDir, "runtime", "php", "ext");
            var err = Path.Combine(appDirPath, "storage", "logs", "php_error.log");

            var lines = File.ReadAllLines(source, Encoding.UTF8);
            for (var i = 0; i < lines.Length; i++)
            {
                var trimmed = lines[i].TrimStart();
                if (trimmed.StartsWith("extension_dir"))
                {
                    lines[i] = "extension_dir = \"" + ext + "\"";
                }
                else if (trimmed.StartsWith("error_log"))
                {
                    lines[i] = "error_log = \"" + err + "\"";
                }
                else if (trimmed.StartsWith("opcache.enable_cli"))
                {
                    lines[i] = "opcache.enable_cli = Off";
                }
                else if (trimmed.StartsWith("opcache.enable "))
                {
                    lines[i] = "opcache.enable = Off";
                }
            }

            File.WriteAllLines(target, lines, new UTF8Encoding(false));
            return target;
        }

        private int FindPort(int start)
        {
            for (var port = start; port <= start + 20; port++)
            {
                try
                {
                    var listener = new TcpListener(IPAddress.Loopback, port);
                    listener.Start();
                    listener.Stop();
                    return port;
                }
                catch
                {
                    // занят
                }
            }

            return 0;
        }

        private bool WaitForServer(int port)
        {
            var until = DateTime.UtcNow.AddSeconds(30);

            while (DateTime.UtcNow < until)
            {
                try
                {
                    var request = (HttpWebRequest)WebRequest.Create("http://127.0.0.1:" + port + "/login");
                    request.Timeout = 1000;
                    request.ReadWriteTimeout = 1000;
                    using (var response = request.GetResponse())
                    {
                        return true;
                    }
                }
                catch
                {
                    Thread.Sleep(500);
                }
            }

            return false;
        }

        private bool SyncApp(string source, string target)
        {
            var sourceVersion = ReadVersion(source);
            var targetVersion = ReadVersion(target);

            if (File.Exists(Path.Combine(target, "artisan"))
                && sourceVersion != ""
                && sourceVersion == targetVersion)
            {
                return false;
            }

            SyncDirectory(source, target, source);

            if (sourceVersion != "")
            {
                File.WriteAllText(Path.Combine(target, "version.txt"), sourceVersion);
            }

            return true;
        }

        private string ReadVersion(string dir)
        {
            var path = Path.Combine(dir, "version.txt");

            return File.Exists(path) ? File.ReadAllText(path).Trim() : "";
        }

        private void SyncDirectory(string source, string target, string root)
        {
            Directory.CreateDirectory(target);

            foreach (var dir in Directory.GetDirectories(source))
            {
                var relative = dir.Substring(root.Length).TrimStart('\\').ToLowerInvariant();

                if (relative.StartsWith("storage\\logs")
                    || relative.StartsWith("storage\\framework\\cache")
                    || relative.StartsWith("storage\\framework\\sessions")
                    || relative.StartsWith("storage\\framework\\views")
                    || relative.StartsWith("storage\\app\\public"))
                {
                    continue;
                }

                SyncDirectory(dir, Path.Combine(target, Path.GetFileName(dir)), root);
            }

            foreach (var file in Directory.GetFiles(source))
            {
                var relative = file.Substring(root.Length).TrimStart('\\').ToLowerInvariant();
                var name = Path.GetFileName(file);

                if (relative == "database\\database.sqlite"
                    || relative == "storage\\app\\installed"
                    || relative == "version.txt")
                {
                    continue;
                }

                if (relative == ".env" && File.Exists(Path.Combine(target, ".env")))
                {
                    continue;
                }

                File.Copy(file, Path.Combine(target, name), true);
            }
        }

        private void OnClosing(object sender, FormClosingEventArgs e)
        {
            if (server != null)
            {
                try
                {
                    if (!server.HasExited)
                    {
                        Process.Start(new ProcessStartInfo("taskkill", "/PID " + server.Id + " /T /F")
                        {
                            UseShellExecute = false,
                            CreateNoWindow = true
                        }).WaitForExit(5000);
                    }
                }
                catch { }
            }

            if (log != null)
            {
                log.Flush();
                log.Dispose();
                log = null;
            }
        }
    }
}
