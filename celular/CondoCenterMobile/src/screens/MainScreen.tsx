import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  Alert,
  ScrollView,
  RefreshControl,
  Modal,
  TextInput,
} from 'react-native';
import { useAuth } from '../hooks/useAuth';
import { useNotification } from '../hooks/useNotification';
import { PanicAlertRequest } from '../types';

const MainScreen: React.FC = () => {
  const [refreshing, setRefreshing] = useState(false);
  const [panicModalVisible, setPanicModalVisible] = useState(false);
  const [selectedAlertType, setSelectedAlertType] = useState<string>('');
  const [additionalInfo, setAdditionalInfo] = useState('');
  const [sendingAlert, setSendingAlert] = useState(false);
  
  const { user, logout } = useAuth();
  const { 
    activePanicAlerts, 
    hasActiveAlerts, 
    sendPanicAlert, 
    resolvePanicAlert,
    stopPanicSound 
  } = useNotification();

  const alertTypes = [
    { key: 'fire', label: '🔥 INCÊNDIO', color: '#e74c3c' },
    { key: 'lost_child', label: '👶 CRIANÇA PERDIDA', color: '#f39c12' },
    { key: 'flood', label: '🌊 ENCHENTE', color: '#3498db' },
    { key: 'robbery', label: '🚨 ROUBO/FURTO', color: '#e67e22' },
    { key: 'police', label: '🚓 CHAMEM A POLÍCIA', color: '#9b59b6' },
    { key: 'domestic_violence', label: '⚠️ VIOLÊNCIA DOMÉSTICA', color: '#e91e63' },
    { key: 'ambulance', label: '🚑 CHAMEM UMA AMBULÂNCIA', color: '#27ae60' },
  ];

  const handlePanicAlert = () => {
    setPanicModalVisible(true);
  };

  const handleSendAlert = async () => {
    if (!selectedAlertType) {
      Alert.alert('Erro', 'Selecione um tipo de emergência');
      return;
    }

    Alert.alert(
      'Confirmar Alerta de Pânico',
      'Tem certeza que deseja enviar este alerta de emergência? Todos os moradores serão notificados.',
      [
        { text: 'Cancelar', style: 'cancel' },
        { text: 'Confirmar', onPress: confirmSendAlert },
      ]
    );
  };

  const confirmSendAlert = async () => {
    try {
      setSendingAlert(true);
      
      const alertData: PanicAlertRequest = {
        alert_type: selectedAlertType as any,
        additional_info: additionalInfo.trim() || undefined,
      };

      await sendPanicAlert(alertData);
      
      Alert.alert(
        'Alerta Enviado!',
        'O alerta de emergência foi enviado para todos os moradores e a administração.',
        [{ text: 'OK', onPress: () => setPanicModalVisible(false) }]
      );
      
      setSelectedAlertType('');
      setAdditionalInfo('');
    } catch (error) {
      Alert.alert('Erro', error.message || 'Não foi possível enviar o alerta');
    } finally {
      setSendingAlert(false);
    }
  };

  const handleResolveAlert = (alertId: number) => {
    Alert.alert(
      'Resolver Alerta',
      'Tem certeza que deseja marcar este alerta como resolvido?',
      [
        { text: 'Cancelar', style: 'cancel' },
        { text: 'Resolver', onPress: () => resolveAlert(alertId) },
      ]
    );
  };

  const resolveAlert = async (alertId: number) => {
    try {
      await resolvePanicAlert(alertId);
      Alert.alert('Sucesso', 'Alerta resolvido com sucesso');
    } catch (error) {
      Alert.alert('Erro', error.message || 'Não foi possível resolver o alerta');
    }
  };

  const handleLogout = () => {
    Alert.alert(
      'Sair',
      'Tem certeza que deseja sair do aplicativo?',
      [
        { text: 'Cancelar', style: 'cancel' },
        { text: 'Sair', onPress: logout },
      ]
    );
  };

  const onRefresh = async () => {
    setRefreshing(true);
    // Aqui você pode adicionar lógica para atualizar dados
    setTimeout(() => setRefreshing(false), 1000);
  };

  return (
    <ScrollView
      style={styles.container}
      refreshControl={
        <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
      }
    >
      <View style={styles.header}>
        <Text style={styles.welcomeText}>Bem-vindo, {user?.name}</Text>
        <Text style={styles.unitText}>Unidade: {user?.unit?.full_identifier || 'N/A'}</Text>
        <TouchableOpacity style={styles.logoutButton} onPress={handleLogout}>
          <Text style={styles.logoutButtonText}>Sair</Text>
        </TouchableOpacity>
      </View>

      {hasActiveAlerts && (
        <View style={styles.activeAlertsContainer}>
          <Text style={styles.activeAlertsTitle}>🚨 ALERTAS ATIVOS 🚨</Text>
          {activePanicAlerts.map((alert) => (
            <View key={alert.id} style={styles.alertItem}>
              <Text style={styles.alertTitle}>{alert.title}</Text>
              <Text style={styles.alertUser}>Por: {alert.user.name}</Text>
              <Text style={styles.alertLocation}>Local: {alert.location}</Text>
              <Text style={styles.alertTime}>
                {new Date(alert.created_at).toLocaleString('pt-BR')}
              </Text>
              <TouchableOpacity
                style={styles.resolveButton}
                onPress={() => handleResolveAlert(alert.id)}
              >
                <Text style={styles.resolveButtonText}>Resolver</Text>
              </TouchableOpacity>
            </View>
          ))}
        </View>
      )}

      <View style={styles.panicContainer}>
        <TouchableOpacity
          style={styles.panicButton}
          onPress={handlePanicAlert}
          activeOpacity={0.8}
        >
          <Text style={styles.panicButtonText}>🚨</Text>
          <Text style={styles.panicButtonLabel}>ALERTA DE PÂNICO</Text>
          <Text style={styles.panicButtonSubtext}>Toque em caso de emergência</Text>
        </TouchableOpacity>
      </View>

      <View style={styles.infoContainer}>
        <Text style={styles.infoTitle}>Instruções:</Text>
        <Text style={styles.infoText}>
          • Use o botão de pânico apenas em situações de emergência real
        </Text>
        <Text style={styles.infoText}>
          • Todos os moradores e a administração serão notificados
        </Text>
        <Text style={styles.infoText}>
          • O alerta incluirá sua localização e informações de contato
        </Text>
      </View>

      {/* Modal de Seleção de Tipo de Alerta */}
      <Modal
        visible={panicModalVisible}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setPanicModalVisible(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>Selecione o Tipo de Emergência</Text>
            
            <ScrollView style={styles.alertTypesContainer}>
              {alertTypes.map((type) => (
                <TouchableOpacity
                  key={type.key}
                  style={[
                    styles.alertTypeButton,
                    selectedAlertType === type.key && styles.alertTypeButtonSelected,
                    { borderLeftColor: type.color }
                  ]}
                  onPress={() => setSelectedAlertType(type.key)}
                >
                  <Text style={styles.alertTypeText}>{type.label}</Text>
                </TouchableOpacity>
              ))}
            </ScrollView>

            <View style={styles.additionalInfoContainer}>
              <Text style={styles.additionalInfoLabel}>Informações Adicionais (Opcional)</Text>
              <TextInput
                style={styles.additionalInfoInput}
                value={additionalInfo}
                onChangeText={setAdditionalInfo}
                placeholder="Descreva brevemente a situação..."
                multiline
                numberOfLines={3}
              />
            </View>

            <View style={styles.modalButtons}>
              <TouchableOpacity
                style={styles.cancelButton}
                onPress={() => setPanicModalVisible(false)}
              >
                <Text style={styles.cancelButtonText}>Cancelar</Text>
              </TouchableOpacity>
              
              <TouchableOpacity
                style={[styles.sendButton, sendingAlert && styles.sendButtonDisabled]}
                onPress={handleSendAlert}
                disabled={sendingAlert}
              >
                <Text style={styles.sendButtonText}>
                  {sendingAlert ? 'Enviando...' : 'Enviar Alerta'}
                </Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  header: {
    backgroundColor: 'white',
    padding: 20,
    alignItems: 'center',
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  welcomeText: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#2c3e50',
    marginBottom: 5,
  },
  unitText: {
    fontSize: 16,
    color: '#7f8c8d',
    marginBottom: 15,
  },
  logoutButton: {
    backgroundColor: '#e74c3c',
    paddingHorizontal: 20,
    paddingVertical: 10,
    borderRadius: 20,
  },
  logoutButtonText: {
    color: 'white',
    fontWeight: 'bold',
  },
  activeAlertsContainer: {
    margin: 15,
    backgroundColor: '#fff3cd',
    borderRadius: 10,
    padding: 15,
    borderWidth: 2,
    borderColor: '#ffc107',
  },
  activeAlertsTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#856404',
    textAlign: 'center',
    marginBottom: 10,
  },
  alertItem: {
    backgroundColor: 'white',
    borderRadius: 8,
    padding: 15,
    marginBottom: 10,
    borderLeftWidth: 4,
    borderLeftColor: '#e74c3c',
  },
  alertTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#e74c3c',
    marginBottom: 5,
  },
  alertUser: {
    fontSize: 14,
    color: '#2c3e50',
    marginBottom: 3,
  },
  alertLocation: {
    fontSize: 14,
    color: '#7f8c8d',
    marginBottom: 3,
  },
  alertTime: {
    fontSize: 12,
    color: '#95a5a6',
    marginBottom: 10,
  },
  resolveButton: {
    backgroundColor: '#27ae60',
    paddingHorizontal: 15,
    paddingVertical: 8,
    borderRadius: 15,
    alignSelf: 'flex-start',
  },
  resolveButtonText: {
    color: 'white',
    fontWeight: 'bold',
    fontSize: 12,
  },
  panicContainer: {
    alignItems: 'center',
    marginVertical: 30,
  },
  panicButton: {
    backgroundColor: '#e74c3c',
    borderRadius: 100,
    width: 200,
    height: 200,
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.3,
    shadowRadius: 4.65,
    elevation: 8,
  },
  panicButtonText: {
    fontSize: 60,
    marginBottom: 5,
  },
  panicButtonLabel: {
    color: 'white',
    fontSize: 18,
    fontWeight: 'bold',
    textAlign: 'center',
  },
  panicButtonSubtext: {
    color: 'white',
    fontSize: 12,
    textAlign: 'center',
    marginTop: 5,
  },
  infoContainer: {
    backgroundColor: 'white',
    margin: 15,
    padding: 20,
    borderRadius: 10,
  },
  infoTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#2c3e50',
    marginBottom: 10,
  },
  infoText: {
    fontSize: 14,
    color: '#7f8c8d',
    marginBottom: 5,
    lineHeight: 20,
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  modalContent: {
    backgroundColor: 'white',
    borderRadius: 15,
    padding: 20,
    width: '90%',
    maxHeight: '80%',
  },
  modalTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#2c3e50',
    textAlign: 'center',
    marginBottom: 20,
  },
  alertTypesContainer: {
    maxHeight: 200,
    marginBottom: 20,
  },
  alertTypeButton: {
    backgroundColor: '#f8f9fa',
    padding: 15,
    borderRadius: 8,
    marginBottom: 10,
    borderLeftWidth: 4,
  },
  alertTypeButtonSelected: {
    backgroundColor: '#e3f2fd',
  },
  alertTypeText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#2c3e50',
  },
  additionalInfoContainer: {
    marginBottom: 20,
  },
  additionalInfoLabel: {
    fontSize: 16,
    fontWeight: '600',
    color: '#2c3e50',
    marginBottom: 10,
  },
  additionalInfoInput: {
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    padding: 15,
    fontSize: 16,
    backgroundColor: '#f9f9f9',
    textAlignVertical: 'top',
  },
  modalButtons: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  cancelButton: {
    backgroundColor: '#95a5a6',
    paddingHorizontal: 20,
    paddingVertical: 12,
    borderRadius: 8,
    flex: 1,
    marginRight: 10,
  },
  cancelButtonText: {
    color: 'white',
    fontWeight: 'bold',
    textAlign: 'center',
  },
  sendButton: {
    backgroundColor: '#e74c3c',
    paddingHorizontal: 20,
    paddingVertical: 12,
    borderRadius: 8,
    flex: 1,
    marginLeft: 10,
  },
  sendButtonDisabled: {
    backgroundColor: '#bdc3c7',
  },
  sendButtonText: {
    color: 'white',
    fontWeight: 'bold',
    textAlign: 'center',
  },
});

export default MainScreen;
