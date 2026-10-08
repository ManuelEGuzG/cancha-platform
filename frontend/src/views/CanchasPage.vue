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

                <div v-if="cancha.fotos?.length" class="court-photo-list">
                  <figure v-for="foto in cancha.fotos" :key="foto.id" class="court-photo-item">
                    <img :src="foto.url" :alt="foto.caption || `Foto de ${cancha.nombre}`" loading="lazy" />
                    <figcaption>{{ foto.estado_verificacion === 'aprobada' ? 'Publicada' : etiquetaVerificacion(foto.estado_verificacion) }}</figcaption>
                    <button class="mini-button-danger" :aria-label="`Eliminar foto de ${cancha.nombre}`" @click="eliminarFoto(foto.id)">Eliminar</button>
                  </figure>
                </div>

                <div class="action-row">
                  <button class="secondary-btn" @click="editar(cancha)">
                    <ion-icon :icon="createOutline"></ion-icon> Editar
                  </button>
                  <button class="primary-btn wave-effect" @click="verHorarios(cancha.id)">
                    Horarios <ion-icon :icon="arrowForwardOutline"></ion-icon>
                  </button>
                  <label class="secondary-btn">
                    <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
                    Subir foto
                    <input type="file" accept="image/jpeg,image/png,image/webp" class="photo-file-input" @change="subirFoto($event, cancha.id)" />
                  </label>
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

async function subirFoto(event: Event, canchaId: number) {
  const input = event.target as HTMLInputElement;
  const archivo = input.files?.[0];
  if (!archivo) return;

  try {
    await canchasService.subirFoto(canchaId, archivo);
    mostrarMensaje('Foto subida y enviada a revisión.');
    await cargarCanchas();
  } catch (error: any) {
    mostrarMensaje(error.response?.data?.message || 'No se pudo subir la foto.');
  } finally {
    input.value = '';
  }
}

async function eliminarFoto(fotoId: number) {
  try {
    await canchasService.eliminarFoto(fotoId);
    mostrarMensaje('Foto eliminada.');
    await cargarCanchas();
  } catch (error: any) {
    mostrarMensaje(error.response?.data?.message || 'No se pudo eliminar la foto.');
  }
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
/* BASE & LAYOUT */
ion-content.sportra-main-viewport {
  --background: #f1f6f1;
  font-family: 'DM Sans', -apple-system, sans-serif;
  color: #17251e;
}

/* NAVBAR */
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
  background: rgba(255, 255, 255, 0.96);
  border: 1px solid #d7e2d8;
  border-radius: 6px;
  padding: 0.5rem 0.6rem 0.5rem 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 8px 24px rgba(17, 42, 29, 0.1);
}

.brand-box {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  cursor: pointer;
  user-select: none;
}

.brand-badge {
  width: 36px;
  height: 36px;
  background: #17634b;
  border-radius: 5px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s ease;
}

.brand-box:hover .brand-badge {
  transform: scale(1.05);
}

.brand-icon {
  font-size: 1.2rem;
  color: #d4ed66;
}

.brand-name {
  font-family: 'Barlow Condensed', sans-serif;
  font-weight: 700;
  font-size: 1.3rem;
  color: #17251e;
}

.neon-dot {
  color: #17634b;
}

.btn-portal-glow {
  background: #ffffff;
  color: #17634b;
  border: 1px solid #d7e2d8;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  font-weight: 600;
  font-size: 0.825rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: background-color 0.18s ease, border-color 0.18s ease;
}

.btn-portal-glow:hover {
  background: #eef5ec;
  border-color: #c3d6c6;
}

/* DECORATIVOS DESACTIVADOS */
.page-background-glow,
.bg-grid {
  display: none;
}

/* MAIN CONTENT CONTAINER */
.management-wrapper {
  position: relative;
  z-index: 2;
  max-width: 1280px;
  margin: 0 auto;
  padding: 7rem 1.5rem 4rem;
}

.loading-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 8rem 0;
  gap: 1rem;
  color: #66736b;
  font-weight: 600;
}

