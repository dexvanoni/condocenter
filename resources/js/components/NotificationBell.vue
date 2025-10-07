<template>
  <div class="dropdown">
    <a href="#" 
       class="position-relative text-dark text-decoration-none" 
       id="notificationDropdown" 
       data-bs-toggle="dropdown"
       @click="loadNotifications">
      <i class="bi bi-bell fs-5"></i>
      <span v-if="unreadCount > 0" 
            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
        {{ unreadCount }}
      </span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end" 
        style="width: 350px; max-height: 400px; overflow-y: auto;">
      <li>
        <h6 class="dropdown-header d-flex justify-content-between">
          <span>Notificações</span>
          <a href="#" @click.prevent="markAllRead" class="text-primary small">
            Marcar todas como lidas
          </a>
        </h6>
      </li>
      <li><hr class="dropdown-divider"></li>
      
      <li v-if="loading" class="text-center py-3">
        <div class="spinner-border spinner-border-sm" role="status">
          <span class="visually-hidden">Carregando...</span>
        </div>
      </li>
      
      <template v-else>
        <li v-for="notification in notifications" :key="notification.id">
          <a class="dropdown-item" 
             :class="{'bg-light': !notification.is_read}"
             href="#" 
             @click.prevent="markAsRead(notification)">
            <div class="d-flex">
              <div class="me-2">
                <i :class="getIcon(notification.type)" class="fs-5"></i>
              </div>
              <div class="flex-grow-1">
                <strong class="d-block">{{ notification.title }}</strong>
                <small class="text-muted d-block text-truncate" style="max-width: 280px;">
                  {{ notification.message }}
                </small>
                <small class="text-muted">{{ formatDate(notification.created_at) }}</small>
              </div>
            </div>
          </a>
        </li>
        
        <li v-if="notifications.length === 0" class="text-center py-3 text-muted">
          <i class="bi bi-inbox fs-1 d-block"></i>
          Nenhuma notificação
        </li>
      </template>
      
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item text-center" href="/notifications">Ver todas</a></li>
    </ul>
  </div>
</template>

<script>
export default {
  name: 'NotificationBell',
  data() {
    return {
      notifications: [],
      unreadCount: 0,
      loading: false,
    };
  },
  mounted() {
    this.loadUnreadCount();
    this.startPolling();
  },
  methods: {
    async loadNotifications() {
      this.loading = true;
      try {
        const response = await axios.get('/api/notifications?is_read=false');
        this.notifications = response.data.data || response.data;
      } catch (error) {
        console.error('Erro ao carregar notificações:', error);
      } finally {
        this.loading = false;
      }
    },
    async loadUnreadCount() {
      try {
        const response = await axios.get('/api/notifications/unread-count');
        this.unreadCount = response.data.count;
      } catch (error) {
        console.error('Erro ao carregar contador:', error);
      }
    },
    async markAsRead(notification) {
      try {
        await axios.post(`/api/notifications/${notification.id}/read`);
        notification.is_read = true;
        this.unreadCount = Math.max(0, this.unreadCount - 1);
      } catch (error) {
        console.error('Erro ao marcar como lida:', error);
      }
    },
    async markAllRead() {
      try {
        await axios.post('/api/notifications/mark-all-read');
        this.notifications.forEach(n => n.is_read = true);
        this.unreadCount = 0;
      } catch (error) {
        console.error('Erro:', error);
      }
    },
    getIcon(type) {
      const icons = {
        'package_arrived': 'bi bi-box-seam text-info',
        'payment_overdue': 'bi bi-exclamation-triangle text-warning',
        'reservation_approved': 'bi bi-calendar-check text-success',
        'reservation_rejected': 'bi bi-x-circle text-danger',
      };
      return icons[type] || 'bi bi-info-circle text-primary';
    },
    formatDate(date) {
      const d = new Date(date);
      const now = new Date();
      const diff = now - d;
      const minutes = Math.floor(diff / 60000);
      const hours = Math.floor(diff / 3600000);
      const days = Math.floor(diff / 86400000);
      
      if (minutes < 1) return 'Agora';
      if (minutes < 60) return `${minutes}m atrás`;
      if (hours < 24) return `${hours}h atrás`;
      return `${days}d atrás`;
    },
    startPolling() {
      // Atualizar contador a cada 30 segundos
      setInterval(() => {
        this.loadUnreadCount();
      }, 30000);
    },
  },
};
</script>

