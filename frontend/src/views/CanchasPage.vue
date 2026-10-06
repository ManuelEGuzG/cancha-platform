<template>
  <ion-page class="sportra-app">
    <!-- Navbar Flotante Neón Glass -->
    <header class="navbar-container">
      <div class="navbar-bar">
        <div class="brand-box" @click="irAlPanel" role="button" tabindex="0">
          <div class="brand-badge glow-pulse">
            <ion-icon :icon="footballOutline" class="brand-icon"></ion-icon>
          </div>
          <span class="brand-name">SPORTRA<span class="neon-dot">.</span></span>
        </div>

        <div class="header-right">
          <button class="btn-portal-glow" @click="irAlPanel">
            <ion-icon :icon="arrowBackOutline"></ion-icon>
            <span>Volver al Panel</span>
          </button>
        </div>
      </div>
    </header>

    <ion-content :fullscreen="true" class="sportra-main-viewport">
      <div class="page-background-glow glow-float-1"></div>
      <div class="page-background-glow glow-float-2"></div>
      <div class="bg-grid"></div>

      <main class="management-wrapper">
        <div v-if="cargando" class="loading-wrap">
          <ion-spinner name="crescent" class="lime-spinner"></ion-spinner>
          <span>Cargando instalaciones...</span>
        </div>

        <div v-else class="management-layout">
          <!-- LISTADO DE CANCHAS -->
          <section class="panel-card main-panel">
            <div class="card-header">
              <div>
                <span class="eyebrow">Inventario</span>
                <h2>Listado de Canchas</h2>
              </div>
              <span class="counter-pill">{{ canchas.length }}</span>
            </div>

            <div v-if="canchas.length" class="cards-list">
              <article v-for="cancha in canchas" :key="cancha.id" class="cancha-card">
                <div class="cancha-top">
                  <div>
                    <h3>{{ cancha.nombre }}</h3>
                    <span class="deporte-tag">
                      <ion-icon :icon="footballOutline"></ion-icon>
                      {{ cancha.deporte.nombre }}
                    </span>
                  </div>
                  <span :class="['status-pill', cancha.activa ? 'active' : 'inactive']">
                    <span class="beacon-dot"></span>
                    {{ cancha.activa ? 'Activa' : 'Inactiva' }}
                  </span>
                  <span :class="['status-pill', `review-${cancha.estado_verificacion}`]">
                    {{ etiquetaVerificacion(cancha.estado_verificacion) }}
                  </span>
                </div>

                <p v-if="cancha.observaciones_admin" class="review-observation">{{ cancha.observaciones_admin }}</p>

                <div class="cancha-meta">
                  <span class="price-val">₡{{ formatearPrecio(cancha.precio_hora) }}</span>
                  <small class="price-unit">/ hora</small>
                </div>

                <div class="action-row">
                  <button class="secondary-btn" @click="editar(cancha)">
                    <ion-icon :icon="createOutline"></ion-icon> Editar
                  </button>
                  <button class="primary-btn wave-effect" @click="verHorarios(cancha.id)">
                    Horarios <ion-icon :icon="arrowForwardOutline"></ion-icon>
                  </button>
                </div>
              </article>
            </div>

            <div v-else class="empty-state">
              <div class="empty-icon-glow">
                <ion-icon :icon="footballOutline"></ion-icon>
              </div>
              <p>Aún no hay canchas registradas para este complejo.</p>
            </div>
          </section>

          <!-- PANEL DE CONFIGURACIÓN / EDICIÓN -->
          <aside class="panel-card form-panel">
            <div class="card-header">
              <div>
                <span class="eyebrow">Configuración</span>
                <h2>{{ canchaEditando ? 'Editar Cancha' : 'Nueva Cancha' }}</h2>
              </div>
            </div>

            <div v-if="canchaEditando" class="editor-form">
              <div class="field-block">
                <label>Nombre de la cancha</label>
                <div class="input-glow-box">
                  <input v-model="formEdicion.nombre" type="text" placeholder="Ej: Cancha 1 (Sintética)" />
                </div>
              </div>

              <div class="field-block">
                <label>Precio por hora (₡)</label>
                <div class="input-glow-box">
                  <input v-model.number="formEdicion.precio_hora" type="number" placeholder="18000" />
                </div>
              </div>

              <div class="switch-row">
                <span>Estado de la cancha</span>
                <ion-toggle v-model="formEdicion.activa" class="neon-toggle"></ion-toggle>
              </div>

              <div class="form-actions">
                <button class="btn-cancel" @click="cancelarEdicion">Cancelar</button>
                <button class="submit-btn" @click="guardarEdicion">Guardar cambios</button>
              </div>
            </div>

            <div v-else class="editor-form">
              <div class="field-block">
                <label>Nombre de la cancha</label>
                <div class="input-glow-box">
                  <input v-model="nuevaCancha.nombre" type="text" placeholder="Ej: Cancha 1" />
                </div>
              </div>

              <div class="field-block">
                <label>Precio por hora (₡)</label>
                <div class="input-glow-box">
                  <input v-model.number="nuevaCancha.precio_hora" type="number" placeholder="18000" />
                </div>
              </div>

              <button class="submit-btn" @click="crearCancha">Crear Cancha</button>
            </div>

            <transition name="fade-banner">
              <p v-if="mensaje" class="message-banner success">
                <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
                <span>{{ mensaje }}</span>
              </p>
            </transition>
          </aside>
        </div>
      </main>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { IonPage, IonContent, IonToggle, IonSpinner, IonIcon } from '@ionic/vue';
