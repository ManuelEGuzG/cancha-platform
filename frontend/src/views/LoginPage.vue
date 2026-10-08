<template>
  <ion-page class="sportra-app">
    <ion-content :fullscreen="true" class="sportra-main-viewport">
      <div class="split-container">
        
        <!-- ================= PANEL IZQUIERDO (AZUL - CON CANCHA ANIMADA) ================= -->
        <div class="left-hero-panel">
          
          <!-- Marca / Logo Superior -->
          <div class="hero-top">
            <span class="hero-brand">SPORTRA<span class="dot">.</span></span>
          </div>

          <!-- Bloque Central Perfeccionado y Centrado -->
          <div class="hero-center-box">
            
            <!-- Badge / Etiqueta con Viñeta -->
            <div class="hero-badge">
              <span class="badge-dot"></span>
              <span>EL CENTRO DE CONTROL DE TU COMPLEJO</span>
            </div>

            <!-- Titular Principal -->
            <h1 class="hero-title">
              Que cada cancha<br />
              juegue a tu favor.
            </h1>

            <!-- Subtítulo Explicativo -->
            <p class="hero-description">
              Reservas, operación e ingresos conectados en una experiencia diseñada para moverse al ritmo de tu negocio.
            </p>

            <!-- Área Visual 3D (Cancha + Jugadores + Tarjetas Flotantes) -->
            <div class="pitch-visual-container">
              
              <!-- Tarjeta Flotante Superior -->
              <div class="floating-card top-card">
                <div class="card-check">
                  <ion-icon name="checkmark-outline" aria-hidden="true"></ion-icon>
                </div>
                <div class="card-text">
                  <span class="card-sub">Más tiempo para tu negocio</span>
                  <span class="card-main">Menos tareas repetitivas</span>
                </div>
              </div>

              <!-- Gráfico 3D de la Cancha con Animación de Partido -->
              <div class="pitch-3d-wrapper">
                <div class="pitch-board">
                  <!-- Líneas de Marcación -->
                  <div class="pitch-line pitch-border"></div>
                  <div class="pitch-line center-line"></div>
                  <div class="pitch-line center-circle"></div>
                  <div class="pitch-line penalty-area left"></div>
                  <div class="pitch-line penalty-area right"></div>
                  
                  <!-- JUGADORES EQUIPO A (BLANCO / AZUL CLARO) -->
                  <div class="player team-a gk"></div>
                  <div class="player team-a def-1"></div>
                  <div class="player team-a def-2"></div>
                  <div class="player team-a def-3"></div>
                  <div class="player team-a mid-1"></div>
                  <div class="player team-a mid-2"></div>
                  <div class="player team-a att-1"></div>

                  <!-- JUGADORES EQUIPO B (CELESTE BRILLANTE) -->
                  <div class="player team-b gk"></div>
                  <div class="player team-b def-1"></div>
                  <div class="player team-b def-2"></div>
                  <div class="player team-b mid-1"></div>
                  <div class="player team-b mid-2"></div>
                  <div class="player team-b att-1"></div>

                  <!-- BALÓN CON MOVIMIENTO DINÁMICO -->
                  <div class="ball-glow"></div>
                </div>
              </div>

              <!-- Tarjeta Flotante Inferior -->
              <div class="floating-card bottom-card">
                <div class="card-icon-badge">
                  <ion-icon name="shield-checkmark-outline" aria-hidden="true"></ion-icon>
                </div>
                <div class="card-text">
                  <span class="card-sub">Todo bajo control</span>
                  <span class="card-main">Una gestión clara y ordenada</span>
                </div>
              </div>

            </div>

          </div>

          <!-- Pie del Panel Izquierdo -->
          <div class="hero-footer">
            <div class="feature-item">
              <ion-icon name="checkmark-outline"></ion-icon>
              <span>Reservas sin fricción</span>
            </div>
            <div class="feature-item">
              <ion-icon name="checkmark-outline"></ion-icon>
              <span>Operación en tiempo real</span>
            </div>
            <div class="feature-item">
              <ion-icon name="checkmark-outline"></ion-icon>
              <span>Decisiones con claridad</span>
            </div>
          </div>

        </div>

        <!-- ================= PANEL DERECHO (FORMULARIO DE ACCESO) ================= -->
        <div class="right-form-panel">
          
          <!-- Botón Superior Regresar -->
          <div class="form-top-nav">
            <button class="btn-back-link" @click="volverAlInicio">
              <ion-icon name="chevron-back-outline"></ion-icon>
              <span>Volver al inicio</span>
            </button>
          </div>

          <!-- Formulario Centrado -->
          <div class="form-wrapper">
            
            <!-- Badge / Chip del Formulario -->
            <div class="portal-badge">
              <ion-icon name="shield-checkmark-outline"></ion-icon>
              <span>PORTAL DE PROPIETARIOS</span>
            </div>

            <h2 class="form-title">Bienvenido de nuevo</h2>
            <p class="form-subtitle">Accede al panel de gestión de tu centro deportivo.</p>

            <!-- Banner de Error -->
            <Transition name="fade">
              <div v-if="error" class="error-banner">
                <ion-icon name="alert-circle-outline"></ion-icon>
                <span>{{ error }}</span>
              </div>
            </Transition>

            <form @submit.prevent="ingresar" class="login-form" novalidate>
              
              <!-- Input: Correo Electrónico -->
              <div class="form-group">
                <label for="email" class="form-label">Correo electrónico</label>
                <div class="input-wrapper">
                  <ion-icon name="mail-outline" class="input-icon"></ion-icon>
                  <input
                    id="email"
                    v-model="email"
                    type="email"
                    class="custom-input"
                    placeholder="tu@complejo.com"
                    required
                  />
                </div>
              </div>

              <!-- Input Opcional: Código 2FA -->
              <Transition name="fade">
                <div v-if="requiereCodigo" class="form-group">
                  <label for="code" class="form-label">Código 2FA</label>
                  <div class="input-wrapper">
                    <ion-icon name="key-outline" class="input-icon"></ion-icon>
                    <input
                      id="code"
                      v-model="codigoSegundoFactor"
                      type="text"
                      class="custom-input"
                      placeholder="Mínimo 6 caracteres"
                      required
                    />
                  </div>
                </div>
              </Transition>

              <!-- Input: Contraseña -->
              <div class="form-group">
                <label for="password" class="form-label">Contraseña</label>
                <div class="input-wrapper">
                  <ion-icon name="lock-closed-outline" class="input-icon"></ion-icon>
                  <input
                    id="password"
                    v-model="password"
                    :type="mostrarPassword ? 'text' : 'password'"
                    class="custom-input"
                    placeholder="Mínimo 6 caracteres"
                    required
                  />
                  <button
                    type="button"
                    class="btn-eye"
                    @click="mostrarPassword = !mostrarPassword"
                  >
                    <ion-icon :name="mostrarPassword ? 'eye-off-outline' : 'eye-outline'"></ion-icon>
                  </button>
                </div>
              </div>

              <!-- Botón Submit -->
              <button
                type="submit"
                class="btn-submit"
                :disabled="cargando || !email || !password || (requiereCodigo && !codigoSegundoFactor)"
              >
                <ion-spinner v-if="cargando" name="crescent"></ion-spinner>
                <span v-else>
                  Ingresar al panel
                  <ion-icon name="chevron-forward-outline"></ion-icon>
                </span>
              </button>
            </form>

            <!-- Nota de Seguridad -->
            <div class="security-note">
              <ion-icon name="shield-checkmark-outline"></ion-icon>
              <span>Tu acceso está protegido con cifrado de nivel bancario.</span>
            </div>

            <!-- Registro / Contacto -->
            <p class="register-prompt">
              ¿Quieres registrar tu complejo?
              <a href="mailto:soporte@sportra.cr" class="prompt-link">Habla con nuestro equipo</a>
            </p>
          </div>

          <!-- Pie del Formulario -->
          <footer class="form-footer">
            <span class="copyright">©2025 Sportra</span>
            <div class="legal-links">
              <a href="#">Privacidad</a>
              <a href="#">Términos</a>
            </div>
          </footer>

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
  mailOutline, 
  lockClosedOutline, 
  keyOutline,
  eyeOutline, 
  eyeOffOutline, 
  alertCircleOutline,
  chevronForwardOutline,
  chevronBackOutline,
  shieldCheckmarkOutline,
  checkmarkOutline
} from 'ionicons/icons';
import { useAuthStore } from '@/stores/auth';

