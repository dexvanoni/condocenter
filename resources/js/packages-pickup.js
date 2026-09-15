import './bootstrap';
import { createApp } from 'vue';
import PackagePickupApp from './components/packages/PackagePickupApp.vue';

const el = document.getElementById('package-pickup-app');
if (el) {
    createApp(PackagePickupApp).mount(el);
}
