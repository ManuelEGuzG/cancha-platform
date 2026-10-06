<template>
  <ion-page class="panel-page">
    <!-- Navbar flotante estilo Cápsula Glass -->
    <header class="top-navbar-floating">
      <div class="navbar-pill">
        <!-- Logo Brand SPORTRA -->
        <div class="brand-logo">
          <div class="logo-circle-icon">
            <ion-icon name="football-outline"></ion-icon>
          </div>
          <span class="brand-name">SPORTRA<span class="dot-neon">.</span></span>
        </div>

        <!-- Acciones del Menú -->
        <div class="navbar-actions">
          <button class="nav-chip-btn" @click="irACanchas">
            <ion-icon name="tennisball-outline" class="chip-icon"></ion-icon>
            <span>Canchas</span>
          </button>

          <button class="nav-chip-btn danger" @click="cerrarSesion">
            <ion-icon name="log-out-outline" class="chip-icon"></ion-icon>
            <span>Salir</span>
          </button>
        </div>
      </div>
    </header>

    <ion-content class="panel-content">
      <!-- Glow Background Effects -->
      <div class="page-background-glow glow-float-1"></div>
      <div class="page-background-glow glow-float-2"></div>
      <div class="bg-grid"></div>

      <!-- Spinner Inicial -->
      <div v-if="cargandoInicial" class="loading-shell">
        <ion-spinner name="crescent" class="main-spinner"></ion-spinner>
      </div>

      <!-- Dashboard Shell -->
      <div v-else class="dashboard-shell">
        <section class="topbar">
          <div>
            <span class="eyebrow">Gestión Deportiva</span>
            <h1 class="page-title">{{ complejoActual?.nombre || 'Panel del Complejo' }}</h1>
          </div>
          <div class="topbar-actions">
            <button class="btn-primary-neon" @click="irACanchas">
              <ion-icon name="shapes-outline"></ion-icon>
              Gestionar Canchas
            </button>
          </div>
        </section>

        <!-- Selector de Complejos (Si tiene más de 1) -->
        <div v-if="misComplejos.length > 1" class="selector-card-glass">
          <label class="selector-label">Complejo activo</label>
          <ion-select 
            v-model="complejoIdSeleccionado" 
            @ionChange="cargarAgenda" 
            class="complex-select-dark"
            interface="popover"
          >
            <ion-select-option v-for="c in misComplejos" :key="c.id" :value="c.id">
              {{ c.nombre }}
            </ion-select-option>
          </ion-select>
        </div>

        <!-- Tarjetas de Estadísticas -->
        <section class="stats-grid" v-if="estadisticas">
          <div class="stat-card-glass">
            <div class="stat-header">
              <span class="stat-label">Canchas</span>
              <ion-icon name="shapes-outline" class="stat-icon"></ion-icon>
            </div>
            <strong>{{ estadisticas.total_canchas }}</strong>
          </div>
          <div class="stat-card-glass">
            <div class="stat-header">
              <span class="stat-label">Reservas Hoy</span>
              <ion-icon name="calendar-outline" class="stat-icon"></ion-icon>
            </div>
            <strong>{{ estadisticas.reservas_hoy }}</strong>
          </div>
          <div class="stat-card-glass">
            <div class="stat-header">
              <span class="stat-label">Este Mes</span>
              <ion-icon name="stats-chart-outline" class="stat-icon"></ion-icon>
            </div>
            <strong>{{ estadisticas.reservas_mes.total }}</strong>
          </div>
          <div class="stat-card-glass">
            <div class="stat-header"><span class="stat-label">Solicitudes aceptadas</span></div>
            <strong>{{ estadisticas.solicitudes_mes.aceptadas }}</strong>
          </div>
          <div class="stat-card-glass">
            <div class="stat-header"><span class="stat-label">Solicitudes rechazadas</span></div>
            <strong>{{ estadisticas.solicitudes_mes.rechazadas }}</strong>
          </div>
          <div class="stat-card-glass">
            <div class="stat-header"><span class="stat-label">Ingreso estimado</span></div>
            <strong>₡{{ Number(estadisticas.ingreso_estimado_mes).toLocaleString('es-CR') }}</strong>
          </div>
        </section>

        <div v-if="estadisticas?.demanda_por_hora?.length" class="demand-strip">
          <span>Horas con más demanda:</span>
          <strong v-for="hora in estadisticas.demanda_por_hora" :key="hora.hora">
            {{ hora.hora }} · {{ hora.reservas }}
          </strong>
        </div>

        <!-- Content Grid -->
        <section class="content-grid">
          <!-- Columna Agenda -->
          <div class="panel-card-glass wide-card">
            <div class="card-header">
              <div>
                <span class="card-kicker">Agenda</span>
                <h2 class="card-title">Horarios del Día</h2>
              </div>
            </div>

            <div v-if="agenda?.canchas?.length" class="agenda-list">
              <div v-for="cancha in agenda.canchas" :key="cancha.cancha_id" class="agenda-item">
                <div class="agenda-head">
                  <h3>{{ cancha.nombre }}</h3>
                  <span class="badge-deporte">{{ cancha.deporte }}</span>
                </div>

                <div class="timeline-list">
                  <div v-for="r in cancha.reservas" :key="r.id" class="timeline-row reservation">
                    <div class="timeline-time">
                      <ion-icon name="time-outline"></ion-icon>
                      {{ r.hora_inicio }} - {{ r.hora_fin }}
                    </div>
                    <div class="timeline-content">
                      <strong>{{ r.nombre_cliente }}</strong>
                      <small class="status-tag">{{ r.estado }}</small>
                    </div>
                    <div class="reservation-actions">
                      <template v-if="r.estado === 'pendiente' && r.solicitud_id">
                        <small v-if="r.expira_en">Vence {{ new Date(r.expira_en).toLocaleTimeString('es-CR', { hour: '2-digit', minute: '2-digit' }) }}</small>
                        <button class="mini-button-success" @click="responderSolicitud(r.id, 'aceptar')">Aceptar</button>
                        <button class="mini-button-danger" @click="responderSolicitud(r.id, 'rechazar')">Rechazar</button>
                      </template>
                      <button v-else class="mini-button-danger" @click="cancelar(r.id)">Cancelar</button>
                    </div>
                  </div>

                  <div v-for="b in cancha.bloqueos" :key="'b' + b.id" class="timeline-row blocked">
                    <div class="timeline-time">
                      <ion-icon name="lock-closed-outline"></ion-icon>
                      {{ b.hora_inicio }} - {{ b.hora_fin }}
                    </div>
                    <div class="timeline-content">
                      <strong>Bloqueado</strong>
                      <small>{{ b.motivo }}</small>
                    </div>
                  </div>
                </div>

                <p v-if="!cancha.reservas.length && !cancha.bloqueos.length" class="empty-inline">
                  Sin reservas ni bloqueos registrados para hoy.
                </p>
              </div>
            </div>

            <p v-else class="empty-message">No hay agenda disponible para este complejo.</p>
          </div>

          <!-- Columna Nueva Reserva -->
          <div class="panel-card-glass">
            <div class="card-header">
              <div>
                <span class="card-kicker">Operación</span>
                <h2 class="card-title">Nueva Reserva</h2>
              </div>
            </div>

            <div class="form-grid">
              <div class="field-group full">
                <label>Cancha</label>
                <ion-select 
                  v-model="nuevaReserva.cancha_id" 
                  interface="popover" 
                  placeholder="Selecciona una cancha" 
                  class="custom-input-dark"
                >
                  <ion-select-option v-for="cancha in agenda?.canchas" :key="cancha.cancha_id" :value="cancha.cancha_id">
                    {{ cancha.nombre }}
                  </ion-select-option>
                </ion-select>
              </div>

              <div class="field-group full">
                <label>Nombre del Cliente</label>
                <ion-input 
                  v-model="nuevaReserva.nombre_cliente" 
                  placeholder="Ej. Ana García" 
                  class="custom-input-dark"
                ></ion-input>
              </div>

              <div class="field-group full">
                <label>Teléfono</label>
                <ion-input 
                  v-model="nuevaReserva.telefono_cliente" 
                  placeholder="Opcional" 
                  class="custom-input-dark"
                ></ion-input>
              </div>

              <div class="field-group">
                <label>Fecha</label>
                <ion-input 
                  v-model="nuevaReserva.fecha" 
                  type="date" 
                  class="custom-input-dark"
                ></ion-input>
              </div>

              <div class="field-group">
                <label>Hora Inicio</label>
                <ion-input 
                  v-model="nuevaReserva.hora_inicio" 
                  type="time" 
                  class="custom-input-dark"
                ></ion-input>
              </div>

              <div class="field-group full">
                <label>Hora Fin</label>
                <ion-input 
                  v-model="nuevaReserva.hora_fin" 
                  type="time" 
                  class="custom-input-dark"
                ></ion-input>
              </div>
            </div>

            <button class="btn-submit-neon" @click="crearReserva">Guardar Reserva</button>

            <p v-if="mensajeReserva" :class="['message-banner', errorReserva ? 'error' : 'success']">
              <ion-icon :name="errorReserva ? 'alert-circle-outline' : 'checkmark-circle-outline'"></ion-icon>
              <span>{{ mensajeReserva }}</span>
            </p>
          </div>

        </section>

        <section class="panel-card-glass history-card">
          <div class="card-header">
            <div>
              <span class="card-kicker">Registro</span>
              <h2 class="card-title">Historial de reservas</h2>
            </div>
            <div class="history-filters">
              <input v-model="fechaHistorial" type="date" class="custom-input-dark" @change="cargarHistorial(1)" />
              <button class="nav-chip-btn" @click="limpiarFiltroHistorial">Todas</button>
            </div>
          </div>
          <div class="table-responsive">
            <table class="neon-table">
              <thead>
                <tr><th>Fecha</th><th>Cancha</th><th>Cliente</th><th>Horario</th><th>Estado</th></tr>
              </thead>
              <tbody>
                <tr v-for="reserva in historialReservas" :key="reserva.id">
                  <td>{{ reserva.fecha }}</td>
                  <td>{{ reserva.cancha?.nombre }}</td>
                  <td>{{ reserva.nombre_cliente }}</td>
                  <td>{{ reserva.hora_inicio }} - {{ reserva.hora_fin }}</td>
                  <td>{{ reserva.decision_propietario || reserva.estado }}</td>
                </tr>
                <tr v-if="historialReservas.length === 0">
                  <td colspan="5" class="text-center py-4">No hay reservas para este filtro.</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="history-pagination">
            <button class="nav-chip-btn" :disabled="paginaHistorial <= 1" @click="cargarHistorial(paginaHistorial - 1)">Anterior</button>
            <span>{{ paginaHistorial }} / {{ ultimaPaginaHistorial }}</span>
            <button class="nav-chip-btn" :disabled="paginaHistorial >= ultimaPaginaHistorial" @click="cargarHistorial(paginaHistorial + 1)">Siguiente</button>
          </div>
        </section>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import {
  IonPage, IonContent, IonSpinner, IonSelect, IonSelectOption, IonInput, IonIcon
} from '@ionic/vue';
import { addIcons } from 'ionicons';
import { 
  footballOutline, 
  tennisballOutline, 
  logOutOutline, 
  shapesOutline, 
  calendarOutline, 
  statsChartOutline, 
  timeOutline, 
  lockClosedOutline, 
  alertCircleOutline, 
  checkmarkCircleOutline 
} from 'ionicons/icons';
import { useAuthStore } from '@/stores/auth';
import panelService from '@/services/panel.service';

