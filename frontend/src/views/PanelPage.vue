<template>
  <ion-page class="panel-page">
    <!-- Navbar Flotante Glassmorphism -->
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
          <button class="nav-chip-btn" type="button" @click="irACanchas">
            <ion-icon name="tennisball-outline" class="chip-icon"></ion-icon>
            <span class="btn-text">Canchas</span>
          </button>

          <button class="nav-chip-btn danger" type="button" @click="cerrarSesion">
            <ion-icon name="log-out-outline" class="chip-icon"></ion-icon>
            <span class="btn-text">Salir</span>
          </button>
        </div>
      </div>
    </header>

    <ion-content class="panel-content" :scroll-y="true">
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
        <!-- Topbar Cabecera -->
        <section class="topbar">
          <div class="title-block">
            <span class="eyebrow">
              <span class="pulse-indicator"></span>
              Gestión Deportiva
            </span>
            <h1 class="page-title">{{ complejoActual?.nombre || 'Panel del Complejo' }}</h1>
          </div>
        </section>

        <!-- Selector de Complejos (Si tiene más de 1) -->
        <div v-if="misComplejos.length > 1" class="selector-card-glass">
          <div class="selector-info">
            <ion-icon name="business-outline" class="selector-icon"></ion-icon>
            <label class="selector-label" for="select-complejo">Complejo activo</label>
          </div>
          <ion-select 
            id="select-complejo"
            v-model="complejoIdSeleccionado" 
            class="complex-select-dark"
            interface="popover"
            @ionChange="cambiarComplejo" 
          >
            <ion-select-option v-for="c in misComplejos" :key="c.id" :value="c.id">
              {{ c.nombre }}
            </ion-select-option>
          </ion-select>
        </div>

        <!-- Tarjetas de Estadísticas principales -->
        <section v-if="estadisticas" class="stats-grid">
          <div class="stat-card-glass">
            <div class="stat-header">
              <span class="stat-label">Canchas</span>
              <div class="icon-wrapper">
                <ion-icon name="shapes-outline" class="stat-icon"></ion-icon>
              </div>
            </div>
            <strong class="stat-value">{{ estadisticas.total_canchas }}</strong>
          </div>

          <div class="stat-card-glass highlight">
            <div class="stat-header">
              <span class="stat-label">Reservas Hoy</span>
              <div class="icon-wrapper neon">
                <ion-icon name="calendar-outline" class="stat-icon"></ion-icon>
              </div>
            </div>
            <strong class="stat-value text-neon">{{ estadisticas.reservas_hoy }}</strong>
          </div>

          <div class="stat-card-glass">
            <div class="stat-header">
              <span class="stat-label">Reservas Mes</span>
              <div class="icon-wrapper">
                <ion-icon name="stats-chart-outline" class="stat-icon"></ion-icon>
              </div>
            </div>
            <strong class="stat-value">{{ estadisticas.reservas_mes?.total ?? 0 }}</strong>
          </div>

          <div class="stat-card-glass">
            <div class="stat-header">
              <span class="stat-label">Aceptadas</span>
              <div class="icon-wrapper success">
                <ion-icon name="checkmark-circle-outline" class="stat-icon"></ion-icon>
              </div>
            </div>
            <strong class="stat-value text-success">{{ estadisticas.solicitudes_mes?.aceptadas ?? 0 }}</strong>
          </div>

          <div class="stat-card-glass">
            <div class="stat-header">
              <span class="stat-label">Rechazadas</span>
              <div class="icon-wrapper danger">
                <ion-icon name="close-circle-outline" class="stat-icon"></ion-icon>
              </div>
            </div>
            <strong class="stat-value text-danger">{{ estadisticas.solicitudes_mes?.rechazadas ?? 0 }}</strong>
          </div>

          <div class="stat-card-glass revenue-card">
            <div class="stat-header">
              <span class="stat-label">Ingreso estimado</span>
              <div class="icon-wrapper gold">
                <ion-icon name="cash-outline" class="stat-icon"></ion-icon>
              </div>
            </div>
            <strong class="stat-value text-gold">₡{{ Number(estadisticas.ingreso_estimado_mes || 0).toLocaleString('es-CR') }}</strong>
          </div>
        </section>

        <!-- Tira de demanda por hora -->
        <div v-if="estadisticas?.demanda_por_hora?.length" class="demand-strip">
          <div class="demand-title">
            <ion-icon name="flame-outline" class="flame-icon"></ion-icon>
            <span>Horas pico:</span>
          </div>
          <div class="demand-tags">
            <span v-for="hora in estadisticas.demanda_por_hora" :key="hora.hora" class="demand-pill">
              <span class="hour">{{ hora.hora }}</span>
              <span class="count">{{ hora.reservas }} res.</span>
            </span>
          </div>
        </div>

        <!-- Seccion Solicitudes Pendientes -->
        <section class="requests-section">
          <div class="panel-card-glass requests-panel">
            <div class="card-header request-header">
              <div>
                <span class="card-kicker">Bandeja del complejo</span>
                <h2 class="card-title">Solicitudes Pendientes</h2>
              </div>
              <span class="request-count">{{ solicitudesPendientes.length }}</span>
            </div>

            <div v-if="cargandoSolicitudes" class="request-loading">
              <ion-spinner name="crescent" class="main-spinner-sm"></ion-spinner>
              <span>Actualizando bandeja...</span>
            </div>
            
            <div v-else-if="errorSolicitudes" class="empty-state-card error" role="alert">
              <ion-icon name="alert-circle-outline"></ion-icon>
              <p>{{ errorSolicitudes }}</p>
            </div>

            <div v-else-if="solicitudesPendientes.length === 0" class="empty-state-card">
              <div class="empty-icon-wrap">
                <ion-icon name="checkmark-done-circle-outline"></ion-icon>
              </div>
              <p class="empty-title">¡Todo al día!</p>
              <span class="empty-sub">No hay solicitudes pendientes de respuesta por el momento.</span>
            </div>

            <div v-else class="requests-list">
              <article v-for="solicitud in solicitudesPendientes" :key="solicitud.solicitud_id" class="request-card">
                <div class="request-main">
                  <div class="request-title-row">
                    <div class="request-info-head">
                      <span class="request-kicker">{{ solicitud.cancha }} · {{ solicitud.complejo }}</span>
                      <h3 class="client-name">{{ solicitud.nombre_cliente }}</h3>
                    </div>
                    <span v-if="solicitud.expira_en" class="request-expiry">
                      <ion-icon name="time-outline"></ion-icon>
                      Vence {{ formatearHora(solicitud.expira_en) }}
                    </span>
                  </div>

                  <div class="request-meta">
                    <span class="meta-item"><ion-icon name="calendar-outline"></ion-icon> {{ solicitud.fecha }}</span>
                    <span class="meta-item"><ion-icon name="card-outline"></ion-icon> Cédula {{ solicitud.cedula_cliente }}</span>
                    <a :href="`tel:${solicitud.telefono_cliente}`" class="meta-item phone">
                      <ion-icon name="call-outline"></ion-icon> {{ solicitud.telefono_cliente }}
                    </a>
                  </div>

                  <div class="request-hours">
                    <span v-for="hora in solicitud.horas" :key="`${solicitud.solicitud_id}-${hora.inicio}`" class="time-badge">
                      {{ hora.inicio }} – {{ hora.fin }}
                    </span>
                  </div>

                  <p v-if="solicitud.observaciones" class="request-notes">
                    <ion-icon name="chatbox-ellipses-outline"></ion-icon>
                    "{{ solicitud.observaciones }}"
                  </p>
                </div>

                <div class="request-actions">
                  <a 
                    v-if="solicitud.whatsapp_url" 
                    :href="solicitud.whatsapp_url" 
                    target="_blank" 
                    rel="noopener noreferrer" 
                    class="request-btn whatsapp"
                  >
                    <ion-icon name="logo-whatsapp"></ion-icon>
                    <span>WhatsApp</span>
                  </a>
                  
                  <button 
                    v-if="solicitud.estado === 'pendiente'"
                    class="request-btn reject" 
                    type="button" 
                    :disabled="respondiendoSolicitud" 
                    @click="responderSolicitud(solicitud.reserva_id, 'rechazar')"
                  >
                    <ion-icon name="close-outline"></ion-icon>
                    <span>Rechazar</span>
                  </button>

                  <button 
                    v-if="solicitud.estado === 'pendiente'"
                    class="request-btn accept" 
                    type="button" 
                    :disabled="respondiendoSolicitud" 
                    @click="responderSolicitud(solicitud.reserva_id, 'aceptar')"
                  >
                    <ion-icon name="checkmark-outline"></ion-icon>
                    <span>{{ respondiendoSolicitud ? 'Procesando…' : 'Aceptar' }}</span>
                  </button>

                  <button 
                    v-else-if="solicitud.estado === 'aceptada'"
                    class="request-btn confirm-pay" 
                    type="button" 
                    :disabled="respondiendoSolicitud" 
                    @click="confirmarPago(solicitud.reserva_id)"
                  >
                    <ion-icon name="cash-outline"></ion-icon>
                    <span>{{ respondiendoSolicitud ? 'Procesando…' : 'Confirmar pago' }}</span>
                  </button>
                </div>
              </article>
            </div>
          </div>
        </section>

        <!-- Grid Contenido Agenda & Flujo -->
        <section class="content-grid">
          <!-- Columna Agenda Diario -->
          <div class="panel-card-glass wide-card">
            <div class="card-header">
              <div>
                <span class="card-kicker">Agenda Diaria</span>
                <h2 class="card-title">Horarios del Día</h2>
              </div>
            </div>

            <div v-if="agenda?.canchas?.length" class="agenda-list">
              <div v-for="cancha in agenda.canchas" :key="cancha.cancha_id" class="agenda-item">
                <div class="agenda-head">
                  <div class="cancha-title-wrap">
                    <ion-icon name="football-outline" class="cancha-icon"></ion-icon>
                    <h3>{{ cancha.nombre }}</h3>
                  </div>
                  <span class="badge-deporte">{{ cancha.deporte }}</span>
                </div>

                <div class="timeline-list">
                  <!-- Reservas -->
                  <div v-for="r in cancha.reservas" :key="r.id" class="timeline-row reservation">
                    <div class="timeline-time">
                      <ion-icon name="time-outline"></ion-icon>
                      <span>{{ r.hora_inicio }} - {{ r.hora_fin }}</span>
                    </div>
                    
                    <div class="timeline-content">
                      <span class="client-name-sm">{{ r.nombre_cliente }}</span>
                      <small :class="['status-chip', r.estado]">{{ r.estado }}</small>
                    </div>

                    <div class="reservation-actions">
                      <template v-if="r.estado === 'pendiente' && r.solicitud_id">
                        <small v-if="r.expira_en" class="expiry-warn">Vence {{ formatearHora(r.expira_en) }}</small>
                        <button class="mini-button-success" type="button" @click="responderSolicitud(r.id, 'aceptar')">Aceptar</button>
                        <button class="mini-button-danger" type="button" @click="responderSolicitud(r.id, 'rechazar')">Rechazar</button>
                      </template>
                      <button v-else class="mini-button-danger" type="button" @click="cancelar(r.id)">Cancelar</button>
                    </div>
                  </div>

                  <!-- Bloqueos -->
                  <div v-for="b in cancha.bloqueos" :key="'b' + b.id" class="timeline-row blocked">
                    <div class="timeline-time muted">
                      <ion-icon name="lock-closed-outline"></ion-icon>
                      <span>{{ b.hora_inicio }} - {{ b.hora_fin }}</span>
                    </div>
                    <div class="timeline-content">
                      <span class="client-name-sm">Horario Bloqueado</span>
                      <small class="status-chip blocked-tag">{{ b.motivo || 'Mantenimiento / Uso Interno' }}</small>
                    </div>
                  </div>
                </div>

                <p v-if="!cancha.reservas?.length && !cancha.bloqueos?.length" class="empty-inline">
                  <ion-icon name="calendar-clear-outline"></ion-icon>
                  Sin reservas ni bloqueos registrados para hoy.
                </p>
              </div>
            </div>

            <div v-else class="empty-state-card">
              <ion-icon name="information-circle-outline"></ion-icon>
              <p>No hay agenda disponible para este complejo.</p>
            </div>
          </div>

          <!-- Columna Lateral: Flujo de Operación -->
          <div class="panel-card-glass side-card">
            <div class="card-header">
              <div>
                <span class="card-kicker">Guía de Operación</span>
                <h2 class="card-title">Flujo de Solicitudes</h2>
              </div>
            </div>

            <ol class="flow-steps">
              <li class="step-item">
                <div class="step-number">1</div>
                <div class="step-body">
                  <strong>Aceptar Solicitud</strong>
                  <span>Reserva provisional del horario y contacto directo con el cliente vía WhatsApp.</span>
                </div>
              </li>
              <li class="step-item">
                <div class="step-number">2</div>
                <div class="step-body">
                  <strong>Coordinar Pago</strong>
                  <span>Solicita el comprobante del SINPE / transferencia acordada.</span>
                </div>
              </li>
              <li class="step-item">
                <div class="step-number">3</div>
                <div class="step-body">
                  <strong>Confirmar y Bloquear</strong>
                  <span>Marca la solicitud como pagada para asegurar el bloque definitivamente.</span>
                </div>
              </li>
            </ol>

            <div v-if="mensajeReserva" :class="['message-banner', errorReserva ? 'error' : 'success']">
              <ion-icon :name="errorReserva ? 'alert-circle-outline' : 'checkmark-circle-outline'"></ion-icon>
              <span>{{ mensajeReserva }}</span>
            </div>
          </div>
        </section>

        <!-- Historial de Reservas -->
        <section class="panel-card-glass history-card">
          <div class="card-header history-header">
            <div>
              <span class="card-kicker">Registro Histórico</span>
              <h2 class="card-title">Historial de Reservas</h2>
            </div>
            
            <div class="history-filters">
              <div class="filter-input-wrap">
                <ion-icon name="funnel-outline" class="filter-icon"></ion-icon>
                <ion-input 
                  v-model="fechaHistorial" 
                  type="date" 
                  class="custom-input-dark history-date-picker" 
                  @ionChange="cargarHistorial(1)"
                ></ion-input>
              </div>
              <button class="nav-chip-btn" type="button" @click="limpiarFiltroHistorial">Todas</button>
            </div>
          </div>

          <div class="table-responsive">
            <table class="neon-table">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Cancha</th>
                  <th>Cliente</th>
                  <th>Horario</th>
                  <th>Estado</th>
                  <th class="text-right">Acción</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="reserva in historialReservas" :key="reserva.id">
                  <td class="font-mono">{{ reserva.fecha }}</td>
                  <td><span class="cancha-badge">{{ reserva.cancha?.nombre || 'N/A' }}</span></td>
                  <td class="font-bold">{{ reserva.nombre_cliente }}</td>
                  <td class="font-mono text-neon-subtle">{{ reserva.hora_inicio }} - {{ reserva.hora_fin }}</td>
                  <td>
                    <span :class="['table-status-pill', reserva.decision_propietario || reserva.estado]">
                      {{ reserva.decision_propietario || reserva.estado }}
                    </span>
                  </td>
                  <td class="text-right">
                    <button
                      v-if="reserva.estado === 'confirmada' && horaFinalizada(reserva)"
                      class="mini-button-success"
                      type="button"
                      @click="completarAlquiler(reserva)"
                    >
                      Marcar completada
                    </button>
                    <span v-else-if="reserva.estado === 'completada'" class="text-success font-bold text-xs">
                      ✓ Completada
                    </span>
                    <span v-else class="text-muted">—</span>
                  </td>
                </tr>
                <tr v-if="historialReservas.length === 0">
                  <td colspan="6" class="text-center py-6 text-muted">
                    No hay reservas registradas para este filtro.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="history-pagination">
            <span class="page-info">Página {{ paginaHistorial }} de {{ ultimaPaginaHistorial }}</span>
            <div class="pagination-buttons">
              <button 
                class="nav-chip-btn" 
                type="button" 
                :disabled="paginaHistorial <= 1" 
                @click="cargarHistorial(paginaHistorial - 1)"
              >
                Anterior
              </button>
              <button 
                class="nav-chip-btn" 
                type="button" 
                :disabled="paginaHistorial >= ultimaPaginaHistorial" 
                @click="cargarHistorial(paginaHistorial + 1)"
              >
                Siguiente
              </button>
            </div>
          </div>
        </section>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
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
  checkmarkCircleOutline,
  logoWhatsapp,
  cashOutline,
  flameOutline,
  checkmarkDoneCircleOutline,
  cardOutline,
  callOutline,
  chatboxEllipsesOutline,
  closeOutline,
  checkmarkOutline,
  calendarClearOutline,
  informationCircleOutline,
  businessOutline,
  closeCircleOutline,
  funnelOutline
} from 'ionicons/icons';
import { useAuthStore } from '@/stores/auth';
import panelService from '@/services/panel.service';
import echo from '@/services/echo';