import { addIcons } from 'ionicons';
import { 
  footballOutline, 
  arrowBackOutline, 
  arrowForwardOutline, 
  createOutline, 
  checkmarkCircleOutline 
} from 'ionicons/icons';
import canchasService from '@/services/canchas.service';

addIcons({
  'football-outline': footballOutline,
  'arrow-back-outline': arrowBackOutline,
  'arrow-forward-outline': arrowForwardOutline,
  'create-outline': createOutline,
  'checkmark-circle-outline': checkmarkCircleOutline
});

const route = useRoute();
const router = useRouter();
const complejoId = Number(route.query.complejoId);

const canchas = ref<any[]>([]);
const cargando = ref(true);
const mensaje = ref('');

const canchaEditando = ref<any>(null);
const formEdicion = ref({ nombre: '', precio_hora: 0, activa: true });

const nuevaCancha = ref({ nombre: '', precio_hora: 0 });

async function cargarCanchas() {
  cargando.value = true;
  try {
    const { data } = await canchasService.listar(complejoId);
    canchas.value = data.data;
  } catch (error) {
    console.error('Error cargando canchas:', error);
  } finally {
    cargando.value = false;
  }
}

function editar(cancha: any) {
  canchaEditando.value = cancha;
  formEdicion.value = { nombre: cancha.nombre, precio_hora: cancha.precio_hora, activa: cancha.activa };
}

function cancelarEdicion() {
  canchaEditando.value = null;
  formEdicion.value = { nombre: '', precio_hora: 0, activa: true };
}

async function guardarEdicion() {
  await canchasService.actualizar(canchaEditando.value.id, formEdicion.value);
  mostrarMensaje('Cancha actualizada con éxito.');
  canchaEditando.value = null;
  await cargarCanchas();
}

async function crearCancha() {
  if (!nuevaCancha.value.nombre || !nuevaCancha.value.precio_hora) return;
  await canchasService.crear({
    complejo_id: complejoId,
    deporte_id: 1, // Fútbol
    nombre: nuevaCancha.value.nombre,
    precio_hora: nuevaCancha.value.precio_hora,
  });
  mostrarMensaje('Cancha creada con éxito.');
  nuevaCancha.value = { nombre: '', precio_hora: 0 };
  await cargarCanchas();
}

function verHorarios(canchaId: number) {
  router.push(`/panel/canchas/${canchaId}/horarios`);
}

function irAlPanel() {
  router.push('/panel');
}

function formatearPrecio(precio: number): string {
  return precio ? precio.toLocaleString('es-CR') : '0';
}

function etiquetaVerificacion(estado: string): string {
  return {
    pendiente: 'Pendiente de revisión',
    aprobada: 'Aprobada',
    rechazada: 'Rechazada',
  }[estado] || estado;
}

function mostrarMensaje(text: string) {
  mensaje.value = text;
  setTimeout(() => {
    mensaje.value = '';
  }, 4000);
}

onMounted(cargarCanchas);
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700;800&family=Inter:wght@400;500;600;700;800&display=swap');

/* BASE & LAYOUT */
ion-content.sportra-main-viewport {
  --background: #030712;
  font-family: 'Inter', -apple-system, sans-serif;
  color: #f3f4f6;
}

/* NAVBAR NEON GLASS */
.navbar-container {
  position: fixed;
  top: 1.25rem;
  left: 0;
  right: 0;
  z-index: 1000;
  padding: 0 1.5rem;
}

