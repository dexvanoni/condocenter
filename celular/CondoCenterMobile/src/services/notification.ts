import * as Notifications from 'expo-notifications';
import * as Device from 'expo-device';
import { Platform } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import Sound from 'react-native-sound';
import { NotificationData } from '../types';

// Configurar comportamento das notificações
Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowAlert: true,
    shouldPlaySound: true,
    shouldSetBadge: true,
  }),
});

class NotificationService {
  private sound: Sound | null = null;

  constructor() {
    this.initializeSound();
  }

  private initializeSound() {
    // Configurar som de pânico
    Sound.setCategory('Playback');
    this.sound = new Sound('panic_alert.mp3', Sound.MAIN_BUNDLE, (error) => {
      if (error) {
        console.log('Erro ao carregar som:', error);
      }
    });
  }

  async requestPermissions(): Promise<boolean> {
    try {
      if (Device.isDevice) {
        const { status: existingStatus } = await Notifications.getPermissionsAsync();
        let finalStatus = existingStatus;
        
        if (existingStatus !== 'granted') {
          const { status } = await Notifications.requestPermissionsAsync();
          finalStatus = status;
        }
        
        if (finalStatus !== 'granted') {
          console.log('Permissão de notificação negada');
          return false;
        }

        // Configurar canal de notificação para Android
        if (Platform.OS === 'android') {
          await Notifications.setNotificationChannelAsync('panic-alerts', {
            name: 'Alertas de Pânico',
            importance: Notifications.AndroidImportance.MAX,
            vibrationPattern: [0, 250, 250, 250],
            lightColor: '#FF231F7C',
            sound: 'panic_alert.wav',
          });
        }

        return true;
      } else {
        console.log('Dispositivo físico necessário para notificações push');
        return false;
      }
    } catch (error) {
      console.error('Erro ao solicitar permissões:', error);
      return false;
    }
  }

  async getExpoPushToken(): Promise<string | null> {
    try {
      if (!Device.isDevice) {
        console.log('Dispositivo físico necessário para token push');
        return null;
      }

      const token = await Notifications.getExpoPushTokenAsync();
      return token.data;
    } catch (error) {
      console.error('Erro ao obter token push:', error);
      return null;
    }
  }

  async schedulePanicNotification(alertData: any): Promise<void> {
    try {
      const notificationId = await Notifications.scheduleNotificationAsync({
        content: {
          title: '🚨 ALERTA DE PÂNICO 🚨',
          body: `${alertData.alert_title} - ${alertData.user_name} (${alertData.location})`,
          data: alertData,
          sound: 'panic_alert.wav',
          priority: Notifications.AndroidNotificationPriority.MAX,
        },
        trigger: null, // Enviar imediatamente
      });

      // Tocar som de sirene
      this.playPanicSound();

      // Vibrar o dispositivo
      await Notifications.vibrateAsync();

      console.log('Notificação de pânico agendada:', notificationId);
    } catch (error) {
      console.error('Erro ao agendar notificação de pânico:', error);
    }
  }

  async scheduleResolutionNotification(alertData: any): Promise<void> {
    try {
      await Notifications.scheduleNotificationAsync({
        content: {
          title: '✅ Alerta Resolvido',
          body: `O alerta de ${alertData.alert_type} foi resolvido por ${alertData.resolved_by}`,
          data: alertData,
          sound: 'default',
        },
        trigger: null,
      });
    } catch (error) {
      console.error('Erro ao agendar notificação de resolução:', error);
    }
  }

  private playPanicSound(): void {
    if (this.sound) {
      this.sound.play((success) => {
        if (success) {
          console.log('Som de pânico reproduzido com sucesso');
          // Repetir o som por 10 segundos
          setTimeout(() => {
            this.sound?.play();
          }, 2000);
        } else {
          console.log('Erro ao reproduzir som de pânico');
        }
      });
    }
  }

  stopPanicSound(): void {
    if (this.sound) {
      this.sound.stop();
    }
  }

  // Listener para notificações recebidas
  addNotificationReceivedListener(listener: (notification: Notifications.Notification) => void) {
    return Notifications.addNotificationReceivedListener(listener);
  }

  // Listener para interações com notificações
  addNotificationResponseReceivedListener(listener: (response: Notifications.NotificationResponse) => void) {
    return Notifications.addNotificationResponseReceivedListener(listener);
  }

  // Limpar todas as notificações
  async clearAllNotifications(): Promise<void> {
    await Notifications.dismissAllNotificationsAsync();
  }

  // Obter notificações pendentes
  async getPendingNotifications(): Promise<Notifications.NotificationRequest[]> {
    return await Notifications.getAllScheduledNotificationsAsync();
  }
}

export default new NotificationService();
