<!-- filepath: d:\project\akuntansiku\frontend\src\components\CompanyProfile.vue -->
<template>
  <div id="page-wrapper">
    <div class="row">
      <div class="col-lg-12">
        <h1 class="page-header">Profil Perusahaan</h1>
        <p v-if="apiError" style="color: red;">Error: {{ apiError }}</p>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading">Form Profil Perusahaan</div>
          <div class="panel-body">
            <form @submit.prevent="saveProfile">
              <div class="form-group">
                <label for="id">ID</label>
                <input type="text" id="id" v-model="profile.id" class="form-control" readonly />
              </div>
              <div class="form-group">
                <label for="nama">Nama Perusahaan</label>
                <input type="text" id="nama" v-model="profile.nama" class="form-control" required />
              </div>
              <div class="form-group">
                <label for="tanggal_berdiri">Tanggal Berdiri</label>
                <input type="date" id="tanggal_berdiri" v-model="profile.tanggal_berdiri" class="form-control" required />
              </div>
              <div class="form-group">
                <label for="telepon">Telepon</label>
                <input type="text" id="telepon" v-model="profile.telepon" class="form-control" required />
              </div>
              <div class="form-group">
                <label for="alamat">Alamat</label>
                <textarea id="alamat" v-model="profile.alamat" class="form-control" required></textarea>
              </div>
              <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" v-model="profile.email" class="form-control" required />
              </div>
              <button type="submit" class="btn btn-primary">Simpan</button>
              <button type="button" class="btn btn-danger" @click="deleteProfile">Hapus</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'CompanyProfile',
  data () {
    return {
      profile: {
        id: '',
        nama: '',
        tanggal_berdiri: '',
        telepon: '',
        alamat: '',
        email: ''
      },
      apiError: null
    }
  },
  methods: {
    async fetchProfile () {
      try {
        const headers = this.getAuthHeaders()
        if (!headers) return
        const response = await axios.get('http://localhost:8000/api/profil-perusahaan', { headers })
        this.profile = response.data
      } catch (error) {
        console.error('Gagal mengambil data profil perusahaan:', error)
        this.apiError = 'Gagal mengambil data profil perusahaan.'
      }
    },
    async saveProfile () {
      try {
        const headers = this.getAuthHeaders()
        if (!headers) return

        console.log('Data yang dikirim:', this.profile) // Debug log

        if (this.profile.id) {
          // Jika ID ada, lakukan update (PUT)
          await axios.put('http://localhost:8000/api/profil-perusahaan', this.profile, { headers })
          alert('Profil perusahaan berhasil diperbarui.')
        } else {
          // Jika ID tidak ada, lakukan penambahan (POST)
          const response = await axios.post('http://localhost:8000/api/profil-perusahaan', this.profile, { headers })
          this.profile.id = response.data.id // Set ID dari respons
          alert('Profil perusahaan berhasil ditambahkan.')
        }
      } catch (error) {
        console.error(
          'Gagal menyimpan profil perusahaan:',
          error.response && error.response.data ? error.response.data : error.message
        )
        if (error.response && error.response.data.errors) {
          this.apiError = Object.values(error.response.data.errors).flat().join(', ')
        } else {
          this.apiError = 'Gagal menyimpan profil perusahaan.'
        }
      }
    },
    async deleteProfile () {
      if (!confirm('Apakah Anda yakin ingin menghapus profil perusahaan?')) return
      try {
        const headers = this.getAuthHeaders()
        if (!headers) return
        await axios.delete('http://localhost:8000/api/profil-perusahaan', { headers })
        alert('Profil perusahaan berhasil dihapus.')
        this.profile = {
          id: '',
          nama: '',
          tanggal_berdiri: '',
          telepon: '',
          alamat: '',
          email: ''
        }
      } catch (error) {
        console.error('Gagal menghapus profil perusahaan:', error)
        this.apiError = 'Gagal menghapus profil perusahaan.'
      }
    },
    getAuthHeaders () {
      const token = localStorage.getItem('user-token')
      if (!token) {
        console.warn('Token tidak ditemukan. Redirect ke login.')
        this.$router.push('/login')
        return null
      }
      return {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json'
      }
    }
  },
  mounted () {
    this.fetchProfile()
  }
}
</script>

<style scoped>
.form-group {
  margin-bottom: 15px;
}
button {
  padding: 10px 15px;
  cursor: pointer;
}
</style>