interface Complejo {
  id: number;
  nombre: string;
}

interface Estadisticas {
  total_canchas: number;
  reservas_hoy: number;
  reservas_mes: { total: number };
  solicitudes_mes: { aceptadas: number; rechazadas: number };
  ingreso_estimado_mes: number | string;
  demanda_por_hora?: Array<{ hora: string; reservas: number }>;
}

interface Solicitud {
  solicitud_id: number;
  reserva_id: number;
  estado: 'pendiente' | 'aceptada';
  cancha: string;
  complejo: string;
  nombre_cliente: string;
  expira_en?: string;
  fecha: string;
  cedula_cliente: string;
  telefono_cliente: string;
  horas: Array<{ inicio: string; fin: string }>;
  observaciones?: string;
  whatsapp_url?: string;
}

interface CanchaAgenda {
  cancha_id: number;
  nombre: string;
  deporte: string;
  reservas: Array<any>;
  bloqueos: Array<any>;
}

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
  'logo-whatsapp': logoWhatsapp,
  'cash-outline': cashOutline,
  'flame-outline': flameOutline,
  'checkmark-done-circle-outline': checkmarkDoneCircleOutline,
  'card-outline': cardOutline,
  'call-outline': callOutline,
  'chatbox-ellipses-outline': chatboxEllipsesOutline,
  'close-outline': closeOutline,
  'checkmark-outline': checkmarkOutline,
  'calendar-clear-outline': calendarClearOutline,
  'information-circle-outline': informationCircleOutline,
  'business-outline': businessOutline,
  'close-circle-outline': closeCircleOutline,
  'funnel-outline': funnelOutline,
});

