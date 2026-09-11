<template>
  <ion-page class="horarios-page">
    <ion-header>
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-back-button default-href="/panel"></ion-back-button>
        </ion-buttons>
        <ion-title>Horarios y bloqueos</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content class="ion-padding content-shell">
      <div v-if="cargando" class="loading-wrap">
        <ion-spinner name="crescent"></ion-spinner>
      </div>

      <div v-else class="management-layout">
        <section class="panel-card">
          <div class="card-header">
            <div>
              <span class="eyebrow">Programación</span>
              <h2>Horario regular</h2>
            </div>
          </div>

          <div class="field-grid">
            <div class="field-group">
              <label>Hora apertura</label>
              <ion-input v-model="horaApertura" type="time"></ion-input>
            </div>
            <div class="field-group">
              <label>Hora cierre</label>
              <ion-input v-model="horaCierre" type="time"></ion-input>
            </div>
          </div>

          <button class="submit-btn" @click="guardarHorarioRegular">Guardar horario semanal</button>
        </section>

        <section class="panel-card">
          <div class="card-header">
            <div>
              <span class="eyebrow">Excepciones</span>
              <h2>Feriados y eventos</h2>
            </div>
          </div>

          <div class="stack-list">
            <div v-for="ex in horariosExcepcion" :key="ex.id" class="list-item">
              <div>
                <strong>{{ ex.fecha }}</strong>
                <small>
                  {{ ex.hora_apertura ? `${ex.hora_apertura} a ${ex.hora_cierre}` : 'Cerrado todo el día' }}
                  — {{ ex.motivo }}
                </small>
              </div>
              <button class="delete-btn" @click="eliminarExcepcion(ex.id)">Eliminar</button>
            </div>
          </div>

          <div class="field-grid compact">
            <div class="field-group full">
              <label>Fecha</label>
              <ion-input v-model="nuevaExcepcion.fecha" type="date"></ion-input>
            </div>
            <div class="field-group">
              <label>Hora apertura</label>
              <ion-input v-model="nuevaExcepcion.hora_apertura" type="time"></ion-input>
            </div>
            <div class="field-group">
              <label>Hora cierre</label>
              <ion-input v-model="nuevaExcepcion.hora_cierre" type="time"></ion-input>
            </div>
            <div class="field-group full">
              <label>Motivo</label>
              <ion-input v-model="nuevaExcepcion.motivo" placeholder="Ej. Feriado, evento, mantenimiento"></ion-input>
            </div>
          </div>

          <button class="submit-btn secondary" @click="crearExcepcion">Agregar excepción</button>
        </section>

        <section class="panel-card">
          <div class="card-header">
            <div>
              <span class="eyebrow">Bloqueo</span>
              <h2>Bloquear horario</h2>
            </div>
          </div>

          <div class="field-grid compact">
            <div class="field-group full">
              <label>Fecha</label>
              <ion-input v-model="nuevoBloqueo.fecha" type="date"></ion-input>
            </div>
            <div class="field-group">
              <label>Hora inicio</label>
              <ion-input v-model="nuevoBloqueo.hora_inicio" type="time"></ion-input>
            </div>
            <div class="field-group">
              <label>Hora fin</label>
              <ion-input v-model="nuevoBloqueo.hora_fin" type="time"></ion-input>
            </div>
            <div class="field-group full">
              <label>Motivo</label>
              <ion-select v-model="nuevoBloqueo.motivo" interface="popover">
                <ion-select-option value="mantenimiento">Mantenimiento</ion-select-option>
                <ion-select-option value="evento">Evento</ion-select-option>
                <ion-select-option value="reparacion">Reparación</ion-select-option>
                <ion-select-option value="uso_interno">Uso interno</ion-select-option>
                <ion-select-option value="otro">Otro</ion-select-option>
              </ion-select>
            </div>
          </div>

          <button class="submit-btn dark" @click="crearBloqueo">Bloquear horario</button>
        </section>

        <p v-if="mensaje" class="message-banner">{{ mensaje }}</p>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonButtons, IonBackButton, IonContent,
  IonItem, IonLabel, IonInput, IonButton, IonSpinner, IonSelect, IonSelectOption,
} from '@ionic/vue';
import horariosService from '@/services/horarios.service';
import panelService from '@/services/panel.service';

const route = useRoute();
const canchaId = Number(route.params.canchaId);

const cargando = ref(true);
const horariosExcepcion = ref<any[]>([]);
const horaApertura = ref('16:00');
const horaCierre = ref('23:00');
const mensaje = ref('');

const nuevaExcepcion = ref({ fecha: '', hora_apertura: '', hora_cierre: '', motivo: '' });
const nuevoBloqueo = ref({ fecha: '', hora_inicio: '', hora_fin: '', motivo: 'mantenimiento' });

