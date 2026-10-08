<template>
  <ion-page class="sportra-hybrid-app">
    <ion-content class="sportra-main-viewport" :scroll-y="true">
      <!-- HEADER Y HERO BANNER OSCURO SUPERIOR -->
      <header class="dark-top-section">
        <div class="header-container">
          <!-- Logo Brand SPORTRA -->
          <div class="brand-brand-text" @click="irAlPanel" role="button" tabindex="0">
            <span class="brand-title">SPORTRA<span class="dot-blue">.</span></span>
            <small class="brand-sub">GESTIÓN DE CANCHAS</small>
          </div>

          <!-- Acciones del Menú (Volver al Panel) -->
          <div class="navbar-actions">
            <button class="nav-btn-danger-pill" type="button" @click="irAlPanel">
              <ion-icon name="arrow-back-outline"></ion-icon>
              <span>Volver al Panel</span>
            </button>
          </div>
        </div>

        <!-- Banner Hero Contenido Oscuro -->
        <div class="hero-card-banner">
          <div class="hero-banner-content">
            <div class="hero-left-info">
              <span class="platform-badge">
                INVENTARIO DE INSTALACIONES <span class="badge-separator">/</span> <span class="badge-subtext">Canchas y Horarios</span>
              </span>
              <h1 class="hero-main-title">Gestión de Canchas</h1>
              <p class="hero-description">
                <ion-icon name="tennisball-outline" class="hero-location-icon"></ion-icon>
                Administra tarifas, estados y fotografías del complejo deportivo
              </p>
            </div>
          </div>
        </div>
      </header>

      <!-- CUERPO CLARO CON TARJETAS BLANCAS Y BORDES LIMPIOS -->
      <main class="light-body-section">
        <div class="body-container">
          
          <!-- Loader Inicial -->
          <div v-if="cargando" class="loading-state-box">
            <ion-spinner name="crescent" class="main-spinner"></ion-spinner>
            <span>Cargando instalaciones...</span>
          </div>

          <div v-else class="management-layout">
            
            <!-- LISTADO DE CANCHAS -->
            <section class="white-panel-card main-panel">
              <div class="card-header-row">
                <div>
                  <span class="section-kicker">INVENTARIO</span>
                  <h2 class="section-title">Listado de Canchas</h2>
                </div>
                <span class="badge-count-blue">{{ canchas.length }}</span>
              </div>

              <div v-if="canchas.length" class="cards-list">
                <article v-for="cancha in canchas" :key="cancha.id" class="cancha-item-card">
                  <div class="cancha-main-info">
                    <div class="cancha-title-row">
                      <div>
                        <span class="request-kicker">
                          <ion-icon name="football-outline"></ion-icon>
                          {{ cancha.deporte?.nombre || 'Deporte' }}
                        </span>
                        <h3 class="client-name">{{ cancha.nombre }}</h3>
                      </div>
                      <div class="cancha-badges-group">
                        <span :class="['table-status-pill', cancha.activa ? 'confirmada' : 'rechazada']">
                          {{ cancha.activa ? 'Activa' : 'Inactiva' }}
                        </span>
                        <span :class="['table-status-pill', `review-${cancha.estado_verificacion}`]">
                          {{ etiquetaVerificacion(cancha.estado_verificacion) }}
                        </span>
                      </div>
                    </div>

                    <p v-if="cancha.observaciones_admin" class="request-notes error-note">
                      <ion-icon name="alert-circle-outline"></ion-icon>
                      {{ cancha.observaciones_admin }}
                    </p>

                    <div class="cancha-meta-row">
                      <span class="metric-main-number">₡{{ formatearPrecio(cancha.precio_hora) }}</span>
                      <small class="price-unit">/ hora</small>
                    </div>

                    <div v-if="cancha.fotos?.length" class="court-photo-list">
                      <figure v-for="foto in cancha.fotos" :key="foto.id" class="court-photo-item">
                        <img :src="foto.url" :alt="foto.caption || `Foto de ${cancha.nombre}`" loading="lazy" />
                        <figcaption>{{ foto.estado_verificacion === 'aprobada' ? 'Publicada' : etiquetaVerificacion(foto.estado_verificacion) }}</figcaption>
                        <button class="mini-btn danger" type="button" @click="eliminarFoto(foto.id)">Eliminar</button>
                      </figure>
                    </div>
                  </div>

                  <div class="request-actions">
                    <button class="btn-action-soft" type="button" @click="editar(cancha)">
                      <ion-icon name="create-outline"></ion-icon>
                      <span>Editar</span>
                    </button>
                    <button class="btn-action-primary" type="button" @click="verHorarios(cancha.id)">
                      <span>Horarios</span>
                      <ion-icon name="arrow-forward-outline"></ion-icon>
                    </button>
                    <label class="btn-action-soft whatsapp photo-label-btn">
                      <ion-icon name="camera-outline"></ion-icon>
                      <span>Subir foto</span>
                      <input type="file" accept="image/jpeg,image/png,image/webp" class="photo-file-input" @change="subirFoto($event, cancha.id)" />
                    </label>
                  </div>
                </article>
              </div>

              <div v-else class="empty-state-card">
                <div class="empty-icon-wrap">
                  <ion-icon name="football-outline"></ion-icon>
                </div>
                <p class="empty-title">Sin canchas registradas</p>
                <span class="empty-sub">Aún no hay canchas configuradas para este complejo.</span>
              </div>
            </section>

            <!-- PANEL DE CONFIGURACIÓN / EDICIÓN -->
            <aside class="white-panel-card form-panel">
              <div class="card-header-row">
                <div>
                  <span class="section-kicker">CONFIGURACIÓN</span>
                  <h2 class="section-title">{{ canchaEditando ? 'Editar Cancha' : 'Nueva Cancha' }}</h2>
                </div>
              </div>

              <div v-if="canchaEditando" class="editor-form">
                <div class="field-block">
                  <label class="metric-label">Nombre de la cancha</label>
                  <div class="input-glow-box">
                    <ion-input v-model="formEdicion.nombre" type="text" class="light-date-input full-w" placeholder="Ej: Cancha 1 (Sintética)"></ion-input>
                  </div>
                </div>

                <div class="field-block">
                  <label class="metric-label">Precio por hora (₡)</label>
                  <div class="input-glow-box">
                    <ion-input v-model.number="formEdicion.precio_hora" type="number" class="light-date-input full-w" placeholder="18000"></ion-input>
                  </div>
                </div>

                <div class="switch-row">
                  <span class="client-name-sm">Estado de la cancha</span>
                  <ion-toggle v-model="formEdicion.activa" class="neon-toggle"></ion-toggle>
                </div>

                <div class="form-actions-grid">
                  <button class="btn-action-soft" type="button" @click="cancelarEdicion">Cancelar</button>
                  <button class="btn-action-primary" type="button" @click="guardarEdicion">Guardar cambios</button>
                </div>
              </div>

              <div v-else class="editor-form">
                <div class="field-block">
                  <label class="metric-label">Nombre de la cancha</label>
                  <div class="input-glow-box">
                    <ion-input v-model="nuevaCancha.nombre" type="text" class="light-date-input full-w" placeholder="Ej: Cancha 1"></ion-input>
                  </div>
                </div>

                <div class="field-block">
                  <label class="metric-label">Precio por hora (₡)</label>
                  <div class="input-glow-box">
                    <ion-input v-model.number="nuevaCancha.precio_hora" type="number" class="light-date-input full-w" placeholder="18000"></ion-input>
                  </div>
                </div>

                <button class="btn-action-primary full-btn" type="button" @click="crearCancha">Crear Cancha</button>
              </div>

              <div v-if="mensaje" :class="['message-banner', errorMensaje ? 'error' : 'success']">
                <ion-icon :name="errorMensaje ? 'alert-circle-outline' : 'checkmark-circle-outline'"></ion-icon>
                <span>{{ mensaje }}</span>
              </div>
            </aside>

          </div>
        </div>
      </main>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { IonPage, IonContent, IonToggle, IonSpinner, IonInput, IonIcon } from '@ionic/vue';
