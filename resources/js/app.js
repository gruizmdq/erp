/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
//import Vuetify from 'vuetify'
import VueMaterial from 'vue-material'
import 'vue-material/dist/vue-material.min.css'
import 'vue-material/dist/theme/default.css'

window.Vue = require('vue')
/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.use(VueMaterial)

Vue.component('stock-article-selector', require('./components/stock/stock_article_selector.vue').default);
Vue.component('stock-color-selector', require('./components/stock/stock_color_selector.vue').default);
Vue.component('stock-brand-selector', require('./components/stock/stock_brand_selector.vue').default);
Vue.component('stock-add-form', require('./components/stock/stock_add_form.vue').default);
Vue.component('stock-item-row', require('./components/stock/stock_item_row.vue').default);
Vue.component('stock-list', require('./components/stock/stock_list.vue').default);
Vue.component('stock-movements', require('./components/stock/stock_movements.vue').default);
Vue.component('stock-movement-item', require('./components/stock/stock_movement_item.vue').default);

// Utils
Vue.component('sucursal-selector', require('./components/utils/sucursal_selector.vue').default);
Vue.component('user-selector', require('./components/utils/user_selector.vue').default);

Vue.component('new-order-form', require('./components/order/new_order_form.vue').default);
Vue.component('new-order-row', require('./components/order/new_order_row.vue').default);
Vue.component('payment-method-selector', require('./components/order/payment_method_selector.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app'
});
