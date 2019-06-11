import Vue from 'vue';
import VueJsModal from 'vue-js-modal';
import App from './components/app.vue';
import Translate from './plugins/translate';
import editposition from './components/editposition.vue';
import editgrid from './components/editgrid.vue';
import addcolumn from './components/addcolumn.vue';

// Add the plugins
Vue.use(Translate);
Vue.use(VueJsModal);

//Use the Components
Vue.component('edit-position', editposition);
Vue.component('edit-grid', editgrid);
Vue.component('add-column', addcolumn);

Vue.config.productionTip = false;

document.addEventListener('DOMContentLoaded',
  () => new Vue({
    el: '#com-templates',
    render: h => h(App),
  }));
