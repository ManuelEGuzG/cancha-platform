<template>
  <ion-page>
    <ion-content :fullscreen="true">
      <div class="auth-container">
        <!-- Botón de retorno al sitio principal -->
        <button class="btn-back" @click="volverAlInicio">
          <ion-icon name="arrow-back-outline"></ion-icon>
          <span>Volver a Sportra</span>
        </button>

        <div class="auth-card">
          <!-- Header del Formulario -->
          <div class="auth-header">
            <div class="logo" @click="volverAlInicio">
              <img src="/Sportra_Logo.svg" alt="Sportra Logo" class="logo-img" />
              <span class="logo-text">Sportra</span>
            </div>
            <h1 class="auth-title">Acceso Propietarios</h1>
            <p class="auth-subtitle">Ingresa a tu panel para gestionar tus canchas y reservas</p>
          </div>

          <!-- Alerta de Error -->
          <div v-if="error" class="error-banner">
            <ion-icon name="alert-circle-outline" class="error-icon"></ion-icon>
            <span>{{ error }}</span>
          </div>

          <!-- Formulario -->
          <form @submit.prevent="ingresar" class="auth-form">
            <div class="field-group">
              <label class="field-label">Correo electrónico</label>
              <div class="input-wrapper">
                <ion-icon name="mail-outline" class="input-icon"></ion-icon>
                <input
                  v-model="email"
                  type="email"
                  class="custom-input"
                  placeholder="ejemplo@correo.com"
                  required
                />
              </div>
            </div>

            <div class="field-group">
              <div class="label-row">
                <label class="field-label">Contraseña</label>
              </div>
              <div class="input-wrapper">
                <ion-icon name="lock-closed-outline" class="input-icon"></ion-icon>
                <input
                  v-model="password"
                  :type="mostrarPassword ? 'text' : 'password'"
                  class="custom-input"
                  placeholder="••••••••"
                  required
                />
                <button
                  type="button"
                  class="btn-toggle-pw"
                  @click="mostrarPassword = !mostrarPassword"
                >
                  <ion-icon :name="mostrarPassword ? 'eye-off-outline' : 'eye-outline'"></ion-icon>
                </button>
              </div>
            </div>

            <button
              type="submit"
              class="btn-submit"
              :disabled="cargando || !email || !password"
            >
              <ion-spinner v-if="cargando" name="crescent"></ion-spinner>
              <span v-else>Ingresar al panel</span>
            </button>
          </form>

          <div class="auth-footer">
            <span>¿Necesitas registrar tu complejo deportivo?</span>
            <a href="mailto:soporte@sportra.cr" class="support-link">Contactar a soporte</a>
          </div>
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { IonPage, IonContent, IonSpinner, IonIcon } from '@ionic/vue';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const email = ref('');
const password = ref('');
const cargando = ref(false);
const error = ref('');
const mostrarPassword = ref(false);

async function ingresar() {
  cargando.value = true;
  error.value = '';
  try {
    await authStore.login(email.value, password.value);
    router.push('/panel');
  } catch (e) {
    error.value = 'Correo o contraseña incorrectos.';
  } finally {
    cargando.value = false;
  }
}

function volverAlInicio() {
  router.push('/');
}
</script>

<style scoped>
ion-content {
  --background: radial-gradient(circle at top, rgba(102, 227, 218, 0.22), transparent 32%), #f4fbff;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

.auth-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 1.5rem;
  position: relative;
}

.btn-back {
  position: absolute;
  top: 1.5rem;
  left: 1.5rem;
  background: #ffffff;
  border: 1px solid #dfeaf7;
  border-radius: 10px;
  padding: 0.5rem 0.85rem;
  color: #64748b;
  font-size: 0.825rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-back:hover {
  color: #1D5C94;
  border-color: #cbd5e1;
  box-shadow: 0 8px 18px rgba(29, 92, 148, 0.08);
}

.auth-card {
  background: rgba(255, 255, 255, 0.94);
  border: 1px solid rgba(17, 61, 128, 0.1);
  border-radius: 22px;
  padding: 2.25rem 2rem;
  width: 100%;
  max-width: 430px;
  box-shadow: 0 18px 40px -18px rgba(8, 34, 108, 0.22);
}

.auth-header {
  text-align: center;
  margin-bottom: 1.75rem;
}

.logo {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  cursor: pointer;
  margin-bottom: 1.25rem;
}

.logo-img {
  height: 1.8rem;
  width: auto;
  object-fit: contain;
  filter: invert(26%) sepia(73%) saturate(713%) hue-rotate(182deg) brightness(94%) contrast(101%);
}

.logo-text {
  font-weight: 800;
  font-size: 1.8rem;
  color: #1D5C94;
  letter-spacing: -0.03em;
  line-height: 1;
}

.auth-title {
  font-size: 1.35rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 0.35rem 0;
  letter-spacing: -0.02em;
}

.auth-subtitle {
  font-size: 0.825rem;
  color: #64748b;
  margin: 0;
  line-height: 1.4;
}

/* Banner de Error */
.error-banner {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
  padding: 0.65rem 0.85rem;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1.25rem;
}

.error-icon {
  font-size: 1.1rem;
  flex-shrink: 0;
}

/* Formulario e Inputs */
.auth-form {
  display: flex;
  flex-direction: column;
  gap: 1.1rem;
}

.field-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.field-label {
  font-size: 0.775rem;
  font-weight: 700;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 0.85rem;
  color: #94a3b8;
  font-size: 1.1rem;
  pointer-events: none;
}

.custom-input {
  width: 100%;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  border-radius: 8px;
  padding: 0.7rem 0.85rem 0.7rem 2.5rem;
  font-size: 0.9rem;
  color: #0f172a;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.custom-input:focus {
  border-color: #1D5C94;
  box-shadow: 0 0 0 3px rgba(29, 92, 148, 0.1);
}

.btn-toggle-pw {
  position: absolute;
  right: 0.75rem;
  background: transparent;
  border: none;
  color: #94a3b8;
  font-size: 1.1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-toggle-pw:hover {
  color: #64748b;
}

.btn-submit {
  margin-top: 0.5rem;
  background: linear-gradient(135deg, #1D5C94 0%, #08226C 100%);
  color: #ffffff;
  border: none;
  border-radius: 10px;
  padding: 0.8rem;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  height: 46px;
  box-shadow: 0 12px 22px rgba(29, 92, 148, 0.18);
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 14px 24px rgba(29, 92, 148, 0.2);
}

.btn-submit:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.btn-submit ion-spinner {
  width: 22px;
  height: 22px;
  --color: #ffffff;
}

/* Footer de la tarjeta */
.auth-footer {
  margin-top: 1.75rem;
  padding-top: 1.25rem;
  border-top: 1px solid #f1f5f9;
  text-align: center;
  font-size: 0.775rem;
  color: #64748b;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.support-link {
  color: #0066ff;
  font-weight: 600;
  text-decoration: none;
}

.support-link:hover {
  text-decoration: underline;
}

@media (max-width: 480px) {
  .btn-back {
    position: static;
    margin-bottom: 1.5rem;
    align-self: flex-start;
  }

  .auth-card {
    padding: 1.75rem 1.25rem;
  }
}
</style>