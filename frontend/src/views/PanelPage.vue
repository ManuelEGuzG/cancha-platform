<template>
  <ion-page class="sportra-hybrid-app">
    <ion-content class="sportra-main-viewport" :scroll-y="true">
      <!-- HEADER Y HERO BANNER OSCURO SUPERIOR -->
      <header class="dark-top-section">
        <div class="header-container">
          <div class="brand-brand-text" @click="router.push('/home')">
            <span class="brand-title">SPORTRA<span class="dot-blue">.</span></span>
            <small class="brand-sub">PANEL DE GESTIÓN</small>
          </div>

          <div class="navbar-actions">
            <button class="nav-btn-danger-pill" type="button" @click="cerrarSesion">
              <ion-icon name="log-out-outline"></ion-icon>
              <span>Salir</span>
            </button>
          </div>
        </div>

        <div class="hero-card-banner">
          <div class="hero-banner-content">
            <div class="hero-left-info">
              <span class="platform-badge">
                GESTIÓN DEPORTIVA <span class="badge-separator">/</span>
                <span class="badge-subtext">Panel en vivo</span>
              </span>
              <h1 class="hero-main-title">{{ complejoActual?.nombre || 'Panel del complejo' }}</h1>
              <p class="hero-description">
                <ion-icon name="location-outline" class="hero-location-icon"></ion-icon>
                {{ complejoActual?.ubicacion || 'Ubicación no especificada' }}
              </p>
            </div>
          </div>
        </div>
      </header>

      <!-- CUERPO CLARO -->
      <main class="light-body-section">
        <div class="body-container">

          <div v-if="cargandoInicial" class="loading-state-box">
            <ion-spinner name="crescent" class="main-spinner"></ion-spinner>
            <span>Cargando datos del complejo...</span>
          </div>

          <div v-else-if="errorInicial" class="empty-state-card error" role="alert">
            <ion-icon name="alert-circle-outline"></ion-icon>
            <p>{{ errorInicial }}</p>
          </div>

          <div v-else class="dashboard-grid-content">

            <!-- Banner global de mensajes -->
            <div v-if="mensajeReserva" :class="['message-banner', errorReserva ? 'error' : 'success']" role="status">
              <ion-icon :name="errorReserva ? 'alert-circle-outline' : 'checkmark-circle-outline'"></ion-icon>
              <span>{{ mensajeReserva }}</span>
            </div>

            <!-- Pestañas -->
            <div class="panel-tabs-bar">
              <button class="tab-item active" type="button">
                <ion-icon name="apps-outline"></ion-icon>
                <span>Resumen</span>
              </button>
              <button class="tab-item" type="button" @click="irACanchas">
                <ion-icon name="tennisball-outline"></ion-icon>
                <span>Canchas</span>
              </button>
            </div>

            <!-- 4 tarjetas de métricas: CANCHAS / HOY / MES / INGRESOS -->
            <div class="metrics-grid-four">
              <div class="metric-white-card">
                <div class="metric-card-header">
                  <span class="metric-label">Canchas</span>
                  <ion-icon name="tennisball-outline" class="metric-icon-blue"></ion-icon>
                </div>
                <div class="metric-value-row">
                  <strong class="metric-main-number">{{ estadisticas?.total_canchas ?? 0 }}</strong>
                </div>
                <div class="metric-footer-note">Canchas registradas en el complejo</div>
              </div>

              <div class="metric-white-card">
                <div class="metric-card-header">
                  <span class="metric-label">Hoy</span>
                  <ion-icon name="calendar-outline" class="metric-icon-blue"></ion-icon>
                </div>
                <div class="metric-value-row">
                  <strong class="metric-main-number">{{ estadisticas?.reservas_hoy ?? 0 }}</strong>
                </div>
                <div class="metric-footer-note">Reservas programadas para hoy</div>
              </div>

              <div class="metric-white-card">
                <div class="metric-card-header">
                  <span class="metric-label">Mes</span>
                  <ion-icon name="stats-chart-outline" class="metric-icon-blue"></ion-icon>
                </div>
                <div class="metric-value-row">
                  <strong class="metric-main-number">{{ estadisticas?.reservas_mes?.total ?? 0 }}</strong>
                </div>
                <div class="metric-footer-note">
                  {{ estadisticas?.solicitudes_mes?.aceptadas ?? 0 }} aceptadas ·
                  {{ estadisticas?.solicitudes_mes?.rechazadas ?? 0 }} rechazadas
                </div>
              </div>

              <div class="metric-white-card">
                <div class="metric-card-header">
                  <span class="metric-label">Ingresos</span>
                  <ion-icon name="wallet-outline" class="metric-icon-blue"></ion-icon>
                </div>
                <div class="metric-value-row">
                  <strong class="metric-main-number">{{ moneda(estadisticas?.ingreso_estimado_mes) }}</strong>
                </div>
                <div class="metric-footer-note">Ingreso estimado del mes</div>
              </div>
            </div>

            <!-- Horas pico -->
            <div v-if="estadisticas?.demanda_por_hora?.length" class="demand-strip-card">
              <div class="demand-title">
                <ion-icon name="flame-outline" class="flame-icon"></ion-icon>
                <span>Horas pico de mayor demanda:</span>
              </div>
              <div class="demand-tags">
                <span v-for="hora in estadisticas.demanda_por_hora" :key="hora.hora" class="demand-pill">
                  <span class="hour">{{ hora.hora }}</span>
                  <span class="count">{{ hora.reservas }} res.</span>
                </span>
              </div>
            </div>

            <!-- Solicitudes pendientes -->
            <section class="requests-section">
              <div class="white-panel-card">
                <div class="card-header-row">
                  <div>
                    <span class="section-kicker">BANDEJA DEL COMPLEJO</span>
                    <h2 class="section-title">Solicitudes Pendientes</h2>
                  </div>
                  <span class="badge-count-blue">{{ solicitudesPendientes.length }}</span>
                </div>

                <div v-if="cargandoSolicitudes" class="card-loading-inline">
                  <ion-spinner name="crescent"></ion-spinner>
                  <span>Cargando solicitudes...</span>
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
                  <article
                    v-for="solicitud in solicitudesPendientes"
                    :key="solicitud.solicitud_id || solicitud.reserva_id"
                    class="request-item-card"
                  >
                    <div class="request-main">
                      <div class="request-title-row">
                        <div>
                          <span class="request-kicker">{{ solicitud.cancha }} · {{ solicitud.complejo }}</span>
                          <h3 class="client-name">{{ solicitud.nombre_cliente }}</h3>
                        </div>
                        <span v-if="solicitud.expira_en" class="request-expiry-tag">
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
                        <span
                          v-for="hora in solicitud.horas"
                          :key="`${solicitud.reserva_id}-${hora.inicio}`"
                          class="time-badge"
                        >
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
                        class="btn-action-soft whatsapp"
                      >
                        <ion-icon name="logo-whatsapp"></ion-icon>
                        <span>WhatsApp</span>
                      </a>

                      <button
                        v-if="solicitud.estado === 'pendiente'"
                        class="btn-action-soft danger"
                        type="button"
                        :disabled="respondiendoSolicitud"
                        @click="responderSolicitud(solicitud.reserva_id, 'rechazar')"
                      >
                        <ion-icon name="close-outline"></ion-icon>
                        <span>Rechazar</span>
                      </button>

                      <button
                        v-if="solicitud.estado === 'pendiente'"
                        class="btn-action-primary"
                        type="button"
                        :disabled="respondiendoSolicitud"
                        @click="responderSolicitud(solicitud.reserva_id, 'aceptar')"
                      >
                        <ion-icon name="checkmark-outline"></ion-icon>
                        <span>{{ respondiendoSolicitud ? 'Procesando…' : 'Aceptar' }}</span>
                      </button>

                      <button
                        v-else-if="solicitud.estado === 'aceptada'"
                        class="btn-action-gold"
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

            <!-- Agenda + Guía -->
            <div class="two-columns-grid">

              <div class="white-panel-card">
                <div class="card-header-row">
                  <div>
                    <span class="section-kicker">AGENDA DIARIA</span>
                    <h2 class="section-title">Horarios del Día</h2>
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
                      <div
                        v-for="r in reservasActivas(cancha)"
                        :key="r.id"
                        class="timeline-row reservation"
                      >
                        <div class="timeline-time">
                          <ion-icon name="time-outline"></ion-icon>
                          <span>{{ r.hora_inicio }} - {{ r.hora_fin }}</span>
                        </div>

                        <div class="timeline-content">
                          <span class="client-name-sm">{{ r.nombre_cliente }}</span>
                          <small :class="['status-chip', r.estado]">{{ r.estado }}</small>
                        </div>

                        <div class="reservation-actions">
                          <button
                            v-if="puedeCancelar(r)"
                            class="mini-btn danger"
                            type="button"
                            @click="cancelar(r.id)"
                          >
                            Cancelar
                          </button>
                        </div>
                      </div>

                      <div v-for="b in cancha.bloqueos" :key="'b' + b.id" class="timeline-row blocked">
                        <div class="timeline-time muted">
                          <ion-icon name="lock-closed-outline"></ion-icon>
                          <span>{{ b.hora_inicio }} - {{ b.hora_fin }}</span>
                        </div>
                        <div class="timeline-content">
                          <span class="client-name-sm">Horario Bloqueado</span>
                          <small class="status-chip blocked-tag">{{ b.motivo || 'Uso Interno' }}</small>
                        </div>
                        <div></div>
                      </div>
                    </div>

                    <p v-if="!reservasActivas(cancha).length && !cancha.bloqueos?.length" class="empty-inline">
                      <ion-icon name="calendar-clear-outline"></ion-icon>
                      Sin reservas activas ni bloqueos registrados para hoy.
                    </p>
                  </div>
                </div>

                <div v-else class="empty-state-card">
                  <ion-icon name="information-circle-outline"></ion-icon>
                  <p>No hay agenda disponible para este complejo.</p>
                </div>
              </div>

              <div class="white-panel-card">
                <div class="card-header-row">
                  <div>
                    <span class="section-kicker">GUÍA RÁPIDA</span>
                    <h2 class="section-title">Flujo de Operación</h2>
                  </div>
                </div>

                <ol class="flow-steps">
                  <li class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-body">
                      <strong>Aceptar Solicitud</strong>
                      <span>Asegura el bloque provisional y comunícate con el cliente vía WhatsApp.</span>
                    </div>
                  </li>
                  <li class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-body">
                      <strong>Coordinar Pago</strong>
                      <span>Confirma el pago por SINPE Móvil o transferencia.</span>
                    </div>
                  </li>
                  <li class="step-item">
                    <div class="step-number">3</div>
                    <div class="step-body">
                      <strong>Confirmar Reserva</strong>
                      <span>Presiona "Confirmar pago" para bloquear el horario definitivamente.</span>
                    </div>
                  </li>
                </ol>
              </div>
            </div>

            <!-- Historial -->
            <section class="white-panel-card history-section">
              <div class="card-header-row history-header">
                <div>
                  <span class="section-kicker">REGISTRO DE RESERVAS</span>
                  <h2 class="section-title">Historial de Reservas</h2>
                </div>

                <div class="history-filters">
                  <div class="filter-input-wrap">
                    <ion-icon name="funnel-outline" class="filter-icon"></ion-icon>
                    <ion-input v-model="fechaHistorial" type="date" class="light-date-input"></ion-input>
                  </div>
                  <button class="btn-filter-reset" type="button" @click="limpiarFiltroHistorial">Todas</button>
                </div>
              </div>

              <div class="table-responsive">
                <table class="light-table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Fecha</th>
                      <th>Cancha</th>
                      <th>Cliente</th>
                      <th>Horario</th>
                      <th>Estado</th>
                      <th class="text-right">Acción</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(reserva, idx) in historialReservas" :key="reserva.id">
                      <td class="font-bold text-muted">{{ (paginaHistorial - 1) * porPaginaHistorial + idx + 1 }}</td>
                      <td class="font-mono">{{ reserva.fecha }}</td>
                      <td><span class="cancha-badge">{{ reserva.cancha?.nombre || 'N/A' }}</span></td>
                      <td class="font-bold">{{ reserva.nombre_cliente }}</td>
                      <td class="font-mono text-blue">{{ reserva.hora_inicio }} - {{ reserva.hora_fin }}</td>
                      <td>
                        <span :class="['table-status-pill', reserva.decision_propietario || reserva.estado]">
                          {{ reserva.decision_propietario || reserva.estado }}
                        </span>
                      </td>
                      <td class="text-right">
                        <button
                          v-if="reserva.estado === 'confirmada' && horaFinalizada(reserva)"
                          class="mini-btn success"
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
                      <td colspan="7" class="text-center py-6 text-muted">
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
                    class="btn-pagination"
                    type="button"
                    :disabled="paginaHistorial <= 1"
                    @click="cargarHistorial(paginaHistorial - 1)"
                  >
                    Anterior
                  </button>
                  <button
                    class="btn-pagination"
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
        </div>
      </main>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { IonPage, IonContent, IonSpinner, IonInput, IonIcon } from '@ionic/vue';