const router = useRouter();
const authStore = useAuthStore();

const misComplejos = ref<Complejo[]>([]);
const complejoIdSeleccionado = ref<number | null>(null);
const complejoActual = ref<Complejo | null>(null);
const agenda = ref<{ canchas: CanchaAgenda[] } | null>(null);
const estadisticas = ref<Estadisticas | null>(null);
const cargandoInicial = ref(true);

const mensajeReserva = ref('');
const errorReserva = ref(false);
const solicitudesPendientes = ref<Solicitud[]>([]);
const cargandoSolicitudes = ref(false);
const errorSolicitudes = ref('');
const respondiendoSolicitud = ref(false);
const historialReservas = ref<any[]>([]);
const fechaHistorial = ref('');
const paginaHistorial = ref(1);
const ultimaPaginaHistorial = ref(1);

let complejoSuscritoId: number | null = null;

function formatearHora(fechaIso: string): string {
  try {
    return new Date(fechaIso).toLocaleTimeString('es-CR', { hour: '2-digit', minute: '2-digit' });
  } catch {
    return '';
  }
}

async function cargarAgenda() {
  if (!complejoIdSeleccionado.value) return;

  complejoActual.value = misComplejos.value.find((c) => c.id === complejoIdSeleccionado.value) || null;

  try {
    const [agendaRes, statsRes] = await Promise.all([
      panelService.agenda(complejoIdSeleccionado.value),
      panelService.estadisticas(complejoIdSeleccionado.value),
    ]);

    agenda.value = agendaRes.data.data;
    estadisticas.value = statsRes.data.data;
    await cargarSolicitudes();
  } catch (error) {
    console.error('Error al cargar la agenda o estadísticas:', error);
  }
}

