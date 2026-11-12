const { app, BrowserWindow, Menu, shell, dialog } = require('electron');
const path = require('path');
const fs = require('fs');
const http = require('http');
const { spawn, spawnSync } = require('child_process');

const APP_NAME = 'DE-2027';
const IS_WINDOWS = process.platform === 'win32';

app.setName(APP_NAME);
app.setPath('userData', path.join(app.getPath('appData'), APP_NAME));

let mainWindow = null;
let phpServer = null;
let isQuitting = false;
let logStream = null;

const singleInstance = app.requestSingleInstanceLock();
if (!singleInstance) {
  app.quit();
}

function log(line) {
  if (!logStream) return;
  logStream.write(`[${new Date().toISOString()}] ${line}\n`);
}

function resourcesRoot() {
  return app.isPackaged ? process.resourcesPath : path.resolve(__dirname, '..', 'dist', 'DE-2027');
}

function sourceAppDir() {
  return app.isPackaged
    ? path.join(process.resourcesPath, 'app')
    : path.join(resourcesRoot(), 'app');
}

function phpExe() {
  return app.isPackaged
    ? path.join(process.resourcesPath, 'php', 'php.exe')
    : path.join(resourcesRoot(), 'runtime', 'php', 'php.exe');
}

function phpSourceIni() {
  return path.join(path.dirname(phpExe()), 'php.ini');
}

function userAppDir() {
  return path.join(app.getPath('userData'), 'app');
}

function ensureUserApp() {
  const target = userAppDir();
  const source = sourceAppDir();

  if (!fs.existsSync(source)) {
    throw new Error('Не найдена папка приложения: ' + source);
  }

  if (!fs.existsSync(path.join(target, 'artisan'))) {
    fs.mkdirSync(target, { recursive: true });
    fs.cpSync(source, target, { recursive: true, force: true });
  }

  for (const dir of ['storage/logs', 'storage/framework/cache', 'storage/framework/sessions', 'storage/framework/views', 'storage/app/public']) {
    fs.mkdirSync(path.join(target, dir), { recursive: true });
  }

  return target;
}

function prepareIni(appDir) {
  const source = phpSourceIni();
  const target = path.join(app.getPath('userData'), 'php.local.ini');
  const ext = path.join(path.dirname(phpExe()), 'ext');
  const err = path.join(appDir, 'storage', 'logs', 'php_error.log');

  const lines = fs.readFileSync(source, 'utf8').split(/\r?\n/);
  const patched = lines.map((line) => {
    const trimmed = line.trimStart();
    if (trimmed.startsWith('extension_dir')) return `extension_dir = "${ext}"`;
    if (trimmed.startsWith('error_log')) return `error_log = "${err}"`;
    return line;
  });

  fs.writeFileSync(target, patched.join('\r\n'), 'utf8');
  return target;
}

function findPort(start) {
  return new Promise((resolve) => {
    const net = require('net');
    const tryPort = (port) => {
      if (port > start + 20) return resolve(0);
      const server = net.createServer();
      server.once('error', () => tryPort(port + 1));
      server.once('listening', () => server.close(() => resolve(port)));
      server.listen(port, '127.0.0.1');
    };
    tryPort(start);
  });
}

function waitForServer(port, timeoutMs = 30000) {
  const started = Date.now();
  return new Promise((resolve) => {
    const ping = () => {
      const req = http.get({ host: '127.0.0.1', port, path: '/login', timeout: 1000 }, (res) => {
        res.resume();
        resolve(true);
      });
      req.on('error', () => {
        if (Date.now() - started > timeoutMs) return resolve(false);
        setTimeout(ping, 500);
      });
      req.on('timeout', () => req.destroy());
    };
    ping();
  });
}

function killServer() {
  if (!phpServer || phpServer.killed) return;
  try {
    if (IS_WINDOWS) {
      spawnSync('taskkill', ['/PID', String(phpServer.pid), '/T', '/F'], { windowsHide: true });
    } else {
      phpServer.kill('SIGTERM');
    }
  } catch (e) {
    log('kill error: ' + e.message);
  }
  phpServer = null;
}

