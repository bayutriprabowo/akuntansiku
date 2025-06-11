<template>
  <div id="wrapper">
    <!-- Navigation -->
    <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="index.html">Akuntansiku</a>
      </div>
      <!-- /.navbar-header -->

      <ul class="nav navbar-top-links navbar-right">
        <li class="dropdown">
          <a class="dropdown-toggle" data-toggle="dropdown" href="#">
            <i class="fa fa-user fa-fw"></i> <i class="fa fa-caret-down"></i>
          </a>
          <ul class="dropdown-menu dropdown-user">
            <li>
              <a href="#" @click.prevent="logout">
                <i class="fa fa-sign-out fa-fw"></i> Logout
              </a>
            </li>
          </ul>
          <!-- /.dropdown-user -->
        </li>
        <!-- /.dropdown -->
      </ul>
      <!-- /.navbar-top-links -->

      <div class="navbar-default sidebar" role="navigation">
        <div class="sidebar-nav navbar-collapse">
          <ul class="nav" id="side-menu">
            <li>
              <router-link to="/dashboard"> <i class="fa fa-dashboard fa-fw"></i> Dashboard </router-link>
            </li>
            <!-- Menu untuk Admin -->
            <template v-if="userRole === 'admin'">
              <li>
                <router-link to="/user"> <i class="fa fa-user fa-fw"></i> Pengguna </router-link>
              </li>
              <li>
                  <router-link to="/profil-perusahaan">  <i class="fa fa-building-o fa-fw"></i> Profil Perusahaan </router-link>
              </li>
            </template>
            <!-- Menu untuk Admin dan User -->
            <template v-if="userRole === 'admin' || userRole === 'user'">
              <li>
                <router-link to="/akun"> <i class="fa fa-list-ol fa-fw"></i> Akun </router-link>
              </li>
              <li>
                <router-link to="/jurnal-umum"> <i class="fa fa-pencil-square fa-fw"></i> Jurnal Umum </router-link>
              </li>
              <li>
                <router-link to="/buku-besar"> <i class="fa fa-book fa-fw"></i> Buku Besar </router-link>
              </li>
              <li>
                <router-link to="/neraca-saldo"> <i class="fa fa-file fa-fw"></i> Neraca Saldo </router-link>
              </li>
            </template>
            <!-- Menu untuk Admin dan Direktur -->
            <template v-if="userRole === 'admin' || userRole === 'direktur'">
              <li>
                <a href="#"><i class="fa fa-bar-chart-o fa-fw"></i> Laporan Keuangan <span class="fa arrow"></span></a>
                <ul class="nav nav-second-level">
                  <li>
                    <router-link to="/laba-rugi"> Laba Rugi </router-link>
                  </li>
                  <li>
                    <router-link to="/neraca"> Neraca </router-link>
                  </li>
                </ul>
              </li>
            </template>
          </ul>
        </div>
        <!-- /.sidebar-collapse -->
      </div>
      <!-- /.navbar-static-side -->
    </nav>

    <router-view />
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Home',
  data () {
    return {
      userRole: null // Role pengguna yang sedang login
    }
  },
  methods: {
    async logout () {
      try {
        const token = localStorage.getItem('user-token')
        if (!token) {
          alert('Anda sudah logout.')
          this.$router.push('/login')
          return
        }

        // Kirim permintaan logout ke backend
        await axios.post(
          'http://localhost:8000/api/logout',
          {},
          {
            headers: {
              Authorization: `Bearer ${token}`
            }
          }
        )

        // Hapus token dari localStorage
        localStorage.removeItem('user-token')
        localStorage.removeItem('user-data')

        // Redirect ke halaman login
        alert('Logout berhasil.')
        this.$router.push('/login')
      } catch (error) {
        // Perbaikan: Ganti optional chaining dengan pengecekan manual
        const errorMessage = error.response && error.response.data ? error.response.data : error.message
        console.error('Gagal logout:', errorMessage)
        alert('Gagal logout. Silakan coba lagi.')
      }
    },
    getUserRole () {
      const userData = localStorage.getItem('user-data')
      if (userData) {
        const user = JSON.parse(userData)
        this.userRole = user.role // Ambil role dari data user
      } else {
        this.$router.push('/login') // Redirect ke login jika data user tidak ditemukan
      }
    }
  },
  mounted () {
    this.getUserRole()
  }
}
</script>

<style scoped>
/* Tambahkan style jika diperlukan */
</style>
