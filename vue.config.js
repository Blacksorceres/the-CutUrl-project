module.exports = {
  pwa: {
    themeColor: '#1fad5a',
    msTileColor: '#16753A',
    name: 'Cuturl',
    
    workboxPluginMode: 'InjectManifest',
    workboxOptions: {
      swDest: 'service-worker.js',
      swSrc: './src/sw.js',
      importWorkboxFrom: 'local',
      exclude: [/\.php$/]
    }
  },

  css: {
    sourceMap: true
  }
}