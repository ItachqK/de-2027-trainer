using System;
using System.Diagnostics;
using System.IO;
using System.Net;
using System.Net.Sockets;
using System.Text;
using System.Threading;

namespace De2027
{
    internal static class Program
    {
        private static Process server;
        private static StreamWriter log;

        private static int Main()
        {
            Console.OutputEncoding = Encoding.UTF8;
            Console.Title = "ДЭ-2027 — платформа подготовки";

            var baseDir = AppDomain.CurrentDomain.BaseDirectory.TrimEnd('\\');
            var appDir = Path.Combine(baseDir, "app");
            var phpDir = Path.Combine(baseDir, "runtime", "php");
            var php = Path.Combine(phpDir, "php.exe");
            var ini = Path.Combine(phpDir, "php.local.ini");

            if (!Directory.Exists(appDir))
            {
                Fail("Не найдена папка app. Распакуйте архив целиком, не меняя структуру.");
                return 1;
            }

            if (!File.Exists(php))
            {
                Fail("Не найден встроенный PHP: runtime\\php\\php.exe");
                return 1;
            }

            try
            {
                PrepareIni(phpDir, appDir, ini);
            }
            catch (Exception ex)
            {
                Fail("Не удалось подготовить php.ini: " + ex.Message);
                return 1;
            }

            var port = FindPort(8100);
            if (port == 0)
            {
                Fail("Не удалось найти свободный порт 8100–8110.");
                return 1;
            }

            var db = Path.Combine(appDir, "database", "database.sqlite");
            var marker = Path.Combine(appDir, "storage", "app", "installed");
            var env = new System.Collections.Generic.Dictionary<string, string>
            {
                { "APP_ENV", "production" },
                { "APP_DEBUG", "false" },
                { "APP_URL", "http://127.0.0.1:" + port },
                { "DB_CONNECTION", "sqlite" },
                { "DB_DATABASE", db },
                { "SESSION_DRIVER", "database" },
                { "CACHE_STORE", "file" },
                { "QUEUE_CONNECTION", "sync" },
                { "MAIL_MAILER", "log" },
                { "PHPRC", ini }
            };

            Directory.CreateDirectory(Path.GetDirectoryName(db));
            Directory.CreateDirectory(Path.Combine(appDir, "storage", "logs"));
            Directory.CreateDirectory(Path.Combine(appDir, "storage", "framework", "cache"));
            Directory.CreateDirectory(Path.Combine(appDir, "storage", "framework", "sessions"));
            Directory.CreateDirectory(Path.Combine(appDir, "storage", "framework", "views"));

            Console.WriteLine();
            Console.WriteLine("  ДЭ-2027 — платформа подготовки к демоэкзамену");
            Console.WriteLine("  ------------------------------------------------");
            Console.WriteLine();

            if (!File.Exists(db))
            {
                File.WriteAllBytes(db, new byte[0]);
                if (File.Exists(marker))
                {
                    File.Delete(marker);
                }
                Console.WriteLine("  Первый запуск: создаю базу и уроки...");
                RunPhp(php, ini, appDir, env, "artisan migrate --seed --force");
                File.WriteAllText(marker, "installed");
            }
            else if (!File.Exists(marker))
            {
                Console.WriteLine("  Применяю миграции...");
                RunPhp(php, ini, appDir, env, "artisan migrate --force");
                File.WriteAllText(marker, "installed");
            }

            log = new StreamWriter(Path.Combine(appDir, "storage", "logs", "portable-serve.log"), false, Encoding.UTF8) { AutoFlush = true };

            var psi = new ProcessStartInfo
            {
                FileName = php,
                Arguments = "-c \"" + ini + "\" artisan serve --host=127.0.0.1 --port=" + port,
                WorkingDirectory = appDir,
                UseShellExecute = false,
                CreateNoWindow = true,
                RedirectStandardOutput = true,
                RedirectStandardError = true
            };

            foreach (var pair in env)
            {
                psi.EnvironmentVariables[pair.Key] = pair.Value;
            }

            server = new Process { StartInfo = psi, EnableRaisingEvents = true };
            server.OutputDataReceived += (s, e) => { if (e.Data != null) log.WriteLine(e.Data); };
            server.ErrorDataReceived += (s, e) => { if (e.Data != null) log.WriteLine(e.Data); };
            server.Start();
            server.BeginOutputReadLine();
            server.BeginErrorReadLine();

            var url = "http://127.0.0.1:" + port;
            Console.WriteLine("  Запускаю сервер: " + url);

            if (!WaitForPort(port, 20000))
            {
                Console.WriteLine("  Сервер не ответил за 20 секунд. Лог: app\\storage\\logs\\portable-serve.log");
                StopServer();
                Console.ReadLine();
                return 1;
            }

            Console.WriteLine("  Готово. Открываю браузер.");
            Console.WriteLine();
            Console.WriteLine("  Администратор: admin / Admin2027!");
            Console.WriteLine("  Студент:       student / Student2027!");
            Console.WriteLine();
            Console.WriteLine("  Закройте это окно или нажмите Enter, чтобы остановить сервер.");
            Console.WriteLine();

            try
            {
                Process.Start(new ProcessStartInfo(url) { UseShellExecute = true });
            }
            catch
            {
                Console.WriteLine("  Откройте вручную: " + url);
            }

            Console.CancelKeyPress += (s, e) =>
            {
                e.Cancel = true;
                StopServer();
                Environment.Exit(0);
            };

            Console.ReadLine();
            StopServer();
            return 0;
        }