addIcons({
  'football-outline': footballOutline,
  'tennisball-outline': tennisballOutline,
  'log-out-outline': logOutOutline,
  'shapes-outline': shapesOutline,
  'calendar-outline': calendarOutline,
  'stats-chart-outline': statsChartOutline,
  'time-outline': timeOutline,
  'lock-closed-outline': lockClosedOutline,
  'alert-circle-outline': alertCircleOutline,
  'checkmark-circle-outline': checkmarkCircleOutline,
});

const router = useRouter();
const authStore = useAuthStore();

const misComplejos = ref<any[]>([]);
const complejoIdSeleccionado = ref<number | null>(null);
const complejoActual = ref<any>(null);
const agenda = ref<any>(null);
const estadisticas = ref<any>(null);
const cargandoInicial = ref(true);

const nuevaReserva = ref({
  cancha_id: null as number | null,
  nombre_cliente: '',
  telefono_cliente: '',
  fecha: new Date().toISOString().split('T')[0],
  hora_inicio: '',
  hora_fin: '',
});

const mensajeReserva = ref('');
const errorReserva = ref(false);
const historialReservas = ref<any[]>([]);
const fechaHistorial = ref('');
const paginaHistorial = ref(1);
const ultimaPaginaHistorial = ref(1);

async function cargarAgenda() {
  if (!complejoIdSeleccionado.value) return;

  complejoActual.value = misComplejos.value.find((c) => c.id === complejoIdSeleccionado.value);

  const [agendaRes, statsRes] = await Promise.all([
    panelService.agenda(complejoIdSeleccionado.value),
    panelService.estadisticas(complejoIdSeleccionado.value),
  ]);

  agenda.value = agendaRes.data.data;
  estadisticas.value = statsRes.data.data;
}