import { addIcons } from 'ionicons';
import {
  footballOutline,
  tennisballOutline,
  logOutOutline,
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
  funnelOutline,
  locationOutline,
  appsOutline,
  walletOutline
} from 'ionicons/icons';
import { useAuthStore } from '@/stores/auth';
import panelService from '@/services/panel.service';
import echo from '@/services/echo';

interface Complejo {
  id: number;
  nombre: string;
  ubicacion?: string;
}

interface Estadisticas {
  total_canchas: number;
  reservas_hoy: number;
  reservas_mes?: { total: number };
  solicitudes_mes?: { aceptadas: number; rechazadas: number };
  ingreso_estimado_mes?: number | string;
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
  'funnel-outline': funnelOutline,
  'location-outline': locationOutline,
  'apps-outline': appsOutline,
  'wallet-outline': walletOutline,
});

const router = useRouter();
const authStore = useAuthStore();

const complejoIdSeleccionado = ref<number | null>(null);
const complejoActual = ref<Complejo | null>(null);
const agenda = ref<{ canchas: CanchaAgenda[] } | null>(null);
const estadisticas = ref<Estadisticas | null>(null);
const cargandoInicial = ref(true);
const errorInicial = ref('');

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
const porPaginaHistorial = ref(15);

let complejoSuscritoId: number | null = null;
let mensajeTimer: ReturnType<typeof setTimeout> | null = null;

