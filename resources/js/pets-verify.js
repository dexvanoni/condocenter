import { createApp } from 'vue';
import PetVerifyApp from './components/pets/PetVerifyApp.vue';

const mountEl = document.getElementById('pet-verify-app');

if (mountEl) {
  createApp(PetVerifyApp, {
    csrfToken: mountEl.dataset.csrf || '',
    verifyUrl: mountEl.dataset.verifyUrl || '',
  }).mount(mountEl);
}
