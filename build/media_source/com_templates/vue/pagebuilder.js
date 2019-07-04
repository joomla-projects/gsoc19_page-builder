import Vue from 'vue';
import VueJsModal from 'vue-js-modal';
import App from './components/app.vue';
import Translate from './plugins/translate';
import Grid from './components/elements/grid.vue';
import editposition from './components/settings/editposition.vue';
import editelement from './components/settings/editelement.vue';
import AddElementModal from './components/modals/modal-add-element.vue';
import store from './store/store';

// Add the plugins
Vue.use(Translate);
Vue.use(VueJsModal);

// Use the Components
Vue.component('grid', Grid);
Vue.component('edit-position', editposition);
Vue.component('edit-element', editelement);
Vue.component('add-element-modal', AddElementModal);

Vue.config.productionTip = false;

document.addEventListener('DOMContentLoaded',
  () => new Vue({
    el: '#com-templates',
    store,
    render: h => h(App),
  }));
