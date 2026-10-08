<template>
  <ion-page class="sportra-hybrid-app">
    <ion-content class="sportra-main-viewport" :scroll-y="true">
      <!-- HEADER Y HERO BANNER OSCURO SUPERIOR -->
      <header class="dark-top-section">
        <div class="header-container">
          <!-- Logo Brand SPORTRA -->
          <div class="brand-brand-text" @click="volverAlPanel" role="button" tabindex="0">
            <span class="brand-title">SPORTRA<span class="dot-blue">.</span></span>
            <small class="brand-sub">GESTIÓN DE CANCHAS</small>
          </div>

          <!-- Acciones del Menú (Volver al Panel) -->
          <div class="navbar-actions">
            <button class="nav-btn-danger-pill" type="button" @click="volverAlPanel">
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
                CONFIGURACIÓN DE DISPONIBILIDAD <span class="badge-separator">/</span> <span class="badge-subtext">Horarios y Bloqueos</span>
              </span>
              <h1 class="hero-main-title">Horarios y Bloqueos</h1>
              <p class="hero-description">
                <ion-icon name="time-outline" class="hero-location-icon"></ion-icon>
                Administra la programación semanal, excepciones y bloqueos de la instalación
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
            <span>Cargando configuración de horarios...</span>
          </div>

          <div v-else class="management-layout">
            
            <!-- COLUMNA IZQUIERDA: HORARIO REGULAR Y EXCEPCIONES -->
            <div class="left-column-sections">
              
              <!-- SECCIÓN HORARIO REGULAR -->
              <section class="white-panel-card mb-28">
                <div class="card-header-row">
                  <div>
                    <span class="section-kicker">PROGRAMACIÓN</span>
                    <h2 class="section-title">Horario regular</h2>
                  </div>
                </div>

                <div class="weekly-schedule-list">
                  <div v-for="dia in horariosSemanal" :key="dia.dia_semana" class="weekly-schedule-row">
                    <div class="weekday-toggle">
                      <span class="client-name-sm">{{ dia.nombre }}</span>
                      <ion-toggle v-model="dia.abierto" class="neon-toggle"></ion-toggle>
                    </div>
                    
                    <template v-if="dia.abierto">
                      <div class="schedule-inputs-group">
                        <div class="field-block-inline">
                          <label :for="`apertura-${dia.dia_semana}`" class="metric-label">Abre</label>
                          <div class="input-glow-box">
                            <ion-input :id="`apertura-${dia.dia_semana}`" v-model="dia.hora_apertura" type="time" class="light-date-input"></ion-input>
                          </div>
                        </div>
                        <div class="field-block-inline">
                          <label :for="`cierre-${dia.dia_semana}`" class="metric-label">Cierra</label>
                          <div class="input-glow-box">
                            <ion-input :id="`cierre-${dia.dia_semana}`" v-model="dia.hora_cierre" type="time" class="light-date-input"></ion-input>
                          </div>
                        </div>
                      </div>
                    </template>
                    <span v-else class="table-status-pill rechazada">Cerrado</span>
                  </div>
                </div>

                <button class="btn-action-primary full-btn mt-20" type="button" @click="guardarHorarioRegular">
                  Guardar horario semanal
                </button>
              </section>

              <!-- SECCIÓN EXCEPCIONES (FERIADOS Y EVENTOS) -->
              <section class="white-panel-card">
                <div class="card-header-row">
                  <div>
                    <span class="section-kicker">EXCEPCIONES</span>
                    <h2 class="section-title">Feriados y eventos</h2>
                  </div>
                </div>

                <div v-if="horariosExcepcion.length" class="cards-list mb-20">
                  <div v-for="ex in horariosExcepcion" :key="ex.id" class="cancha-item-card exception-item">
                    <div>
                      <span class="request-kicker">{{ ex.fecha }}</span>
                      <h3 class="client-name-sm">{{ ex.hora_apertura ? `${ex.hora_apertura} a ${ex.hora_cierre}` : 'Cerrado todo el día' }}</h3>
                      <p class="request-notes mt-4">{{ ex.motivo }}</p>
                    </div>
                    <button class="mini-btn danger" type="button" @click="eliminarExcepcion(ex.id)">Eliminar</button>
                  </div>
                </div>

                <div v-else class="empty-inline-box mb-20">
                  <ion-icon name="calendar-clear-outline"></ion-icon>
                  <span>No hay excepciones programadas.</span>
                </div>

                <div class="editor-form border-top-divider pt-20">
                  <div class="field-block">
                    <label class="metric-label">Fecha de la excepción</label>
                    <div class="input-glow-box">
                      <ion-input v-model="nuevaExcepcion.fecha" type="date" class="light-date-input full-w"></ion-input>
                    </div>
                  </div>

                  <div class="form-grid-two">
                    <div class="field-block">
                      <label class="metric-label">Hora apertura (Opcional)</label>
                      <div class="input-glow-box">
                        <ion-input v-model="nuevaExcepcion.hora_apertura" type="time" class="light-date-input full-w"></ion-input>
                      </div>
                    </div>
                    <div class="field-block">
                      <label class="metric-label">Hora cierre (Opcional)</label>
                      <div class="input-glow-box">
                        <ion-input v-model="nuevaExcepcion.hora_cierre" type="time" class="light-date-input full-w"></ion-input>
                      </div>
                    </div>
                  </div>

                  <div class="field-block">
                    <label class="metric-label">Motivo</label>
                    <div class="input-glow-box">
                      <ion-input v-model="nuevaExcepcion.motivo" type="text" class="light-date-input full-w" placeholder="Ej. Feriado, evento, mantenimiento"></ion-input>
                    </div>
                  </div>

                  <button class="btn-action-soft full-btn" type="button" @click="crearExcepcion">
                    <ion-icon name="add-outline"></ion-icon>
                    <span>Agregar excepción</span>
                  </button>
                </div>
              </section>

            </div>

            <!-- COLUMNA DERECHA: BLOQUEOS DE HORARIO -->
            <aside class="white-panel-card form-panel">
              <div class="card-header-row">
                <div>
                  <span class="section-kicker">BLOQUEO</span>
                  <h2 class="section-title">Bloquear horario</h2>
                </div>
              </div>

              <div class="editor-form">
                <div class="field-block">
                  <label class="metric-label">Fecha</label>
                  <div class="input-glow-box">
                    <ion-input v-model="nuevoBloqueo.fecha" type="date" class="light-date-input full-w"></ion-input>
                  </div>
                </div>

                <div class="form-grid-two">
                  <div class="field-block">
                    <label class="metric-label">Hora inicio</label>
                    <div class="input-glow-box">
                      <ion-input v-model="nuevoBloqueo.hora_inicio" type="time" class="light-date-input full-w"></ion-input>
                    </div>
                  </div>
                  <div class="field-block">
                    <label class="metric-label">Hora fin</label>
                    <div class="input-glow-box">
                      <ion-input v-model="nuevoBloqueo.hora_fin" type="time" class="light-date-input full-w"></ion-input>
                    </div>
                  </div>
                </div>

                <div class="field-block">
                  <label class="metric-label">Motivo del bloqueo</label>
                  <div class="input-glow-box select-wrapper-box">
                    <ion-select v-model="nuevoBloqueo.motivo" interface="popover" class="light-date-input full-w select-custom">
                      <ion-select-option value="mantenimiento">Mantenimiento</ion-select-option>
                      <ion-select-option value="evento">Evento</ion-select-option>
                      <ion-select-option value="reparacion">Reparación</ion-select-option>
                      <ion-select-option value="uso_interno">Uso interno</ion-select-option>
                      <ion-select-option value="otro">Otro</ion-select-option>
                    </ion-select>
                  </div>
                </div>

                <button class="btn-action-primary full-btn" type="button" @click="crearBloqueo">
                  Bloquear horario
                </button>
              </div>

              <!-- LISTADO DE BLOQUEOS ACTIVOS -->
              <div class="mt-24 border-top-divider pt-20">
                <h3 class="section-title-sm mb-12">Bloqueos futuros</h3>
                
                <div v-if="bloqueos.length" class="cards-list">
                  <div v-for="bloqueo in bloqueos" :key="bloqueo.id" class="cancha-item-card exception-item">
                    <div>
                      <span class="request-kicker">{{ bloqueo.fecha }}</span>
                      <h3 class="client-name-sm">{{ bloqueo.hora_inicio }} - {{ bloqueo.hora_fin }}</h3>
                      <p class="request-notes mt-4">{{ bloqueo.motivo }}</p>
                    </div>
                    <button class="mini-btn danger" type="button" @click="eliminarBloqueo(bloqueo.id)">Quitar</button>
                  </div>
                </div>

                <div v-else class="empty-inline-box">
                  <ion-icon name="lock-closed-outline"></ion-icon>
                  <span>No hay bloqueos futuros.</span>
                </div>
              </div>

              <!-- Banner de Mensaje -->
              <div v-if="mensaje || error" :class="['message-banner', error ? 'error' : 'success']" role="status">
                <ion-icon :name="error ? 'alert-circle-outline' : 'checkmark-circle-outline'"></ion-icon>
                <span>{{ error || mensaje }}</span>
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
import {
  IonPage, IonContent, IonInput, IonSpinner, IonSelect, IonSelectOption, IonIcon, IonToggle
} from '@ionic/vue';
import { addIcons } from 'ionicons';
import { 
  footballOutline, 
  arrowBackOutline, 
  checkmarkCircleOutline,
  alertCircleOutline,
  timeOutline,
  calendarClearOutline,
  addOutline,
  lockClosedOutline
} from 'ionicons/icons';
import horariosService from '@/services/horarios.service';
import panelService from '@/services/panel.service';

