import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import { createApp } from 'vue'
import TwoFactorAuth from './components/TwoFactorAuth'


const app = createApp({})


app.component('two-factor-auth', TwoFactorAuth)
app.mount('#app')
//window.app = new Vue({
 //   el: '#app',
//})