<script setup>
import { onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import AppLayout from '../components/AppLayout.vue'
import { useAuthStore } from '../stores/auth'
import { useProfilesStore } from '../stores/profiles'
import { useUsersStore } from '../stores/users'

const auth = useAuthStore()
const usersStore = useUsersStore()
const profilesStore = useProfilesStore()

const showForm = ref(false)
const editingId = ref(null)
const deleteTarget = ref(null)
const profilesUser = ref(null)
const linkedProfileIds = ref([])
const profilesLoading = ref(false)

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

function isLinked(profileId) {
  return linkedProfileIds.value.includes(profileId)
}

function isCurrentUser(user) {
  const loggedUser = auth.user
  if (!loggedUser) {
    return false
  }

  return Number(user.id) === Number(loggedUser.id)
    || user.email === loggedUser.email
}

onMounted(() => {
  usersStore.clearMessages()
  usersStore.fetchUsers()
})

onBeforeUnmount(() => {
  usersStore.clearMessages()
})

function openCreate() {
  editingId.value = null
  form.name = ''
  form.email = ''
  form.password = ''
  form.password_confirmation = ''
  usersStore.clearMessages()
  showForm.value = true
}

function openEdit(user) {
  editingId.value = user.id
  form.name = user.name
  form.email = user.email
  form.password = ''
  form.password_confirmation = ''
  usersStore.clearMessages()
  showForm.value = true
}

function closeForm() {
  showForm.value = false
}

async function onSubmit() {
  const payload = {
    name: form.name,
    email: form.email,
  }

  if (form.password) {
    payload.password = form.password
    payload.password_confirmation = form.password_confirmation
  }

  // no cadastro a senha é obrigatória
  if (!editingId.value) {
    payload.password = form.password
    payload.password_confirmation = form.password_confirmation
  }

  const ok = editingId.value
    ? await usersStore.updateUser(editingId.value, payload)
    : await usersStore.createUser(payload)

  if (ok) {
    showForm.value = false
  }
}

function openDeleteModal(user) {
  deleteTarget.value = user
  usersStore.clearMessages()
}

function closeDeleteModal() {
  deleteTarget.value = null
}

async function confirmDelete() {
  await usersStore.deleteUser(deleteTarget.value.id)
  closeDeleteModal()
}

async function openProfilesModal(user) {
  profilesUser.value = user
  usersStore.clearMessages()
  profilesLoading.value = true

  try {
    await profilesStore.fetchProfiles(1)
    const profiles = await usersStore.fetchUserProfiles(user.id)
    linkedProfileIds.value = profiles.map((profile) => profile.id)
  } catch (err) {
    usersStore.error = err.response?.data?.message || 'Erro ao carregar perfis do usuário.'
    profilesUser.value = null
  } finally {
    profilesLoading.value = false
  }
}

function closeProfilesModal() {
  profilesUser.value = null
  linkedProfileIds.value = []
}

function onToggleProfile(profileId) {
  linkedProfileIds.value = isLinked(profileId)
    ? linkedProfileIds.value.filter((id) => id !== profileId)
    : [...linkedProfileIds.value, profileId]
}

async function onSaveProfiles() {
  try {
    const result = await usersStore.syncProfiles(
      profilesUser.value.id,
      linkedProfileIds.value,
    )

    if (result) {
      closeProfilesModal()
    }
  } catch (err) {
    usersStore.error = err.message || 'Erro ao salvar perfis.'
  }
}

function goToPage(page) {
  if (!page || page === usersStore.meta?.current_page) {
    return
  }

  usersStore.fetchUsers(page)
}
</script>

<template>
  <AppLayout title="Usuários">
    <section class="panel">
      <div class="panel-header">
        <div>
          <h1>Usuários</h1>
          <p class="subtitle">Cadastre, edite e exclua usuários do sistema</p>
        </div>

        <button type="button" @click="openCreate">Novo usuário</button>
      </div>

      <p v-if="usersStore.success && !profilesUser" class="success">
        {{ usersStore.success }}
      </p>
      <p v-if="usersStore.error && !showForm && !profilesUser" class="error">
        {{ usersStore.error }}
      </p>

      <div v-if="usersStore.loading" class="empty">Carregando...</div>

      <div v-else-if="!usersStore.users.length" class="empty">
        Nenhum usuário encontrado.
      </div>

      <div v-else class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nome</th>
              <th>E-mail</th>
              <th>Perfis</th>
              <th>Criado em</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in usersStore.users" :key="user.id">
              <td>{{ user.id }}</td>
              <td>
                {{ user.name }}
                <span v-if="isCurrentUser(user)" class="badge">
                  Protegido
                </span>
              </td>
              <td>{{ user.email }}</td>
              <td>
                {{ user.profiles?.map((p) => p.name).join(', ') || '-' }}
              </td>
              <td>{{ user.created_at }}</td>
              <td class="actions">
                <button type="button" class="btn-secondary" @click="openEdit(user)">
                  Editar
                </button>
                <button
                  v-if="auth.isAdmin"
                  type="button"
                  class="btn-secondary"
                  @click="openProfilesModal(user)"
                >
                  Perfis
                </button>
                <button
                  type="button"
                  class="btn-danger"
                  :disabled="isCurrentUser(user)"
                  @click="openDeleteModal(user)"
                >
                  Excluir
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="usersStore.meta" class="pagination">
        <button
          type="button"
          class="btn-secondary"
          :disabled="usersStore.meta.current_page <= 1"
          @click="goToPage(usersStore.meta.current_page - 1)"
        >
          Anterior
        </button>

        <span>
          Página {{ usersStore.meta.current_page }} de {{ usersStore.meta.last_page }}
        </span>

        <button
          type="button"
          class="btn-secondary"
          :disabled="usersStore.meta.current_page >= usersStore.meta.last_page"
          @click="goToPage(usersStore.meta.current_page + 1)"
        >
          Próxima
        </button>
      </div>
    </section>

    <div v-if="showForm" class="modal-backdrop" @click.self="closeForm">
      <form class="modal-card" @submit.prevent="onSubmit">
        <h2>{{ editingId ? 'Editar usuário' : 'Novo usuário' }}</h2>

        <label>
          Nome
          <input v-model="form.name" type="text" required minlength="3" />
        </label>

        <label>
          E-mail
          <input v-model="form.email" type="email" required />
        </label>

        <label>
          Senha
          <input
            v-model="form.password"
            type="password"
            :required="!editingId"
            minlength="6"
          />
        </label>

        <label>
          Confirmar senha
          <input
            v-model="form.password_confirmation"
            type="password"
            :required="!editingId || Boolean(form.password)"
            minlength="6"
          />
        </label>

        <p v-if="usersStore.error" class="error">{{ usersStore.error }}</p>

        <div class="modal-actions">
          <button type="button" class="btn-secondary" @click="closeForm">
            Cancelar
          </button>
          <button type="submit" :disabled="usersStore.saving">
            {{ usersStore.saving ? 'Salvando...' : 'Salvar' }}
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
          Confirma a exclusão do usuário
          <strong>{{ deleteTarget.name }}</strong>?
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

    <div
      v-if="profilesUser"
      class="modal-backdrop"
      @click.self="closeProfilesModal"
    >
      <div class="modal-card profiles-modal">
        <h2>Perfis de {{ profilesUser.name }}</h2>
        <p class="hint">Marque os perfis que este usuário deve ter.</p>

        <div v-if="profilesLoading" class="empty">Carregando...</div>

        <div v-else-if="!profilesStore.profiles.length" class="empty">
          Nenhum perfil cadastrado.
        </div>

        <ul v-else class="profile-list">
          <li v-for="profile in profilesStore.profiles" :key="profile.id">
            <label class="profile-check">
              <input
                type="checkbox"
                :checked="isLinked(profile.id)"
                @change="onToggleProfile(profile.id)"
              />
              <span>{{ profile.name }}</span>
            </label>
          </li>
        </ul>

        <p v-if="usersStore.error" class="error">{{ usersStore.error }}</p>

        <div class="modal-actions">
          <button type="button" class="btn-secondary" @click="closeProfilesModal">
            Cancelar
          </button>
          <button
            type="button"
            :disabled="profilesLoading || usersStore.saving"
            @click="onSaveProfiles"
          >
            {{ usersStore.saving ? 'Salvando...' : 'Salvar' }}
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
