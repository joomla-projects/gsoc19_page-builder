import Vue from 'vue';
import App from './components/app.vue';

Vue.config.productionTip = false;

document.addEventListener("DOMContentLoaded",
  () => new Vue({
    el: '#com-templates',
    render: h => h(App)
  })
);
