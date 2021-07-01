const { app, BrowserWindow, screen, ipcMain } = require('electron');
const path = require('path');

const config = require(path.join(__dirname, 'config.json'));

try {
	require('electron-reloader')(module)
} catch (_) {}

function createWindow() {
	const { width, height } = screen.getPrimaryDisplay().workAreaSize;

	const win = new BrowserWindow({
		width: width,
		height: height,
		webPreferences: {
			contextIsolation: false,
			enableRemoteModule: true,
			nodeIntegration: true
        }
	});

	win.loadFile('index.html');
	win.webContents.openDevTools();
}

app.whenReady().then(() => {
	createWindow();

	app.on('window-all-closed', function () {
		if (process.platform !== 'darwin') app.quit();
	});

	app.on('activate', function () {
		if (BrowserWindow.getAllWindows().length === 0) createWindow();
	});

	ipcMain.on('requestForInitialize', (event) => {

	});
})

