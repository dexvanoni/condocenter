import Constants from 'expo-constants';

// Configurações da API
export const API_CONFIG = {
  // Substitua pela URL do seu servidor Laravel
  BASE_URL: __DEV__ 
    ? 'http://localhost:8000/api'  // Desenvolvimento local
    : 'https://seu-dominio.com/api', // Produção
  
  TIMEOUT: 10000,
  RETRY_ATTEMPTS: 3,
};

// Configurações de notificação
export const NOTIFICATION_CONFIG = {
  PANIC_SOUND_DURATION: 10000, // 10 segundos
  VIBRATION_PATTERN: [0, 250, 250, 250],
  CHANNEL_ID: 'panic-alerts',
  CHANNEL_NAME: 'Alertas de Pânico',
};

// Configurações do app
export const APP_CONFIG = {
  NAME: 'SindCON Mobile',
  VERSION: '1.0.0',
  SUPPORTED_LANGUAGES: ['pt-BR'],
  DEFAULT_LANGUAGE: 'pt-BR',
};
