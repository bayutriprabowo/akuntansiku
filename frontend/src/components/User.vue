<!-- eslint-disable vue/no-unused-vars -->
<!-- eslint-disable semi -->
<template>
  <div id="page-wrapper">
    <div class="row">
      <div class="col-lg-12">
        <h1 class="page-header">User</h1>
        <div class="filter">
          <label>Filter Role:
            <select v-model="userFilter">
              <option value="">Semua Role</option>
              <!-- Menggunakan klasifikasiUser untuk opsi filter -->
              <option v-for="(role, index) in klasifikasiUser" :key="index" :value="role">
                {{ role }}
              </option>
            </select>
          </label>
          <button @click="fetchUsers">Filter</button>
        </div>
        <!-- <input type="file" ref="fileInput" @change="importFromExcel" style="display: none;" accept=".xlsx, .xls" />
        <button @click="triggerFileInput">Upload Excel</button> -->
        <button @click="exportToExcel">Download Excel</button>
        <p v-if="apiError" style="color: red;">Error: {{ apiError }}</p>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading">Tabel User</div>
          <div class="panel-body">
            <table width="100%" class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <th>Username</th>
                  <th>Email</th>
                  <th>Nama</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <!-- Input untuk menambahkan user baru -->
                <tr>
                  <td><input type="text" v-model="newEntry.username" placeholder="Username" /></td>
                  <td><input type="text" v-model="newEntry.email" placeholder="Email" /></td>
                  <td><input type="text" v-model="newEntry.name" placeholder="Nama" /></td>
                  <!-- Password hanya diinput saat tambah baru -->
                  <td>
                    <select v-model="newEntry.role">
                      <option value="" disabled>Pilih Role</option>
                      <option v-for="role in klasifikasiUser" :key="'new-'+role" :value="role">{{ role }}</option>
                    </select>
                  </td>
                  <td>
                    <select v-model="newEntry.status">
                      <option value="" disabled>Pilih Status</option>
                      <option value="aktif">Aktif</option>
                      <option value="nonaktif">Nonaktif</option>
                    </select>
                  </td>
                  <td>
                    <input type="text" v-model="newEntry.password" placeholder="Password" style="margin-bottom: 5px;"/>
                    <button @click="addEntry">Tambah</button>
                  </td>
                </tr>
                <!-- Daftar user -->
                <tr v-for="entry in userEntries" :key="entry.id">
                  <td><input type="text" v-model="entry.username" /></td>
                  <td><input type="text" v-model="entry.email" /></td>
                  <td><input type="text" v-model="entry.name" /></td>
                  <td>
                    <select v-model="entry.role">
                       <option v-for="role in klasifikasiUser" :key="entry.id+'-'+role" :value="role">{{ role }}</option>
                    </select>
                  </td>
                  <td>
                    <select v-model="entry.status">
                      <option value="aktif">Aktif</option>
                      <option value="nonaktif">Nonaktif</option>
                    </select>
                  </td>
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

// Konfigurasi Base URL API (opsional, bisa diatur global di main.js)
// axios.defaults.baseURL = 'http://localhost:8000/api';