async function startServer() {
  const appDir = ensureUserApp();
  const ini = prepareIni(appDir);
  const db = path.join(appDir, 'database', 'database.sqlite');
  const marker = path.join(appDir, 'storage', 'app', 'installed');
  const port = await findPort(8100);

  if (!port) throw new Error('Не удалось найти свободный порт.');

  logStream = fs.createWriteStream(path.join(appDir, 'storage', 'logs', 'desktop.log'), { flags: 'a' });
  log('app dir: ' + appDir);
  log('port: ' + port);

  if (!fs.existsSync(db)) {
    fs.writeFileSync(db, '');
    if (fs.existsSync(marker)) fs.unlinkSync(marker);
  }

  const env = {
    ...process.env,
    APP_ENV: 'production',
    APP_DEBUG: 'false',
    APP_URL: `http://127.0.0.1:${port}`,
    DB_CONNECTION: 'sqlite',
    DB_DATABASE: db,
    SESSION_DRIVER: 'database',
    CACHE_STORE: 'file',
    QUEUE_CONNECTION: 'sync',
    MAIL_MAILER: 'log',
    PHPRC: ini
  };

  if (!fs.existsSync(marker)) {
    const migrate = spawnSync(phpExe(), ['-c', ini, 'artisan', 'migrate', '--seed', '--force'], {
      cwd: appDir,
      env,
      windowsHide: true,
      timeout: 180000,
      encoding: 'utf8'
    });
    if (migrate.status !== 0) {
      throw new Error('Миграции не выполнились: ' + (migrate.stderr || migrate.stdout || '').slice(0, 500));
    }
    fs.writeFileSync(marker, 'installed');
  }

  phpServer = spawn(phpExe(), ['-c', ini, 'artisan', 'serve', '--host=127.0.0.1', '--port=' + port], {
    cwd: appDir,
    env,
    windowsHide: true,
    stdio: ['ignore', 'pipe', 'pipe']
  });

  phpServer.stdout.on('data', (d) => log('php: ' + d.toString().trim()));
  phpServer.stderr.on('data', (d) => log('php err: ' + d.toString().trim()));
  phpServer.on('exit', (code) => log('php exit: ' + code));

  const ready = await waitForServer(port);
  if (!ready) throw new Error('Сервер не ответил за 30 секунд. Лог: ' + path.join(appDir, 'storage', 'logs', 'desktop.log'));

  return `http://127.0.0.1:${port}`;
}

function createWindow() {
  mainWindow = new BrowserWindow({
    width: 1440,
    height: 920,
    minWidth: 1024,
    minHeight: 700,
    show: false,
    backgroundColor: '#e7e4dc',
    title: 'ДЭ-2027 — платформа подготовки',
    icon: path.join(__dirname, 'build', 'icon.ico'),
    autoHideMenuBar: true,
    webPreferences: {
      contextIsolation: true,
      nodeIntegration: false,
      sandbox: true
    }
  });

  Menu.setApplicationMenu(null);
  mainWindow.loadFile(path.join(__dirname, 'loading.html'));

  mainWindow.webContents.setWindowOpenHandler(({ url }) => {
    shell.openExternal(url);
    return { action: 'deny' };
  });

  mainWindow.on('closed', () => {
    mainWindow = null;
  });

  return mainWindow;
}

async function boot() {
  createWindow();

  try {
    const url = await startServer();
    if (!mainWindow) return;

    await mainWindow.loadURL(url);
    mainWindow.show();

    if (process.argv.includes('--screenshot')) {
      setTimeout(async () => {
        try {
          const image = await mainWindow.webContents.capturePage();
          fs.writeFileSync(path.join(app.getPath('userData'), 'screenshot.png'), image.toPNG());
          log('screenshot saved');
        } catch (e) {
          log('screenshot error: ' + e.message);
        }
        app.quit();
      }, 1800);
    }
  } catch (e) {
    log('boot error: ' + e.message);
    if (mainWindow) {
      mainWindow.show();
      mainWindow.loadURL('data:text/html;charset=utf-8,' + encodeURIComponent(
        `<body style="font-family:Segoe UI,Arial;background:#e7e4dc;color:#16181a;padding:40px">
          <h1>Не удалось запустить платформу</h1>
          <p>${String(e.message).replace(/</g, '&lt;')}</p>
          <p>Лог: <code>${path.join(app.getPath('userData'), 'app', 'storage', 'logs', 'desktop.log')}</code></p>
        </body>`
      ));
    } else {
      dialog.showErrorBox('ДЭ-2027', String(e.message));
      app.quit();
    }
  }
}

app.on('second-instance', () => {
  if (mainWindow) {
    if (mainWindow.isMinimized()) mainWindow.restore();
    mainWindow.focus();
  }
});

app.whenReady().then(boot);

app.on('before-quit', () => {
  isQuitting = true;
  killServer();
});

app.on('window-all-closed', () => {
  killServer();
  app.quit();
});
