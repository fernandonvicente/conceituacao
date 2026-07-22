<script setup>
import { reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()

const form = reactive({
  email: 'admin@gmail.com',
  password: '123456',
})

async function onSubmit() {
  const ok = await auth.login(form.email, form.password)

  if (ok) {
    router.push({ name: 'users' })
  }
}
</script>

<template>
  <main class="login-page">
    <form class="login-card" @submit.prevent="onSubmit">
      <h1>Entrar</h1>
      <p class="subtitle">Acesse o sistema de usuários e perfis</p>

      <label>
        E-mail
        <input
          v-model="form.email"
          type="email"
          required
          autocomplete="username"
        />
      </label>

      <label>
        Senha
        <input
          v-model="form.password"
          type="password"
          required
          autocomplete="current-password"
        />
      </label>

      <p v-if="auth.error" class="error">{{ auth.error }}</p>

      <button type="submit" :disabled="auth.loading">
        {{ auth.loading ? 'Entrando...' : 'Entrar' }}
      </button>
    </form>
  </main>
</template>