addIcons({
  'football-outline': footballOutline,
  'arrow-back-outline': arrowBackOutline,
  'checkmark-circle-outline': checkmarkCircleOutline,
  'alert-circle-outline': alertCircleOutline,
  'time-outline': timeOutline,
  'calendar-clear-outline': calendarClearOutline,
  'add-outline': addOutline,
  'lock-closed-outline': lockClosedOutline,
});

const route = useRoute();
const router = useRouter();
const canchaId = Number(route.params.canchaId);
const nombresDias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

const cargando = ref(true);
const horariosExcepcion = ref<any[]>([]);
const bloqueos = ref<any[]>([]);
const horariosSemanal = ref(nombresDias.map((nombre, dia_semana) => ({
  dia_semana,
  nombre,
  abierto: false,
  hora_apertura: '16:00',
  hora_cierre: '23:00',
})));
const mensaje = ref('');
const error = ref('');

const nuevaExcepcion = ref({ fecha: '', hora_apertura: '', hora_cierre: '', motivo: '' });
const nuevoBloqueo = ref({ fecha: '', hora_inicio: '', hora_fin: '', motivo: 'mantenimiento' });

function volverAlPanel() {
  router.push('/panel');
}

async function cargarHorarios() {
  cargando.value = true;
  try {
    const { data } = await horariosService.index(canchaId);
    horariosExcepcion.value = data.data.horarios_excepcion;
    bloqueos.value = data.data.bloqueos || [];
    const regulares = data.data.horarios_regulares as { dia_semana: number; hora_apertura: string; hora_cierre: string }[];
    horariosSemanal.value = nombresDias.map((nombre, dia_semana) => {
      const horario = regulares.find((item) => item.dia_semana === dia_semana);
      return {
        dia_semana,
        nombre,
        abierto: Boolean(horario),
        hora_apertura: horario?.hora_apertura.slice(0, 5) || '16:00',
        hora_cierre: horario?.hora_cierre.slice(0, 5) || '23:00',
      };
    });
  } catch (fallo: any) {
    error.value = fallo.response?.data?.message || 'No se pudieron cargar los horarios.';
  } finally {
    cargando.value = false;
  }
}

