import './bootstrap';
import { createApp } from 'vue';
import PackageIntakeApp from './components/packages/PackageIntakeApp.vue';

const el = document.getElementById('package-intake-app');
if (el) {
    createApp(PackageIntakeApp).mount(el);
}
