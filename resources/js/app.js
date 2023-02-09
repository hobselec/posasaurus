//import './bootstrap';
import { Modal } from 'bootstrap'

//import $ from "jquery";
//window.$ = $;

import Alpine from 'alpinejs';

//import * as core from '../../public/js/core.js'

window.Alpine = Alpine;

Alpine.start();

import { createApp } from 'vue'
import TwoFactorAuth from './components/TwoFactorAuth.vue'


const app = createApp({})


app.component('two-factor-auth', TwoFactorAuth)
app.mount('#app')

import VueBootstrapAutocomplete from 'vue3-bootstrap-autocomplete'
import SearchBox from './components/SearchBox.vue'

const customerSearchApp = createApp({})

customerSearchApp.component('vue-bootstrap-autocomplete', VueBootstrapAutocomplete)
customerSearchApp.component('search-box', SearchBox)
customerSearchApp.mount('#customerApp')

//import _ from 'lodash';
//window._ = _;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_ABLY_PUBLIC_KEY,
    wsHost: 'realtime-pusher.ably.io',
    wsPort: 443,
    disableStats: true,
    encrypted: true,
    cluster:import.meta.env.VITE_PUSHER_APP_CLUSTER,
    authEndpoint: '/pos/broadcasting/auth'
});

import { setupGlobals } from './PosGlobals.js'
import { startUp } from './PosStartup.js'
import { ticketDialogs } from './TicketDialogs';
import { billingDialogs } from './BillingDialogs';
import { paymentDialogs } from './PaymentDialogs';
import { catalogDialogs } from './CatalogDialogs'


setupGlobals()
startUp()

ticketDialogs()
billingDialogs()
catalogDialogs()
paymentDialogs()

import * as Reports from './Reports';
Object.assign(window, Reports)
import * as Helpers from './Helpers';
Object.assign(window, Helpers)

import * as Billing from './pos_functions/billing.js';
Object.assign(window, Billing)
import * as Cart from './pos_functions/cart.js';
Object.assign(window, Cart)
import * as Payments from './pos_functions/payments.js';
Object.assign(window, Payments)
import * as Customer from './pos_functions/customer.js';
Object.assign(window, Customer)