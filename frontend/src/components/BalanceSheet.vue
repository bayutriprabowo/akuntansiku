<!-- filepath: d:\project\akuntansiku\frontend\src\components\BalanceSheet.vue -->
<template>
  <div id="page-wrapper">
    <div class="row">
      <div class="col-lg-12">
        <h1 class="page-header">Laporan Neraca</h1>
        <p v-if="apiError" style="color: red;">Error: {{ apiError }}</p>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="filter">
          <label>Dari: <input type="date" v-model="startDate" /></label>
          <label>Sampai: <input type="date" v-model="endDate" /></label>
          <button @click="fetchBalanceSheet">Filter</button>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-6">
        <div class="panel panel-default">
          <div class="panel-heading">Debit (Aset)</div>
          <div class="panel-body">
            <table width="100%" class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <th>Kode Akun</th>
                  <th>Nama Akun</th>
                  <th>Jumlah</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(entry, index) in debitEntries" :key="index">
                  <td>{{ entry.kode }}</td>
                  <td>{{ entry.nama_akun }}</td>
                  <td>{{ formatNumber(entry.jumlah) }}</td>
                </tr>
                <tr>
                  <td colspan="2"><strong>Total Debit</strong></td>
                  <td><strong>{{ formatNumber(totalDebit) }}</strong></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="panel panel-default">
          <div class="panel-heading">Kredit (Liabilitas dan Ekuitas)</div>
          <div class="panel-body">
            <table width="100%" class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <th>Kode Akun</th>
                  <th>Nama Akun</th>
                  <th>Jumlah</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(entry, index) in creditEntries" :key="index">
                  <td>{{ entry.kode }}</td>
                  <td>{{ entry.nama_akun }}</td>
                  <td>{{ formatNumber(entry.jumlah) }}</td>
                </tr>
                <tr>
                  <td colspan="2"><strong>Total Kredit</strong></td>
                  <td><strong>{{ formatNumber(totalCredit) }}</strong></td>
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
  name: 'BalanceSheet',
  data () {
    return {
      debitEntries: [], // Data sisi debit (aset)
      creditEntries: [], // Data sisi kredit (liabilitas dan ekuitas)
      startDate: '', // Tanggal awal
      endDate: '', // Tanggal akhir
      apiError: null
    }
  },
  computed: {
    totalDebit () {
      return this.debitEntries.reduce((total, entry) => total + (entry.jumlah || 0), 0)
    },
    totalCredit () {
      return this.creditEntries.reduce((total, entry) => total + (entry.jumlah || 0), 0)
    }
  },
  methods: {
    getAuthHeaders () {
      const token = localStorage.getItem('user-token')
      console.log('Token:', token)
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
    async fetchBalanceSheet () {
      const headers = this.getAuthHeaders()
      if (!headers) return
      try {
        let url = 'http://localhost:8000/api/neraca'
        if (this.startDate && this.endDate) {
          url += `?start_date=${this.startDate}&end_date=${this.endDate}`
        }
        const response = await axios.get(url, { headers })
        console.log('Respons dari backend:', response.data) // Debug log
        const rawEntries = response.data

        // Gabungkan akun dengan kode yang sama
        const mergedEntries = rawEntries.reduce((acc, entry) => {
          const existingEntry = acc.find(e => e.kode === entry.kode)
          if (existingEntry) {
            existingEntry.debit += Number(entry.debit)
            existingEntry.kredit += Number(entry.kredit)
          } else {
            acc.push({
              ...entry,
              debit: Number(entry.debit),
              kredit: Number(entry.kredit)
            })
          }
          return acc
        }, [])

        // Hitung pendapatan dan beban
        const pendapatan = mergedEntries
          .filter(entry => entry.tipe_klasifikasi === 'Pendapatan')
          .reduce((total, entry) => total + (entry.kredit - entry.debit), 0)

        const beban = mergedEntries
          .filter(entry => entry.tipe_klasifikasi === 'Beban')
          .reduce((total, entry) => total + (entry.debit - entry.kredit), 0)

        const ekuitasBelumDibagi = pendapatan - beban // Ekuitas yang belum dibagi = Pendapatan - Beban

        // Pisahkan data debit (aset) dan kredit (liabilitas dan ekuitas)
        this.debitEntries = mergedEntries
          .filter(entry => ['Aset Lancar', 'Aset Tetap'].includes(entry.tipe_klasifikasi))
          .map(entry => ({
            ...entry,
            jumlah: entry.debit - entry.kredit // Hitung debit - kredit untuk aset
          }))

        this.creditEntries = mergedEntries
          .filter(entry => ['Liabilitas Lancar', 'Liabilitas Jangka Panjang', 'Ekuitas'].includes(entry.tipe_klasifikasi))
          .map(entry => ({
            ...entry,
            jumlah: entry.kredit - entry.debit // Hitung kredit - debit untuk liabilitas dan ekuitas
          }))

        // Tambahkan ekuitas yang belum dibagi ke sisi kredit
        this.creditEntries.push({
          kode: 'EKUITAS_BELUM_DIBAGI',
          nama_akun: 'Ekuitas yang Belum Dibagi',
          tipe_klasifikasi: 'Ekuitas',
          jumlah: ekuitasBelumDibagi
        })
      } catch (error) {
        console.error(
          'Gagal mengambil data laporan neraca:',
          error.response && error.response.data ? error.response.data : error.message
        )
        this.apiError = 'Gagal mengambil data laporan neraca.'
      }
    },
    formatNumber (value) {
      // Format angka dengan tanda kurung jika negatif
      const formatted = Math.abs(value).toLocaleString()
      return value < 0 ? `(${formatted})` : formatted
    }
  },
  mounted () {
    this.fetchBalanceSheet()
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
