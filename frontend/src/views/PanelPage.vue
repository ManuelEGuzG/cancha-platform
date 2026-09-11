<template>
  <ion-page class="panel-page">
    <ion-header class="panel-header">
      <ion-toolbar class="panel-toolbar">
        <ion-title>{{ complejoActual?.nombre || 'Panel' }}</ion-title>
        <ion-buttons slot="end">
          <ion-button class="header-link" @click="irACanchas">Canchas</ion-button>
          <ion-button class="header-link danger" @click="cerrarSesion">Salir</ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content class="panel-content">
      <div v-if="cargandoInicial" class="loading-shell">
        <ion-spinner name="crescent"></ion-spinner>
      </div>

      <div v-else class="dashboard-shell">
        <section class="topbar">
          <div>
            <span class="eyebrow">Gestión deportiva</span>
            <h1>Panel del complejo</h1>
          </div>
          <div class="topbar-actions">
            <button class="btn-primary" @click="irACanchas">Gestionar canchas</button>
          </div>
        </section>

        <div v-if="misComplejos.length > 1" class="selector-card">
          <label class="selector-label">Complejo activo</label>
          <ion-select v-model="complejoIdSeleccionado" @ionChange="cargarAgenda" class="complex-select">
            <ion-select-option v-for="c in misComplejos" :key="c.id" :value="c.id">{{ c.nombre }}</ion-select-option>
          </ion-select>
        </div>

        <section class="stats-grid" v-if="estadisticas">
          <div class="stat-card">
            <span class="stat-label">Canchas</span>
            <strong>{{ estadisticas.total_canchas }}</strong>
          </div>
          <div class="stat-card">
            <span class="stat-label">Reservas hoy</span>
            <strong>{{ estadisticas.reservas_hoy }}</strong>
          </div>
          <div class="stat-card">
            <span class="stat-label">Este mes</span>
            <strong>{{ estadisticas.reservas_mes.total }}</strong>
          </div>
        </section>

        <section class="content-grid">
          <div class="panel-card wide-card">
            <div class="card-header">
              <div>
                <span class="card-kicker">Agenda</span>
                <h2>Horarios del día</h2>
              </div>
            </div>

            <div v-if="agenda?.canchas?.length" class="agenda-list">
              <div v-for="cancha in agenda.canchas" :key="cancha.cancha_id" class="agenda-item">
                <div class="agenda-head">
                  <h3>{{ cancha.nombre }}</h3>
                  <span>{{ cancha.deporte }}</span>
                </div>

                <div class="timeline-list">
                  <div v-for="r in cancha.reservas" :key="r.id" class="timeline-row reservation">
                    <div class="timeline-time">{{ r.hora_inicio }} - {{ r.hora_fin }}</div>
                    <div class="timeline-content">
                      <strong>{{ r.nombre_cliente }}</strong>
                      <small>{{ r.estado }}</small>
                    </div>
                    <button class="mini-button danger" @click="cancelar(r.id)">Cancelar</button>
                  </div>

                  <div v-for="b in cancha.bloqueos" :key="'b' + b.id" class="timeline-row blocked">
                    <div class="timeline-time">{{ b.hora_inicio }} - {{ b.hora_fin }}</div>
                    <div class="timeline-content">
                      <strong>Bloqueado</strong>
                      <small>{{ b.motivo }}</small>
                    </div>
                  </div>
                </div>

                <p v-if="!cancha.reservas.length && !cancha.bloqueos.length" class="empty-inline">Sin reservas ni bloqueos hoy.</p>
              </div>
            </div>

            <p v-else class="empty-message">No hay agenda disponible para este complejo.</p>
          </div>

          <div class="panel-card">
            <div class="card-header">
              <div>
                <span class="card-kicker">Operación</span>
                <h2>Nueva reserva</h2>
              </div>
            </div>

            <div class="form-grid">
              <div class="field-group full">
                <label>Cancha</label>
                <ion-select v-model="nuevaReserva.cancha_id" interface="popover" placeholder="Selecciona una cancha">
                  <ion-select-option v-for="cancha in agenda?.canchas" :key="cancha.cancha_id" :value="cancha.cancha_id">
                    {{ cancha.nombre }}
                  </ion-select-option>
                </ion-select>
              </div>

              <div class="field-group full">
                <label>Nombre del cliente</label>
                <ion-input v-model="nuevaReserva.nombre_cliente" placeholder="Ej. Ana García"></ion-input>
              </div>

              <div class="field-group full">
                <label>Teléfono</label>
                <ion-input v-model="nuevaReserva.telefono_cliente" placeholder="Opcional"></ion-input>
              </div>

              <div class="field-group">
                <label>Fecha</label>
                <ion-input v-model="nuevaReserva.fecha" type="date"></ion-input>
              </div>

              <div class="field-group">
                <label>Hora inicio</label>
                <ion-input v-model="nuevaReserva.hora_inicio" type="time"></ion-input>
              </div>

              <div class="field-group">
                <label>Hora fin</label>
                <ion-input v-model="nuevaReserva.hora_fin" type="time"></ion-input>
              </div>
            </div>

            <button class="btn-submit" @click="crearReserva">Guardar reserva</button>

            <p v-if="mensajeReserva" :class="['message-banner', errorReserva ? 'error' : 'success']">{{ mensajeReserva }}</p>
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
  IonPage, IonHeader, IonToolbar, IonTitle, IonButtons, IonButton, IonContent,
  IonSpinner, IonSelect, IonSelectOption, IonItem, IonLabel, IonInput,
} from '@ionic/vue';
import { useAuthStore } from '@/stores/auth';
import panelService from '@/services/panel.service';

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
  } catch (e: any) {
    errorReserva.value = true;
    mensajeReserva.value = e.response?.data?.message || 'Error al crear la reserva.';
  }
}