async function cargarSolicitudes() {
  cargandoSolicitudes.value = true;
  errorSolicitudes.value = '';
  try {
    const { data } = await panelService.solicitudes();
    solicitudesPendientes.value = data.data;
  } catch (error: any) {
    errorSolicitudes.value = error.response?.data?.message || 'No se pudieron cargar las solicitudes.';
  } finally {
    cargandoSolicitudes.value = false;
  }
}

function suscribirComplejoActual() {
  const id = complejoIdSeleccionado.value;
  if (!id || complejoSuscritoId === id) return;
  if (complejoSuscritoId !== null) echo.leaveChannel(`complejo.${complejoSuscritoId}.disponibilidad`);
  complejoSuscritoId = id;
  echo.channel(`complejo.${id}.disponibilidad`).listen('.disponibilidad.actualizada', () => {
    void Promise.all([cargarAgenda(), cargarHistorial(paginaHistorial.value)]);
  });
}

async function cambiarComplejo() {
  await cargarAgenda();
  suscribirComplejoActual();
}

async function cargarHistorial(pagina = 1) {
  try {
    const { data } = await panelService.listarReservas({
      fecha: fechaHistorial.value || undefined,
      page: pagina,
    });
    historialReservas.value = data.data.data;
    paginaHistorial.value = data.data.current_page;
    ultimaPaginaHistorial.value = data.data.last_page;
  } catch (error) {
    console.error('Error cargando el historial:', error);
  }
}

async function limpiarFiltroHistorial() {
  fechaHistorial.value = '';
  await cargarHistorial(1);
}

function horaFinalizada(reserva: any): boolean {
  if (!reserva.fecha || !reserva.hora_fin) return false;
  const [year, month, day] = String(reserva.fecha).slice(0, 10).split('-').map(Number);
  const [hour, minute] = String(reserva.hora_fin).split(':').map(Number);
  return new Date(year, month - 1, day, hour, minute).getTime() <= Date.now();
}

async function completarAlquiler(reserva: any) {
  try {
    await panelService.completarReserva(reserva.id);
    await Promise.all([cargarAgenda(), cargarHistorial(paginaHistorial.value)]);
  } catch (error: any) {
    mensajeReserva.value = error.response?.data?.message || 'No se pudo completar el alquiler.';
    errorReserva.value = true;
  }
}

