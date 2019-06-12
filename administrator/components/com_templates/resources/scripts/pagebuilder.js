import Vue from 'vue';
import VueJsModal from 'vue-js-modal';
import Vuetify from 'vuetify';
import App from './components/app.vue';
import Translate from './plugins/translate';
import editposition from './components/editposition.vue';
import editgrid from './components/editgrid.vue';
import addcolumn from './components/addcolumn.vue';
import AddGridModal from './components/modals/modal-add-grid.vue';
import EditColumnModal from './components/modals/modal-edit-column.vue';

// Add the plugins
Vue.use(Translate);
Vue.use(VueJsModal);
Vue.use(Vuetify);

// Use the Components
Vue.component('edit-position', editposition);
Vue.component('edit-grid', editgrid);
Vue.component('add-column', addcolumn);
Vue.component('add-grid-modal', AddGridModal);
Vue.component('edit-column-modal', EditColumnModal);

Vue.config.productionTip = false;

document.addEventListener('DOMContentLoaded',
  () => new Vue({
    el: '#com-templates',
    render: h => h(App),
  }));
