<template>
  <ion-page class="horarios-page">
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
          <button class="nav-chip-btn" @click="volverAlPanel">
            <ion-icon name="arrow-back-outline" class="chip-icon"></ion-icon>
            <span>Volver al Panel</span>
          </button>
        </div>
      </div>
    </header>

    <ion-content class="horarios-content">
      <!-- Glow Background Effects -->
      <div class="page-background-glow glow-float-1"></div>
      <div class="page-background-glow glow-float-2"></div>
      <div class="bg-grid"></div>

      <!-- Spinner Inicial -->
      <div v-if="cargando" class="loading-shell">
        <ion-spinner name="crescent" class="main-spinner"></ion-spinner>
      </div>

      <!-- Main Layout -->
      <div v-else class="management-layout">
        <div class="page-header">
          <span class="eyebrow">Configuración de Disponibilidad</span>
          <h1 class="page-title">Horarios y Bloqueos</h1>
        </div>

        <!-- Sección Horario Regular -->
        <section class="panel-card-glass">
          <div class="card-header">
            <div>
              <span class="card-kicker">Programación</span>
              <h2 class="card-title">Horario regular</h2>
            </div>
          </div>

          <div class="weekly-schedule-list">
            <div v-for="dia in horariosSemanal" :key="dia.dia_semana" class="weekly-schedule-row">
              <div class="weekday-toggle">
                <strong>{{ dia.nombre }}</strong>
                <ion-toggle v-model="dia.abierto" class="neon-toggle"></ion-toggle>
              </div>
              <template v-if="dia.abierto">
                <div class="field-group">
                  <label :for="`apertura-${dia.dia_semana}`">Abre</label>
                  <ion-input :id="`apertura-${dia.dia_semana}`" v-model="dia.hora_apertura" type="time" class="custom-input-dark"></ion-input>
                </div>
                <div class="field-group">
                  <label :for="`cierre-${dia.dia_semana}`">Cierra</label>
                  <ion-input :id="`cierre-${dia.dia_semana}`" v-model="dia.hora_cierre" type="time" class="custom-input-dark"></ion-input>
                </div>
              </template>
              <span v-else class="closed-day-label">Cerrado</span>
            </div>
          </div>

          <button class="btn-submit-neon" @click="guardarHorarioRegular">Guardar horario semanal</button>
        </section>

        <!-- Sección Excepciones -->
        <section class="panel-card-glass">
          <div class="card-header">
            <div>
              <span class="card-kicker">Excepciones</span>
              <h2 class="card-title">Feriados y eventos</h2>
            </div>
          </div>

          <div class="stack-list" v-if="horariosExcepcion.length">
            <div v-for="ex in horariosExcepcion" :key="ex.id" class="list-item-glass">
              <div>
                <strong>{{ ex.fecha }}</strong>
                <small>
                  {{ ex.hora_apertura ? `${ex.hora_apertura} a ${ex.hora_cierre}` : 'Cerrado todo el día' }}
                  — {{ ex.motivo }}
                </small>
              </div>
              <button class="mini-button-danger" @click="eliminarExcepcion(ex.id)">Eliminar</button>
            </div>
          </div>

          <p v-else class="empty-inline">No hay excepciones programadas.</p>

          <div class="field-grid compact">
            <div class="field-group full">
              <label>Fecha</label>
              <ion-input v-model="nuevaExcepcion.fecha" type="date" class="custom-input-dark"></ion-input>
            </div>
            <div class="field-group">
              <label>Hora apertura</label>
              <ion-input v-model="nuevaExcepcion.hora_apertura" type="time" class="custom-input-dark"></ion-input>
            </div>
            <div class="field-group">
              <label>Hora cierre</label>
              <ion-input v-model="nuevaExcepcion.hora_cierre" type="time" class="custom-input-dark"></ion-input>
            </div>
            <div class="field-group full">
              <label>Motivo</label>
              <ion-input 
                v-model="nuevaExcepcion.motivo" 
                placeholder="Ej. Feriado, evento, mantenimiento" 
                class="custom-input-dark"
              ></ion-input>
            </div>
          </div>

          <button class="btn-secondary-glass" @click="crearExcepcion">Agregar excepción</button>
        </section>

        <!-- Sección Bloqueos -->
        <section class="panel-card-glass">
          <div class="card-header">
            <div>
              <span class="card-kicker">Bloqueo</span>
              <h2 class="card-title">Bloquear horario</h2>
            </div>
          </div>

          <div class="field-grid compact">
            <div class="field-group full">
              <label>Fecha</label>
              <ion-input v-model="nuevoBloqueo.fecha" type="date" class="custom-input-dark"></ion-input>
            </div>
            <div class="field-group">
              <label>Hora inicio</label>
              <ion-input v-model="nuevoBloqueo.hora_inicio" type="time" class="custom-input-dark"></ion-input>
            </div>
            <div class="field-group">
              <label>Hora fin</label>
              <ion-input v-model="nuevoBloqueo.hora_fin" type="time" class="custom-input-dark"></ion-input>
            </div>
            <div class="field-group full">
              <label>Motivo</label>
              <ion-select v-model="nuevoBloqueo.motivo" interface="popover" class="custom-input-dark">
                <ion-select-option value="mantenimiento">Mantenimiento</ion-select-option>
                <ion-select-option value="evento">Evento</ion-select-option>
                <ion-select-option value="reparacion">Reparación</ion-select-option>
                <ion-select-option value="uso_interno">Uso interno</ion-select-option>
                <ion-select-option value="otro">Otro</ion-select-option>
              </ion-select>
            </div>
          </div>

          <button class="btn-dark-glass" @click="crearBloqueo">Bloquear horario</button>
          <div v-if="bloqueos.length" class="stack-list block-list">
            <div v-for="bloqueo in bloqueos" :key="bloqueo.id" class="list-item-glass">
              <div><strong>{{ bloqueo.fecha }}</strong><small>{{ bloqueo.hora_inicio }} - {{ bloqueo.hora_fin }} · {{ bloqueo.motivo }}</small></div>
              <button class="mini-button-danger" @click="eliminarBloqueo(bloqueo.id)">Quitar</button>
            </div>
          </div>
          <p v-else class="empty-inline">No hay bloqueos futuros.</p>
        </section>

        <!-- Banner de Mensaje -->
        <p v-if="mensaje || error" class="message-banner" :class="{ 'message-error': error }" role="status">
          <ion-icon name="checkmark-circle-outline"></ion-icon>
          <span>{{ error || mensaje }}</span>
        </p>
      </div>
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
  checkmarkCircleOutline 
} from 'ionicons/icons';
import horariosService from '@/services/horarios.service';
import panelService from '@/services/panel.service';

