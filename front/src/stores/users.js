import { acceptHMRUpdate, defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'

export const useUsersStore = defineStore('users', () => {
  const users = ref([])
  const meta = ref(null)
  const loading = ref(false)
  const saving = ref(false)
  const error = ref('')
  const success = ref('')

  async function fetchUsers(page = 1) {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.get('/users', {
        params: { page, per_page: 10 },
      })

      users.value = data.data
      meta.value = data.meta
    } catch (err) {
      error.value = err.response?.data?.message || 'Erro ao listar usuários.'
    } finally {
      loading.value = false
    }
  }

  async function createUser(payload) {
    saving.value = true
    error.value = ''
    success.value = ''

    try {
      const { data } = await api.post('/users', payload)
      success.value = data.message
      await fetchUsers(meta.value?.current_page || 1)
      return true
    } catch (err) {
      error.value = getErrorMessage(err, 'Erro ao cadastrar usuário.')
      return false
    } finally {
      saving.value = false
    }
  }

  async function updateUser(id, payload) {
    saving.value = true
    error.value = ''
    success.value = ''

    try {
      const { data } = await api.put(`/users/${id}`, payload)
      success.value = data.message
      await fetchUsers(meta.value?.current_page || 1)
      return true
    } catch (err) {
      error.value = getErrorMessage(err, 'Erro ao atualizar usuário.')
      return false
    } finally {
      saving.value = false
    }
  }

  async function deleteUser(id) {
    error.value = ''
    success.value = ''

    try {
      const { data } = await api.delete(`/users/${id}`)
      success.value = data.message
      await fetchUsers(meta.value?.current_page || 1)
      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Erro ao excluir usuário.'
      return false
    }
  }

  async function fetchUserProfiles(userId) {
    const { data } = await api.get(`/users/${userId}/profiles`)
    return data.data
  }

  async function syncProfiles(userId, profileIds) {
    saving.value = true
    error.value = ''
    success.value = ''

    try {
      const { data } = await api.put(`/users/${userId}/profiles`, {
        profile_ids: profileIds,
      })

      const queued = Number(data.queued_detaches || 0)

      if (queued > 0) {
        success.value = 'Desassociação em processamento.'
        await fetchUsers(meta.value?.current_page || 1)

        // limpa a msg depois do worker processar
        setTimeout(async () => {
          await fetchUsers(meta.value?.current_page || 1)
          success.value = ''
          error.value = ''
        }, 1500)
      } else {
        success.value = data.message
        await fetchUsers(meta.value?.current_page || 1)
      }

      return data
    } catch (err) {
      error.value = getErrorMessage(err, 'Erro ao salvar perfis.')
      return null
    } finally {
      saving.value = false
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
    users,
    meta,
    loading,
    saving,
    error,
    success,
    fetchUsers,
    createUser,
    updateUser,
    deleteUser,
    fetchUserProfiles,
    syncProfiles,
    clearMessages,
  }
})

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useUsersStore, import.meta.hot))
}