async function cargarHistorial(pagina = 1) {
  const { data } = await panelService.listarReservas({
    fecha: fechaHistorial.value || undefined,
    page: pagina,
  });
  historialReservas.value = data.data.data;
  paginaHistorial.value = data.data.current_page;
  ultimaPaginaHistorial.value = data.data.last_page;
}

async function limpiarFiltroHistorial() {
  fechaHistorial.value = '';
  await cargarHistorial(1);
}

async function crearReserva() {
  mensajeReserva.value = '';
  errorReserva.value = false;
  try {
    await panelService.crearReserva({
      cancha_id: nuevaReserva.value.cancha_id!,
      nombre_cliente: nuevaReserva.value.nombre_cliente,
      telefono_cliente: nuevaReserva.value.telefono_cliente,
      fecha: nuevaReserva.value.fecha,
      hora_inicio: nuevaReserva.value.hora_inicio,
      hora_fin: nuevaReserva.value.hora_fin,
    });
    mensajeReserva.value = 'Reserva creada correctamente.';
    await cargarAgenda();
    await cargarHistorial(1);
  } catch (e: any) {
    errorReserva.value = true;
    mensajeReserva.value = e.response?.data?.message || 'Error al crear la reserva.';
  }
}

async function cancelar(reservaId: number) {
  await panelService.cancelarReserva(reservaId);
  await cargarAgenda();
  await cargarHistorial(paginaHistorial.value);
}

