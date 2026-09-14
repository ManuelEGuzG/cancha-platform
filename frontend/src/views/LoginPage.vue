<template>
  <ion-page class="sportra-app">
    <ion-content :fullscreen="true" class="sportra-main-viewport">
      <!-- Efectos de Fondo y Glow Neón -->
      <div class="page-background-glow glow-float-1"></div>
      <div class="page-background-glow glow-float-2"></div>
      <div class="bg-grid"></div>

      <div class="auth-container">
        <!-- Botón Flotante Neón de Retorno -->
        <button class="btn-back-glow" @click="volverAlInicio">
          <ion-icon name="arrow-back-outline"></ion-icon>
          <span>Volver a Sportra</span>
        </button>

        <!-- Tarjeta Neón Glass Container -->
        <div class="auth-card-glass">
          
          <!-- Header del Formulario -->
          <div class="auth-header">
            <div class="brand-box" @click="volverAlInicio" role="button" tabindex="0">
              <div class="brand-badge glow-pulse">
                <ion-icon name="football-outline" class="brand-icon"></ion-icon>
              </div>
              <span class="brand-name">SPORTRA<span class="neon-dot">.</span></span>
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
              <div class="input-glow-wrapper">
                <ion-icon name="mail-outline" class="input-icon"></ion-icon>
                <input
                  v-model="email"
                  type="email"
                  class="custom-input-dark"
                  placeholder="ejemplo@correo.com"
                  required
                />
              </div>
            </div>

            <div class="field-group">
              <div class="label-row">
                <label class="field-label">Contraseña</label>
              </div>
              <div class="input-glow-wrapper">
                <ion-icon name="lock-closed-outline" class="input-icon"></ion-icon>
                <input
                  v-model="password"
                  :type="mostrarPassword ? 'text' : 'password'"
                  class="custom-input-dark"
                  placeholder="••••••••"
                  required
                />
                <button
                  type="button"
                  class="btn-toggle-pw"
                  @click="mostrarPassword = !mostrarPassword"
                  aria-label="Toggle password visibility"
                >
                  <ion-icon :name="mostrarPassword ? 'eye-off-outline' : 'eye-outline'"></ion-icon>
                </button>
              </div>
            </div>

            <button
              type="submit"
              class="btn-submit-neon wave-effect"
              :disabled="cargando || !email || !password"
            >
              <ion-spinner v-if="cargando" name="crescent" class="btn-spinner"></ion-spinner>
              <span v-else>Ingresar al Panel</span>
            </button>
          </form>

          <!-- Footer de la tarjeta -->
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
import { addIcons } from 'ionicons';
import { 
  arrowBackOutline, 
  footballOutline, 
  mailOutline, 
  lockClosedOutline, 
  eyeOutline, 
  eyeOffOutline, 
  alertCircleOutline 
} from 'ionicons/icons';
import { useAuthStore } from '@/stores/auth';

// Registro explícito de los iconos usados
addIcons({
  'arrow-back-outline': arrowBackOutline,
  'football-outline': footballOutline,
  'mail-outline': mailOutline,
  'lock-closed-outline': lockClosedOutline,
  'eye-outline': eyeOutline,
  'eye-off-outline': eyeOffOutline,
  'alert-circle-outline': alertCircleOutline,
});

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
    router.push(authStore.usuario?.is_platform_admin ? '/admin' : '/panel');
  } catch (e) {
    error.value = 'Correo o contraseña incorrectos.';
  } finally {
    cargando.value = false;
  }
}

function volverAlInicio() {
  router.push('/home');
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700;800&family=Inter:wght@400;500;600;700;800&display=swap');

/* BASE LAYOUT DARK */
ion-content.sportra-main-viewport {
  --background: #030712;
  font-family: 'Inter', -apple-system, sans-serif;
  color: #f3f4f6;
}

/* BACKGROUND GLOW EFFECTS */
.page-background-glow {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
  filter: blur(90px);
}

.glow-float-1 {
  top: 10%;
  left: 20%;
  width: 450px;
  height: 450px;
  background: radial-gradient(circle, rgba(132, 204, 22, 0.12) 0%, rgba(3, 7, 18, 0) 70%);
}

.glow-float-2 {
  bottom: 15%;
  right: 20%;
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(163, 230, 53, 0.08) 0%, rgba(3, 7, 18, 0) 70%);
}

.bg-grid {
  position: absolute;
  inset: 0;
  background-image: linear-gradient(to right, rgba(255,255,255,0.02) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(255,255,255,0.02) 1px, transparent 1px);
  background-size: 48px 48px;
  pointer-events: none;
  mask-image: radial-gradient(circle at center, black 50%, transparent 90%);
}

/* CONTAINER PRINCIPAL */
.auth-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 2rem 1.5rem;
  position: relative;
  z-index: 2;
}