addIcons({
  'mail-outline': mailOutline,
  'lock-closed-outline': lockClosedOutline,
  'key-outline': keyOutline,
  'eye-outline': eyeOutline,
  'eye-off-outline': eyeOffOutline,
  'alert-circle-outline': alertCircleOutline,
  'chevron-forward-outline': chevronForwardOutline,
  'chevron-back-outline': chevronBackOutline,
  'shield-checkmark-outline': shieldCheckmarkOutline,
  'checkmark-outline': checkmarkOutline
});

const router = useRouter();
const authStore = useAuthStore();

const email = ref('');
const password = ref('');
const cargando = ref(false);
const error = ref('');
const mostrarPassword = ref(false);
const requiereCodigo = ref(false);
const codigoSegundoFactor = ref('');

async function ingresar() {
  cargando.value = true;
  error.value = '';
  try {
    await authStore.login(email.value, password.value, codigoSegundoFactor.value || undefined);
    router.push(authStore.usuario?.is_platform_admin ? '/admin' : '/panel');
  } catch (fallo: any) {
    if (fallo.response?.status === 422 && fallo.response?.data?.errors?.code) {
      requiereCodigo.value = true;
      codigoSegundoFactor.value = '';
      error.value = 'Ingresa el código de autenticación.';
      return;
    }
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
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

ion-content.sportra-main-viewport {
  --background: #FFFFFF;
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
}

.split-container {
  display: flex;
  min-height: 100vh;
  width: 100%;
}

/* ==========================================================================
   PANEL IZQUIERDO (COLORES ORIGINALES CON CANCHA Y JUGADORES ANIMADOS)
   ========================================================================== */
.left-hero-panel {
  flex: 1.1;
  background: radial-gradient(circle at 80% 20%, #112675 0%, #060e36 60%, #03071e 100%);
  color: #FFFFFF;
  padding: 3rem 4rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  position: relative;
  overflow: hidden;
}

.hero-top {
  position: relative;
  z-index: 2;
}

.hero-brand {
  font-size: 1.5rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  color: #FFFFFF;
}

.hero-brand .dot {
  color: #5C82FF;
}

.hero-center-box {
  position: relative;
  z-index: 2;
  max-width: 480px;
  width: 100%;
  margin: auto;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  text-align: left;
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  color: #8CA9FF;
  margin-bottom: 1.25rem;
  text-transform: uppercase;
}

.badge-dot {
  width: 6px;
  height: 6px;
  background-color: #5C82FF;
  border-radius: 50%;
}

.hero-title {
  font-size: 2.85rem;
  font-weight: 800;
  line-height: 1.15;
  letter-spacing: -0.03em;
  margin: 0 0 1.1rem 0;
  color: #FFFFFF;
}

.hero-description {
  font-size: 0.925rem;
  line-height: 1.6;
  color: #A3B8EE;
  margin: 0 0 2.25rem 0;
  font-weight: 400;
}

/* Área Visual */
.pitch-visual-container {
  position: relative;
  width: 100%;
  height: 220px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.floating-card {
  position: absolute;
  background: rgba(15, 27, 72, 0.75);
  border: 1px solid rgba(255, 255, 255, 0.12);
  backdrop-filter: blur(12px);
  border-radius: 14px;
  padding: 0.75rem 1.1rem;
  display: flex;
  align-items: center;
  gap: 0.8rem;
  z-index: 5;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
}

.top-card {
  top: -5px;
  right: -10px;
}

.bottom-card {
  bottom: 0px;
  left: -15px;
}

.card-check {
  background: #C4D3FF;
  color: #0B2D9A;
  width: 28px;
  height: 28px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  font-weight: bold;
}

.card-icon-badge {
  background: rgba(255, 255, 255, 0.1);
  color: #8CA9FF;
  width: 30px;
  height: 30px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}

.card-text {
  display: flex;
  flex-direction: column;
}

.card-sub {
  font-size: 0.65rem;
  color: #728FCB;
}

.card-main {
  font-size: 0.78rem;
  font-weight: 700;
  color: #FFFFFF;
}

/* --- CANCHA 3D Y FORMACIÓN TÁCTICA DE JUGADORES --- */
.pitch-3d-wrapper {
  perspective: 1000px;
  width: 310px;
  height: 175px;
}

.pitch-board {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #1b3d92 0%, #0d225c 100%);
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.25);
  transform: rotateX(60deg) rotateZ(-18deg);
  position: relative;
  box-shadow: 0 30px 60px rgba(0,0,0,0.6);
}

.pitch-line {
  position: absolute;
  border: 1px solid rgba(255, 255, 255, 0.35);
}

.pitch-border { inset: 10px; }
.center-line { top: 10px; bottom: 10px; left: 50%; width: 0; }
.center-circle { width: 48px; height: 48px; border-radius: 50%; top: 50%; left: 50%; transform: translate(-50%, -50%); }
.penalty-area.left { top: 32px; bottom: 32px; left: 10px; width: 38px; }
.penalty-area.right { top: 32px; bottom: 32px; right: 10px; width: 38px; }

/* ESTILOS DE LOS JUGADORES (PUNTITOS) */
.player {
  position: absolute;
  width: 7px;
  height: 7px;
  border-radius: 50%;
  transition: all 0.5s ease-in-out;
}

/* Equipo A (Blanco / Azul Claro) */
.team-a {
  background: #FFFFFF;
  box-shadow: 0 0 8px 1px rgba(255, 255, 255, 0.9);
}

/* Equipo B (Celeste Neón) */
.team-b {
  background: #38BDF8;
  box-shadow: 0 0 8px 1px rgba(56, 189, 248, 0.9);
}

/* Posiciones Iniciales + Animaciones de Movimiento */
.team-a.gk { top: 48%; left: 14px; }

.team-a.def-1 { top: 25%; left: 50px; animation: moveDefA1 6s infinite ease-in-out; }
.team-a.def-2 { top: 50%; left: 45px; animation: moveDefA2 7s infinite ease-in-out; }
.team-a.def-3 { top: 75%; left: 50px; animation: moveDefA1 6.5s infinite ease-in-out reverse; }

.team-a.mid-1 { top: 35%; left: 110px; animation: moveMidA1 5.5s infinite ease-in-out; }
.team-a.mid-2 { top: 65%; left: 105px; animation: moveMidA2 6.2s infinite ease-in-out; }

.team-a.att-1 { top: 48%; left: 170px; animation: moveAttA1 5s infinite ease-in-out; }

/* Equipo B Posiciones y Movimiento */
.team-b.gk { top: 48%; right: 14px; }

.team-b.def-1 { top: 30%; right: 55px; animation: moveDefB1 6.8s infinite ease-in-out; }
.team-b.def-2 { top: 70%; right: 55px; animation: moveDefB1 6.2s infinite ease-in-out reverse; }

.team-b.mid-1 { top: 40%; right: 110px; animation: moveMidB1 5.8s infinite ease-in-out; }
.team-b.mid-2 { top: 60%; right: 115px; animation: moveMidB2 6.5s infinite ease-in-out; }

.team-b.att-1 { top: 52%; right: 165px; animation: moveAttB1 5.2s infinite ease-in-out; }

/* Balón Animado */
.ball-glow {
  position: absolute;
  width: 5px;
  height: 5px;
  background: #FFED4A;
  border-radius: 50%;
  box-shadow: 0 0 10px 3px #FFED4A;
  animation: moveBall 5s infinite ease-in-out;
}

/* --- KEYFRAMES DE ANIMACIÓN PARTIDO DE FÚTBOL --- */
@keyframes moveBall {
  0%   { top: 48%; left: 50%; }
  25%  { top: 38%; left: 38%; }
  50%  { top: 62%; left: 60%; }
  75%  { top: 45%; left: 75%; }
  100% { top: 48%; left: 50%; }
}

@keyframes moveAttA1 {
  0%   { left: 170px; top: 48%; }
  50%  { left: 200px; top: 40%; }
  100% { left: 170px; top: 48%; }
}

@keyframes moveMidA1 {
  0%   { left: 110px; top: 35%; }
  50%  { left: 130px; top: 45%; }
  100% { left: 110px; top: 35%; }
}

@keyframes moveMidA2 {
  0%   { left: 105px; top: 65%; }
  50%  { left: 125px; top: 55%; }
  100% { left: 105px; top: 65%; }
}

@keyframes moveDefA1 {
  0%   { left: 50px; }
  50%  { left: 62px; }
  100% { left: 50px; }
}

@keyframes moveDefA2 {
  0%   { left: 45px; }
  50%  { left: 55px; }
  100% { left: 45px; }
}

@keyframes moveAttB1 {
  0%   { right: 165px; top: 52%; }
  50%  { right: 135px; top: 60%; }
  100% { right: 165px; top: 52%; }
}

@keyframes moveMidB1 {
  0%   { right: 110px; }
  50%  { right: 125px; }
  100% { right: 110px; }
}

@keyframes moveMidB2 {
  0%   { right: 115px; }
  50%  { right: 95px; }
  100% { right: 115px; }
}

@keyframes moveDefB1 {
  0%   { right: 55px; }
  50%  { right: 65px; }
  100% { right: 55px; }
}

/* Pie del Panel Izquierdo */
.hero-footer {
  position: relative;
  z-index: 2;
  display: flex;
  align-items: center;
  gap: 2rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  padding-top: 1.5rem;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.75rem;
  color: #728FCB;
}

.feature-item ion-icon {
  font-size: 0.85rem;
  color: #728FCB;
}

/* ==========================================================================
   PANEL DERECHO (FORMULARIO DE ACCESO)
   ========================================================================== */
.right-form-panel {
  flex: 0.9;
  background: #FAFCFF;
  padding: 3rem 4rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.form-top-nav {
  display: flex;
  justify-content: flex-start;
}

.btn-back-link {
  background: transparent;
  border: none;
  color: #4A5578;
  font-size: 0.825rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.3rem;
  cursor: pointer;
  transition: color 150ms ease;
}

.btn-back-link:hover {
  color: #0B2D9A;
}

.form-wrapper {
  max-width: 380px;
  width: 100%;
  margin: auto;
}

.portal-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  background: #EEF2FF;
  color: #3B62F6;
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  padding: 0.35rem 0.75rem;
  border-radius: 999px;
  margin-bottom: 1.5rem;
}

.portal-badge ion-icon {
  font-size: 0.85rem;
}

.form-title {
  font-size: 2rem;
  font-weight: 800;
  color: #0A0F29;
  letter-spacing: -0.02em;
  margin: 0 0 0.5rem 0;
}

.form-subtitle {
  font-size: 0.875rem;
  color: #64748B;
  margin: 0 0 2rem 0;
}

.error-banner {
  background: #FEF2F2;
  color: #991B1B;
  border: 1px solid #FEE2E2;
  border-radius: 8px;
  padding: 0.65rem 0.85rem;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1.25rem;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.form-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #1E293B;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 1rem;
  color: #94A3B8;
  font-size: 1.1rem;
}

.custom-input {
  width: 100%;
  background: #FFFFFF;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  padding: 0.8rem 1rem 0.8rem 2.6rem;
  font-size: 0.875rem;
  color: #0F172A;
  outline: none;
  transition: all 150ms ease;
}

.custom-input::placeholder {
  color: #CBD5E1;
}

.custom-input:focus {
  border-color: #818CF8;
  box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.15);
}

.btn-eye {
  position: absolute;
  right: 0.8rem;
  background: transparent;
  border: none;
  color: #94A3B8;
  cursor: pointer;
  display: flex;
  align-items: center;
  font-size: 1.1rem;
}

.btn-submit {
  margin-top: 0.5rem;
  width: 100%;
  height: 46px;
  background: linear-gradient(135deg, #789BFA 0%, #5C82FF 100%);
  color: #FFFFFF;
  border: none;
  border-radius: 12px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 180ms ease;
  box-shadow: 0 4px 12px rgba(92, 130, 255, 0.3);
}

.btn-submit span {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.btn-submit:hover:not(:disabled) {
  opacity: 0.95;
  transform: translateY(-1px);
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.security-note {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  font-size: 0.72rem;
  color: #94A3B8;
  margin-top: 1.5rem;
  text-align: center;
}

.security-note ion-icon {
  font-size: 0.85rem;
}

.register-prompt {
  text-align: center;
  font-size: 0.8rem;
  color: #64748B;
  margin-top: 2.5rem;
}

.prompt-link {
  color: #0B2D9A;
  font-weight: 700;
  text-decoration: none;
  margin-left: 0.25rem;
}

.prompt-link:hover {
  text-decoration: underline;
}

.form-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.75rem;
  color: #94A3B8;
}

.legal-links {
  display: flex;
  gap: 1rem;
}

.legal-links a {
  color: #94A3B8;
  text-decoration: none;
}

.legal-links a:hover {
  color: #64748B;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 180ms ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* ==========================================================================
   RESPONSIVE
   ========================================================================== */
@media (max-width: 960px) {
  .split-container {
    flex-direction: column;
  }

  .left-hero-panel {
    display: none;
  }

  .right-form-panel {
    flex: 1;
    padding: 2.5rem 1.5rem;
  }
}
</style>