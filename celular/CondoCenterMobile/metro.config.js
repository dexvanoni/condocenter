const { getDefaultConfig } = require('expo/metro-config');

const config = getDefaultConfig(__dirname);

// Configurações para assets de áudio
config.resolver.assetExts.push('mp3', 'wav', 'm4a');

module.exports = config;