async function cargarHorarios() {
  cargando.value = true;
  const { data } = await horariosService.index(canchaId);
  horariosExcepcion.value = data.data.horarios_excepcion;

  if (data.data.horarios_regulares.length > 0) {
    horaApertura.value = data.data.horarios_regulares[0].hora_apertura.slice(0, 5);
    horaCierre.value = data.data.horarios_regulares[0].hora_cierre.slice(0, 5);
  }

  cargando.value = false;
}

async function guardarHorarioRegular() {
  const horarios = Array.from({ length: 7 }, (_, dia) => ({
    dia_semana: dia,
    hora_apertura: horaApertura.value,
    hora_cierre: horaCierre.value,
  }));

  await horariosService.actualizarRegular(canchaId, horarios);
  mensaje.value = 'Horario semanal actualizado.';
}

async function crearExcepcion() {
  await horariosService.crearExcepcion({
    cancha_id: canchaId,
    fecha: nuevaExcepcion.value.fecha,
    hora_apertura: nuevaExcepcion.value.hora_apertura || undefined,
    hora_cierre: nuevaExcepcion.value.hora_cierre || undefined,
    motivo: nuevaExcepcion.value.motivo,
  });
  mensaje.value = 'Excepción creada.';
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
.horarios-page {
  --bg-page: #f4fbff;
  --panel-surface: #ffffff;
  --panel-soft: #f7fbff;
  --primary: #1D5C94;
  --primary-dark: #08226C;
  --primary-soft: #edf8ff;
  --text-main: #0f172a;
  --text-soft: #64748b;
  --border: #dfeaf7;
  --danger: #ef4444;
}

ion-content.content-shell {
  --background: var(--bg-page);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.management-layout {
  max-width: 1100px;
  margin: 0 auto;
  display: grid;
  gap: 1.1rem;
  padding: 1.2rem 0 2rem;
}

.panel-card {
  background: var(--panel-surface);
  border: 1px solid var(--border);
  border-radius: 20px;
  box-shadow: 0 12px 26px rgba(15, 23, 42, 0.04);
  padding: 1.2rem;
}

.card-header {
  margin-bottom: 1rem;
}

.eyebrow {
  display: inline-block;
  color: var(--primary);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 0.7rem;
  font-weight: 800;
}

.card-header h2 {
  margin: 0.25rem 0 0;
  color: var(--text-main);
  font-size: 1.4rem;
  letter-spacing: -0.03em;
}

.field-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.9rem;
}

.field-grid.compact {
  margin-top: 1rem;
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
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.field-group ion-input,
.field-group ion-select {
  --background: #f8fafc;
  --padding-start: 0.8rem;
  --padding-end: 0.8rem;
  min-height: 46px;
  border: 1px solid var(--border);
  border-radius: 12px;
}

.submit-btn {
  width: 100%;
  margin-top: 1rem;
  border: none;
  border-radius: 10px;
  padding: 0.8rem 1rem;
  font-weight: 800;
  cursor: pointer;
  transition: transform 0.2s ease;
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  color: #ffffff;
  box-shadow: 0 10px 18px rgba(29, 92, 148, 0.18);
}

.submit-btn.secondary {
  background: var(--primary-soft);
  color: var(--primary);
  border: 1px solid rgba(29, 92, 148, 0.1);
  box-shadow: none;
}

.submit-btn.dark {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  color: #ffffff;
  box-shadow: 0 10px 18px rgba(15, 23, 42, 0.12);
}

.submit-btn:hover {
  transform: translateY(-1px);
}

.stack-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.list-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.8rem;
  border: 1px solid var(--border);
  background: linear-gradient(180deg, #ffffff 0%, #f9fcff 100%);
  border-radius: 14px;
  padding: 0.8rem 0.9rem;
}

.list-item strong {
  display: block;
  color: var(--text-main);
  font-size: 0.9rem;
}

.list-item small {
  display: block;
  color: var(--text-soft);
  margin-top: 0.15rem;
  line-height: 1.5;
}

.delete-btn {
  border: 1px solid rgba(239, 68, 68, 0.15);
  background: rgba(239, 68, 68, 0.08);
  color: var(--danger);
  font-weight: 700;
  border-radius: 10px;
  padding: 0.55rem 0.75rem;
  cursor: pointer;
}

.message-banner {
  margin-top: 1rem;
  padding: 0.75rem 0.85rem;
  border-radius: 10px;
  border: 1px solid rgba(16, 185, 129, 0.2);
  background: rgba(16, 185, 129, 0.08);
  color: #047857;
  font-size: 0.8rem;
  font-weight: 700;
}

@media (max-width: 700px) {
  .field-grid {
    grid-template-columns: 1fr;
  }

  .list-item {
    align-items: flex-start;
    flex-direction: column;
  }
}
</style>