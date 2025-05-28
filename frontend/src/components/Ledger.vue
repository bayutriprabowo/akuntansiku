<!-- filepath: d:\project\akuntansiku\frontend\src\components\Ledger.vue -->
<template>
  <div id="page-wrapper">
    <div class="row">
      <div class="col-lg-12">
        <h1 class="page-header">Buku Besar</h1>
        <div class="filter">
          <label>Pilih Akun:
            <select v-model="selectedAccount" @change="fetchLedger">
              <option value="">Semua Akun</option>
              <option v-for="account in accounts" :key="account.kode" :value="account.kode">
                {{ account.kode }} - {{ account.nama_akun }}
              </option>
            </select>
          </label>
        </div>
        <p v-if="apiError" style="color: red;">Error: {{ apiError }}</p>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading">Tabel Buku Besar</div>
          <div class="panel-body">
            <table width="100%" class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <th>Tanggal</th>
                  <th>Keterangan</th>
                  <th>Debit</th>
                  <th>Kredit</th>
                  <th>Saldo</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(entry, index) in ledgerEntries" :key="index">
                  <td>{{ entry.tanggal }}</td>
                  <td>{{ entry.nama_akun }}</td>
                  <td>{{ entry.debit.toLocaleString() }}</td>
                  <td>{{ entry.kredit.toLocaleString() }}</td>
                  <td>{{ entry.saldo.toLocaleString() }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Ledger',
  data () {
    return {
      accounts: [], // Daftar akun
      selectedAccount: '', // Akun yang dipilih
      ledgerEntries: [], // Data buku besar
      apiError: null
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
    async fetchAccounts () {
      const headers = this.getAuthHeaders()
      if (!headers) return
      try {
        const response = await axios.get('http://localhost:8000/api/akun', { headers })
        this.accounts = response.data
      } catch (error) {
        console.error('Gagal mengambil data akun:', error)
        this.apiError = 'Gagal mengambil data akun.'
      }
    },
    async fetchLedger () {
      const headers = this.getAuthHeaders()
      if (!headers) return
      try {
        let url = 'http://localhost:8000/api/buku-besar'
        if (this.selectedAccount) {
          url += `?kode_akun=${this.selectedAccount}`
        }
        const response = await axios.get(url, { headers })
        this.ledgerEntries = response.data
      } catch (error) {
        console.error('Gagal mengambil data buku besar:', error)
        this.apiError = 'Gagal mengambil data buku besar.'
      }
    }
  },
  mounted () {
    this.fetchAccounts()
    this.fetchLedger()
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
</style>