async function cancelar(reservaId: number) {
  await panelService.cancelarReserva(reservaId);
  await cargarAgenda();
}

async function cerrarSesion() {
  await authStore.logout();
  router.push('/login');
}

function irACanchas() {
  router.push({ path: '/panel/canchas', query: { complejoId: complejoIdSeleccionado.value } });
}

onMounted(async () => {
  const { data } = await panelService.misComplejos();
  misComplejos.value = data.data;

  if (misComplejos.value.length > 0) {
    complejoIdSeleccionado.value = misComplejos.value[0].id;
    await cargarAgenda();
  }

  cargandoInicial.value = false;
});
</script>

<style scoped>
.panel-page {
  --bg-page: #f4fbff;
  --panel-surface: #ffffff;
  --panel-muted: #edf9ff;
  --primary: #1D5C94;
  --primary-dark: #08226C;
  --text-main: #0f172a;
  --text-soft: #64748b;
  --border: #dfeaf7;
  --danger: #ef4444;
  --success: #10b981;
  --accent: #66E3DA;
}

.panel-content {
  --background: var(--bg-page);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.dashboard-shell {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1.5rem;
}

.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.25rem;
}

.eyebrow,
.card-kicker {
  display: inline-block;
  color: var(--primary);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 0.7rem;
  font-weight: 800;
}

.topbar h1,
.card-header h2 {
  margin: 0.25rem 0 0;
  color: var(--text-main);
  letter-spacing: -0.03em;
}

.topbar h1 {
  font-size: clamp(1.8rem, 3vw, 2.5rem);
}

.topbar-actions {
  display: flex;
  gap: 0.7rem;
}