import { addIcons } from 'ionicons';
import { 
  footballOutline, 
  tennisballOutline,
  arrowBackOutline, 
  arrowForwardOutline, 
  createOutline, 
  checkmarkCircleOutline,
  alertCircleOutline,
  cameraOutline
} from 'ionicons/icons';
import canchasService from '@/services/canchas.service';

addIcons({
  'football-outline': footballOutline,
  'tennisball-outline': tennisballOutline,
  'arrow-back-outline': arrowBackOutline,
  'arrow-forward-outline': arrowForwardOutline,
  'create-outline': createOutline,
  'checkmark-circle-outline': checkmarkCircleOutline,
  'alert-circle-outline': alertCircleOutline,
  'camera-outline': cameraOutline,
});

const route = useRoute();
const router = useRouter();
const complejoId = Number(route.query.complejoId);

const canchas = ref<any[]>([]);
const cargando = ref(true);
const mensaje = ref('');
const errorMensaje = ref(false);

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
  try {
    await canchasService.actualizar(canchaEditando.value.id, formEdicion.value);
    mostrarMensaje('Cancha actualizada con éxito.', false);
    canchaEditando.value = null;
    await cargarCanchas();
  } catch (error: any) {
    mostrarMensaje(error.response?.data?.message || 'No se pudo actualizar la cancha.', true);
  }
}