async function cancelar(reservaId: number) {
  try {
    await panelService.cancelarReserva(reservaId);
    await cargarAgenda();
    await cargarHistorial(paginaHistorial.value);
  } catch (error: any) {
    console.error('Error cancelando reserva:', error);
  }
}

async function responderSolicitud(reservaId: number, decision: 'aceptar' | 'rechazar') {
  respondiendoSolicitud.value = true;
  mensajeReserva.value = '';
  errorReserva.value = false;
  try {
    await panelService.responderSolicitud(reservaId, decision);
    mensajeReserva.value = decision === 'aceptar'
      ? 'Solicitud aceptada. Contacta al cliente por WhatsApp para coordinar el pago.'
      : 'Solicitud rechazada.';
    await Promise.all([cargarAgenda(), cargarHistorial(paginaHistorial.value)]);
  } catch (error: any) {
    mensajeReserva.value = error.response?.data?.message || 'No se pudo responder la solicitud.';
    errorReserva.value = true;
  } finally {
    respondiendoSolicitud.value = false;
  }
}

async function confirmarPago(reservaId: number) {
  respondiendoSolicitud.value = true;
  mensajeReserva.value = '';
  errorReserva.value = false;
  try {
    await panelService.confirmarPago(reservaId);
    mensajeReserva.value = 'Pago confirmado. La cancha quedó reservada.';
    await Promise.all([cargarAgenda(), cargarHistorial(paginaHistorial.value)]);
  } catch (error: any) {
    mensajeReserva.value = error.response?.data?.message || 'No se pudo confirmar el pago.';
    errorReserva.value = true;
  } finally {
    respondiendoSolicitud.value = false;
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
      suscribirComplejoActual();
    }
    await cargarHistorial();
  } catch (err) {
    console.error('Error cargando los complejos:', err);
  } finally {
    cargandoInicial.value = false;
  }
});

onUnmounted(() => {
  if (complejoSuscritoId !== null) echo.leaveChannel(`complejo.${complejoSuscritoId}.disponibilidad`);
});
</script>

<style scoped>
.panel-page {
  font-family: 'DM Sans', -apple-system, sans-serif;
  color: #17251e;
  background-color: #f1f6f1;
}

.panel-content {
  --background: #f1f6f1;
}

/* NAVBAR */
.top-navbar-floating {
  position: sticky;
  top: 0;
  left: 0;
  right: 0;
  z-index: 100;
  padding: 1rem 1.5rem 0.5rem;
  background: linear-gradient(180deg, rgba(241, 246, 241, 0.96) 0%, rgba(241, 246, 241, 0) 100%);
}

.navbar-pill {
  max-width: 1240px;
  margin: 0 auto;
  background: rgba(255, 255, 255, 0.96);
  border: 1px solid #d7e2d8;
  border-radius: 6px;
  padding: 0.55rem 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 8px 24px rgba(17, 42, 29, 0.1);
}

.brand-logo {
  display: flex;
  align-items: center;
  gap: 0.65rem;
}

.logo-circle-icon {
  width: 34px;
  height: 34px;
  border-radius: 5px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #17634b;
}

.logo-circle-icon ion-icon {
  color: #d4ed66;
  font-size: 1.15rem;
}

.brand-name {
  font-family: 'Barlow Condensed', sans-serif;
  font-weight: 700;
  font-size: 1.3rem;
  color: #17251e;
}

.dot-neon {
  color: #17634b;
}

.navbar-actions {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.nav-chip-btn {
  background: #ffffff;
  border: 1px solid #d7e2d8;
  border-radius: 4px;
  padding: 0.45rem 1rem;
  color: #17634b;
  font-size: 0.825rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.45rem;
  cursor: pointer;
  transition: background-color 0.18s ease, border-color 0.18s ease;
}

.nav-chip-btn .chip-icon {
  font-size: 0.95rem;
  color: #17634b;
}

.nav-chip-btn:hover:not(:disabled) {
  background: #eef5ec;
  border-color: #c3d6c6;
}

.nav-chip-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.nav-chip-btn.danger {
  background: #fff0eb;
  border-color: #efc5b9;
  color: #9d3c2f;
}

.nav-chip-btn.danger .chip-icon {
  color: #9d3c2f;
}

.nav-chip-btn.danger:hover {
  background: #f9ddd3;
  border-color: #e5a794;
  color: #7c2d20;
}

/* FONDOS DECORATIVOS DESACTIVADOS */
.page-background-glow,
.bg-grid {
  display: none;
}

/* SPINNER CARGA INICIAL */
.loading-shell {
  min-height: 70vh;
  display: flex;
  justify-content: center;
  align-items: center;
}

.main-spinner {
  width: 44px;
  height: 44px;
  color: #17634b;
}

.main-spinner-sm {
  width: 18px;
  height: 18px;
  color: #17634b;
}

/* ESTRUCTURA DASHBOARD */
.dashboard-shell {
  max-width: 1240px;
  margin: 0 auto;
  padding: 1.5rem 1.5rem 3rem;
  position: relative;
  z-index: 2;
}

.topbar {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 1rem;
  margin-bottom: 1.75rem;
}

.eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: #17634b;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 0.725rem;
  font-weight: 700;
}

.pulse-indicator {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background-color: #17634b;
}

.page-title {
  font-family: 'Barlow Condensed', sans-serif;
  margin: 0.25rem 0 0;
  color: #17251e;
  font-size: clamp(2rem, 3.5vw, 2.6rem);
  font-weight: 700;
  line-height: 1.05;
}

/* SELECTOR DE COMPLEJO */
.selector-card-glass {
  background: #ffffff;
  border: 1px solid #d7e2d8;
  border-radius: 6px;
  padding: 0.9rem 1.25rem;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  box-shadow: 0 10px 28px rgba(23, 49, 35, 0.06);
}