async function responderSolicitud(reservaId: number, decision: 'aceptar' | 'rechazar') {
  try {
    await panelService.responderSolicitud(reservaId, decision);
    await cargarAgenda();
    await cargarHistorial(paginaHistorial.value);
  } catch (error: any) {
    mensajeReserva.value = error.response?.data?.message || 'No se pudo responder la solicitud.';
    errorReserva.value = true;
  }
}

async function cerrarSesion() {
  await authStore.logout();
  router.push('/login');
}

function irACanchas() {
  router.push({ path: '/panel/canchas', query: { complejoId: complejoIdSeleccionado.value } });
}

onMounted(async () => {
  try {
    const { data } = await panelService.misComplejos();
    misComplejos.value = data.data;

    if (misComplejos.value.length > 0) {
      complejoIdSeleccionado.value = misComplejos.value[0].id;
      await cargarAgenda();
    }
    await cargarHistorial();
  } catch (err) {
    console.error('Error cargando los complejos:', err);
  } finally {
    cargandoInicial.value = false;
  }
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700;800&family=Inter:wght@400;500;600;700;800&display=swap');

.panel-page {
  font-family: 'Inter', -apple-system, sans-serif;
  color: #f3f4f6;
  background-color: #030712;
}

/* NAVBAR CÁPSULA FLOTANTE (ESTILO SPORTRA) */
.top-navbar-floating {
  position: sticky;
  top: 0;
  left: 0;
  right: 0;
  z-index: 100;
  padding: 1.25rem 1.5rem 0.5rem;
  background: transparent;
}

.navbar-pill {
  max-width: 1200px;
  margin: 0 auto;
  background: rgba(11, 15, 25, 0.75);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 9999px;
  padding: 0.6rem 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
}

.brand-logo {
  display: flex;
  align-items: center;
  gap: 0.65rem;
}

.logo-circle-icon {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: 1px solid #84cc16;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(132, 204, 22, 0.08);
  box-shadow: 0 0 12px rgba(132, 204, 22, 0.3);
}

.logo-circle-icon ion-icon {
  color: #84cc16;
  font-size: 1.1rem;
}

.brand-name {
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 800;
  font-size: 1.2rem;
  letter-spacing: 0.04em;
  color: #ffffff;
}

.dot-neon {
  color: #84cc16;
}

/* ACCIONES DE NAVEGACIÓN */
.navbar-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.nav-chip-btn {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 9999px;
  padding: 0.45rem 1rem;
  color: #ffffff;
  font-size: 0.825rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.45rem;
  cursor: pointer;
  transition: all 0.25s ease;
}

.nav-chip-btn .chip-icon {
  font-size: 0.95rem;
  color: #84cc16;
}

.nav-chip-btn:hover {
  background: rgba(132, 204, 22, 0.15);
  border-color: rgba(132, 204, 22, 0.4);
  transform: translateY(-1px);
}

.nav-chip-btn.danger {
  background: rgba(239, 68, 68, 0.08);
  border-color: rgba(239, 68, 68, 0.2);
  color: #f87171;
}

.nav-chip-btn.danger .chip-icon {
  color: #f87171;
}

.nav-chip-btn.danger:hover {
  background: rgba(239, 68, 68, 0.2);
  border-color: rgba(239, 68, 68, 0.5);
  color: #ffffff;
}

/* VIEWPORT & BACKGROUND */
.panel-content {
  --background: #030712;
}

.page-background-glow {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
  filter: blur(100px);
}

.glow-float-1 {
  top: 5%;
  left: 10%;
  width: 450px;
  height: 450px;
  background: radial-gradient(circle, rgba(132, 204, 22, 0.08) 0%, rgba(3, 7, 18, 0) 70%);
}

.glow-float-2 {
  bottom: 10%;
  right: 15%;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(163, 230, 53, 0.05) 0%, rgba(3, 7, 18, 0) 70%);
}

.bg-grid {
  position: absolute;
  inset: 0;
  background-image: linear-gradient(to right, rgba(255,255,255,0.02) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(255,255,255,0.02) 1px, transparent 1px);
  background-size: 48px 48px;
  pointer-events: none;
}

/* SPINNER */
.loading-shell {
  min-height: 70vh;
  display: flex;
  justify-content: center;
  align-items: center;
}

.main-spinner {
  width: 42px;
  height: 42px;
  color: #84cc16;
}

/* DASHBOARD CONTAINER */
.dashboard-shell {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1.5rem 1.5rem 2.5rem;
  position: relative;
  z-index: 2;
}

.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.75rem;
}