.navbar-bar {
  max-width: 1280px;
  margin: 0 auto;
  background: rgba(11, 15, 25, 0.75);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 99px;
  padding: 0.5rem 0.6rem 0.5rem 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8), 0 0 1px rgba(132, 204, 22, 0.2);
}

.brand-box {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  cursor: pointer;
  user-select: none;
}

.brand-badge {
  width: 38px;
  height: 38px;
  background: rgba(132, 204, 22, 0.15);
  border: 1px solid rgba(132, 204, 22, 0.4);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 15px rgba(132, 204, 22, 0.2);
  transition: transform 0.3s ease;
}

.brand-box:hover .brand-badge {
  transform: rotate(15deg) scale(1.08);
}

.brand-icon {
  font-size: 1.25rem;
  color: #84cc16;
}

.brand-name {
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 800;
  font-size: 1.35rem;
  color: #ffffff;
  letter-spacing: -0.04em;
}

.neon-dot {
  color: #84cc16;
  text-shadow: 0 0 8px rgba(132, 204, 22, 0.8);
}

.btn-portal-glow {
  background: #ffffff;
  color: #030712;
  border: none;
  padding: 0.5rem 1.1rem;
  border-radius: 99px;
  font-weight: 800;
  font-size: 0.825rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.btn-portal-glow:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(255, 255, 255, 0.25);
}

/* DECORATIVO GLOW BACKGROUND */
.page-background-glow {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
  filter: blur(90px);
}

.glow-float-1 {
  top: 5%;
  left: 10%;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(132, 204, 22, 0.12) 0%, rgba(3, 7, 18, 0) 70%);
}

.glow-float-2 {
  bottom: 10%;
  right: 10%;
  width: 450px;
  height: 450px;
  background: radial-gradient(circle, rgba(163, 230, 53, 0.08) 0%, rgba(3, 7, 18, 0) 70%);
}

.bg-grid {
  position: absolute;
  inset: 0;
  background-image: linear-gradient(to right, rgba(255,255,255,0.02) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(255,255,255,0.02) 1px, transparent 1px);
  background-size: 48px 48px;
  pointer-events: none;
  mask-image: radial-gradient(circle at center, black 40%, transparent 80%);
}

/* MAIN CONTENT CONTAINER */
.management-wrapper {
  position: relative;
  z-index: 2;
  max-width: 1280px;
  margin: 0 auto;
  padding: 8rem 1.5rem 4rem;
}

.loading-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 8rem 0;
  gap: 1rem;
  color: #94a3b8;
  font-weight: 600;
}

.lime-spinner {
  color: #84cc16;
  width: 42px;
  height: 42px;
}

.management-layout {
  display: grid;
  grid-template-columns: 1.35fr 0.9fr;
  gap: 2rem;
  align-items: start;
}

/* PANEL CARDS GLASS */
.panel-card {
  background: rgba(11, 15, 25, 0.85);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 26px;
  padding: 1.75rem;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.eyebrow {
  display: inline-block;
  font-size: 0.68rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-weight: 800;
  color: #84cc16;
}

.card-header h2 {
  margin: 0.2rem 0 0;
  color: #ffffff;
  font-family: 'Space Grotesk', sans-serif;
  letter-spacing: -0.03em;
  font-size: 1.6rem;
  font-weight: 800;
}

.counter-pill {
  background: rgba(132, 204, 22, 0.15);
  color: #84cc16;
  font-size: 0.85rem;
  padding: 0.25rem 0.75rem;
  border-radius: 99px;
  font-weight: 800;
  border: 1px solid rgba(132, 204, 22, 0.3);
}

/* CARDS LIST & ITEM */
.cards-list {
  display: grid;
  gap: 1.1rem;
}

.cancha-card {
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(255, 255, 255, 0.02);
  border-radius: 20px;
  padding: 1.25rem;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.cancha-card:hover {
  border-color: rgba(132, 204, 22, 0.4);
  background: rgba(132, 204, 22, 0.03);
  transform: translateY(-2px);
}

.cancha-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 0.75rem;
  margin-bottom: 0.85rem;
}

.cancha-top h3 {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 800;
  color: #ffffff;
}

.deporte-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  margin-top: 0.35rem;
  color: #94a3b8;
  font-size: 0.78rem;
  font-weight: 600;
}

.deporte-tag ion-icon {
  color: #84cc16;
}

/* STATUS PILLS */
.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.3rem 0.75rem;
  border-radius: 99px;
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.beacon-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}