.selector-info {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.selector-icon {
  color: #17634b;
  font-size: 1.15rem;
}

.selector-label {
  color: #66736b;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.complex-select-dark {
  background: #ffffff;
  border: 1px solid #d7e2d8;
  border-radius: 4px;
  color: #17251e;
  min-width: 220px;
}

/* GRID DE ESTADÍSTICAS */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(175px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card-glass {
  background: #ffffff;
  border: 1px solid #d7e2d8;
  border-radius: 6px;
  padding: 1.1rem 1.15rem;
  box-shadow: 0 10px 28px rgba(23, 49, 35, 0.06);
  transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.stat-card-glass:hover {
  transform: translateY(-2px);
  box-shadow: 0 14px 32px rgba(23, 49, 35, 0.1);
}

.stat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.65rem;
}

.stat-label {
  color: #66736b;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  font-weight: 700;
}

.icon-wrapper {
  width: 30px;
  height: 30px;
  border-radius: 6px;
  background: #eef3ec;
  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-wrapper .stat-icon {
  font-size: 1.05rem;
  color: #17634b;
}

.icon-wrapper.neon { background: #eef6d8; }
.icon-wrapper.neon .stat-icon { color: #5a7a12; }

.icon-wrapper.success { background: #e8f3e9; }
.icon-wrapper.success .stat-icon { color: #17634b; }

.icon-wrapper.danger { background: #fff0eb; }
.icon-wrapper.danger .stat-icon { color: #c0513c; }

.icon-wrapper.gold { background: #f6efdd; }
.icon-wrapper.gold .stat-icon { color: #8a6d1d; }

.stat-value {
  font-family: 'Barlow Condensed', sans-serif;
  color: #17251e;
  font-size: clamp(1.6rem, 2.5vw, 2rem);
  font-weight: 700;
  line-height: 1;
  display: block;
}

.text-neon { color: #17634b; }
.text-success { color: #17634b; }
.text-danger { color: #c0513c; }
.text-gold { color: #17251e; }

/* TIRA DEMANDA DE HORAS */
.demand-strip {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.8rem;
  padding: 0.8rem 1.15rem;
  background: #ffffff;
  border: 1px solid #d7e2d8;
  border-radius: 6px;
  margin-bottom: 1.5rem;
  box-shadow: 0 10px 28px rgba(23, 49, 35, 0.06);
}

.demand-title {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  color: #66736b;
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.flame-icon {
  color: #d96e5a;
  font-size: 1.05rem;
}

.demand-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.45rem;
}

.demand-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  background: #f2f7e2;
  border: 1px solid #dde9b8;
  padding: 0.25rem 0.6rem;
  border-radius: 4px;
  font-size: 0.775rem;
}

.demand-pill .hour {
  color: #17251e;
  font-weight: 700;
}

.demand-pill .count {
  color: #17634b;
  font-weight: 700;
}

/* CARDS GENERALES */
.panel-card-glass {
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
  margin-bottom: 1.25rem;
}

.card-kicker {
  display: inline-block;
  color: #17634b;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 0.7rem;
  font-weight: 700;
}

.card-title {
  font-family: 'Barlow Condensed', sans-serif;
  margin: 0.15rem 0 0;
  color: #17251e;
  font-size: 1.45rem;
  font-weight: 700;
}

/* SECCION SOLICITUDES */
.requests-section {
  margin-bottom: 1.5rem;
}

.requests-panel {
  border-left: 3px solid #17634b;
}

.request-count {
  display: grid;
  place-items: center;
  min-width: 2.1rem;
  height: 2.1rem;
  padding: 0 0.4rem;
  border-radius: 999px;
  background: #17634b;
  color: #ffffff;
  font-weight: 700;
  font-size: 0.85rem;
}

.request-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.7rem;
  padding: 2.25rem 1rem;
  color: #66736b;
  font-size: 0.875rem;
}

.empty-state-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2.25rem 1.5rem;
  text-align: center;
  background: #f7faf5;
  border: 1px dashed #cfdccd;
  border-radius: 6px;
}

.empty-state-card.error {
  border-color: #efc5b9;
  color: #9d3c2f;
}

.empty-state-card > ion-icon {
  font-size: 1.5rem;
  color: #17634b;
  margin-bottom: 0.5rem;
}

.empty-state-card.error > ion-icon {
  color: #c0513c;
}

.empty-state-card p {
  margin: 0;
  color: #17251e;
  font-weight: 600;
}

.empty-icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: #e8f3e9;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.7rem;
}

.empty-icon-wrap ion-icon {
  font-size: 1.45rem;
  color: #17634b;
}

.empty-title {
  margin: 0;
  color: #17251e;
  font-weight: 700;
  font-size: 0.95rem;
}

.empty-sub {
  color: #66736b;
  font-size: 0.825rem;
  margin-top: 0.25rem;
}

.requests-list {
  display: grid;
  gap: 0.75rem;
}

.request-card {
  display: grid;
  grid-template-columns: 1fr auto;
  align-items: center;
  gap: 1.25rem;
  padding: 1.05rem 1.2rem;
  border: 1px solid #d7e2d8;
  border-radius: 6px;
  background: #fbfdfb;
  transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.request-card:hover {
  border-color: #b9cfba;
  box-shadow: 0 6px 18px rgba(23, 49, 35, 0.08);
}

.request-main {
  min-width: 0;
}

.request-title-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
}

.request-kicker {
  color: #17634b;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.client-name {
  margin: 0.15rem 0 0;
  color: #17251e;
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 1.25rem;
  font-weight: 700;
}

.request-expiry {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  color: #8a6d1d;
  font-size: 0.75rem;
  font-weight: 700;
  background: #f6efdd;
  padding: 0.25rem 0.55rem;
  border-radius: 4px;
  white-space: nowrap;
}

.request-meta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.9rem;
  margin-top: 0.6rem;
  color: #66736b;
  font-size: 0.8rem;
}

.meta-item {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

.meta-item ion-icon {
  color: #8a968d;
  font-size: 0.9rem;
}

.meta-item.phone {
  color: #17634b;
  text-decoration: none;
  font-weight: 600;
}

.meta-item.phone:hover {
  text-decoration: underline;
}

.request-hours {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
  margin-top: 0.7rem;
}

.time-badge {
  padding: 0.3rem 0.6rem;
  border: 1px solid #dde9b8;
  border-radius: 4px;
  background: #f2f7e2;
  color: #40520c;
  font-size: 0.775rem;
  font-weight: 600;
  font-variant-numeric: tabular-nums;
}

.request-notes {
  display: flex;
  align-items: flex-start;
  gap: 0.4rem;
  margin: 0.7rem 0 0;
  color: #55615a;
  font-size: 0.8rem;
  background: #f2f5f0;
  padding: 0.45rem 0.65rem;
  border-radius: 4px;
}

.request-notes ion-icon {
  color: #17634b;
  flex-shrink: 0;
  margin-top: 0.1rem;
}

.request-actions {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
  width: 175px;
}

.request-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.45rem;
  padding: 0.55rem 0.8rem;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 700;
  border: 1px solid transparent;
  cursor: pointer;
  text-decoration: none;
  transition: background-color 0.18s ease, border-color 0.18s ease;
}

.request-btn ion-icon {
  font-size: 1rem;
}

.request-btn.whatsapp {
  background: #ffffff;
  border-color: #bfe0ca;
  color: #1d7a46;
}

.request-btn.whatsapp:hover {
  background: #e9f6ee;
}

.request-btn.reject {
  background: #fff0eb;
  border-color: #efc5b9;
  color: #9d3c2f;
}

.request-btn.reject:hover:not(:disabled) {
  background: #f9ddd3;
}

.request-btn.accept {
  background: #17634b;
  border-color: #17634b;
  color: #ffffff;
}

.request-btn.accept:hover:not(:disabled) {
  background: #103b2e;
}

.request-btn.confirm-pay {
  background: #d4ed66;
  border-color: #c2dd4e;
  color: #142219;
}

.request-btn.confirm-pay:hover:not(:disabled) {
  background: #c2dd4e;
}

.request-btn:disabled {
  opacity: 0.55;
  cursor: wait;
}

/* GRID CONTENIDO (AGENDA & FLUJO) */
.content-grid {
  display: grid;
  grid-template-columns: 1.6fr 1fr;
  gap: 1.5rem;
}

.agenda-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.agenda-item {
  padding: 1.1rem 1.15rem;
  border: 1px solid #e0e8de;
  border-radius: 6px;
  background: #f9fbf7;
}

.agenda-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  margin-bottom: 0.9rem;
}

.cancha-title-wrap {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.cancha-icon {
  color: #17634b;
  font-size: 1.1rem;
}

.agenda-head h3 {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 700;
  color: #17251e;
  font-family: 'Barlow Condensed', sans-serif;
}

.badge-deporte {
  font-size: 0.68rem;
  background: #e8f3e9;
  border: 1px solid #c9dfcd;
  color: #17634b;
  padding: 0.2rem 0.55rem;
  border-radius: 4px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.timeline-list {
  display: flex;
  flex-direction: column;
  gap: 0.55rem;
}

.timeline-row {
  display: grid;
  grid-template-columns: 130px 1fr auto;
  gap: 0.75rem;
  align-items: center;
  padding: 0.7rem 0.9rem;
  border-radius: 4px;
  border: 1px solid transparent;
}

.timeline-row.reservation {
  background: #eef5ec;
  border-color: #d5e4d1;
}

.timeline-row.blocked {
  background: #f1f3ef;
  border-color: #e2e6df;
}

.timeline-time {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  font-weight: 700;
  color: #17634b;
  font-size: 0.8rem;
  font-variant-numeric: tabular-nums;
}

.timeline-time.muted {
  color: #66736b;
}

.client-name-sm {
  font-size: 0.875rem;
  font-weight: 600;
  color: #17251e;
}

.status-chip {
  display: inline-block;
  font-size: 0.68rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 700;
  padding: 0.15rem 0.5rem;
  border-radius: 4px;
  background: #eef2ec;
  color: #66736b;
  margin-top: 0.15rem;
}

.status-chip.pendiente {
  background: #f6efdd;
  color: #8a6d1d;
}

.status-chip.aceptada {
  background: #e8f3e9;
  color: #17634b;
}

.status-chip.confirmada {
  background: #17634b;
  color: #ffffff;
}

.status-chip.blocked-tag {
  background: #fff0eb;
  color: #9d3c2f;
}

.reservation-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.4rem;
}

.expiry-warn {
  color: #8a6d1d;
  font-size: 0.7rem;
  font-weight: 700;
}

.mini-button-success {
  background: #e8f3e9;
  border: 1px solid #c9dfcd;
  color: #17634b;
  border-radius: 4px;
  padding: 0.35rem 0.65rem;
  font-size: 0.75rem;
  font-weight: 700;
  cursor: pointer;
  transition: background-color 0.18s ease;
}

.mini-button-success:hover {
  background: #d8ead9;
}

.mini-button-danger {
  background: #fff0eb;
  border: 1px solid #efc5b9;
  color: #9d3c2f;
  border-radius: 4px;
  padding: 0.35rem 0.65rem;
  font-size: 0.75rem;
  font-weight: 700;
  cursor: pointer;
  transition: background-color 0.18s ease;
}

.mini-button-danger:hover {
  background: #f9ddd3;
}

.empty-inline {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  margin: 0.5rem 0 0;
  color: #66736b;
  font-size: 0.825rem;
}

.empty-inline ion-icon {
  color: #8a968d;
}

/* FLUJO DE PASOS */
.flow-steps {
  display: flex;
  flex-direction: column;
  gap: 0.7rem;
  margin: 0;
  padding: 0;
  list-style: none;
}

.step-item {
  display: flex;
  gap: 0.8rem;
  padding: 0.9rem 1rem;
  border: 1px solid #e0e8de;
  border-radius: 6px;
  background: #f9fbf7;
}

.step-number {
  width: 26px;
  height: 26px;
  border-radius: 4px;
  background: #17634b;
  color: #ffffff;
  font-family: 'Barlow Condensed', sans-serif;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 0.95rem;
}

.step-body {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.step-body strong {
  color: #17251e;
  font-size: 0.875rem;
}

.step-body span {
  color: #66736b;
  font-size: 0.8rem;
  line-height: 1.45;
}

.message-banner {
  margin-top: 1.15rem;
  padding: 0.8rem 1rem;
  border-radius: 4px;
  font-size: 0.825rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.55rem;
}

.message-banner ion-icon {
  font-size: 1.15rem;
  flex-shrink: 0;
}

.message-banner.success {
  background: #e8f3e9;
  border: 1px solid #c9dfcd;
  color: #17634b;
}

.message-banner.error {
  background: #fff0eb;
  border: 1px solid #efc5b9;
  color: #9d3c2f;
}

/* HISTORIAL Y TABLA */
.history-card {
  margin-top: 1.5rem;
}

.history-header {
  flex-wrap: wrap;
  gap: 1rem;
}

.history-filters {
  display: flex;
  align-items: center;
  gap: 0.55rem;
}

.filter-input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.filter-icon {
  position: absolute;
  left: 0.7rem;
  z-index: 2;
  color: #17634b;
  font-size: 0.9rem;
  pointer-events: none;
}

.history-date-picker {
  width: 170px;
  --padding-start: 2.1rem;
}

.custom-input-dark {
  --background: #ffffff;
  --color: #17251e;
  --placeholder-color: #8a968d;
  min-height: 38px;
  border: 1px solid #d7e2d8;
  border-radius: 4px;
  font-size: 0.825rem;
}

.table-responsive {
  width: 100%;
  overflow-x: auto;
  margin-top: 1rem;
  border-radius: 6px;
  border: 1px solid #d7e2d8;
}

.neon-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.85rem;
  background: #ffffff;
}

.neon-table th {
  padding: 0.8rem 1rem;
  background: #f4f7f3;
  color: #66736b;
  font-size: 0.68rem;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  font-weight: 700;
  border-bottom: 1px solid #d7e2d8;
}

.neon-table td {
  padding: 0.85rem 1rem;
  border-bottom: 1px solid #edf1ea;
  color: #2c3a32;
}

.neon-table tr:last-child td {
  border-bottom: none;
}

.neon-table tr:hover td {
  background: #f7faf5;
}

.cancha-badge {
  display: inline-block;
  background: #eef3ec;
  border: 1px solid #dde5da;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  font-size: 0.775rem;
  color: #17251e;
  font-weight: 600;
}

.table-status-pill {
  display: inline-block;
  padding: 0.2rem 0.55rem;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  background: #eef2ec;
  color: #66736b;
}

.table-status-pill.aceptada,
.table-status-pill.confirmada {
  background: #e8f3e9;
  color: #17634b;
}

.table-status-pill.completada {
  background: #17634b;
  color: #ffffff;
}

.table-status-pill.rechazada,
.table-status-pill.cancelada {
  background: #fff0eb;
  color: #9d3c2f;
}

.table-status-pill.pendiente,
.table-status-pill.expirada {
  background: #f6efdd;
  color: #8a6d1d;
}

.history-pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1.15rem;
  padding-top: 0.75rem;
}

.page-info {
  font-size: 0.8rem;
  color: #66736b;
  font-weight: 600;
}

.pagination-buttons {
  display: flex;
  gap: 0.5rem;
}

.font-mono { font-variant-numeric: tabular-nums; }
.font-bold { font-weight: 700; }
.text-neon-subtle { color: #17634b; }
.text-muted { color: #8a968d; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.text-xs { font-size: 0.75rem; }
.py-6 { padding-top: 1.5rem; padding-bottom: 1.5rem; }

/* ADAPTACIÓN MÓVIL */
@media (max-width: 850px) {
  .top-navbar-floating {
    padding: 0.75rem 0.75rem 0.25rem;
  }

  .navbar-pill {
    padding: 0.5rem 0.85rem;
  }

  .brand-name {
    font-size: 1.15rem;
  }

  .nav-chip-btn .btn-text {
    display: none;
  }

  .nav-chip-btn {
    padding: 0.45rem;
  }

  .topbar {
    flex-direction: column;
    align-items: flex-start;
  }

  .request-card {
    grid-template-columns: 1fr;
  }

  .request-actions {
    width: 100%;
    flex-direction: row;
    flex-wrap: wrap;
  }

  .request-btn {
    flex: 1 1 140px;
  }

  .content-grid {
    grid-template-columns: 1fr;
  }

  .timeline-row {
    grid-template-columns: 1fr;
    gap: 0.4rem;
  }

  .reservation-actions {
    justify-content: flex-start;
  }

  .history-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .history-filters {
    width: 100%;
    justify-content: space-between;
  }

  .history-pagination {
    flex-direction: column;
    gap: 0.75rem;
  }
}
</style>