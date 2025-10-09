const { app, BrowserWindow } = require("electron");
const path = require("path");

const USE_LOCAL = false;
const LOCAL_URL = "http://localhost:8081/biblioteca_online";
const REMOTE_URL = "https://bibliotecacuryonline.free.nf/biblioteca_online";

function createWindow() {
  const win = new BrowserWindow({
    width: 1280,
    height: 800,
    icon: path.join(__dirname, "icon.png"),
    autoHideMenuBar: true,
    webPreferences: {
      contextIsolation: true,
      nodeIntegration: false,
    },
  });

  const targetURL = USE_LOCAL ? LOCAL_URL : REMOTE_URL;
  win.loadURL(targetURL);

  // win.webContents.openDevTools(); // <- descomente se quiser o console
}

app.whenReady().then(createWindow);

app.on("activate", () => {
  if (BrowserWindow.getAllWindows().length === 0) createWindow();
});

app.on("window-all-closed", () => {
  if (process.platform !== "darwin") app.quit();
});
