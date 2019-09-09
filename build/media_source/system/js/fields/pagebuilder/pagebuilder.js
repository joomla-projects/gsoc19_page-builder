import './bootstrap/bootstrap.min.js';
import Vue from 'vue';
import VueJsModal from 'vue-js-modal';
import VueGridLayout from 'vue-grid-layout';
import App from './components/app.vue';
import Translate from './plugins/translate';
import editelement from './components/settings/editelement.vue';
import Devices from './components/settings/devices.vue';
import AddElementModal from './components/modals/modal-add-element.vue';
import store from './store/store';
import Item from './components/elements/item.vue';
import Grid from './components/elements/grid.vue';
import Verte from 'verte';
import 'verte/dist/verte.css';




// Add the plugins
Vue.use(Translate);
Vue.use(VueJsModal);


// Use the Components
Vue.component('devices', Devices);
Vue.component('item', Item);
Vue.component('grid', Grid);
Vue.component('grid-layout', VueGridLayout.GridLayout);
Vue.component('grid-item', VueGridLayout.GridItem);
Vue.component('edit-element', editelement);
Vue.component('add-element-modal', AddElementModal);
Vue.component(Verte.name, Verte);

Vue.config.productionTip = false;

document.addEventListener('DOMContentLoaded',
  () => new Vue({
    el: '#pagebuilder-editor',
    store,
    render: h => h(App),
  }));
