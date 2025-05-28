<!-- filepath: d:\project\akuntansiku\frontend\src\components\TrialBalance.vue -->
<template>
  <div id="page-wrapper">
    <div class="row">
      <div class="col-lg-12">
        <h1 class="page-header">Neraca Saldo</h1>
        <p v-if="apiError" style="color: red;">Error: {{ apiError }}</p>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading">Tabel Neraca Saldo</div>
          <div class="panel-body">
            <table width="100%" class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <th>Kode Akun</th>
                  <th>Nama Akun</th>
                  <th>Debit</th>
                  <th>Kredit</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(entry, index) in trialBalanceEntries" :key="index">
                  <td>{{ entry.kode }}</td>
                  <td>{{ entry.nama_akun }}</td>
                  <td>{{ entry.tipe_debit_kredit === 'debit' ? formatNumber(entry.saldo) : '' }}</td>
                  <td>{{ entry.tipe_debit_kredit === 'kredit' ? formatNumber(entry.saldo) : '' }}</td>
                </tr>
                <tr>
                  <td colspan="2"><strong>Total</strong></td>
                  <td><strong>{{ formatNumber(totalDebit) }}</strong></td>
                  <td><strong>{{ formatNumber(totalKredit) }}</strong></td>
                </tr>
              </tbody>
            </table>
            <div class="balance-status">
              <span v-if="isBalanced" style="color: green; font-weight: bold;">✔ Neraca Saldo Seimbang</span>
              <span v-else style="color: red; font-weight: bold;">✖ Neraca Saldo Tidak Seimbang</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'TrialBalance',
  data () {
    return {
      trialBalanceEntries: [], // Data neraca saldo
      apiError: null
    }
  },
  computed: {
    totalDebit () {
      return this.trialBalanceEntries
        .filter(entry => entry.tipe_debit_kredit === 'debit')
        .reduce((total, entry) => total + (entry.saldo || 0), 0)
    },
    totalKredit () {
      return this.trialBalanceEntries
        .filter(entry => entry.tipe_debit_kredit === 'kredit')
        .reduce((total, entry) => total + (entry.saldo || 0), 0)
    },
    isBalanced () {
      return this.totalDebit === this.totalKredit
    }
  },
  methods: {
    getAuthHeaders () {
      const token = localStorage.getItem('user-token') // Ambil token dari localStorage
      if (!token) {
        console.warn('Token tidak ditemukan. Redirect ke login.')
        this.$router.push('/login') // Redirect ke halaman login jika token tidak ada
        return null
      }
      return {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json'
      }
    },
    async fetchTrialBalance () {
      const headers = this.getAuthHeaders()
      if (!headers) return
      try {
        const response = await axios.get('http://localhost:8000/api/neraca-saldo', { headers })
        this.trialBalanceEntries = response.data.map(entry => {
          const saldo =
            entry.tipe_debit_kredit === 'debit'
              ? entry.debit - entry.kredit
              : entry.kredit - entry.debit
          return {
            ...entry,
            saldo
          }
        })
      } catch (error) {
        console.error('Gagal mengambil data neraca saldo:', error)
        this.apiError = 'Gagal mengambil data neraca saldo.'
      }
    },
    formatNumber (value) {
      // Format angka dengan tanda kurung jika negatif
      const formatted = Math.abs(value).toLocaleString()
      return value < 0 ? `(${formatted})` : formatted
    }
  },
  mounted () {
    this.fetchTrialBalance()
  }
}
</script>

<style scoped>
input, select {
  width: 100%;
  padding: 5px;
  margin-bottom: 5px;
  box-sizing: border-box;
}
button {
  padding: 5px 10px;
  cursor: pointer;
  margin-right: 5px;
}
.balance-status {
  margin-top: 20px;
  font-size: 16px;
}
</style>
