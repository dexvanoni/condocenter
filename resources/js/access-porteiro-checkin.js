import { createApp } from 'vue';
import AccessCheckinApp from './components/access/AccessCheckinApp.vue';

const mountEl = document.getElementById('access-checkin-app');

if (mountEl) {
  createApp(AccessCheckinApp, {
    csrfToken: mountEl.dataset.csrf || '',
  }).mount(mountEl);
}
