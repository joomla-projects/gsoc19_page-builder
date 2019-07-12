import Vue from 'vue';
import VueJsModal from 'vue-js-modal';
import App from './components/app.vue';
import Translate from './plugins/translate';
import editposition from './components/settings/editposition.vue';
import editelement from './components/settings/editelement.vue';
import AddElementModal from './components/modals/modal-add-element.vue';
import store from './store/store';
import Item from './components/elements/item.vue';
import Grid from './components/elements/grid.vue';
import VueGridLayout from 'vue-grid-layout';

// Add the plugins
Vue.use(Translate);
Vue.use(VueJsModal);

// Use the Components
Vue.component('item', Item);
Vue.component('grid', Grid);
Vue.component('grid-layout', VueGridLayout.GridLayout);
Vue.component('grid-item', VueGridLayout.GridItem);
Vue.component('edit-position', editposition);
Vue.component('edit-element', editelement);
Vue.component('add-element-modal', AddElementModal);

Vue.config.productionTip = false;

document.addEventListener('DOMContentLoaded',
  () => new Vue({
    el: '#com-templates',
    store,
    render: h => h(App),
  })
);
