const { contextBridge, ipcRenderer } = require('electron');

// Expose a safe, limited API to the renderer process
contextBridge.exposeInMainWorld('electronAPI', {
  // App info
  getVersion: () => ipcRenderer.invoke('get-version'),
  getPlatform: () => process.platform,

  // Window controls
  minimize: () => ipcRenderer.send('window-minimize'),
  maximize: () => ipcRenderer.send('window-maximize'),
  close: () => ipcRenderer.send('window-close'),

  // Notifications
  showNotification: (title, body) => ipcRenderer.send('show-notification', { title, body }),

  // Print
  print: () => ipcRenderer.send('print'),
  printSilent: (options) => ipcRenderer.invoke('print-silent', options),

  // File dialog
  openFile: (options) => ipcRenderer.invoke('open-file-dialog', options),
  saveFile: (options) => ipcRenderer.invoke('save-file-dialog', options),

  // App lifecycle
  restart: () => ipcRenderer.send('restart-app'),
  checkForUpdates: () => ipcRenderer.send('check-updates'),

  // Is Electron environment
  isElectron: true,
});
