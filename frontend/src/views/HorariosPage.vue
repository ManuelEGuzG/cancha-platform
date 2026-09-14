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

          <div class="field-grid">
            <div class="field-group">
              <label>Hora apertura</label>
              <ion-input v-model="horaApertura" type="time" class="custom-input-dark"></ion-input>
            </div>
            <div class="field-group">
              <label>Hora cierre</label>
              <ion-input v-model="horaCierre" type="time" class="custom-input-dark"></ion-input>
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
        </section>

        <!-- Banner de Mensaje -->
        <p v-if="mensaje" class="message-banner">
          <ion-icon name="checkmark-circle-outline"></ion-icon>
          <span>{{ mensaje }}</span>
        </p>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  IonPage, IonContent, IonInput, IonSpinner, IonSelect, IonSelectOption, IonIcon
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

const cargando = ref(true);
const horariosExcepcion = ref<any[]>([]);
const horaApertura = ref('16:00');
const horaCierre = ref('23:00');
const mensaje = ref('');

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

    if (data.data.horarios_regulares.length > 0) {
      horaApertura.value = data.data.horarios_regulares[0].hora_apertura.slice(0, 5);
      horaCierre.value = data.data.horarios_regulares[0].hora_cierre.slice(0, 5);
    }
  } catch (error) {
    console.error('Error al cargar horarios:', error);
  } finally {
    cargando.value = false;
  }
}

async function guardarHorarioRegular() {
  const horarios = Array.from({ length: 7 }, (_, dia) => ({
    dia_semana: dia,
    hora_apertura: horaApertura.value,
    hora_cierre: horaCierre.value,
  }));

  await horariosService.actualizarRegular(canchaId, horarios);
  mensaje.value = 'Horario semanal actualizado correctamente.';
}

async function crearExcepcion() {
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
}

async function eliminarExcepcion(id: number) {
  await horariosService.eliminarExcepcion(id);
  await cargarHorarios();
}

async function crearBloqueo() {
  await panelService.crearBloqueo({
    cancha_id: canchaId,
    fecha: nuevoBloqueo.value.fecha,
    hora_inicio: nuevoBloqueo.value.hora_inicio,
    hora_fin: nuevoBloqueo.value.hora_fin,
    motivo: nuevoBloqueo.value.motivo,
  });
  mensaje.value = 'Cancha bloqueada correctamente.';
  nuevoBloqueo.value = { fecha: '', hora_inicio: '', hora_fin: '', motivo: 'mantenimiento' };
}

onMounted(cargarHorarios);
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700;800&family=Inter:wght@400;500;600;700;800&display=swap');

.horarios-page {
  font-family: 'Inter', -apple-system, sans-serif;
  color: #f3f4f6;
  background-color: #030712;
}

/* NAVBAR CÁPSULA FLOTANTE */
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

/* ACCIONES NAVEGACIÓN */
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

/* VIEWPORT & BACKGROUND */
.horarios-content {
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
  margin-bottom: 0.5rem;
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

.panel-card-glass {
  background: rgba(11, 15, 25, 0.8);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 24px;
  padding: 1.5rem;
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

/* BOTONES ESTILIZADOS */
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

.btn-secondary-glass {
  width: 100%;
  margin-top: 1.25rem;
  background: rgba(132, 204, 22, 0.1);
  border: 1px solid rgba(132, 204, 22, 0.3);
  color: #84cc16;
  border-radius: 12px;
  padding: 0.85rem;
  font-size: 0.9rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.25s ease;
}

.btn-secondary-glass:hover {
  background: rgba(132, 204, 22, 0.2);
  transform: translateY(-2px);
}

.btn-dark-glass {
  width: 100%;
  margin-top: 1.25rem;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #ffffff;
  border-radius: 12px;
  padding: 0.85rem;
  font-size: 0.9rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.25s ease;
}

.btn-dark-glass:hover {
  background: rgba(255, 255, 255, 0.12);
  transform: translateY(-2px);
}

/* LISTAS DE EXCEPCIONES */
.stack-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.list-item-glass {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.8rem;
  background: rgba(3, 7, 18, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 14px;
  padding: 0.85rem 1rem;
}

.list-item-glass strong {
  display: block;
  color: #ffffff;
  font-size: 0.9rem;
}

.list-item-glass small {
  display: block;
  color: #94a3b8;
  margin-top: 0.15rem;
  line-height: 1.4;
  font-size: 0.8rem;
}

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

.empty-inline {
  color: #64748b;
  font-size: 0.825rem;
  margin-bottom: 0.5rem;
}

.message-banner {
  margin-top: 0.5rem;
  padding: 0.85rem 1rem;
  border-radius: 12px;
  background: rgba(132, 204, 22, 0.12);
  border: 1px solid rgba(132, 204, 22, 0.3);
  color: #84cc16;
  font-size: 0.85rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.message-banner ion-icon {
  font-size: 1.1rem;
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

  .list-item-glass {
    align-items: flex-start;
    flex-direction: column;
  }
}
</style>