<!-- eslint-disable vue/no-unused-vars -->
<!-- eslint-disable semi -->
<template>
  <div id="page-wrapper">
    <div class="row">
      <div class="col-lg-12">
        <h1 class="page-header">Akun</h1>
        <div class="filter">
          <label>Filter Klasifikasi:
            <select v-model="accountFilter">
              <option value="">Semua Klasifikasi</option>
              <option v-for="(klasifikasi, index) in klasifikasiAkun" :key="index" :value="klasifikasi">
                {{ klasifikasi }}
              </option>
            </select>
          </label>
          <button @click="fetchAccounts">Filter</button>
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
          <div class="panel-heading">Tabel Akun</div>
          <div class="panel-body">
            <table width="100%" class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <th>Kode</th>
                  <th>Nama Akun</th>
                  <th>Tipe Debit Kredit</th>
                  <th>Tipe Klasifikasi</th>
                  <th>Kode Akun Kontra</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <!-- Input untuk menambahkan akun baru -->
                <tr>
                  <td><input type="text" v-model="newEntry.kode" placeholder="Kode Akun" /></td>
                  <td><input type="text" v-model="newEntry.nama_akun" placeholder="Nama Akun" /></td>
                  <td>
                    <select v-model="newEntry.tipe_debit_kredit">
                      <option value="">Pilih</option>
                      <option value="debit">Debit</option>
                      <option value="kredit">Kredit</option>
                    </select>
                  </td>
                  <td>
                    <select v-model="newEntry.tipe_klasifikasi">
                      <option value="">Pilih</option>
                      <option v-for="(klasifikasi, index) in klasifikasiAkun" :key="index" :value="klasifikasi">
                        {{ klasifikasi }}
                      </option>
                    </select>
                  </td>
                  <td><input type="text" v-model="newEntry.kode_akun_kontra" placeholder="Kode Akun Kontra" /></td>
                  <td><button @click="addEntry">Tambah</button></td>
                </tr>
                <!-- Daftar akun -->
                <tr v-for="(entry, index) in accountEntries" :key="entry.id">
                  <td><input type="text" v-model="entry.kode" /></td>
                  <td><input type="text" v-model="entry.nama_akun" /></td>
                  <td>
                    <select v-model="entry.tipe_debit_kredit">
                      <option value="debit">Debit</option>
                      <option value="kredit">Kredit</option>
                    </select>
                  </td>
                  <td>
                    <select v-model="entry.tipe_klasifikasi">
                      <option v-for="(klasifikasi, i) in klasifikasiAkun" :key="i" :value="klasifikasi">
                        {{ klasifikasi }}
                      </option>
                    </select>
                  </td>
                  <td><input type="text" v-model="entry.kode_akun_kontra" /></td>
                  <td>
                    <button @click="updateEntry(entry.id)">Ubah</button>
                    <button @click="deleteEntry(entry.id)">Hapus</button>
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
  name: 'Accounts',
  data () {
    return {
      accountFilter: '',
      klasifikasiAkun: [
        'Aset Lancar',
        'Aset Tetap',
        'Liabilitas Lancar',
        'Liabilitas Jangka Panjang',
        'Ekuitas',
        'Pendapatan',
        'Beban'
      ],
      accountEntries: [],
      newEntry: { kode: '', nama_akun: '', tipe_debit_kredit: '', tipe_klasifikasi: '', kode_akun_kontra: '' },
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
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    },
    async fetchAccounts () {
      const headers = this.getAuthHeaders()
      if (!headers) return
      try {
        let url = 'http://localhost:8000/api/akun'
        if (this.accountFilter) {
          url += `?klasifikasi=${this.accountFilter}`
        }
        const response = await axios.get(url, { headers })
        this.accountEntries = response.data
      } catch (error) {
        console.error('Gagal mengambil data akun:', error)
        this.apiError = 'Gagal mengambil data akun.'
      }
    },
    async addEntry () {
      const headers = this.getAuthHeaders()
      if (!headers) return
      try {
        const response = await axios.post('http://localhost:8000/api/akun', this.newEntry, { headers })
        this.accountEntries.push(response.data)
        this.newEntry = { kode: '', nama_akun: '', tipe_debit_kredit: '', tipe_klasifikasi: '', kode_akun_kontra: '' }
      } catch (error) {
        console.error('Gagal menambahkan akun:', error)
        this.apiError = 'Gagal menambahkan akun.'
      }
    },
    async updateEntry (id) {
      const headers = this.getAuthHeaders()
      if (!headers) return
      const entry = this.accountEntries.find(e => e.id === id)
      if (!entry) {
        alert('Data tidak ditemukan')
        return
      }
      try {
        await axios.put(`http://localhost:8000/api/akun/${id}`, entry, { headers })
        alert('Data berhasil diperbarui!')
      } catch (error) {
        console.error('Gagal memperbarui akun:', error)
        this.apiError = 'Gagal memperbarui akun.'
      }
    },
    async deleteEntry (id) {
      const headers = this.getAuthHeaders()
      if (!headers) return
      try {
        await axios.delete(`http://localhost:8000/api/akun/${id}`, { headers })
        this.accountEntries = this.accountEntries.filter(entry => entry.id !== id)
      } catch (error) {
        console.error('Gagal menghapus akun:', error)
        this.apiError = 'Gagal menghapus akun.'
      }
    },
    exportToExcel () {
      const data = [
        ['Kode', 'Nama Akun', 'Tipe Debit Kredit', 'Tipe Klasifikasi', 'Kode Akun Kontra'],
        ...this.accountEntries.map(entry => [
          entry.kode,
          entry.nama_akun,
          entry.tipe_debit_kredit,
          entry.tipe_klasifikasi,
          entry.kode_akun_kontra
        ])
      ]
      const ws = XLSX.utils.aoa_to_sheet(data)
      const wb = XLSX.utils.book_new()
      XLSX.utils.book_append_sheet(wb, ws, 'Akun')
      XLSX.writeFile(wb, 'akun.xlsx')
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
    //       tipe_debit_kredit: row[2],
    //       tipe_klasifikasi: row[3],
    //       kode_akun_kontra: row[4]
    //     }))
    //     try {
    //       await axios.post('http://localhost:8000/api/akun/import', { entries }, { headers })
    //       this.fetchAccounts()
    //     } catch (error) {
    //       console.error('Gagal mengimpor data:', error)
    //       this.apiError = 'Gagal mengimpor data.'
    //     }
    //   }
    //   reader.readAsArrayBuffer(file)
    // }
  },
  mounted () {
    this.fetchAccounts()
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