async function subirFoto(event: Event, canchaId: number) {
  const input = event.target as HTMLInputElement;
  const archivo = input.files?.[0];
  if (!archivo) return;

  try {
    await canchasService.subirFoto(canchaId, archivo);
    mostrarMensaje('Foto subida y enviada a revisión.', false);
    await cargarCanchas();
  } catch (error: any) {
    mostrarMensaje(error.response?.data?.message || 'No se pudo subir la foto.', true);
  } finally {
    input.value = '';
  }
}

async function eliminarFoto(fotoId: number) {
  try {
    await canchasService.eliminarFoto(fotoId);
    mostrarMensaje('Foto eliminada.', false);
    await cargarCanchas();
  } catch (error: any) {
    mostrarMensaje(error.response?.data?.message || 'No se pudo eliminar la foto.', true);
  }
}

async function crearCancha() {
  if (!nuevaCancha.value.nombre || !nuevaCancha.value.precio_hora) {
    mostrarMensaje('Completa todos los campos obligatorios.', true);
    return;
  }
  try {
    await canchasService.crear({
      complejo_id: complejoId,
      deporte_id: 1, // Fútbol por defecto
      nombre: nuevaCancha.value.nombre,
      precio_hora: nuevaCancha.value.precio_hora,
    });
    mostrarMensaje('Cancha creada con éxito.', false);
    nuevaCancha.value = { nombre: '', precio_hora: 0 };
    await cargarCanchas();
  } catch (error: any) {
    mostrarMensaje(error.response?.data?.message || 'No se pudo crear la cancha.', true);
  }
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

function mostrarMensaje(text: string, esError = false) {
  mensaje.value = text;
  errorMensaje.value = esError;
  setTimeout(() => {
    mensaje.value = '';
  }, 4000);
}

onMounted(cargarCanchas);
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.sportra-hybrid-app,
.sportra-main-viewport,
.sportra-hybrid-app ion-content {
  --background: #f4f6fc !important;
  background-color: #f4f6fc !important;
  font-family: 'Plus Jakarta Sans', -apple-system, sans-serif !important;
  color: #091133;
}

/* Sección superior azul oscura idéntica al PanelPage */
.dark-top-section {
  background: linear-gradient(180deg, #040924 0%, #081039 100%);
  color: #ffffff;
  padding: 24px 48px 36px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.header-container {
  max-width: 1400px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 28px;
}

.brand-brand-text {
  cursor: pointer;
  display: flex;
  flex-direction: column;
}

.brand-title {
  font-size: 22px;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: #ffffff;
  line-height: 1;
}

.brand-title .dot-blue {
  color: #5b7eff;
}

.brand-sub {
  font-size: 9px;
  font-weight: 800;
  letter-spacing: 0.14em;
  color: #64748b;
  margin-top: 4px;
}

.navbar-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.nav-btn-danger-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 18px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 99px;
  color: #cbd5e1;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.nav-btn-danger-pill:hover {
  background: rgba(99, 102, 241, 0.15);
  color: #93c5fd;
  border-color: rgba(99, 102, 241, 0.3);
}

/* Banner Hero */
.hero-card-banner {
  max-width: 1400px;
  margin: 0 auto;
}

.hero-banner-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 32px;
}

.platform-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.05em;
  color: #64748b;
  margin-bottom: 12px;
  text-transform: uppercase;
}

