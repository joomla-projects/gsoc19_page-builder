import Vue from 'vue';
import VueJsModal from 'vue-js-modal';
import App from './components/app.vue';
import Translate from './plugins/translate';
import Grid from './components/elements/grid.vue';
import editposition from './components/settings/editposition.vue';
import editgrid from './components/settings/editgrid.vue';
import addcolumn from './components/settings/addcolumn.vue';
import editColumn from './components/settings/editcolumn.vue';
import AddElementModal from './components/modals/modal-add-element.vue';
import store from './components/store/store';

// Add the plugins
Vue.use(Translate);
Vue.use(VueJsModal);

// Use the Components
Vue.component('grid', Grid);
Vue.component('edit-position', editposition);
Vue.component('edit-grid', editgrid);
Vue.component('add-column', addcolumn);
Vue.component('edit-column', editColumn);
Vue.component('add-element-modal', AddElementModal);

Vue.config.productionTip = false;

document.addEventListener('DOMContentLoaded',
  () => new Vue({
    el: '#com-templates',
    store,
    render: h => h(App),
  }));
