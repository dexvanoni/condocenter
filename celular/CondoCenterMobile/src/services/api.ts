import axios, { AxiosInstance, AxiosResponse } from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { User, LoginCredentials, AuthResponse, PanicAlert, PanicAlertRequest } from '../types';

const API_BASE_URL = 'http://localhost:8000/api'; // Substitua pela URL do seu servidor

class ApiService {
  private api: AxiosInstance;

  constructor() {
    this.api = axios.create({
      baseURL: API_BASE_URL,
      timeout: 10000,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    });

    // Interceptor para adicionar token automaticamente
    this.api.interceptors.request.use(
      async (config) => {
        const token = await AsyncStorage.getItem('auth_token');
        if (token) {
          config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
      },
      (error) => {
        return Promise.reject(error);
      }
    );

    // Interceptor para tratar respostas
    this.api.interceptors.response.use(
      (response) => response,
      async (error) => {
        if (error.response?.status === 401) {
          // Token expirado ou inválido
          await AsyncStorage.removeItem('auth_token');
          await AsyncStorage.removeItem('user_data');
        }
        return Promise.reject(error);
      }
    );
  }

  // Autenticação
  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    try {
      const response: AxiosResponse<AuthResponse> = await this.api.post('/auth/login', credentials);
      
      // Salvar token e dados do usuário
      await AsyncStorage.setItem('auth_token', response.data.token);
      await AsyncStorage.setItem('user_data', JSON.stringify(response.data.user));
      
      return response.data;
    } catch (error) {
      throw this.handleError(error);
    }
  }

  async logout(): Promise<void> {
    try {
      await this.api.post('/auth/logout');
    } catch (error) {
      // Mesmo com erro, limpar dados locais
    } finally {
      await AsyncStorage.removeItem('auth_token');
      await AsyncStorage.removeItem('user_data');
    }
  }

  async getCurrentUser(): Promise<User> {
    try {
      const response: AxiosResponse<User> = await this.api.get('/user');
      return response.data;
    } catch (error) {
      throw this.handleError(error);
    }
  }

  // Alertas de Pânico
  async sendPanicAlert(alertData: PanicAlertRequest): Promise<{ message: string; alert_id: number }> {
    try {
      const response = await this.api.post('/panic-alert', alertData);
      return response.data;
    } catch (error) {
      throw this.handleError(error);
    }
  }

  async getActivePanicAlerts(): Promise<{ has_active_alerts: boolean; alerts: PanicAlert[] }> {
    try {
      const response = await this.api.get('/panic-alerts/active');
      return response.data;
    } catch (error) {
      throw this.handleError(error);
    }
  }

  async resolvePanicAlert(alertId: number): Promise<{ message: string }> {
    try {
      const response = await this.api.post(`/panic-alerts/${alertId}/resolve`);
      return response.data;
    } catch (error) {
      throw this.handleError(error);
    }
  }

  // FCM Token
  async registerFCMToken(token: string): Promise<void> {
    try {
      await this.api.post('/fcm/token', { token });
    } catch (error) {
      throw this.handleError(error);
    }
  }

  async getFCMConfig(): Promise<any> {
    try {
      const response = await this.api.get('/fcm/config');
      return response.data;
    } catch (error) {
      throw this.handleError(error);
    }
  }

  // Utilitários
  private handleError(error: any): Error {
    if (error.response) {
      // Erro da API
      const message = error.response.data?.message || error.response.data?.error || 'Erro do servidor';
      return new Error(message);
    } else if (error.request) {
      // Erro de rede
      return new Error('Erro de conexão. Verifique sua internet.');
    } else {
      // Outros erros
      return new Error(error.message || 'Erro desconhecido');
    }
  }
}

export default new ApiService();
