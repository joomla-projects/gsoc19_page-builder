import Vue from 'vue';
import App from './components/app.vue';
import rawDisplayer from './components/raw-displayer.vue';

Vue.config.productionTip = false;
Vue.component("raw-displayer", rawDisplayer);

document.addEventListener("DOMContentLoaded",
  () => new Vue({
    el: '#com-templates',
    render: h => h(App)
  })
);