.eyebrow {
  display: block;
  color: #84cc16;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 0.725rem;
  font-weight: 800;
}

.page-title {
  font-family: 'Space Grotesk', sans-serif;
  margin: 0.2rem 0 0;
  color: #ffffff;
  font-size: clamp(1.6rem, 3vw, 2.2rem);
  font-weight: 800;
  letter-spacing: -0.03em;
}

.topbar-actions {
  display: flex;
  gap: 0.75rem;
}

.btn-primary-neon {
  background: #84cc16;
  color: #030712;
  border: none;
  border-radius: 12px;
  padding: 0.75rem 1.25rem;
  font-weight: 800;
  font-size: 0.85rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 0 0 20px rgba(132, 204, 22, 0.25);
}

.btn-primary-neon:hover {
  background: #a3e635;
  transform: translateY(-2px);
  box-shadow: 0 0 30px rgba(132, 204, 22, 0.4);
}

.selector-card-glass {
  background: rgba(11, 15, 25, 0.8);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 1rem 1.25rem;
  margin-bottom: 1.5rem;
}

.selector-label {
  display: block;
  color: #84cc16;
  font-size: 0.725rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.4rem;
}

.complex-select-dark {
  background: #030712;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  color: #ffffff;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1.25rem;
  margin-bottom: 1.75rem;
}

.stat-card-glass {
  background: rgba(11, 15, 25, 0.8);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  padding: 1.25rem;
}

.stat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.stat-label {
  color: #94a3b8;
  font-size: 0.725rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-weight: 800;
}

.stat-icon {
  color: #84cc16;
  font-size: 1.2rem;
}

.stat-card-glass strong {
  font-family: 'Space Grotesk', sans-serif;
  color: #ffffff;
  font-size: clamp(1.5rem, 3vw, 2.2rem);
  font-weight: 800;
  letter-spacing: -0.04em;
}

.content-grid {
  display: grid;
  grid-template-columns: 1.6fr 1fr;
  gap: 1.5rem;
}

.panel-card-glass {
  background: rgba(11, 15, 25, 0.8);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 24px;
  padding: 1.5rem;
}

.wide-card {
  min-height: 100%;
}

.card-header {
  margin-bottom: 1.25rem;
}

.card-kicker {
  display: inline-block;
  color: #84cc16;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 0.7rem;
  font-weight: 800;
}

