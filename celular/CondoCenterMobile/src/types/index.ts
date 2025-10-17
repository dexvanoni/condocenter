export interface User {
  id: number;
  name: string;
  email: string;
  phone: string;
  condominium_id: number;
  unit_id?: number;
  is_active: boolean;
  created_at: string;
  updated_at: string;
  unit?: {
    id: number;
    full_identifier: string;
  };
  condominium?: {
    id: number;
    name: string;
  };
}

export interface AuthResponse {
  user: User;
  token: string;
}

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface PanicAlert {
  id: number;
  alert_type: 'fire' | 'lost_child' | 'flood' | 'robbery' | 'police' | 'domestic_violence' | 'ambulance';
  title: string;
  description: string;
  location: string;
  severity: 'low' | 'medium' | 'high';
  status: 'active' | 'resolved';
  created_at: string;
  resolved_at?: string;
  user: User;
  resolved_by?: User;
}

export interface PanicAlertRequest {
  alert_type: 'fire' | 'lost_child' | 'flood' | 'robbery' | 'police' | 'domestic_violence' | 'ambulance';
  additional_info?: string;
}

export interface NotificationData {
  title: string;
  body: string;
  data?: any;
  sound?: string;
}

export interface FCMToken {
  token: string;
  device_type: 'android' | 'ios';
  is_active: boolean;
}