/* ---------- Utilidades ---------- */

function moneda(valor: number | string | undefined | null): string {
  return `₡${Number(valor || 0).toLocaleString('es-CR', { maximumFractionDigits: 0 })}`;
}

function mostrarMensaje(texto: string, esError = false) {
  mensajeReserva.value = texto;
  errorReserva.value = esError;
  if (mensajeTimer) clearTimeout(mensajeTimer);
  mensajeTimer = setTimeout(() => { mensajeReserva.value = ''; }, 6000);
}

function formatearHora(fechaIso: string | null | undefined): string {
  if (!fechaIso) return '';
  try {
    const fechaLimpia = fechaIso.split('.')[0].replace(' ', 'T');
    const date = new Date(fechaLimpia);
    if (isNaN(date.getTime())) return '';
    return date.toLocaleTimeString('es-CR', { hour: '2-digit', minute: '2-digit' });
  } catch {
    return '';
  }
}

const ESTADOS_INACTIVOS = ['pendiente', 'cancelada', 'rechazada', 'expirada'];

function reservasActivas(cancha: CanchaAgenda): any[] {
  return (cancha.reservas || []).filter((r) => !ESTADOS_INACTIVOS.includes(r.estado));
}

function puedeCancelar(r: any): boolean {
  return ['confirmada', 'aceptada'].includes(r.estado);
}

