import Vue from 'vue'
import Router from 'vue-router'
import Home from '@/components/Home'
import DashBoard from '@/components/DashBoard'
import Login from '@/components/pages/Login'

import Journal from '@/components/Journal' // Nama komponen tetap Journal
import Account from '@/components/Account.vue'
import User from '@/components/User.vue'
import CompanyProfile from '../components/CompanyProfile.vue'
import Ledger from '../components/Ledger.vue'
import TrialBalance from '../components/TrialBalance.vue'
import IncomeStatement from '../components/IncomeStatement.vue'
import BalanceSheet from '../components/BalanceSheet.vue'

Vue.use(Router)

const router = new Router({
  mode: 'history', // Disarankan menggunakan mode history untuk URL yang lebih bersih
  routes: [
    {
      path: '/',
      component: Home, // Komponen layout utama
      // Rute di dalam Home memerlukan autentikasi (biasanya ditangani dengan navigation guards)
      children: [
        { path: 'dashboard', name: 'DashBoard', component: DashBoard }, // Path relatif terhadap '/'
        { path: 'user', name: 'User', component: User }, // Path relatif terhadap '/'
        // Sesuaikan path agar konsisten dengan prefix API backend
        { path: 'jurnal-umum', name: 'Journal', component: Journal }, // Path relatif terhadap '/'
        { path: 'akun', name: 'Account', component: Account }, // Path relatif terhadap '/'
        { path: 'profil-perusahaan', name: 'CompanyProfile', component: CompanyProfile },
        { path: 'buku-besar', name: 'Ledger', component: Ledger },
        { path: 'neraca-saldo', name: 'TrialBalance', component: TrialBalance },
        { path: 'laba-rugi', name: 'IncomeStatement', component: IncomeStatement },
        { path: 'neraca', name: 'BalanceSheet', component: BalanceSheet }
      ],
      // Redirect default dari '/' ke '/dashboard' jika Home diakses langsung
      redirect: '/dashboard'
    },
    { path: '/login', name: 'Login', component: Login } // Ubah path login agar lebih umum

    // Rute '/public/login' sebelumnya mungkin tidak diperlukan jika '/login' sudah cukup
    // { path: '/public/login', name: 'Login', component: Login }

  ]
})

// Hapus baris ini, redirect atau logic autentikasi sebaiknya ditangani oleh navigation guards
// router.replace({ path: '/dashboard', redirect: '/dashborad' })

// Contoh Navigation Guard (opsional, tambahkan jika perlu logic autentikasi)
// router.beforeEach((to, from, next) => {
//   const requiresAuth = !['Login'].includes(to.name); // Rute selain Login memerlukan auth
//   const loggedIn = localStorage.getItem('user-token'); // Contoh cek token
//
//   if (requiresAuth && !loggedIn) {
//     next({ name: 'Login' });
//   } else if (!requiresAuth && loggedIn && to.name === 'Login') {
//     // Jika sudah login dan mencoba akses halaman login, redirect ke dashboard
//     next({ name: 'DashBoard' });
//   } else {
//     next(); // Lanjutkan navigasi
//   }
// });

export default router
