import './bootstrap';
import { createApp } from 'vue';

// Bootstrap
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// DataTables
import 'datatables.net-bs5';

// Importar componentes Vue aqui
// import ExampleComponent from './components/ExampleComponent.vue';

const app = createApp({});

// Registrar componentes globais
// app.component('example-component', ExampleComponent);

// Montar apenas se houver um elemento #app
if (document.getElementById('app')) {
    app.mount('#app');
}
