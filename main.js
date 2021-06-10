const { app, BrowserWindow, screen } = require('electron');

try {
	require('electron-reloader')(module)
} catch (_) {}

function createWindow() {
	const { width, height } = screen.getPrimaryDisplay().workAreaSize;

	const win = new BrowserWindow({
		width: width,
		height: height,
		webPreferences: {
			nodeIntegration: true
        }
	});

	win.loadFile('index.html');
	win.webContents.openDevTools();

	document.getElementById('dev').innerText=app.getPath('userData');
}

app.whenReady().then(() => {
	createWindow();

	app.on('window-all-closed', function () {
		if (process.platform !== 'darwin') app.quit();
	});

	app.on('activate', function () {
		if (BrowserWindow.getAllWindows().length === 0) createWindow();
	});
})

