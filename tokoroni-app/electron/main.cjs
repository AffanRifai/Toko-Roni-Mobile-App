const { app, BrowserWindow } = require("electron");
const { spawn } = require("child_process");
const path = require("path");

let mainWindow;
let phpProcess;

const PORT = 8000;
const URL = `http://127.0.0.1:${PORT}`;

// =======================
// START LARAVEL SERVER
// =======================
function startLaravel() {
    const phpPath = path.join(__dirname, "..", "php", "php.exe");
    const laravelPath = path.join(__dirname, "..", "laravel");

    phpProcess = spawn(
        phpPath,
        ["artisan", "serve", "--host=127.0.0.1", `--port=${PORT}`],
        {
            cwd: laravelPath,
            windowsHide: true,
        }
    );

    phpProcess.stdout.on("data", (data) => {
        console.log(`[PHP] ${data}`);
    });

    phpProcess.stderr.on("data", (data) => {
        console.error(`[PHP ERROR] ${data}`);
    });
}

// =======================
// CREATE WINDOW
// =======================
function createWindow() {
    mainWindow = new BrowserWindow({
        width: 1280,
        height: 800,
        show: false,
        webPreferences: {
            contextIsolation: true,
            nodeIntegration: false,
        },
    });

    mainWindow.loadURL(URL);

    mainWindow.once("ready-to-show", () => {
        mainWindow.show();
    });
}

// =======================
// APP LIFECYCLE
// =======================
app.whenReady().then(() => {
    startLaravel();

    // kasih waktu Laravel boot
    setTimeout(() => {
        createWindow();
    }, 4000);
});

app.on("window-all-closed", () => {
    if (phpProcess) {
        phpProcess.kill();
    }
    app.quit();
});
