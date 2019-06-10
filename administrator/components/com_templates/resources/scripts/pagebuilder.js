import Vue from 'vue';
import App from './components/app.vue';
import Translate from './plugins/translate';

// Add the plugins
Vue.use(Translate);

Vue.config.productionTip = false;

document.addEventListener('DOMContentLoaded',
  () => new Vue({
    el: '#com-templates',
    render: h => h(App),
  }));
