import Vue from 'vue';
import VueJsModal from 'vue-js-modal';
import Vuetify from 'vuetify';
import App from './components/app.vue';
import Translate from './plugins/translate';
import editposition from './components/editposition.vue';
import editgrid from './components/editgrid.vue';
import addcolumn from './components/addcolumn.vue';
import editColumn from './components/editcolumn.vue';
import AddGridModal from './components/modals/modal-add-grid.vue';
import AddModuleModal from './components/modals/modal-add-module.vue';

// Add the plugins
Vue.use(Translate);
Vue.use(VueJsModal);
Vue.use(Vuetify);

// Use the Components
Vue.component('edit-position', editposition);
Vue.component('edit-grid', editgrid);
Vue.component('add-column', addcolumn);
Vue.component('edit-column', editColumn);
Vue.component('add-grid-modal', AddGridModal);
Vue.component('add-module-modal', AddModuleModal);

Vue.config.productionTip = false;

document.addEventListener('DOMContentLoaded',
  () => new Vue({
    el: '#com-templates',
    render: h => h(App),
  }));