export default {
  name: 'Users',
  data () {
    return {
      userFilter: '', // Filter berdasarkan role
      klasifikasiUser: ['user', 'admin', 'direktur'], // Opsi role
      userEntries: [], // Data user dari API
      newEntry: { // Data untuk form tambah user
        username: '',
        email: '',
        name: '',
        password: '', // Password hanya untuk user baru
        role: '',
        status: ''
      },
      apiError: null // Untuk menampilkan pesan error API
    }
  },
  methods: {
    // Helper untuk mendapatkan header Authorization
    getAuthHeaders () {
      const token = localStorage.getItem('user-token') // Sesuaikan key token Anda
      if (!token) {
        console.warn('Token tidak ditemukan di localStorage.')
        // Redirect ke login jika token tidak ada
        this.$router.push('/login')
        return null // Kembalikan null jika tidak ada token
      }
      return {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json' // Penting untuk request API Laravel
      }
    },

    // Helper untuk menangani error API
    handleApiError (error, context = 'operasi') {
      this.apiError = `Gagal ${context}.` // Pesan default
      if (error.response) {
        console.error(`Error ${error.response.status} saat ${context}:`, error.response.data)
        if (error.response.status === 401) {
          this.apiError = 'Autentikasi gagal atau sesi berakhir. Silakan login kembali.'
          // Hapus token lama dan redirect ke login
          localStorage.removeItem('user-token')
          this.$router.push('/login')
        } else if (error.response.status === 422) {
          // Menampilkan pesan error validasi dari Laravel
          const errors = error.response.data.errors
          const errorMessages = Object.values(errors).flat().join(' ')
          this.apiError = `Gagal ${context}: ${errorMessages}`
        } else {
          this.apiError = `Gagal ${context}. Status: ${error.response.status}. ${error.response.data.message || ''}`
        }
      } else if (error.request) {
        console.error(`Error request saat ${context}:`, error.request)
        this.apiError = `Gagal ${context}: Tidak ada respons dari server. Periksa koneksi atau status server backend.`
      } else {
        console.error(`Error setup saat ${context}:`, error.message)
        this.apiError = `Gagal ${context}: Terjadi kesalahan saat menyiapkan permintaan. ${error.message}`
      }
      // Hapus pesan error setelah beberapa detik
      setTimeout(() => { this.apiError = null }, 5000)
    },

    async fetchUsers () {
      this.apiError = null // Reset error
      const headers = this.getAuthHeaders()
      if (!headers) return // Stop jika tidak ada token

      try {
        let url = 'http://localhost:8000/api/user' // Ganti dengan baseURL jika dikonfigurasi
        const params = {}
        if (this.userFilter) {
          // Menggunakan 'role' sebagai parameter filter (sesuaikan jika backend berbeda)
          params.role = this.userFilter
        }
        const response = await axios.get(url, { headers, params }) // Kirim filter sebagai query params
        this.userEntries = response.data
      } catch (error) {
        this.handleApiError(error, 'mengambil data user')
      }
    },

    async addEntry () {
      this.apiError = null
      const headers = this.getAuthHeaders()
      if (!headers) return

      // Validasi frontend sederhana
      const { username, email, name, password, role, status } = this.newEntry
      if (!username || !email || !name || !password || !role || !status) {
        this.apiError = 'Semua field (Username, Email, Nama, Password, Role, Status) harus diisi!'
        setTimeout(() => { this.apiError = null }, 3000)
        return
      }

      try {
        const response = await axios.post('http://localhost:8000/api/user', this.newEntry, { headers })
        // Asumsi backend mengembalikan user yang baru dibuat
        this.userEntries.push(response.data)
        // Reset form
        this.newEntry = { username: '', email: '', name: '', password: '', role: '', status: '' }
        alert('User berhasil ditambahkan!') // Feedback sukses
      } catch (error) {
        this.handleApiError(error, 'menambahkan user')
      }
    },

    async updateEntry (id) {
      this.apiError = null
      const headers = this.getAuthHeaders()
      if (!headers) return

      const entry = this.userEntries.find(e => e.id === id)
      if (!entry) {
        this.apiError = 'Data user tidak ditemukan'
        setTimeout(() => { this.apiError = null }, 3000)
        return
      }

      // Validasi frontend sederhana untuk update
      if (!entry.username || !entry.email || !entry.role || !entry.status) {
        this.apiError = 'Username, Email, Role, dan Status tidak boleh kosong saat memperbarui.'
        setTimeout(() => { this.apiError = null }, 3000)
        // Pertimbangkan untuk fetch ulang data agar input kembali ke state sebelum diedit
        // this.fetchUsers();
        return
      }

      // Buat objek data untuk dikirim, HINDARI mengirim password kecuali memang diubah
      const dataToUpdate = {
        username: entry.username,
        email: entry.email,
        name: entry.name,
        role: entry.role,
        status: entry.status
        // Jika ada field password di form edit (tidak direkomendasikan),
        // tambahkan logika untuk hanya mengirimnya jika diisi.
        // if (entry.password) dataToUpdate.password = entry.password;
      }

      try {
        await axios.put(`http://localhost:8000/api/user/${id}`, dataToUpdate, { headers })
        alert('Data user berhasil diperbarui!')
        // Opsional: fetch ulang data untuk konsistensi, terutama jika backend mengubah data lain
        // this.fetchUsers();
      } catch (error) {
        this.handleApiError(error, `memperbarui user ID ${id}`)
        // Jika gagal, fetch ulang data untuk mengembalikan nilai input ke sebelum diedit
        this.fetchUsers()
      }
    },

    async deleteEntry (id) {
      this.apiError = null
      const headers = this.getAuthHeaders()
      if (!headers) return

      if (!confirm('Apakah Anda yakin ingin menghapus user ini?')) {
        return
      }

      try {
        await axios.delete(`http://localhost:8000/api/user/${id}`, { headers })
        this.userEntries = this.userEntries.filter(entry => entry.id !== id)
        alert('User berhasil dihapus.')
      } catch (error) {
        this.handleApiError(error, `menghapus user ID ${id}`)
      }
    },

    exportToExcel () {
      if (this.userEntries.length === 0) {
        alert('Tidak ada data user untuk diekspor.')
        return
      }
      // Data yang akan diekspor (tanpa password)
      const dataToExport = this.userEntries.map(entry => ({
        Username: entry.username,
        Email: entry.email,
        Nama: entry.name,
        Role: entry.role,
        Status: entry.status
      }))

      const header = Object.keys(dataToExport[0]) // Ambil header dari keys objek pertama
      // Buat array of arrays (AoA) untuk XLSX
      const dataAoA = [
        header, // Baris header
        ...dataToExport.map(row => header.map(key => row[key])) // Baris data
      ]

      const ws = XLSX.utils.aoa_to_sheet(dataAoA)
      const wb = XLSX.utils.book_new()
      XLSX.utils.book_append_sheet(wb, ws, 'User') // Nama sheet 'User'
      XLSX.writeFile(wb, 'daftar_user.xlsx') // Nama file
    }

    // triggerFileInput () {
    //   this.$refs.fileInput.value = null // Reset input file
    //   this.$refs.fileInput.click()
    // },

    // async importFromExcel (event) {
    //   this.apiError = null
    //   const headers = this.getAuthHeaders()
    //   if (!headers) return

    //   const file = event.target.files[0]
    //   if (!file) return

    //   const reader = new FileReader()
    //   reader.onload = async (e) => {
    //     try {
    //       const data = new Uint8Array(e.target.result)
    //       const workbook = XLSX.read(data, { type: 'array' })
    //       const sheetName = workbook.SheetNames[0]
    //       const worksheet = workbook.Sheets[sheetName]
    //       // Konversi ke JSON, header: 1 menghasilkan array of arrays
    //       const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1, raw: false, defval: '' }) // raw: false agar format lebih baik, defval untuk sel kosong

    //       if (jsonData.length < 2) { // Minimal 1 baris header + 1 baris data
    //         this.apiError = 'File Excel kosong atau hanya berisi header.'
    //         setTimeout(() => { this.apiError = null }, 3000)
    //         return
    //       }

    //       // Ambil header dan normalisasi (lowercase, trim)
    //       const headerRow = jsonData[0].map(h => String(h).trim().toLowerCase())
    //       const expectedHeaders = ['username', 'email', 'nama', 'password', 'role', 'status'] // Sesuaikan urutan & nama

    //       // Validasi header
    //       if (headerRow.length !== expectedHeaders.length || !expectedHeaders.every((h, i) => headerRow[i] === h)) {
    //         this.apiError = `Format header Excel tidak sesuai. Harusnya: ${expectedHeaders.join(', ')}. Ditemukan: ${headerRow.join(', ')}`
    //         setTimeout(() => { this.apiError = null }, 5000)
    //         return
    //       }

    //       // Map data dari baris setelah header
    //       const entriesToImport = jsonData.slice(1)
    //         .map((row, rowIndex) => {
    //           // Buat objek berdasarkan header
    //           const entry = {}
    //           headerRow.forEach((header, index) => {
    //             entry[header] = row[index] !== undefined ? String(row[index]).trim() : '' // Trim value
    //           })
    //           // Tambahkan nomor baris asli untuk referensi error jika perlu
    //           entry._originalRow = rowIndex + 2
    //           return entry
    //         })
    //         .filter(entry => {
    //           // Filter baris yang benar-benar kosong
    //           return Object.values(entry).some(val => val !== '' && val !== entry._originalRow)
    //         })
    //         .filter(entry => {
    //           // Validasi dasar per baris (contoh: field wajib)
    //           const isValid = entry.username && entry.email && entry.password && entry.role && entry.status
    //           if (!isValid) {
    //             console.warn(`Baris ${entry._originalRow}: Melewati baris karena data tidak lengkap.`, entry)
    //           }
    //           // Validasi tambahan (misal format email, nilai role/status) bisa ditambahkan di sini
    //           const validRoles = this.klasifikasiUser
    //           const validStatuses = ['aktif', 'nonaktif']
    //           if (!validRoles.includes(entry.role)) {
    //             console.warn(`Baris ${entry._originalRow}: Role tidak valid ('${entry.role}').`, entry)
    //             return false
    //           }
    //           if (!validStatuses.includes(entry.status)) {
    //             console.warn(`Baris ${entry._originalRow}: Status tidak valid ('${entry.status}').`, entry)
    //             return false
    //           }
    //           return isValid
    //         })

    //       if (entriesToImport.length === 0) {
    //         this.apiError = 'Tidak ada data valid untuk diimpor dari file Excel setelah validasi.'
    //         setTimeout(() => { this.apiError = null }, 3000)
    //         return
    //       }

    //       // Kirim data yang valid ke backend
    //       // Backend harus bisa menerima array objek dalam body request
    //       await axios.post('http://localhost:8000/api/user/import', { users: entriesToImport }, { headers }) // Kirim dalam key 'users' (sesuaikan dengan backend)

    //       alert(`Berhasil memproses ${entriesToImport.length} user dari file Excel.`)
    //       this.fetchUsers() // Refresh daftar user
    //     } catch (error) {
    //       this.handleApiError(error, 'mengimpor data dari Excel')
    //     }
    //   }
    //   reader.onerror = (error) => {
    //     console.error('Gagal membaca file:', error)
    //     this.apiError = 'Gagal membaca file Excel.'
    //     setTimeout(() => { this.apiError = null }, 3000)
    //   }
    //   reader.readAsArrayBuffer(file)
    // }
  },
  mounted () {
    // Panggil fetchUsers saat komponen dimuat
    this.fetchUsers()
  }
}
</script>

<style scoped>
input, select {
  width: 100%;
  padding: 5px;
  margin-bottom: 5px; /* Tambah margin bawah */
  box-sizing: border-box;
}
button {
  padding: 5px 10px;
  cursor: pointer;
  margin-right: 5px;
  margin-bottom: 5px; /* Tambah margin bawah */
}
.filter {
  margin-bottom: 15px;
}
.filter label {
  margin-right: 10px;
}
/* Style untuk pesan error */
p[style*="color: red"] {
  margin-top: 10px;
  font-weight: bold;
}
</style>