.badge-separator {
  color: #475569;
}

.badge-subtext {
  color: #94a3b8;
}

.hero-main-title {
  font-size: 34px;
  font-weight: 800;
  margin: 0 0 10px;
  color: #ffffff;
  letter-spacing: -0.03em;
}

.hero-description {
  font-size: 13px;
  color: #94a3b8;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 500;
}

.hero-location-icon {
  font-size: 16px;
  color: #60a5fa;
}

/* Cuerpo claro */
.light-body-section {
  padding: 28px 48px 80px;
}

.body-container {
  max-width: 1400px;
  margin: 0 auto;
}

.management-layout {
  display: grid;
  grid-template-columns: 1.4fr 0.9fr;
  gap: 28px;
  align-items: start;
}

.white-panel-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 28px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.card-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
}

.section-kicker {
  display: block;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.12em;
  color: #6366f1;
  margin-bottom: 4px;
}

.section-title {
  font-size: 20px;
  font-weight: 800;
  color: #091133;
  margin: 0;
  letter-spacing: -0.02em;
}

.badge-count-blue {
  background: #eef2ff;
  color: #4338ca;
  font-size: 13px;
  font-weight: 800;
  padding: 4px 12px;
  border-radius: 99px;
}

.loading-state-box {
  min-height: 50vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 12px;
  color: #64748b;
  font-size: 14px;
}

.main-spinner {
  width: 40px;
  height: 40px;
  color: #6366f1;
}

/* Tarjetas de Canchas individuales estilizadas como PanelPage */
.cards-list {
  display: grid;
  gap: 16px;
}

.cancha-item-card {
  display: grid;
  grid-template-columns: 1fr auto;
  align-items: center;
  gap: 20px;
  padding: 20px;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  background: #ffffff;
  transition: box-shadow 0.2s ease, border-color 0.2s ease;
}

.cancha-item-card:hover {
  border-color: #cbd5e1;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}

.cancha-title-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
}

