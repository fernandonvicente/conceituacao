<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import AppLayout from '../components/AppLayout.vue'
import { useProfilesStore } from '../stores/profiles'

const profilesStore = useProfilesStore()

const showForm = ref(false)
const editingId = ref(null)
const name = ref('')
const deleteTarget = ref(null)

onMounted(() => {
  profilesStore.clearMessages()
  profilesStore.fetchProfiles()
})

onBeforeUnmount(() => {
  profilesStore.clearMessages()
})

function openCreate() {
  editingId.value = null
  name.value = ''
  profilesStore.clearMessages()
  showForm.value = true
}

function openEdit(profile) {
  editingId.value = profile.id
  name.value = profile.name
  profilesStore.clearMessages()
  showForm.value = true
}

function closeForm() {
  showForm.value = false
}

async function onSubmit() {
  const ok = editingId.value
    ? await profilesStore.updateProfile(editingId.value, name.value)
    : await profilesStore.createProfile(name.value)

  if (ok) {
    closeForm()
  }
}

function openDeleteModal(profile) {
  deleteTarget.value = profile
  profilesStore.clearMessages()
}

function closeDeleteModal() {
  deleteTarget.value = null
}

async function confirmDelete() {
  const ok = await profilesStore.deleteProfile(deleteTarget.value.id)

  if (ok) {
    closeDeleteModal()
  }
}

function goToPage(page) {
  if (! page || page === profilesStore.meta?.current_page) {
    return
  }

  profilesStore.fetchProfiles(page)
}
</script>

<template>
  <AppLayout title="Perfis">
    <section class="panel">
      <div class="panel-header">
        <div>
          <h1>Perfis</h1>
          <p class="subtitle">Cadastre e gerencie os perfis de acesso</p>
        </div>

        <button type="button" @click="openCreate">Novo perfil</button>
      </div>

      <p v-if="profilesStore.success" class="success">
        {{ profilesStore.success }}
      </p>
      <p v-if="profilesStore.error && !showForm" class="error">
        {{ profilesStore.error }}
      </p>

      <div v-if="profilesStore.loading" class="empty">Carregando...</div>

      <div v-else-if="!profilesStore.profiles.length" class="empty">
        Nenhum perfil encontrado.
      </div>

      <div v-else class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nome</th>
              <th>Criado em</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="profile in profilesStore.profiles" :key="profile.id">
              <td>{{ profile.id }}</td>
              <td>
                {{ profile.name }}
                <span v-if="profile.name === 'Administrador'" class="badge">
                  Protegido
                </span>
              </td>
              <td>{{ profile.created_at }}</td>
              <td class="actions">
                <button
                  type="button"
                  class="btn-secondary"
                  :disabled="profile.name === 'Administrador'"
                  @click="openEdit(profile)"
                >
                  Editar
                </button>
                <button
                  type="button"
                  class="btn-danger"
                  :disabled="profile.name === 'Administrador'"
                  @click="openDeleteModal(profile)"
                >
                  Excluir
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="profilesStore.meta" class="pagination">
        <button
          type="button"
          class="btn-secondary"
          :disabled="profilesStore.meta.current_page <= 1"
          @click="goToPage(profilesStore.meta.current_page - 1)"
        >
          Anterior
        </button>

        <span>
          Página {{ profilesStore.meta.current_page }}
          de {{ profilesStore.meta.last_page }}
        </span>

        <button
          type="button"
          class="btn-secondary"
          :disabled="profilesStore.meta.current_page >= profilesStore.meta.last_page"
          @click="goToPage(profilesStore.meta.current_page + 1)"
        >
          Próxima
        </button>
      </div>
    </section>

    <div v-if="showForm" class="modal-backdrop" @click.self="closeForm">
      <form class="modal-card" @submit.prevent="onSubmit">
        <h2>{{ editingId ? 'Editar perfil' : 'Novo perfil' }}</h2>

        <label>
          Nome do perfil
          <input v-model="name" type="text" required minlength="3" />
        </label>

        <p v-if="profilesStore.error" class="error">
          {{ profilesStore.error }}
        </p>

        <div class="modal-actions">
          <button type="button" class="btn-secondary" @click="closeForm">
            Cancelar
          </button>
          <button type="submit" :disabled="profilesStore.saving">
            {{ profilesStore.saving ? 'Salvando...' : 'Salvar' }}
          </button>
        </div>
      </form>
    </div>

    <div
      v-if="deleteTarget"
      class="modal-backdrop"
      @click.self="closeDeleteModal"
    >
      <div class="modal-card confirm-modal">
        <h2>Confirmar exclusão</h2>
        <p>
          Confirma a exclusão do perfil
          <strong>{{ deleteTarget.name }}</strong>?
        </p>

        <p v-if="profilesStore.error" class="error">
          {{ profilesStore.error }}
        </p>

        <div class="modal-actions">
          <button type="button" class="btn-secondary" @click="closeDeleteModal">
            Não
          </button>
          <button type="button" class="btn-danger" @click="confirmDelete">
            Sim
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