/* ---------- Carga de datos ---------- */

async function cargarAgenda() {
  if (!complejoIdSeleccionado.value) return;

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
    let solicitudes: Solicitud[] = data.data || [];

    const nombreComplejo = complejoActual.value?.nombre;
    if (nombreComplejo) {
      solicitudes = solicitudes.filter((s) => !s.complejo || s.complejo === nombreComplejo);
    }

    solicitudesPendientes.value = solicitudes.sort(
      (a, b) => (a.solicitud_id || a.reserva_id) - (b.solicitud_id || b.reserva_id)
    );
  } catch (error) {
    console.error('Error cargando solicitudes:', error);
    errorSolicitudes.value = 'No se pudieron cargar las solicitudes pendientes.';
  } finally {
    cargandoSolicitudes.value = false;
  }
}

async function cargarHistorial(pagina = 1) {
  try {
    const { data } = await panelService.listarReservas({
      fecha: fechaHistorial.value || undefined,
      page: pagina,
    });

    const items = data.data.data || [];

    // Más antigua primero (primera en llegar arriba)
    items.sort((a: any, b: any) => {
      const fechaA = new Date(`${String(a.fecha).slice(0, 10)}T${a.hora_inicio || '00:00'}`).getTime();
      const fechaB = new Date(`${String(b.fecha).slice(0, 10)}T${b.hora_inicio || '00:00'}`).getTime();
      if (fechaA === fechaB) return (a.id || 0) - (b.id || 0);
      return fechaA - fechaB;
    });

    historialReservas.value = items;
    paginaHistorial.value = data.data.current_page ?? 1;
    ultimaPaginaHistorial.value = data.data.last_page ?? 1;
    porPaginaHistorial.value = data.data.per_page ?? (items.length || 15);
  } catch (error) {
    console.error('Error cargando el historial:', error);
  }
}