.status-pill.active {
  background: rgba(132, 204, 22, 0.12);
  color: #a3e635;
  border: 1px solid rgba(132, 204, 22, 0.3);
}

.status-pill.active .beacon-dot {
  background: #84cc16;
  box-shadow: 0 0 8px #84cc16;
}

.status-pill.inactive {
  background: rgba(245, 158, 11, 0.12);
  color: #fbbf24;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.status-pill.inactive .beacon-dot {
  background: #fbbf24;
}

.cancha-meta {
  display: flex;
  align-items: baseline;
  gap: 0.3rem;
  margin-bottom: 1.25rem;
}

.price-val {
  font-size: 1.4rem;
  font-weight: 800;
  color: #84cc16;
}

.price-unit {
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 600;
}

.action-row {
  display: flex;
  gap: 0.75rem;
}

.secondary-btn,
.primary-btn,
.submit-btn,
.btn-cancel {
  border: none;
  border-radius: 14px;
  font-weight: 800;
  font-size: 0.85rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.secondary-btn {
  flex: 1;
  background: rgba(255, 255, 255, 0.05);
  color: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.12);
  padding: 0.65rem 1rem;
}

.secondary-btn:hover {
  background: rgba(255, 255, 255, 0.12);
  color: #ffffff;
}

.primary-btn {
  flex: 1;
  background: rgba(132, 204, 22, 0.12);
  color: #ffffff;
  border: 1px solid rgba(132, 204, 22, 0.35);
  padding: 0.65rem 1rem;
}

.primary-btn:hover {
  background: #84cc16;
  color: #030712;
  box-shadow: 0 0 20px rgba(132, 204, 22, 0.4);
}

/* EDITOR FORM */
.editor-form {
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}

.field-block label {
  font-size: 0.68rem;
  font-weight: 800;
  color: #a3e635;
  display: block;
  margin-bottom: 0.4rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.input-glow-box input {
  width: 100%;
  height: 46px;
  background: #030712;
  border: 1px solid rgba(132, 204, 22, 0.25);
  border-radius: 14px;
  padding: 0 1rem;
  font-size: 0.875rem;
  color: #ffffff;
  outline: none;
  transition: all 0.25s ease;
}

.input-glow-box input:focus {
  border-color: #84cc16;
  box-shadow: 0 0 15px rgba(132, 204, 22, 0.25);
}

.switch-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #030712;
  border: 1px solid rgba(132, 204, 22, 0.25);
  border-radius: 14px;
  padding: 0.75rem 1rem;
}

.switch-row span {
  color: #ffffff;
  font-size: 0.85rem;
  font-weight: 600;
}

.neon-toggle {
  --background: rgba(255, 255, 255, 0.1);
  --background-checked: #84cc16;
  --handle-background: #ffffff;
  --handle-background-checked: #030712;
}

.form-actions {
  display: flex;
  gap: 0.75rem;
}

.btn-cancel {
  flex: 0.6;
  background: transparent;
  color: #94a3b8;
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 0.75rem 1rem;
}

.btn-cancel:hover {
  color: #ffffff;
  border-color: rgba(255, 255, 255, 0.25);
}

.submit-btn {
  flex: 1;
  background: #84cc16;
  color: #030712;
  padding: 0.75rem 1.25rem;
  box-shadow: 0 0 20px rgba(132, 204, 22, 0.3);
}

.submit-btn:hover {
  background: #a3e635;
  transform: translateY(-2px);
  box-shadow: 0 5px 25px rgba(132, 204, 22, 0.5);
}

.message-banner {
  margin-top: 1.25rem;
  padding: 0.85rem 1rem;
  border-radius: 14px;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  font-size: 0.825rem;
  font-weight: 700;
}

.message-banner.success {
  background: rgba(132, 204, 22, 0.1);
  border: 1px solid rgba(132, 204, 22, 0.3);
  color: #a3e635;
}

.message-banner ion-icon {
  font-size: 1.2rem;
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
  color: #94a3b8;
}

.empty-icon-glow {
  width: 54px;
  height: 54px;
  background: rgba(132, 204, 22, 0.12);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
  color: #84cc16;
  font-size: 1.6rem;
}

/* TRANSICIONES */
.fade-banner-enter-active,
.fade-banner-leave-active {
  transition: all 0.3s ease;
}

.fade-banner-enter-from,
.fade-banner-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

/* RESPONSIVE */
@media (max-width: 900px) {
  .management-layout {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 600px) {
  .management-wrapper {
    padding: 10rem 1rem 3rem;
  }
}
</style>