.request-kicker {
  font-size: 11px;
  font-weight: 800;
  color: #6366f1;
  text-transform: uppercase;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.client-name {
  margin: 2px 0 0;
  font-size: 18px;
  font-weight: 800;
  color: #091133;
}

.cancha-badges-group {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.cancha-meta-row {
  display: flex;
  align-items: baseline;
  gap: 6px;
  margin-top: 12px;
}

.metric-main-number {
  font-size: 24px;
  font-weight: 800;
  color: #0f172a;
  letter-spacing: -0.02em;
  line-height: 1;
}

.price-unit {
  font-size: 12px;
  color: #94a3b8;
  font-weight: 600;
}

.error-note {
  background: #fef2f2 !important;
  color: #991b1b !important;
  border: 1px solid #fecaca;
  margin-top: 8px;
}

.request-notes {
  margin: 8px 0 0;
  font-size: 12px;
  color: #475569;
  background: #f8fafc;
  padding: 6px 10px;
  border-radius: 6px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

/* Acciones laterales de cada cancha */
.request-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
  width: 150px;
}

.btn-action-primary {
  padding: 9px 14px;
  background: #6366f1;
  border: 0;
  border-radius: 8px;
  color: #ffffff;
  font-size: 12px;
  font-weight: 800;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: background 0.2s;
}

.btn-action-primary:hover {
  background: #4f46e5;
}

.full-btn {
  width: 100%;
  margin-top: 8px;
}

.btn-action-soft {
  padding: 9px 14px;
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  color: #334155;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  text-decoration: none;
  transition: all 0.2s;
}

.btn-action-soft:hover {
  background: #e2e8f0;
}

.btn-action-soft.whatsapp {
  background: #f0fdf4;
  border-color: #bbf7d0;
  color: #166534;
}

.btn-action-soft.whatsapp:hover {
  background: #dcfce7;
}

.mini-btn.danger {
  background: #fee2e2;
  color: #b91c1c;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 800;
  cursor: pointer;
  border: 0;
}

/* Galería de fotos dentro de la tarjeta */
.court-photo-list {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 14px;
}

.court-photo-item {
  display: grid;
  gap: 4px;
  width: 120px;
  margin: 0;
  background: #f8fafc;
  padding: 6px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.court-photo-item img {
  width: 100%;
  aspect-ratio: 4 / 3;
  object-fit: cover;
  border-radius: 6px;
}

.court-photo-item figcaption {
  color: #64748b;
  font-size: 10px;
  font-weight: 700;
  text-align: center;
}

/* Formulario de Configuración / Edición */
.editor-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.field-block {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.metric-label {
  font-size: 12px;
  font-weight: 700;
  color: #64748b;
}

.light-date-input {
  --background: #ffffff;
  --color: #091133;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  min-height: 42px;
  font-size: 13px;
}

.full-w {
  width: 100%;
}

.switch-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 12px 16px;
}

.client-name-sm {
  font-size: 13px;
  font-weight: 700;
  color: #091133;
}

.neon-toggle {
  --background: #cbd5e1;
  --background-checked: #6366f1;
  --handle-background: #ffffff;
}

.form-actions-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-top: 6px;
}

.message-banner {
  margin-top: 16px;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 8px;
}

.message-banner.success {
  background: #dcfce7;
  color: #15803d;
}

.message-banner.error {
  background: #fee2e2;
  color: #b91c1c;
}

/* Estados vacíos */
.empty-state-card {
  text-align: center;
  padding: 48px 24px;
  background: #f8fafc;
  border: 1px dashed #cbd5e1;
  border-radius: 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.empty-icon-wrap {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #eef2ff;
  color: #6366f1;
  display: grid;
  place-items: center;
  font-size: 24px;
  margin-bottom: 12px;
}

.empty-title {
  margin: 0;
  font-size: 16px;
  font-weight: 800;
  color: #091133;
}

.empty-sub {
  font-size: 13px;
  color: #64748b;
  margin-top: 4px;
}

/* Badges de estado */
.table-status-pill {
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
  padding: 4px 8px;
  border-radius: 4px;
  background: #f1f5f9;
  color: #64748b;
}

.table-status-pill.confirmada,
.table-status-pill.review-aprobada {
  background: #dcfce7;
  color: #15803d;
}

.table-status-pill.review-pendiente {
  background: #fef3c7;
  color: #b45309;
}

.table-status-pill.rechazada,
.table-status-pill.review-rechazada {
  background: #fee2e2;
  color: #b91c1c;
}

.photo-file-input {
  display: none;
}

.photo-label-btn {
  cursor: pointer;
}

/* Responsivo */
@media (max-width: 1100px) {
  .dark-top-section { padding: 16px 20px 30px; }
  .light-body-section { padding: 24px 20px 60px; }
  .hero-banner-content { flex-direction: column; align-items: flex-start; gap: 20px; }
  .management-layout { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
  .cancha-item-card { grid-template-columns: 1fr; }
  .request-actions { width: 100%; flex-direction: row; }
}
</style>