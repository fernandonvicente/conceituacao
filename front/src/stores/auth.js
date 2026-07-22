import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import api from '../services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(JSON.parse(localStorage.getItem('user') || 'null'))
  const token = ref(localStorage.getItem('token') || '')
  const error = ref('')
  const loading = ref(false)

  const isAuthenticated = computed(() => Boolean(token.value))
  const isAdmin = computed(() => {
    return user.value?.profiles?.some((profile) => profile.name === 'Administrador') ?? false
  })

  function saveSession(nextUser, nextToken) {
    user.value = nextUser
    token.value = nextToken
    localStorage.setItem('user', JSON.stringify(nextUser))
    localStorage.setItem('token', nextToken)
  }

  function clearSession() {
    user.value = null
    token.value = ''
    localStorage.removeItem('user')
    localStorage.removeItem('token')
  }

  async function login(email, password) {
    loading.value = true
    error.value = ''

    try {
      const { data } = await api.post('/login', { email, password })
      saveSession(data.user, data.token)
      return true
    } catch (err) {
      error.value = err.response?.data?.message
        || err.response?.data?.errors?.email?.[0]
        || 'Não foi possível fazer login.'
      return false
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await api.post('/logout')
    } catch {
      // mesmo se a API falhar, limpa a sessão local
    }

    clearSession()
  }

  return {
    user,
    token,
    error,
    loading,
    isAuthenticated,
    isAdmin,
    login,
    logout,
    clearSession,
  }
})