async function guardarHorarioRegular() {
  error.value = '';
  mensaje.value = '';
  const abiertos = horariosSemanal.value.filter((dia) => dia.abierto);
  if (!abiertos.length || abiertos.some((dia) => !dia.hora_apertura || !dia.hora_cierre || dia.hora_cierre <= dia.hora_apertura)) {
    error.value = 'Activa al menos un día y define horas válidas para cada día abierto.';
    return;
  }
  try {
    await horariosService.actualizarRegular(canchaId, abiertos.map(({ dia_semana, hora_apertura, hora_cierre }) => ({
      dia_semana,
      hora_apertura,
      hora_cierre,
    })));
    mensaje.value = 'Horario semanal actualizado correctamente.';
  } catch (fallo: any) {
    error.value = fallo.response?.data?.message || 'No se pudo guardar el horario semanal.';
  }
}

async function crearExcepcion() {
  error.value = '';
  mensaje.value = '';
  if (!nuevaExcepcion.value.fecha || !nuevaExcepcion.value.motivo
    || Boolean(nuevaExcepcion.value.hora_apertura) !== Boolean(nuevaExcepcion.value.hora_cierre)) {
    error.value = 'Ingresa fecha y motivo; para un horario parcial define apertura y cierre.';
    return;
  }
  try {
    await horariosService.crearExcepcion({
      cancha_id: canchaId,
      fecha: nuevaExcepcion.value.fecha,
      hora_apertura: nuevaExcepcion.value.hora_apertura || undefined,
      hora_cierre: nuevaExcepcion.value.hora_cierre || undefined,
      motivo: nuevaExcepcion.value.motivo,
    });
    mensaje.value = 'Excepción creada correctamente.';
    nuevaExcepcion.value = { fecha: '', hora_apertura: '', hora_cierre: '', motivo: '' };
    await cargarHorarios();
  } catch (fallo: any) {
    error.value = fallo.response?.data?.message || 'No se pudo crear la excepción.';
  }
}

async function eliminarExcepcion(id: number) {
  try {
    await horariosService.eliminarExcepcion(id);
    mensaje.value = 'Excepción eliminada.';
    await cargarHorarios();
  } catch (fallo: any) {
    error.value = fallo.response?.data?.message || 'No se pudo eliminar la excepción.';
  }
}