.btn-primary,
.btn-submit,
.mini-button {
  border: none;
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-primary,
.btn-submit {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  color: #ffffff;
  padding: 0.8rem 1rem;
  box-shadow: 0 10px 20px rgba(0, 102, 255, 0.2);
}

.btn-primary:hover,
.btn-submit:hover {
  transform: translateY(-1px);
}

.selector-card,
.panel-card,
.stat-card {
  background: var(--panel-surface);
  border: 1px solid var(--border);
  border-radius: 18px;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
}

.selector-card {
  padding: 1rem 1.1rem;
  margin-bottom: 1.25rem;
}

.selector-label {
  display: block;
  color: var(--text-soft);
  font-size: 0.75rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.complex-select {
  --background: #f8fafc;
  border-radius: 10px;
  overflow: hidden;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  padding: 1.1rem 1rem;
}

.stat-label {
  color: var(--text-soft);
  display: block;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 0.5rem;
  font-weight: 700;
}

.stat-card strong {
  color: var(--text-main);
  font-size: clamp(1.4rem, 3vw, 2rem);
  letter-spacing: -0.04em;
}

.content-grid {
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: 1.25rem;
}

.panel-card {
  padding: 1.15rem;
}

.card-header {
  margin-bottom: 1rem;
}

.agenda-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.agenda-item {
  padding: 1rem;
  border: 1px solid var(--border);
  border-radius: 14px;
  background: #f8fafc;
}

.agenda-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  margin-bottom: 0.8rem;
}

.agenda-head h3 {
  margin: 0;
  font-size: 1.02rem;
  color: var(--text-main);
}

.agenda-head span {
  font-size: 0.72rem;
  background: var(--panel-muted);
  color: var(--primary);
  padding: 0.3rem 0.55rem;
  border-radius: 999px;
  font-weight: 700;
}

.timeline-list {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.timeline-row {
  display: grid;
  grid-template-columns: 120px 1fr auto;
  gap: 0.75rem;
  align-items: center;
  padding: 0.7rem 0.8rem;
  border-radius: 12px;
  border: 1px solid transparent;
}

.timeline-row.reservation {
  background: rgba(16, 185, 129, 0.08);
  border-color: rgba(16, 185, 129, 0.15);
}

.timeline-row.blocked {
  background: rgba(15, 23, 42, 0.04);
  border-color: rgba(100, 116, 139, 0.12);
}

.timeline-time {
  font-weight: 800;
  color: var(--text-main);
  font-size: 0.8rem;
}

.timeline-content {
  display: flex;
  flex-direction: column;
  gap: 0.12rem;
}

.timeline-content strong {
  font-size: 0.85rem;
  color: var(--text-main);
}

.timeline-content small {
  font-size: 0.7rem;
  color: var(--text-soft);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.mini-button {
  background: transparent;
  border: 1px solid rgba(239, 68, 68, 0.22);
  color: var(--danger);
  padding: 0.45rem 0.7rem;
  font-size: 0.7rem;
}

.empty-inline,
.empty-message {
  margin: 0.6rem 0 0;
  color: var(--text-soft);
  font-size: 0.85rem;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.9rem;
}

.field-group {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
}

.field-group.full {
  grid-column: 1 / -1;
}

.field-group label {
  color: var(--text-soft);
  font-weight: 700;
  font-size: 0.76rem;
  text-transform: uppercase;
  letter-spacing: 0.07em;
}

.field-group ion-input,
.field-group ion-select {
  --background: #f8fafc;
  --border-radius: 12px;
  --padding-start: 0.8rem;
  --padding-end: 0.8rem;
  min-height: 46px;
  border: 1px solid var(--border);
  border-radius: 12px;
}

.btn-submit {
  width: 100%;
  margin-top: 1rem;
}

.message-banner {
  margin-top: 0.9rem;
  padding: 0.7rem 0.8rem;
  border-radius: 10px;
  font-size: 0.82rem;
  font-weight: 700;
}

.message-banner.success {
  background: rgba(16, 185, 129, 0.1);
  border: 1px solid rgba(16, 185, 129, 0.18);
  color: #047857;
}

.message-banner.error {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.18);
  color: #b91c1c;
}

@media (max-width: 800px) {
  .stats-grid,
  .content-grid,
  .form-grid {
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

.panel-header {
  box-shadow: 0 1px 0 rgba(148, 163, 184, 0.18);
}

.panel-toolbar {
  --background: rgba(255, 255, 255, 0.92);
  --color: var(--text-main);
  backdrop-filter: blur(8px);
}

.header-link {
  --background: transparent;
  --color: var(--primary);
  font-weight: 700;
}

.header-link.danger {
  --color: var(--danger);
}

.wide-card {
  min-height: 100%;
}
</style>