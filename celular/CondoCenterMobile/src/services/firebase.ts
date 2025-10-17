import { initializeApp } from 'firebase/app';
import { getMessaging } from 'firebase/messaging';

// Configuração do Firebase (substitua pelos valores do seu projeto)
const firebaseConfig = {
  apiKey: "sua-api-key",
  authDomain: "seu-projeto.firebaseapp.com",
  projectId: "seu-projeto-id",
  storageBucket: "seu-projeto.appspot.com",
  messagingSenderId: "seu-sender-id",
  appId: "seu-app-id"
};

// Inicializar Firebase
const app = initializeApp(firebaseConfig);

// Inicializar Firebase Cloud Messaging
export const messaging = getMessaging(app);

export default app;