async function crearBloqueo() {
  error.value = '';
  mensaje.value = '';
  try {
    await panelService.crearBloqueo({
      cancha_id: canchaId,
      fecha: nuevoBloqueo.value.fecha,
      hora_inicio: nuevoBloqueo.value.hora_inicio,
      hora_fin: nuevoBloqueo.value.hora_fin,
      motivo: nuevoBloqueo.value.motivo,
    });
    mensaje.value = 'Cancha bloqueada correctamente.';
    nuevoBloqueo.value = { fecha: '', hora_inicio: '', hora_fin: '', motivo: 'mantenimiento' };
    await cargarHorarios();
  } catch (fallo: any) {
    error.value = fallo.response?.data?.message || 'No se pudo bloquear el horario.';
  }
}

async function eliminarBloqueo(id: number) {
  try {
    await panelService.eliminarBloqueo(id);
    mensaje.value = 'Bloqueo retirado.';
    await cargarHorarios();
  } catch (fallo: any) {
    error.value = fallo.response?.data?.message || 'No se pudo retirar el bloqueo.';
  }
}

onMounted(cargarHorarios);
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

/* Sección superior azul oscura idéntica a PanelPage y CanchasPage */
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

.left-column-sections {
  display: flex;
  flex-direction: column;
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

.section-title-sm {
  font-size: 15px;
  font-weight: 800;
  color: #091133;
  margin: 0;
  letter-spacing: -0.02em;
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

/* Horario Semanal Listado */
.weekly-schedule-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.weekly-schedule-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  gap: 16px;
}

.weekday-toggle {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 140px;
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

.schedule-inputs-group {
  display: flex;
  align-items: center;
  gap: 12px;
}

.field-block-inline {
  display: flex;
  align-items: center;
  gap: 8px;
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
  white-space: nowrap;
}

.light-date-input {
  --background: #ffffff;
  --color: #091133;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  min-height: 40px;
  font-size: 13px;
}

.full-w {
  width: 100%;
}

/* Tarjetas para Excepciones y Bloqueos */
.cards-list {
  display: grid;
  gap: 12px;
}

.cancha-item-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  background: #ffffff;
  transition: box-shadow 0.2s ease, border-color 0.2s ease;
}

.cancha-item-card:hover {
  border-color: #cbd5e1;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}

.request-kicker {
  font-size: 11px;
  font-weight: 800;
  color: #6366f1;
  text-transform: uppercase;
}

.request-notes {
  margin: 0;
  font-size: 12px;
  color: #475569;
}

.mini-btn.danger {
  background: #fee2e2;
  color: #b91c1c;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 800;
  cursor: pointer;
  border: 0;
}

.empty-inline-box {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 14px 16px;
  background: #f8fafc;
  border: 1px dashed #cbd5e1;
  border-radius: 12px;
  font-size: 12px;
  color: #64748b;
  font-weight: 600;
}

.editor-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-grid-two {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

/* Botones */
.btn-action-primary {
  padding: 10px 16px;
  background: #6366f1;
  border: 0;
  border-radius: 8px;
  color: #ffffff;
  font-size: 13px;
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

.btn-action-soft {
  padding: 10px 16px;
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  color: #334155;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: all 0.2s;
}

.btn-action-soft:hover {
  background: #e2e8f0;
}

.full-btn {
  width: 100%;
}

/* Utilidades de espaciado y estructura */
.mb-28 { margin-bottom: 28px; }
.mb-24 { margin-bottom: 24px; }
.mb-20 { margin-bottom: 20px; }
.mb-12 { margin-bottom: 12px; }
.mt-20 { margin-top: 20px; }
.mt-24 { margin-top: 24px; }
.mt-4 { margin-top: 4px; }
.pt-20 { padding-top: 20px; }
.border-top-divider { border-top: 1px solid #e2e8f0; }

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

.table-status-pill {
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
  padding: 4px 8px;
  border-radius: 4px;
  background: #f1f5f9;
  color: #64748b;
}

.table-status-pill.rechazada {
  background: #fee2e2;
  color: #b91c1c;
}

/* Responsivo */
@media (max-width: 1100px) {
  .dark-top-section { padding: 16px 20px 30px; }
  .light-body-section { padding: 24px 20px 60px; }
  .hero-banner-content { flex-direction: column; align-items: flex-start; gap: 20px; }
  .management-layout { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
  .weekly-schedule-row { flex-direction: column; align-items: flex-start; gap: 10px; }
  .weekday-toggle { width: 100%; }
  .schedule-inputs-group { width: 100%; justify-content: space-between; }
}
</style>