function limpiarFiltroHistorial() {
  // El watch de fechaHistorial recarga; si ya estaba vacío, recargamos manualmente.
  if (fechaHistorial.value === '') {
    void cargarHistorial(1);
  } else {
    fechaHistorial.value = '';
  }
}

watch(fechaHistorial, () => {
  void cargarHistorial(1);
});

async function refrescarTodo() {
  await Promise.all([cargarAgenda(), cargarHistorial(paginaHistorial.value)]);
}

/* ---------- Tiempo real ---------- */

function suscribirComplejoActual() {
  const id = complejoIdSeleccionado.value;
  if (!id || complejoSuscritoId === id) return;
  if (complejoSuscritoId !== null) echo.leaveChannel(`complejo.${complejoSuscritoId}.disponibilidad`);
  complejoSuscritoId = id;
  echo.channel(`complejo.${id}.disponibilidad`).listen('.disponibilidad.actualizada', () => {
    void refrescarTodo();
  });
}

/* ---------- Acciones ---------- */

function horaFinalizada(reserva: any): boolean {
  if (!reserva.fecha || !reserva.hora_fin) return false;
  const [year, month, day] = String(reserva.fecha).slice(0, 10).split('-').map(Number);
  const [hour, minute] = String(reserva.hora_fin).split(':').map(Number);
  return new Date(year, month - 1, day, hour, minute).getTime() <= Date.now();
}

async function completarAlquiler(reserva: any) {
  try {
    await panelService.completarReserva(reserva.id);
    mostrarMensaje('Alquiler marcado como completado.');
    await refrescarTodo();
  } catch (error: any) {
    mostrarMensaje(error.response?.data?.message || 'No se pudo completar el alquiler.', true);
  }
}

async function cancelar(reservaId: number) {
  if (!window.confirm('¿Seguro que deseas cancelar esta reserva?')) return;
  try {
    await panelService.cancelarReserva(reservaId);
    mostrarMensaje('Reserva cancelada.');
    await refrescarTodo();
  } catch (error: any) {
    mostrarMensaje(error.response?.data?.message || 'No se pudo cancelar la reserva.', true);
  }
}

async function responderSolicitud(reservaId: number, decision: 'aceptar' | 'rechazar') {
  respondiendoSolicitud.value = true;
  try {
    await panelService.responderSolicitud(reservaId, decision);
    mostrarMensaje(
      decision === 'aceptar'
        ? 'Solicitud aceptada. Contacta al cliente por WhatsApp para coordinar el pago.'
        : 'Solicitud rechazada.'
    );
    await refrescarTodo();
  } catch (error: any) {
    mostrarMensaje(error.response?.data?.message || 'No se pudo responder la solicitud.', true);
  } finally {
    respondiendoSolicitud.value = false;
  }
}

