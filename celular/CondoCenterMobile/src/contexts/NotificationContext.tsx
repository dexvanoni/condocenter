import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import * as Notifications from 'expo-notifications';
import { NotificationService } from '../services/notification';
import { PanicAlert } from '../types';

interface NotificationContextData {
  expoPushToken: string | null;
  notification: Notifications.Notification | null;
  activePanicAlerts: PanicAlert[];
  hasActiveAlerts: boolean;
  registerForPushNotifications: () => Promise<void>;
  sendPanicAlert: (alertData: any) => Promise<void>;
  resolvePanicAlert: (alertId: number) => Promise<void>;
  stopPanicSound: () => void;
}

const NotificationContext = createContext<NotificationContextData>({} as NotificationContextData);

interface NotificationProviderProps {
  children: ReactNode;
}

export const NotificationProvider: React.FC<NotificationProviderProps> = ({ children }) => {
  const [expoPushToken, setExpoPushToken] = useState<string | null>(null);
  const [notification, setNotification] = useState<Notifications.Notification | null>(null);
  const [activePanicAlerts, setActivePanicAlerts] = useState<PanicAlert[]>([]);
  const [hasActiveAlerts, setHasActiveAlerts] = useState(false);

  useEffect(() => {
    registerForPushNotifications();

    // Listener para notificações recebidas
    const notificationListener = Notifications.addNotificationReceivedListener(notification => {
      setNotification(notification);
      
      // Se for um alerta de pânico, atualizar estado
      if (notification.request.content.data?.alert_type) {
        handlePanicNotification(notification.request.content.data);
      }
    });

    // Listener para interações com notificações
    const responseListener = Notifications.addNotificationResponseReceivedListener(response => {
      console.log('Usuário interagiu com notificação:', response);
    });

    return () => {
      Notifications.removeNotificationSubscription(notificationListener);
      Notifications.removeNotificationSubscription(responseListener);
    };
  }, []);

  const registerForPushNotifications = async () => {
    try {
      const hasPermission = await NotificationService.requestPermissions();
      
      if (hasPermission) {
        const token = await NotificationService.getExpoPushToken();
        setExpoPushToken(token);
        
        if (token) {
          // Registrar token no servidor
          await ApiService.registerFCMToken(token);
        }
      }
    } catch (error) {
      console.error('Erro ao registrar notificações push:', error);
    }
  };

  const handlePanicNotification = (alertData: any) => {
    // Adicionar alerta à lista de alertas ativos
    setActivePanicAlerts(prev => {
      const exists = prev.find(alert => alert.id === alertData.alert_id);
      if (!exists) {
        return [...prev, alertData];
      }
      return prev;
    });
    setHasActiveAlerts(true);
  };

  const sendPanicAlert = async (alertData: any) => {
    try {
      // Enviar alerta via API
      const response = await ApiService.sendPanicAlert(alertData);
      
      // Agendar notificação local
      await NotificationService.schedulePanicNotification({
        ...alertData,
        alert_id: response.alert_id,
      });
      
      console.log('Alerta de pânico enviado:', response);
    } catch (error) {
      console.error('Erro ao enviar alerta de pânico:', error);
      throw error;
    }
  };

  const resolvePanicAlert = async (alertId: number) => {
    try {
      await ApiService.resolvePanicAlert(alertId);
      
      // Remover alerta da lista ativa
      setActivePanicAlerts(prev => prev.filter(alert => alert.id !== alertId));
      setHasActiveAlerts(activePanicAlerts.length > 1);
      
      // Agendar notificação de resolução
      await NotificationService.scheduleResolutionNotification({
        alert_id: alertId,
        resolved_by: 'Usuário',
      });
      
      console.log('Alerta de pânico resolvido:', alertId);
    } catch (error) {
      console.error('Erro ao resolver alerta de pânico:', error);
      throw error;
    }
  };

  const stopPanicSound = () => {
    NotificationService.stopPanicSound();
  };

  const value: NotificationContextData = {
    expoPushToken,
    notification,
    activePanicAlerts,
    hasActiveAlerts,
    registerForPushNotifications,
    sendPanicAlert,
    resolvePanicAlert,
    stopPanicSound,
  };

  return (
    <NotificationContext.Provider value={value}>
      {children}
    </NotificationContext.Provider>
  );
};

export const useNotification = (): NotificationContextData => {
  const context = useContext(NotificationContext);
  
  if (!context) {
    throw new Error('useNotification deve ser usado dentro de um NotificationProvider');
  }
  
  return context;
};