.lime-spinner {
  color: #17634b;
  width: 40px;
  height: 40px;
}

.management-layout {
  display: grid;
  grid-template-columns: 1.35fr 0.9fr;
  gap: 1.5rem;
  align-items: start;
}

/* PANEL CARDS */
.panel-card {
  background: #ffffff;
  border: 1px solid #d7e2d8;
  border-radius: 6px;
  padding: 1.5rem;
  box-shadow: 0 10px 28px rgba(23, 49, 35, 0.06);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.4rem;
}

.eyebrow {
  display: inline-block;
  font-size: 0.68rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-weight: 700;
  color: #17634b;
}

.card-header h2 {
  margin: 0.15rem 0 0;
  color: #17251e;
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 1.7rem;
  font-weight: 700;
  line-height: 1.05;
}

.counter-pill {
  background: #17634b;
  color: #ffffff;
  font-size: 0.85rem;
  padding: 0.25rem 0.7rem;
  border-radius: 999px;
  font-weight: 700;
}

/* CARDS LIST & ITEM */
.cards-list {
  display: grid;
  gap: 0.9rem;
}

.cancha-card {
  border: 1px solid #d7e2d8;
  background: #fbfdfb;
  border-radius: 6px;
  padding: 1.15rem 1.25rem;
  transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.cancha-card:hover {
  border-color: #b9cfba;
  box-shadow: 0 6px 18px rgba(23, 49, 35, 0.08);
}

.cancha-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 0.6rem;
  margin-bottom: 0.8rem;
}

.cancha-top h3 {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 700;
  color: #17251e;
  font-family: 'Barlow Condensed', sans-serif;
}

.deporte-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  margin-top: 0.3rem;
  color: #66736b;
  font-size: 0.78rem;
  font-weight: 600;
}

.deporte-tag ion-icon {
  color: #17634b;
}

/* STATUS PILLS */
.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.28rem 0.65rem;
  border-radius: 4px;
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.beacon-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}

.status-pill.active {
  background: #e8f3e9;
  color: #17634b;
  border: 1px solid #c9dfcd;
}

.status-pill.active .beacon-dot {
  background: #17634b;
}

.status-pill.inactive {
  background: #f6efdd;
  color: #8a6d1d;
  border: 1px solid #e7d9b5;
}

.status-pill.inactive .beacon-dot {
  background: #8a6d1d;
}

.status-pill.review-pendiente {
  background: #f6efdd;
  color: #8a6d1d;
  border: 1px solid #e7d9b5;
}

.status-pill.review-aprobada {
  background: #e8f3e9;
  color: #17634b;
  border: 1px solid #c9dfcd;
}

.status-pill.review-rechazada {
  background: #fff0eb;
  color: #9d3c2f;
  border: 1px solid #efc5b9;
}

.review-observation {
  margin: 0 0 0.8rem;
  padding: 0.55rem 0.75rem;
  background: #fff0eb;
  border: 1px solid #efc5b9;
  border-radius: 4px;
  color: #9d3c2f;
  font-size: 0.8rem;
}

.cancha-meta {
  display: flex;
  align-items: baseline;
  gap: 0.35rem;
  margin-bottom: 1.1rem;
}

.price-val {
  font-size: 1.45rem;
  font-weight: 700;
  color: #17251e;
  font-family: 'Barlow Condensed', sans-serif;
}

.price-unit {
  font-size: 0.75rem;
  color: #8a968d;
  font-weight: 600;
}

.action-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.6rem;
}

.secondary-btn,
.primary-btn,
.submit-btn,
.btn-cancel {
  border: 1px solid transparent;
  border-radius: 4px;
  font-weight: 700;
  font-size: 0.82rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.45rem;
  transition: background-color 0.18s ease, border-color 0.18s ease;
}

.secondary-btn {
  flex: 1;
  background: #ffffff;
  color: #17634b;
  border-color: #d7e2d8;
  padding: 0.6rem 0.9rem;
}

.secondary-btn:hover {
  background: #eef5ec;
  border-color: #c3d6c6;
}