addIcons({
  'football-outline': footballOutline,
  'arrow-back-outline': arrowBackOutline,
  'checkmark-circle-outline': checkmarkCircleOutline,
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
    await cargarHorarios();
  } catch (fallo: any) {
    error.value = fallo.response?.data?.message || 'No se pudo eliminar la excepción.';
  }
}

async function crearBloqueo() {
  error.value = '';
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
    await cargarHorarios();
  } catch (fallo: any) {
    error.value = fallo.response?.data?.message || 'No se pudo retirar el bloqueo.';
  }
}

onMounted(cargarHorarios);
</script>

<style scoped>
.horarios-page {
  font-family: 'DM Sans', -apple-system, sans-serif;
  color: #17251e;
  background-color: #f1f6f1;
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
  max-width: 1200px;
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
  font-size: 1.25rem;
  color: #17251e;
}

.dot-neon {
  color: #17634b;
}

/* ACCIONES NAVEGACIÓN */
.navbar-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
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

.nav-chip-btn:hover {
  background: #eef5ec;
  border-color: #c3d6c6;
}

/* VIEWPORT & BACKGROUND */
.horarios-content {
  --background: #f1f6f1;
}

.page-background-glow,
.bg-grid {
  display: none;
}

/* SPINNER */
.loading-shell {
  min-height: 70vh;
  display: flex;
  justify-content: center;
  align-items: center;
}

.main-spinner {
  width: 40px;
  height: 40px;
  color: #17634b;
}

/* LAYOUT PRINCIPAL */
.management-layout {
  max-width: 1000px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  padding: 1.5rem 1.5rem 3rem;
  position: relative;
  z-index: 2;
}

.page-header {
  margin-bottom: 0.25rem;
}

.eyebrow {
  display: block;
  color: #17634b;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 0.725rem;
  font-weight: 700;
}

.page-title {
  font-family: 'Barlow Condensed', sans-serif;
  margin: 0.2rem 0 0;
  color: #17251e;
  font-size: clamp(1.9rem, 3vw, 2.4rem);
  font-weight: 700;
  line-height: 1.05;
}

.panel-card-glass {
  background: #ffffff;
  border: 1px solid #d7e2d8;
  border-radius: 6px;
  padding: 1.5rem;
  box-shadow: 0 10px 28px rgba(23, 49, 35, 0.06);
}

