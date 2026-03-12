const { app, BrowserWindow } = require('electron')

function createWindow () {
  const win = new BrowserWindow({
    width: 1280,
    height: 800,
    webPreferences: {
      nodeIntegration: false
    }
  })

  win.loadURL('http://127.0.0.1:8000')
}

app.whenReady().then(createWindow)