/* BOTÓN DE REGRESO FLOTANTE NEÓN */
.btn-back-glow {
  position: absolute;
  top: 2rem;
  left: 2rem;
  background: rgba(11, 15, 25, 0.75);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 99px;
  padding: 0.55rem 1.1rem;
  color: #cbd5e1;
  font-size: 0.825rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
}

.btn-back-glow ion-icon {
  font-size: 1.1rem;
  color: #84cc16;
}

.btn-back-glow:hover {
  color: #ffffff;
  border-color: rgba(132, 204, 22, 0.4);
  transform: translateY(-2px);
  box-shadow: 0 12px 30px rgba(132, 204, 22, 0.15);
}

/* AUTH CARD GLASS */
.auth-card-glass {
  background: rgba(11, 15, 25, 0.85);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 28px;
  padding: 2.5rem 2.25rem;
  width: 100%;
  max-width: 440px;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8), 0 0 1px rgba(132, 204, 22, 0.2);
}

/* HEADER & BRAND */
.auth-header {
  text-align: center;
  margin-bottom: 2rem;
}

.brand-box {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  cursor: pointer;
  user-select: none;
  margin-bottom: 1.25rem;
}

.brand-badge {
  width: 42px;
  height: 42px;
  background: rgba(132, 204, 22, 0.15);
  border: 1px solid rgba(132, 204, 22, 0.4);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 15px rgba(132, 204, 22, 0.2);
}

.brand-icon {
  font-size: 1.35rem;
  color: #84cc16;
}

.brand-name {
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 800;
  font-size: 1.75rem;
  color: #ffffff;
  letter-spacing: -0.04em;
}

.neon-dot {
  color: #84cc16;
  text-shadow: 0 0 8px rgba(132, 204, 22, 0.8);
}

.auth-title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 1.45rem;
  font-weight: 800;
  color: #ffffff;
  margin: 0 0 0.35rem 0;
  letter-spacing: -0.02em;
}

.auth-subtitle {
  font-size: 0.85rem;
  color: #94a3b8;
  margin: 0;
  line-height: 1.45;
}

/* BANNER DE ERROR */
.error-banner {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #f87171;
  padding: 0.75rem 0.95rem;
  border-radius: 14px;
  font-size: 0.825rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  margin-bottom: 1.5rem;
}

.error-icon {
  font-size: 1.25rem;
  flex-shrink: 0;
}

/* FORMULARIO E INPUTS */
.auth-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.field-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.field-label {
  font-size: 0.725rem;
  font-weight: 800;
  color: #84cc16;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.input-glow-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 1rem;
  color: #64748b;
  font-size: 1.2rem;
  pointer-events: none;
  transition: color 0.25s ease;
}

.custom-input-dark {
  width: 100%;
  background: #030712;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 14px;
  padding: 0.8rem 2.6rem 0.8rem 2.7rem;
  font-size: 0.9rem;
  color: #ffffff;
  outline: none;
  font-family: inherit;
  transition: all 0.25s ease;
}

.custom-input-dark::placeholder {
  color: #475569;
}

.custom-input-dark:focus {
  border-color: #84cc16;
  box-shadow: 0 0 20px rgba(132, 204, 22, 0.2);
}

.custom-input-dark:focus + .input-icon,
.input-glow-wrapper:focus-within .input-icon {
  color: #84cc16;
}

.btn-toggle-pw {
  position: absolute;
  right: 0.85rem;
  background: transparent;
  border: none;
  color: #64748b;
  font-size: 1.2rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s ease;
}

.btn-toggle-pw:hover {
  color: #84cc16;
}

/* BOTÓN SUBMIT NEÓN */
.btn-submit-neon {
  margin-top: 0.5rem;
  background: #84cc16;
  color: #030712;
  border: none;
  border-radius: 14px;
  padding: 0.85rem;
  font-size: 0.925rem;
  font-weight: 800;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  height: 48px;
  box-shadow: 0 0 25px rgba(132, 204, 22, 0.35);
}

.btn-submit-neon:hover:not(:disabled) {
  transform: translateY(-2px);
  background: #a3e635;
  box-shadow: 0 0 35px rgba(132, 204, 22, 0.5);
}

.btn-submit-neon:disabled {
  opacity: 0.4;
  cursor: not-allowed;
  box-shadow: none;
}

.btn-spinner {
  width: 24px;
  height: 24px;
  color: #030712;
}

/* FOOTER TARJETA */
.auth-footer {
  margin-top: 2rem;
  padding-top: 1.35rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  text-align: center;
  font-size: 0.8rem;
  color: #94a3b8;
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.support-link {
  color: #84cc16;
  font-weight: 700;
  text-decoration: none;
  transition: opacity 0.2s ease;
}

.support-link:hover {
  text-decoration: underline;
  opacity: 0.9;
}

/* RESPONSIVE */
@media (max-width: 480px) {
  .btn-back-glow {
    position: static;
    margin-bottom: 1.5rem;
    align-self: flex-start;
  }

  .auth-card-glass {
    padding: 2rem 1.5rem;
  }
}
</style>