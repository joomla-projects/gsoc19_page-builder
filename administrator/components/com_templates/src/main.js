import Vue from 'vue'
import App from './App.vue'
import draggable from 'vuedraggable'
import rawDisplayer from './components/raw-displayer'


Vue.config.productionTip = false
Vue.component("rawDisplayer", rawDisplayer);

new Vue({
  render: h => h(App),
}).$mount('#app')
