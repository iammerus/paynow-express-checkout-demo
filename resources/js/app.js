import './bootstrap';
import Vue from 'vue';
import {default as Router} from './routes'
import App from './views/App'
import Vuetify from 'vuetify'

// Register plugins
Vue.use(Vuetify);

// TODO: Move mixins to separate file
Vue.mixin({
    data: () => ({
        cartUpdatedAt: -1
    }),
    computed: {



        cartItems() {
            this.cartUpdatedAt;

            try {
                // Final
                let cart = this.getCart();

                let final = [];

                Object.keys(cart).forEach((itemId, index) => {
                    let cartItem = this.foods.find((food) => food.id === parseInt(itemId));


                    if (!cartItem) return;

                    cartItem.quantity = cart[itemId];
                    cartItem.subtotal = cartItem.price * cartItem.quantity;

                    final.push(cartItem);
                });

                return final;
            } catch (error) {
                console.error(error);

                return null;
            }
        },

        cartTotal() {
            let items = this.cartItems;

            let total = 0;
            items.forEach((item) => {
                total += item.price * item.quantity;
            });

            return total;
        },

        cartTotalCount() {
            this.cartUpdatedAt;

            let items = this.getCart();

            let total = 0;
            Object.values(items).forEach(function (value, index) {
                total += value;
            });

            return total;
        }
    },
    methods: {
        /**
         * Get the current cart
         *
         * @returns {Object}
         */
        getCart() {
            return JSON.parse(window.localStorage.getItem('cart')) || {};
        },

        /**
         * Persist the cart to local storage
         *
         * @param {Object} cart The cart object to persist
         */
        saveCart(cart) {
            window.localStorage.setItem('cart', JSON.stringify(cart));

            this.cartUpdatedAt = Date.now();
        },

        /**
         * Insert a food item into the cart
         *
         * @param  {{id: Number, title: String}} foodItem
         */
        insertToCart(foodItem) {
            let cart = this.getCart();

            if (!cart.hasOwnProperty(foodItem.id)) {
                cart[foodItem.id] = 0;
            }

            cart[foodItem.id] += 1;

            this.saveCart(cart);
        },

        /**
         * Show the application snackbar
         *
         * @param {String} text The text to show on the snackbar
         * @param {Number} timeout Timeout duration of the snackbar
         */
        showSnackbar(text, timeout = 3000) {
            this.$eventHub.$emit('snackbar.show', text, timeout);
        }
    }
});

Vue.$eventHub = Vue.prototype.$eventHub = new Vue();

// Create Vue instance
const app = new Vue({
    el: '#app',
    router: Router,
    render: h => h(App),
    vuetify: new Vuetify()
});