        private static void PrepareIni(string phpDir, string appDir, string ini)
        {
            var source = Path.Combine(phpDir, "php.ini");
            var lines = File.ReadAllLines(source, Encoding.UTF8);
            var ext = Path.Combine(phpDir, "ext");
            var err = Path.Combine(appDir, "storage", "logs", "php_error.log");

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
            }

            File.WriteAllLines(ini, lines, new UTF8Encoding(false));
        }

        private static int FindPort(int start)
        {
            for (var port = start; port <= start + 10; port++)
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
                    // порт занят
                }
            }

            return 0;
        }

        private static bool WaitForPort(int port, int timeoutMs)
        {
            var until = DateTime.UtcNow.AddMilliseconds(timeoutMs);
            while (DateTime.UtcNow < until)
            {
                try
                {
                    using (var client = new TcpClient())
                    {
                        var task = client.ConnectAsync("127.0.0.1", port);
                        if (task.Wait(500) && client.Connected)
                        {
                            return true;
                        }
                    }
                }
                catch
                {
                    // ещё стартует
                }

                Thread.Sleep(400);
            }

            return false;
        }

        private static void RunPhp(string php, string ini, string appDir, System.Collections.Generic.Dictionary<string, string> env, string args)
        {
            var psi = new ProcessStartInfo
            {
                FileName = php,
                Arguments = "-c \"" + ini + "\" " + args,
                WorkingDirectory = appDir,
                UseShellExecute = false,
                CreateNoWindow = true,
                RedirectStandardOutput = true,
                RedirectStandardError = true
            };

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
                    throw new Exception((output + "\n" + error).Trim());
                }
            }
        }

        private static void StopServer()
        {
            try
            {
                if (server != null && !server.HasExited)
                {
                    Process.Start(new ProcessStartInfo("taskkill", "/PID " + server.Id + " /T /F")
                    {
                        UseShellExecute = false,
                        CreateNoWindow = true
                    }).WaitForExit(5000);
                }
            }
            catch
            {
                // ignore
            }

            if (log != null)
            {
                log.Flush();
                log.Dispose();
                log = null;
            }
        }

        private static void Fail(string message)
        {
            Console.WriteLine();
            Console.WriteLine("  Ошибка: " + message);
            Console.WriteLine();
            Console.WriteLine("  Нажмите Enter, чтобы закрыть.");
            Console.ReadLine();
        }
    }
}