async function confirmarPago(reservaId: number) {
  respondiendoSolicitud.value = true;
  try {
    await panelService.confirmarPago(reservaId);
    mostrarMensaje('Pago confirmado. La cancha quedó reservada.');
    await refrescarTodo();
  } catch (error: any) {
    mostrarMensaje(error.response?.data?.message || 'No se pudo confirmar el pago.', true);
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

/* ---------- Ciclo de vida ---------- */

onMounted(async () => {
  try {
    const { data } = await panelService.misComplejos();
    const complejos = data.data;

    if (complejos && complejos.length > 0) {
      complejoActual.value = complejos[0];
      complejoIdSeleccionado.value = complejos[0].id;
      await cargarAgenda();
      suscribirComplejoActual();
    }
    await cargarHistorial();
  } catch (err) {
    console.error('Error cargando los complejos:', err);
    errorInicial.value = 'No se pudieron cargar los datos del complejo. Intenta de nuevo.';
  } finally {
    cargandoInicial.value = false;
  }
});

onUnmounted(() => {
  if (mensajeTimer) clearTimeout(mensajeTimer);
  if (complejoSuscritoId !== null) echo.leaveChannel(`complejo.${complejoSuscritoId}.disponibilidad`);
});
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

/* Sección superior oscura */
.dark-top-section {
  background: linear-gradient(180deg, #040924 0%, #081039 100%);
  color: #ffffff;
  padding: 24px 48px 36px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.header-container {
  max-width: 1400px;
  margin: 0 auto 28px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.brand-brand-text { cursor: pointer; display: flex; flex-direction: column; }

.brand-title {
  font-size: 22px;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: #ffffff;
  line-height: 1;
}

.brand-title .dot-blue { color: #5b7eff; }

.brand-sub {
  font-size: 9px;
  font-weight: 800;
  letter-spacing: 0.14em;
  color: #64748b;
  margin-top: 4px;
}

.navbar-actions { display: flex; align-items: center; gap: 12px; }

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
  background: rgba(239, 68, 68, 0.15);
  color: #fca5a5;
  border-color: rgba(239, 68, 68, 0.3);
}

.hero-card-banner { max-width: 1400px; margin: 0 auto; }

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

.badge-separator { color: #475569; }
.badge-subtext { color: #94a3b8; }

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

.hero-location-icon { font-size: 16px; color: #60a5fa; }

/* Pestañas */
.panel-tabs-bar {
  display: flex;
  gap: 8px;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 12px;
}

.tab-item {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: transparent;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 700;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
}

.tab-item.active { background: #e0e7ff; color: #4338ca; }
.tab-item:hover:not(.active) { background: #f1f5f9; color: #334155; }

/* Cuerpo */
.light-body-section { padding: 28px 48px 80px; }
.body-container { max-width: 1400px; margin: 0 auto; }
.dashboard-grid-content { display: flex; flex-direction: column; gap: 28px; }

/* Métricas */
.metrics-grid-four {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.metric-white-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 20px 24px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.02);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.metric-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.metric-label { font-size: 13px; font-weight: 700; color: #64748b; }
.metric-icon-blue { font-size: 18px; color: #6366f1; }
.metric-value-row { margin-bottom: 8px; }

.metric-main-number {
  font-size: 28px;
  font-weight: 800;
  color: #0f172a;
  letter-spacing: -0.02em;
  line-height: 1;
}

.metric-footer-note { font-size: 12px; color: #94a3b8; font-weight: 500; }

/* Estados de carga */
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

.main-spinner { width: 40px; height: 40px; color: #6366f1; }

.card-loading-inline {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 32px 0;
  color: #64748b;
  font-size: 13px;
}

/* Demanda */
.demand-strip-card {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  padding: 14px 20px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.demand-title {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #64748b;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.flame-icon { color: #f97316; font-size: 18px; }
.demand-tags { display: flex; flex-wrap: wrap; gap: 8px; }

.demand-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #eef2ff;
  border: 1px solid #c7d2fe;
  padding: 4px 12px;
  border-radius: 8px;
  font-size: 12px;
}

.demand-pill .hour { color: #091133; font-weight: 800; }
.demand-pill .count { color: #4338ca; font-weight: 700; }

/* Tarjetas */
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
  gap: 16px;
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

.empty-state-card.error { border-color: #fca5a5; color: #dc2626; }

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

.empty-title { margin: 0; font-size: 16px; font-weight: 800; color: #091133; }
.empty-sub { font-size: 13px; color: #64748b; margin-top: 4px; }

/* Solicitudes */
.requests-list { display: grid; gap: 12px; }

.request-item-card {
  display: grid;
  grid-template-columns: 1fr auto;
  align-items: center;
  gap: 20px;
  padding: 16px 20px;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  background: #ffffff;
  transition: box-shadow 0.2s ease, border-color 0.2s ease;
}

.request-item-card:hover { border-color: #cbd5e1; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04); }

.request-title-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
}

.request-kicker { font-size: 11px; font-weight: 800; color: #6366f1; text-transform: uppercase; }
.client-name { margin: 2px 0 0; font-size: 16px; font-weight: 800; color: #091133; }

.request-expiry-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 700;
  color: #b45309;
  background: #fef3c7;
  padding: 4px 10px;
  border-radius: 6px;
  white-space: nowrap;
}

.request-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 14px;
  margin-top: 8px;
  font-size: 12px;
  color: #64748b;
}

.meta-item { display: inline-flex; align-items: center; gap: 4px; }
.meta-item.phone { color: #4338ca; font-weight: 700; text-decoration: none; }

.request-hours { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }

.time-badge {
  padding: 4px 10px;
  background: #eef2ff;
  border: 1px solid #c7d2fe;
  border-radius: 6px;
  color: #3730a3;
  font-size: 12px;
  font-weight: 700;
}

.request-notes {
  margin: 8px 0 0;
  font-size: 12px;
  color: #475569;
  background: #f8fafc;
  padding: 6px 10px;
  border-radius: 6px;
}

.request-actions { display: flex; flex-direction: column; gap: 8px; width: 160px; }

.btn-action-primary,
.btn-action-gold,
.btn-action-soft {
  padding: 8px 14px;
  border-radius: 8px;
  font-size: 12px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  text-decoration: none;
}

.btn-action-primary { background: #6366f1; border: 0; color: #ffffff; font-weight: 800; }
.btn-action-gold { background: #f59e0b; border: 0; color: #ffffff; font-weight: 800; }
.btn-action-soft { background: #f1f5f9; border: 1px solid #cbd5e1; color: #334155; font-weight: 700; }
.btn-action-soft.whatsapp { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
.btn-action-soft.danger { background: #fef2f2; border-color: #fecaca; color: #991b1b; }

.btn-action-primary:disabled,
.btn-action-gold:disabled,
.btn-action-soft:disabled { opacity: 0.55; cursor: not-allowed; }

/* Agenda */
.two-columns-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 28px; align-items: start; }
.agenda-list { display: flex; flex-direction: column; gap: 16px; }

.agenda-item {
  border: 1px solid #f1f5f9;
  border-radius: 12px;
  padding: 16px;
  background: #f8fafc;
}

.agenda-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.cancha-title-wrap { display: flex; align-items: center; gap: 8px; }
.cancha-icon { color: #6366f1; font-size: 18px; }
.agenda-head h3 { margin: 0; font-size: 15px; font-weight: 800; color: #091133; }

.badge-deporte {
  font-size: 10px;
  font-weight: 800;
  background: #eef2ff;
  color: #4338ca;
  padding: 2px 8px;
  border-radius: 4px;
  text-transform: uppercase;
}

.timeline-list { display: flex; flex-direction: column; gap: 8px; }

.timeline-row {
  display: grid;
  grid-template-columns: 130px 1fr auto;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
}

.timeline-row.blocked { background: #f8fafc; }

.timeline-time {
  font-size: 12px;
  font-weight: 800;
  color: #6366f1;
  display: flex;
  align-items: center;
  gap: 4px;
}

.timeline-time.muted { color: #94a3b8; }

.timeline-content { display: flex; flex-direction: column; }
.client-name-sm { font-size: 13px; font-weight: 700; color: #091133; }

.status-chip {
  display: inline-block;
  align-self: flex-start;
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
  padding: 2px 6px;
  border-radius: 4px;
  background: #f1f5f9;
  color: #64748b;
  margin-top: 2px;
}

.status-chip.confirmada { background: #dcfce7; color: #15803d; }
.status-chip.aceptada { background: #fef3c7; color: #b45309; }
.status-chip.completada { background: #eef2ff; color: #4338ca; }

.reservation-actions { display: flex; gap: 6px; }

.mini-btn {
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 800;
  cursor: pointer;
  border: 0;
}

.mini-btn.success { background: #dcfce7; color: #15803d; }
.mini-btn.danger { background: #fee2e2; color: #b91c1c; }

.empty-inline {
  font-size: 12px;
  color: #94a3b8;
  margin: 8px 0 0;
  display: flex;
  align-items: center;
  gap: 6px;
}

/* Guía */
.flow-steps { display: flex; flex-direction: column; gap: 12px; margin: 0; padding: 0; list-style: none; }

.step-item {
  display: flex;
  gap: 12px;
  padding: 12px 14px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
}

.step-number {
  width: 26px;
  height: 26px;
  border-radius: 8px;
  background: #6366f1;
  color: #ffffff;
  font-weight: 800;
  display: grid;
  place-items: center;
  font-size: 13px;
  flex-shrink: 0;
}

.step-body strong { display: block; font-size: 13px; color: #091133; }
.step-body span { display: block; font-size: 12px; color: #64748b; margin-top: 2px; }

/* Banner de mensajes */
.message-banner {
  padding: 12px 16px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 8px;
}

.message-banner.success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.message-banner.error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

/* Historial */
.history-filters { display: flex; align-items: center; gap: 8px; }
.filter-input-wrap { display: flex; align-items: center; gap: 6px; }
.filter-icon { color: #94a3b8; font-size: 16px; }

.light-date-input {
  --background: #ffffff;
  --color: #091133;
  --padding-start: 10px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  min-height: 36px;
  font-size: 12px;
  width: 150px;
}

.btn-filter-reset {
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  color: #475569;
  font-size: 12px;
  font-weight: 700;
  padding: 8px 14px;
  border-radius: 8px;
  cursor: pointer;
}

.table-responsive {
  width: 100%;
  overflow-x: auto;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
}

.light-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; }

.light-table th {
  padding: 12px 16px;
  background: #f8fafc;
  color: #64748b;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  border-bottom: 1px solid #e2e8f0;
  white-space: nowrap;
}

.light-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; color: #091133; }

.cancha-badge {
  background: #f1f5f9;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.table-status-pill {
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
  padding: 3px 8px;
  border-radius: 4px;
  background: #f1f5f9;
  color: #64748b;
}

.table-status-pill.aceptada,
.table-status-pill.confirmada { background: #dcfce7; color: #15803d; }
.table-status-pill.completada { background: #eef2ff; color: #4338ca; }
.table-status-pill.rechazada,
.table-status-pill.cancelada { background: #fee2e2; color: #b91c1c; }

.history-pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 16px;
  font-size: 12px;
  color: #64748b;
}

.pagination-buttons { display: flex; gap: 8px; }

.btn-pagination {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  padding: 6px 14px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  color: #091133;
  cursor: pointer;
}

.btn-pagination:disabled { opacity: 0.5; cursor: not-allowed; }

/* Utilidades */
.font-mono { font-variant-numeric: tabular-nums; }
.font-bold { font-weight: 700; }
.text-xs { font-size: 12px; }
.text-blue { color: #4338ca; }
.text-muted { color: #94a3b8; }
.text-success { color: #15803d; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.py-6 { padding-top: 24px; padding-bottom: 24px; }

@media (max-width: 1200px) {
  .metrics-grid-four { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 1100px) {
  .dark-top-section { padding: 16px 20px 30px; }
  .light-body-section { padding: 24px 20px 60px; }
  .hero-banner-content { flex-direction: column; align-items: flex-start; gap: 20px; }
  .two-columns-grid { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
  .metrics-grid-four { grid-template-columns: 1fr; }
  .request-item-card { grid-template-columns: 1fr; }
  .request-actions { width: 100%; flex-direction: row; flex-wrap: wrap; }
  .timeline-row { grid-template-columns: 1fr; }
  .history-header { flex-direction: column; }
  .hero-main-title { font-size: 26px; }
}
</style>