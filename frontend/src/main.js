// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import router from './router'
import axios from 'axios'

// Ganti 8080 dengan port tempat backend Laravel Anda berjalan (biasanya 8000)
axios.defaults.baseURL = 'http://127.0.0.1:8000'

// Konfigurasi lain yang mungkin diperlukan
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.post['Content-Type'] = 'application/json'
// Jika menggunakan Sanctum SPA auth (cookie-based), uncomment ini:
axios.defaults.withCredentials = true
axios.defaults.withXSRFToken = true

Vue.config.productionTip = false

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  components: { App },
  template: '<App/>'
})