.primary-btn {
  flex: 1;
  background: #17634b;
  color: #ffffff;
  border-color: #17634b;
  padding: 0.6rem 0.9rem;
}

.primary-btn:hover {
  background: #103b2e;
}

/* EDITOR FORM */
.editor-form {
  display: flex;
  flex-direction: column;
  gap: 1.1rem;
}

.field-block label {
  font-size: 0.68rem;
  font-weight: 700;
  color: #66736b;
  display: block;
  margin-bottom: 0.4rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.input-glow-box input {
  width: 100%;
  height: 44px;
  background: #ffffff;
  border: 1px solid #d7e2d8;
  border-radius: 4px;
  padding: 0 0.9rem;
  font-size: 0.875rem;
  color: #17251e;
  outline: none;
  transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.input-glow-box input:focus {
  border-color: #17634b;
  box-shadow: 0 0 0 3px rgba(23, 99, 75, 0.13);
}

.switch-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f9fbf7;
  border: 1px solid #d7e2d8;
  border-radius: 4px;
  padding: 0.7rem 0.9rem;
}

.switch-row span {
  color: #17251e;
  font-size: 0.85rem;
  font-weight: 600;
}

.neon-toggle {
  --background: #dfe7dd;
  --background-checked: #17634b;
  --handle-background: #ffffff;
  --handle-background-checked: #ffffff;
}

.form-actions {
  display: flex;
  gap: 0.6rem;
}

.btn-cancel {
  flex: 0.6;
  background: #ffffff;
  color: #66736b;
  border-color: #d7e2d8;
  padding: 0.7rem 1rem;
}

.btn-cancel:hover {
  color: #17251e;
  border-color: #b9cfba;
}

.submit-btn {
  flex: 1;
  background: #d4ed66;
  color: #142219;
  border-color: #c2dd4e;
  padding: 0.7rem 1.2rem;
}

.submit-btn:hover {
  background: #c2dd4e;
}

.message-banner {
  margin-top: 1.15rem;
  padding: 0.8rem 1rem;
  border-radius: 4px;
  display: flex;
  align-items: center;
  gap: 0.55rem;
  font-size: 0.825rem;
  font-weight: 600;
}

.message-banner.success {
  background: #e8f3e9;
  border: 1px solid #c9dfcd;
  color: #17634b;
}

.message-banner ion-icon {
  font-size: 1.15rem;
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
  color: #66736b;
}

.empty-icon-glow {
  width: 52px;
  height: 52px;
  background: #e8f3e9;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
  color: #17634b;
  font-size: 1.5rem;
}

.court-photo-list { display: flex; flex-wrap: wrap; gap: 0.75rem; margin: 1rem 0; }
.court-photo-item { display: grid; gap: 0.35rem; width: min(100%, 150px); margin: 0; }
.court-photo-item img { width: 100%; aspect-ratio: 4 / 3; object-fit: cover; border-radius: 4px; border: 1px solid #d7e2d8; background: #eef3ec; }
.court-photo-item figcaption { color: #66736b; font-size: 0.75rem; }

.court-photo-item .mini-button-danger {
  background: #fff0eb;
  border: 1px solid #efc5b9;
  color: #9d3c2f;
  border-radius: 4px;
  padding: 0.3rem 0.6rem;
  font-size: 0.72rem;
  font-weight: 700;
  cursor: pointer;
}

.court-photo-item .mini-button-danger:hover {
  background: #f9ddd3;
}

.photo-file-input {
  position: absolute;
  width: 1px;
  height: 1px;
  opacity: 0;
  overflow: hidden;
  pointer-events: none;
}

label.secondary-btn {
  position: relative;
}

/* TRANSICIONES */
.fade-banner-enter-active,
.fade-banner-leave-active {
  transition: all 0.25s ease;
}

.fade-banner-enter-from,
.fade-banner-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

/* RESPONSIVE */
@media (max-width: 900px) {
  .management-layout {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 600px) {
  .management-wrapper {
    padding: 6rem 1rem 3rem;
  }

  .navbar-container {
    padding: 0 0.75rem;
  }
}
</style>