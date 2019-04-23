// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
// Vue.config.productionTip = false
import Vue from 'vue';
import Vuetify from 'vuetify';
import pt from 'vuetify/lib/locale/pt';
import Index from './Index';
import {
    store,
    router,
} from './config';

Vue.use(Vuetify, {
    theme: {
        primary: '#0A420E',
        secondary: '#00838F',
        accent: '#9c27b0',
        error: '#f44336',
        warning: '#ffeb3b',
        info: '#2196f3',
        success: '#4caf50',
    },
    lang: {
        locales: { pt },
        current: 'pt',
    },
});
Vue.config.productionTip = false;

// window.onload = () => {
//     /* eslint-disable-next-line */
//     const main = new Vue({
//         el: '#app',
//         router,
//         store,
//         components: {
//             Index,
//         },
//         created: () => {
//         },
//         template: '<Index/>',
//     });
// };

new Vue({
  router,
  store,
  render: h => h(Index),
}).$mount('#app-content');
