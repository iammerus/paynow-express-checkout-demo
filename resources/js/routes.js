import Vue from 'vue'
import VueRouter from "vue-router";
import Home from "./views/pages/Home";
import Checkout from "./views/pages/Checkout";

// Routes
const routes = [
    {
        path: '/',
        name: "home",
        component: Home,
        meta:
            {
                title: "Home"
            }
    },
    {
        path: '/checkout',
        name: "checkout",
        component: Checkout,
        meta:
            {
                title: "Checkout",
                transitionName: 'slide'
            }
    }
];

// Register Vue router
Vue.use(VueRouter);

// Instantiate router
const Router = new VueRouter({
    routes: routes,
    mode: 'history'
});

// Hook run before each route
Router.beforeEach((to, from, next) => {
    let title = "Page Title";

    if (to.meta.title) {
        title = to.meta.title;
    }

    document.title = title + " - Easy Foodz";
    next()
});

/**
 * Create the router instance and pass the `routes` option
 * @type {VueRouter}
 */
export default Router;

