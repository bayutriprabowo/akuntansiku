<!-- filepath: d:\project\akuntansiku\frontend\src\components\IncomeStatement.vue -->
<template>
  <div id="page-wrapper">
    <div class="row">
      <div class="col-lg-12">
        <h1 class="page-header">Laporan Laba Rugi</h1>
        <p v-if="apiError" style="color: red;">Error: {{ apiError }}</p>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="filter">
          <label>Dari: <input type="date" v-model="startDate" /></label>
          <label>Sampai: <input type="date" v-model="endDate" /></label>
          <button @click="fetchIncomeStatement">Filter</button>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading">Tabel Laporan Laba Rugi</div>
          <div class="panel-body">
            <table width="100%" class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <th>Kode Akun</th>
                  <th>Nama Akun</th>
                  <th>Klasifikasi</th>
                  <th>Jumlah</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(entry, index) in incomeStatementEntries" :key="index">
                  <td>{{ entry.kode }}</td>
                  <td>{{ entry.nama_akun }}</td>
                  <td>{{ entry.tipe_klasifikasi }}</td>
                  <td>{{ formatNumber(entry.jumlah) }}</td>
                </tr>
                <tr>
                  <td colspan="3"><strong>Total Pendapatan</strong></td>
                  <td><strong>{{ formatNumber(totalPendapatan) }}</strong></td>
                </tr>
                <tr>
                  <td colspan="3"><strong>Total Beban</strong></td>
                  <td><strong>{{ formatNumber(totalBeban) }}</strong></td>
                </tr>
                <tr>
                  <td colspan="3"><strong>Laba Bersih</strong></td>
                  <td><strong>{{ formatNumber(netIncome) }}</strong></td>
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
  name: 'IncomeStatement',
  data () {
    return {
      incomeStatementEntries: [], // Data laporan laba rugi
      startDate: '', // Tanggal awal
      endDate: '', // Tanggal akhir
      apiError: null
    }
  },
  computed: {
    totalPendapatan () {
      return this.incomeStatementEntries
        .filter(entry => entry.tipe_klasifikasi === 'Pendapatan')
        .reduce((total, entry) => total + (entry.kredit - entry.debit), 0) // Hitung kredit - debit untuk pendapatan
    },
    totalBeban () {
      return this.incomeStatementEntries
        .filter(entry => entry.tipe_klasifikasi === 'Beban')
        .reduce((total, entry) => total + (entry.debit - entry.kredit), 0) // Hitung debit - kredit untuk beban
    },
    netIncome () {
      // Pendapatan dikurangi Beban
      return this.totalPendapatan - this.totalBeban
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
    async fetchIncomeStatement () {
      const headers = this.getAuthHeaders()
      if (!headers) return
      try {
        let url = 'http://localhost:8000/api/laba-rugi'
        if (this.startDate && this.endDate) {
          url += `?start_date=${this.startDate}&end_date=${this.endDate}`
        }
        const response = await axios.get(url, { headers })
        this.incomeStatementEntries = response.data.map(entry => ({
          ...entry,
          debit: Number(entry.debit) || 0, // Pastikan debit adalah angka
          kredit: Number(entry.kredit) || 0, // Pastikan kredit adalah angka
          jumlah:
            entry.tipe_klasifikasi === 'Pendapatan'
              ? Number(entry.kredit) - Number(entry.debit) // Hitung kredit - debit untuk pendapatan
              : Number(entry.debit) - Number(entry.kredit) // Hitung debit - kredit untuk beban
        }))
      } catch (error) {
        console.error('Gagal mengambil data laporan laba rugi:', error)
        this.apiError = 'Gagal mengambil data laporan laba rugi.'
      }
    },
    formatNumber (value) {
      // Format angka dengan tanda kurung jika negatif
      const formatted = Math.abs(value).toLocaleString()
      return value < 0 ? `(${formatted})` : formatted
    }
  },
  mounted () {
    this.fetchIncomeStatement()
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