.card-title {
  font-family: 'Space Grotesk', sans-serif;
  margin: 0.2rem 0 0;
  color: #ffffff;
  font-size: 1.35rem;
  font-weight: 800;
}

.agenda-list {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.agenda-item {
  padding: 1.2rem;
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 18px;
  background: rgba(3, 7, 18, 0.6);
}

.agenda-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.agenda-head h3 {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 700;
  color: #ffffff;
}

.badge-deporte {
  font-size: 0.7rem;
  background: rgba(132, 204, 22, 0.12);
  border: 1px solid rgba(132, 204, 22, 0.3);
  color: #84cc16;
  padding: 0.25rem 0.6rem;
  border-radius: 999px;
  font-weight: 700;
}

.timeline-list {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}

.timeline-row {
  display: grid;
  grid-template-columns: 130px 1fr auto;
  gap: 0.75rem;
  align-items: center;
  padding: 0.75rem 0.9rem;
  border-radius: 12px;
  border: 1px solid transparent;
}

.timeline-row.reservation {
  background: rgba(132, 204, 22, 0.06);
  border-color: rgba(132, 204, 22, 0.15);
}

.timeline-row.blocked {
  background: rgba(255, 255, 255, 0.03);
  border-color: rgba(255, 255, 255, 0.06);
}

.timeline-time {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  font-weight: 700;
  color: #84cc16;
  font-size: 0.775rem;
}

.timeline-content {
  display: flex;
  flex-direction: column;
}

.timeline-content strong {
  font-size: 0.85rem;
  color: #ffffff;
}

.status-tag {
  font-size: 0.68rem;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 700;
}

.reservation-actions { display: flex; align-items: center; justify-content: flex-end; gap: 0.4rem; flex-wrap: wrap; }
.reservation-actions small { width: 100%; color: #fbbf24; text-align: right; }
.mini-button-success {
  background: rgba(34, 197, 94, 0.12);
  border: 1px solid rgba(34, 197, 94, 0.3);
  color: #86efac;
  border-radius: 6px;
  padding: 0.4rem 0.6rem;
  cursor: pointer;
}
.mini-button-success:hover { background: rgba(34, 197, 94, 0.22); }

.mini-button-danger {
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #f87171;
  padding: 0.4rem 0.75rem;
  font-size: 0.725rem;
  font-weight: 700;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.mini-button-danger:hover {
  background: rgba(239, 68, 68, 0.25);
  color: #ffffff;
}

.empty-inline,
.empty-message {
  margin: 0.6rem 0 0;
  color: #64748b;
  font-size: 0.825rem;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
}

.field-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.field-group.full {
  grid-column: 1 / -1;
}

.field-group label {
  color: #84cc16;
  font-weight: 800;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.custom-input-dark {
  --background: #030712;
  --color: #ffffff;
  --placeholder-color: #475569;
  --padding-start: 0.85rem;
  --padding-end: 0.85rem;
  min-height: 44px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  font-size: 0.875rem;
  transition: border-color 0.25s ease;
}

.custom-input-dark:focus-within {
  border-color: #84cc16;
}

.btn-submit-neon {
  width: 100%;
  margin-top: 1.25rem;
  background: #84cc16;
  color: #030712;
  border: none;
  border-radius: 12px;
  padding: 0.85rem;
  font-size: 0.9rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.25s ease;
  box-shadow: 0 0 20px rgba(132, 204, 22, 0.3);
}

.btn-submit-neon:hover {
  background: #a3e635;
  transform: translateY(-2px);
  box-shadow: 0 0 30px rgba(132, 204, 22, 0.45);
}

.message-banner {
  margin-top: 1rem;
  padding: 0.75rem 0.9rem;
  border-radius: 12px;
  font-size: 0.825rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.message-banner ion-icon {
  font-size: 1.1rem;
}

.message-banner.success {
  background: rgba(132, 204, 22, 0.12);
  border: 1px solid rgba(132, 204, 22, 0.3);
  color: #84cc16;
}

.message-banner.error {
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #f87171;
}

@media (max-width: 850px) {
  .top-navbar-floating {
    padding: 0.75rem 0.75rem 0.25rem;
  }

  .navbar-pill {
    padding: 0.5rem 0.85rem;
  }
  
  .stats-grid,
  .content-grid {
    grid-template-columns: 1fr;
  }

  .topbar {
    flex-direction: column;
    align-items: flex-start;
  }

  .timeline-row {
    grid-template-columns: 1fr;
  }
}
</style>