.card-header {
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

.field-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
}

.field-grid.compact {
  margin-top: 1.25rem;
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
  color: #66736b;
  font-weight: 700;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.custom-input-dark {
  --background: #ffffff;
  --color: #17251e;
  --placeholder-color: #8a968d;
  --padding-start: 0.85rem;
  --padding-end: 0.85rem;
  min-height: 42px;
  border: 1px solid #d7e2d8;
  border-radius: 4px;
  font-size: 0.875rem;
  transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.custom-input-dark:focus-within {
  border-color: #17634b;
  box-shadow: 0 0 0 3px rgba(23, 99, 75, 0.13);
}

/* BOTONES */
.btn-submit-neon {
  width: 100%;
  margin-top: 1.25rem;
  background: #d4ed66;
  color: #142219;
  border: 1px solid #c2dd4e;
  border-radius: 4px;
  padding: 0.8rem;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  transition: background-color 0.18s ease;
}

.btn-submit-neon:hover {
  background: #c2dd4e;
}

.btn-secondary-glass {
  width: 100%;
  margin-top: 1.25rem;
  background: #ffffff;
  border: 1px solid #17634b;
  color: #17634b;
  border-radius: 4px;
  padding: 0.8rem;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  transition: background-color 0.18s ease;
}

.btn-secondary-glass:hover {
  background: #eef5ec;
}

.btn-dark-glass {
  width: 100%;
  margin-top: 1.25rem;
  background: #17634b;
  border: 1px solid #17634b;
  color: #ffffff;
  border-radius: 4px;
  padding: 0.8rem;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  transition: background-color 0.18s ease;
}

.btn-dark-glass:hover {
  background: #103b2e;
}

/* LISTAS */
.stack-list {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}

.list-item-glass {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.8rem;
  background: #f9fbf7;
  border: 1px solid #e0e8de;
  border-radius: 6px;
  padding: 0.8rem 1rem;
}

.list-item-glass strong {
  display: block;
  color: #17251e;
  font-size: 0.9rem;
  font-variant-numeric: tabular-nums;
}

.list-item-glass small {
  display: block;
  color: #66736b;
  margin-top: 0.15rem;
  line-height: 1.4;
  font-size: 0.8rem;
}

.mini-button-danger {
  background: #fff0eb;
  border: 1px solid #efc5b9;
  color: #9d3c2f;
  padding: 0.4rem 0.75rem;
  font-size: 0.725rem;
  font-weight: 700;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.18s ease;
}

.mini-button-danger:hover {
  background: #f9ddd3;
}

.empty-inline {
  color: #66736b;
  font-size: 0.825rem;
  margin-bottom: 0.5rem;
}

.message-banner {
  margin-top: 0.5rem;
  padding: 0.8rem 1rem;
  border-radius: 4px;
  background: #e8f3e9;
  border: 1px solid #c9dfcd;
  color: #17634b;
  font-size: 0.85rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.message-banner.message-error {
  background: #fff0eb;
  border-color: #efc5b9;
  color: #9d3c2f;
}

.message-banner ion-icon {
  font-size: 1.1rem;
}

/* HORARIO SEMANAL */
.weekly-schedule-list {
  display: flex;
  flex-direction: column;
  gap: 0.55rem;
}

.weekly-schedule-row {
  display: grid;
  grid-template-columns: 170px 1fr 1fr;
  gap: 0.9rem;
  align-items: center;
  padding: 0.7rem 0.9rem;
  background: #f9fbf7;
  border: 1px solid #e0e8de;
  border-radius: 6px;
}

.weekday-toggle {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.6rem;
}

.weekday-toggle strong {
  color: #17251e;
  font-size: 0.875rem;
}

.neon-toggle {
  --background: #dfe7dd;
  --background-checked: #17634b;
  --handle-background: #ffffff;
  --handle-background-checked: #ffffff;
}

.closed-day-label {
  color: #9d3c2f;
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.block-list {
  margin-top: 1.25rem;
}

@media (max-width: 700px) {
  .top-navbar-floating {
    padding: 0.75rem 0.75rem 0.25rem;
  }

  .navbar-pill {
    padding: 0.5rem 0.85rem;
  }

  .field-grid {
    grid-template-columns: 1fr;
  }

  .weekly-schedule-row {
    grid-template-columns: 1fr;
    gap: 0.6rem;
  }

  .list-item-glass {
    align-items: flex-start;
    flex-direction: column;
  }
}
</style>