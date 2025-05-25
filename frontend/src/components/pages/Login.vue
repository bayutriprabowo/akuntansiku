<template>
  <div class="container">
    <div class="row">
      <div class="col-md-4 col-md-offset-4">
        <div class="login-panel panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Please Sign In</h3>
          </div>
          <div class="panel-body">
            <form role="form" @submit.prevent="handleLogin">
              <fieldset>
                <div v-if="errorMessage" class="alert alert-danger" role="alert">
                  {{ errorMessage }}
                </div>

                <div class="form-group">
                   <label for="login-identifier" class="sr-only">Username or Email</label>
                  <input
                    id="login-identifier"
                    class="form-control"
                    placeholder="Username atau E-mail"
                    v-model="credentials.identifier"
                    type="text"
                    required
                    autofocus
                    autocomplete="username"
                    aria-required="true"
                  />
                </div>
                <div class="form-group">
                   <label for="login-password" class="sr-only">Password</label>
                  <input
                    id="login-password"
                    class="form-control"
                    placeholder="Password"
                    v-model="credentials.password"
                    type="password"
                    required
                    autocomplete="current-password"
                    aria-required="true"
                  />
                </div>

                <button type="submit" class="btn btn-lg btn-success btn-block" :disabled="loading">
                  {{ loading ? 'Logging in...' : 'Login' }}
                </button>
              </fieldset>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

// Komponen ini mengasumsikan Axios sudah dikonfigurasi dengan baseURL
// yang benar (misalnya 'http://localhost:8000') di file main.js atau plugin Axios.

export default {
  name: 'Login',
  data () {
    return {
      credentials: {
        identifier: '',
        password: ''
      },
      loading: false,
      errorMessage: null
    }
  },
  methods: {
    async handleLogin () {
      this.loading = true
      this.errorMessage = null

      try {
        // --- PERBAIKAN: Gunakan path relatif ---
        // Axios akan otomatis menggabungkan ini dengan baseURL yang sudah diatur.
        const response = await axios.post('/api/login', this.credentials)
        // ---------------------------------------

        const token = response.data.token
        const userData = response.data.user

        if (token && userData) {
          localStorage.setItem('user-token', token)
          localStorage.setItem('user-data', JSON.stringify(userData))
          axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
          this.$router.push({ name: 'DashBoard' })
        } else {
          this.errorMessage = 'Login gagal: Respons tidak lengkap dari server.'
          console.error('Incomplete login response:', response.data)
        }
      } catch (error) {
        console.error('Login error:', error.response || error)
        if (error.response) {
          const status = error.response.status
          const data = error.response.data
          if (status === 401) {
            this.errorMessage = data.message || 'Login gagal: Username/Email atau password salah.'
          } else if (status === 403) {
            this.errorMessage = data.message || 'Login gagal: Akun tidak diizinkan atau tidak aktif.'
          } else if (status === 422) {
            const errors = data.errors
            this.errorMessage = errors ? Object.values(errors).flat().join(' ') : (data.message || 'Login gagal: Data yang dikirim tidak valid.')
          } else if (status === 404) {
            // Tambahkan penanganan spesifik jika 404 masih terjadi (meskipun seharusnya tidak jika backend benar)
            this.errorMessage = 'Login gagal: Endpoint API tidak ditemukan (404). Pastikan backend berjalan dan rute benar.'
          } else {
            this.errorMessage = `Login gagal: Terjadi kesalahan pada server (${status}). Coba lagi nanti.`
          }
        } else if (error.request) {
          this.errorMessage = 'Login gagal: Tidak dapat terhubung ke server. Periksa koneksi, URL backend, dan konfigurasi CORS.'
        } else {
          this.errorMessage = `Login gagal: Terjadi kesalahan pada aplikasi (${error.message}).`
        }
      } finally {
        this.loading = false
      }
    }
  }
  // PENTING: Gunakan Navigation Guards di router/index.js untuk redirect otomatis jika sudah login.
}
</script>

<style>
.login-panel {
    margin-top: 20%;
}
/* Style untuk menyembunyikan label secara visual tapi tetap ada untuk screen reader */
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}
</style>
