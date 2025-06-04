<!-- eslint-disable vue/no-unused-vars -->
<template>
  <div id="page-wrapper">
    <div class="row">
      <div class="col-lg-12">
        <h1 class="page-header">Jurnal Umum</h1>
        <div class="filter">
          <label>Dari: <input type="date" v-model="startDate" /></label>
          <label>Sampai: <input type="date" v-model="endDate" /></label>
          <button @click="fetchJournalEntries">Filter</button>
        </div>
        <!-- <input type="file" ref="fileInput" @change="importFromExcel" style="display: none;" />
        <button @click="triggerFileInput">Upload Excel</button> -->
        <button @click="exportToExcel">Download Excel</button>
        <p v-if="apiError" style="color: red;">Error: {{ apiError }}</p>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading">Tabel Jurnal</div>
          <div class="panel-body">
            <table width="100%" class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <th>Kode</th>
                  <th>Nama Akun</th>
                  <th>Debit</th>
                  <th>Kredit</th>
                  <th>Tanggal</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <!-- Input untuk menambahkan entri baru -->
                <tr>
                  <td><input type="text" v-model="newEntry.kode" placeholder="Kode Akun" /></td>
                  <td><input type="text" v-model="newEntry.nama_akun" placeholder="Nama Akun" /></td>
                  <td><input type="number" v-model="newEntry.debit" placeholder="Debit" /></td>
                  <td><input type="number" v-model="newEntry.kredit" placeholder="Kredit" /></td>
                  <td><input type="date" v-model="newEntry.tanggal" placeholder="Tanggal" /></td>
                  <td><button @click="addEntry">Tambah</button></td>
                </tr>

                <!-- Daftar jurnal -->
                <tr v-for="(entry, index) in journalEntries" :key="entry.id">
                  <td>{{ entry.kode }}</td>
                  <td>{{ entry.nama_akun }}</td>
                  <td>{{ entry.debit.toLocaleString() }}</td>
                  <td>{{ entry.kredit.toLocaleString() }}</td>
                  <td>{{ entry.tanggal }}</td>
                  <td>
                    <button @click="deleteEntry(entry.id)">Hapus</button>
                  </td>
                </tr>
                <tr>
                  <td colspan="2"><strong>Total</strong></td>
                  <td><strong>{{ balanceDebit.toLocaleString() }}</strong></td>
                  <td><strong>{{ balanceKredit.toLocaleString() }}</strong></td>
                  <td colspan="2">
                    <span v-if="isBalanced" style="color: green; font-weight: bold;">✔ Seimbang</span>
                    <span v-else style="color: red; font-weight: bold;">✖ Tidak Seimbang</span>
                  </td>
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
import * as XLSX from 'xlsx'

export default {
  name: 'Journal',
  data () {
    return {
      journalEntries: [],
      newEntry: {
        kode: '',
        nama_akun: '',
        debit: 0,
        kredit: 0,
        tanggal: ''
      },
      startDate: '',
      endDate: '',
      apiError: null
    }
  },
  computed: {
    balanceDebit () {
      return this.journalEntries.reduce((total, entry) => total + (Number(entry.debit) || 0), 0)
    },
    balanceKredit () {
      return this.journalEntries.reduce((total, entry) => total + (Number(entry.kredit) || 0), 0)
    },
    isBalanced () {
      return this.balanceDebit === this.balanceKredit
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
    async fetchJournalEntries () {
      const headers = this.getAuthHeaders()
      if (!headers) return
      try {
        let url = 'http://localhost:8000/api/jurnal-umum'
        if (this.startDate && this.endDate) {
          url += `?start_date=${this.startDate}&end_date=${this.endDate}`
        }
        const response = await axios.get(url, { headers })
        this.journalEntries = response.data
      } catch (error) {
        console.error('Gagal mengambil data jurnal:', error)
        this.apiError = 'Gagal mengambil data jurnal.'
      }
    },
    async addEntry () {
      const headers = this.getAuthHeaders()
      if (!headers) return
      try {
        const response = await axios.post('http://localhost:8000/api/jurnal-umum', this.newEntry, { headers })
        this.journalEntries.push(response.data)
        this.newEntry = { kode: '', nama_akun: '', debit: 0, kredit: 0, tanggal: '' }
      } catch (error) {
        console.error('Gagal menambahkan entri:', error)
        this.apiError = 'Gagal menambahkan entri.'
      }
    },
    async deleteEntry (id) {
      const headers = this.getAuthHeaders()
      if (!headers) return
      try {
        await axios.delete(`http://localhost:8000/api/jurnal-umum/${id}`, { headers })
        this.journalEntries = this.journalEntries.filter(entry => entry.id !== id)
      } catch (error) {
        console.error('Gagal menghapus entri:', error)
        this.apiError = 'Gagal menghapus entri.'
      }
    },
    exportToExcel () {
      const data = [
        ['Kode', 'Nama Akun', 'Debit', 'Kredit', 'Tanggal'],
        ...this.journalEntries.map(entry => [
          entry.kode,
          entry.nama_akun,
          entry.debit,
          entry.kredit,
          entry.tanggal
        ])
      ]
      const ws = XLSX.utils.aoa_to_sheet(data)
      const wb = XLSX.utils.book_new()
      XLSX.utils.book_append_sheet(wb, ws, 'Jurnal Umum')
      XLSX.writeFile(wb, 'jurnal_umum.xlsx')
    }
    // triggerFileInput () {
    //   this.$refs.fileInput.click()
    // },
    // async importFromExcel (event) {
    //   const headers = this.getAuthHeaders()
    //   if (!headers) return
    //   const file = event.target.files[0]
    //   if (!file) return
    //   const reader = new FileReader()
    //   reader.onload = async (e) => {
    //     const data = new Uint8Array(e.target.result)
    //     const workbook = XLSX.read(data, { type: 'array' })
    //     const sheet = workbook.Sheets[workbook.SheetNames[0]]
    //     const jsonData = XLSX.utils.sheet_to_json(sheet, { header: 1 })

    //     const entries = jsonData.slice(1).map(row => ({
    //       kode: row[0],
    //       nama_akun: row[1],
    //       debit: Number(row[2]) || 0,
    //       kredit: Number(row[3]) || 0,
    //       tanggal: row[4]
    //     }))

    //     try {
    //       await axios.post('http://localhost:8000/api/jurnal-umum/import', { entries }, { headers })
    //       this.fetchJournalEntries()
    //     } catch (error) {
    //       console.error('Gagal mengimpor data:', error)
    //       this.apiError = 'Gagal mengimpor data.'
    //     }
    //   }
    //   reader.readAsArrayBuffer(file)
    // }
  },
  mounted () {
    this.fetchJournalEntries()
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
