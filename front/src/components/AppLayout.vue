<script setup>
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

defineProps({
  title: {
    type: String,
    default: 'Gestão de usuários',
  },
})

const router = useRouter()
const auth = useAuthStore()

async function onLogout() {
  await auth.logout()
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="app-shell">
    <aside class="sidebar">
      <div class="sidebar-brand">
        <strong>Gestão de Acessos</strong>
        <span>{{ title }}</span>
      </div>

      <nav class="sidebar-nav">
        <RouterLink to="/users">
          <span class="menu-icon">U</span>
          Usuários
        </RouterLink>
        <RouterLink v-if="auth.isAdmin" to="/profiles">
          <span class="menu-icon">P</span>
          Perfis
        </RouterLink>
      </nav>

      <div class="sidebar-user">
        <strong>{{ auth.user?.name }}</strong>
        <span>{{ auth.user?.email }}</span>
        <button type="button" class="btn-secondary" @click="onLogout">
          Sair
        </button>
      </div>
    </aside>

    <main class="page-content">
      <slot />
    </main>
  </div>
</template>
