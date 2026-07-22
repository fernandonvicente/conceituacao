import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'

export const useProfilesStore = defineStore('profiles', () => {
  const profiles = ref([])
  const meta = ref(null)
  const loading = ref(false)
  const saving = ref(false)
  const error = ref('')
  const success = ref('')

  async function fetchProfiles(page = 1, perPage = 50) {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.get('/profiles', {
        params: { page, per_page: perPage },
      })

      profiles.value = data.data
      meta.value = data.meta
    } catch (err) {
      error.value = err.response?.data?.message || 'Erro ao listar perfis.'
    } finally {
      loading.value = false
    }
  }

  async function createProfile(name) {
    return saveProfile(() => api.post('/profiles', { name }))
  }

  async function updateProfile(id, name) {
    return saveProfile(() => api.put(`/profiles/${id}`, { name }))
  }

  async function saveProfile(request) {
    saving.value = true
    error.value = ''
    success.value = ''

    try {
      const { data } = await request()
      success.value = data.message
      await fetchProfiles(meta.value?.current_page || 1)
      return true
    } catch (err) {
      error.value = getErrorMessage(err, 'Erro ao salvar perfil.')
      return false
    } finally {
      saving.value = false
    }
  }

  async function deleteProfile(id) {
    error.value = ''
    success.value = ''

    try {
      const { data } = await api.delete(`/profiles/${id}`)
      success.value = data.message
      await fetchProfiles(meta.value?.current_page || 1)
      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Erro ao excluir perfil.'
      return false
    }
  }

  function clearMessages() {
    error.value = ''
    success.value = ''
  }

  function getErrorMessage(err, fallback) {
    const errors = err.response?.data?.errors

    if (errors) {
      return Object.values(errors).flat().join(' ')
    }

    return err.response?.data?.message || fallback
  }

  return {
    profiles,
    meta,
    loading,
    saving,
    error,
    success,
    fetchProfiles,
    createProfile,
    updateProfile,
    deleteProfile,
    clearMessages,
  }
})
