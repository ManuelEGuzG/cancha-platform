<template>
  <ion-page class="canchas-page">
    <ion-header>
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-back-button default-href="/panel"></ion-back-button>
        </ion-buttons>
        <ion-title>Mis canchas</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content class="ion-padding cancha-content">
      <div v-if="cargando" class="loading-wrap">
        <ion-spinner name="crescent"></ion-spinner>
      </div>

      <div v-else class="management-layout">
        <section class="panel-card main-panel">
          <div class="card-header">
            <div>
              <span class="eyebrow">Inventario</span>
              <h2>Listado de canchas</h2>
            </div>
          </div>

          <div v-if="canchas.length" class="cards-list">
            <article v-for="cancha in canchas" :key="cancha.id" class="cancha-card">
              <div class="cancha-top">
                <div>
                  <h3>{{ cancha.nombre }}</h3>
                  <span>{{ cancha.deporte.nombre }}</span>
                </div>
                <span :class="['status-pill', cancha.activa ? 'active' : 'inactive']">
                  {{ cancha.activa ? 'Activa' : 'Inactiva' }}
                </span>
              </div>

              <div class="cancha-meta">
                <strong>₡{{ cancha.precio_hora }}</strong>
                <small>/ hora</small>
              </div>

              <div class="action-row">
                <button class="secondary-btn" @click="editar(cancha)">Editar</button>
                <button class="primary-btn" @click="verHorarios(cancha.id)">Horarios</button>
              </div>
            </article>
          </div>

          <p v-else class="empty-message">Aún no hay canchas registradas para este complejo.</p>
        </section>

        <aside class="panel-card form-panel">
          <div class="card-header">
            <div>
              <span class="eyebrow">Configuración</span>
              <h2>{{ canchaEditando ? 'Editar cancha' : 'Nueva cancha' }}</h2>
            </div>
          </div>

          <div v-if="canchaEditando" class="editor-form">
            <div class="field-group">
              <label>Nombre</label>
              <ion-input v-model="formEdicion.nombre" placeholder="Nombre de la cancha"></ion-input>
            </div>

            <div class="field-group">
              <label>Precio por hora</label>
              <ion-input v-model.number="formEdicion.precio_hora" type="number" placeholder="18000"></ion-input>
            </div>

            <div class="switch-row">
              <span>Estado</span>
              <ion-toggle v-model="formEdicion.activa"></ion-toggle>
            </div>

            <button class="submit-btn" @click="guardarEdicion">Guardar cambios</button>
          </div>

          <div v-else class="editor-form">
            <div class="field-group">
              <label>Nombre</label>
              <ion-input v-model="nuevaCancha.nombre" placeholder="Cancha 1"></ion-input>
            </div>

            <div class="field-group">
              <label>Precio por hora</label>
              <ion-input v-model.number="nuevaCancha.precio_hora" type="number" placeholder="18000"></ion-input>
            </div>

            <button class="submit-btn" @click="crearCancha">Crear cancha</button>
          </div>

          <p v-if="mensaje" class="message-banner success">{{ mensaje }}</p>
        </aside>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonButtons, IonBackButton, IonContent,
  IonList, IonItem, IonLabel, IonButton, IonInput, IonToggle, IonSpinner,
} from '@ionic/vue';
import canchasService from '@/services/canchas.service';

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
  const { data } = await canchasService.listar(complejoId);
  canchas.value = data.data;
  cargando.value = false;
}

function editar(cancha: any) {
  canchaEditando.value = cancha;
  formEdicion.value = { nombre: cancha.nombre, precio_hora: cancha.precio_hora, activa: cancha.activa };
}

async function guardarEdicion() {
  await canchasService.actualizar(canchaEditando.value.id, formEdicion.value);
  mensaje.value = 'Cancha actualizada.';
  canchaEditando.value = null;
  await cargarCanchas();
}

async function crearCancha() {
  await canchasService.crear({
    complejo_id: complejoId,
    deporte_id: 1, // Fútbol — único deporte disponible en el MVP
    nombre: nuevaCancha.value.nombre,
    precio_hora: nuevaCancha.value.precio_hora,
  });
  mensaje.value = 'Cancha creada.';
  nuevaCancha.value = { nombre: '', precio_hora: 0 };
  await cargarCanchas();
}

function verHorarios(canchaId: number) {
  router.push(`/panel/canchas/${canchaId}/horarios`);
}

onMounted(cargarCanchas);
</script>

<style scoped>
.canchas-page {
  --bg-page: #f4fbff;
  --panel-surface: #ffffff;
  --panel-soft: #f8fbff;
  --primary: #1D5C94;
  --primary-dark: #08226C;
  --primary-soft: #edf8ff;
  --text-main: #0f172a;
  --text-soft: #64748b;
  --border: #dfeaf7;
  --success: #10b981;
  --warning: #f59e0b;
}

ion-content.cancha-content {
  --background: var(--bg-page);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.management-layout {
  max-width: 1180px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1.4fr 0.9fr;
  gap: 1.2rem;
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
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.eyebrow {
  display: inline-block;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-weight: 800;
  color: var(--primary);
}

.card-header h2 {
  margin: 0.3rem 0 0;
  color: var(--text-main);
  letter-spacing: -0.03em;
  font-size: 1.5rem;
}

.cards-list {
  display: grid;
  gap: 0.9rem;
}

.cancha-card {
  border: 1px solid var(--border);
  background: linear-gradient(180deg, #ffffff 0%, #f9fcff 100%);
  border-radius: 16px;
  padding: 1rem;
}

.cancha-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 0.75rem;
  margin-bottom: 0.8rem;
}

.cancha-top h3 {
  margin: 0;
  font-size: 1.05rem;
  color: var(--text-main);
}

.cancha-top span {
  display: block;
  margin-top: 0.2rem;
  color: var(--text-soft);
  font-size: 0.78rem;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.33rem 0.7rem;
  border-radius: 999px;
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 0.04em;
}

.status-pill.active {
  background: rgba(16, 185, 129, 0.12);
  color: #047857;
}

.status-pill.inactive {
  background: rgba(245, 158, 11, 0.12);
  color: #b45309;
}

.cancha-meta {
  display: flex;
  align-items: baseline;
  gap: 0.25rem;
  margin-bottom: 1rem;
}

.cancha-meta strong {
  font-size: 1.25rem;
  color: var(--primary);
  letter-spacing: -0.03em;
}

.cancha-meta small {
  font-size: 0.75rem;
  color: var(--text-soft);
}

.action-row {
  display: flex;
  gap: 0.7rem;
}

.secondary-btn,
.primary-btn,
.submit-btn {
  border: none;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
}

.secondary-btn {
  flex: 1;
  background: var(--primary-soft);
  color: var(--primary);
  border: 1px solid rgba(29, 92, 148, 0.12);
  padding: 0.72rem 0.8rem;
}

.primary-btn,
.submit-btn {
  flex: 1;
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  color: #ffffff;
  padding: 0.72rem 0.8rem;
  box-shadow: 0 10px 18px rgba(29, 92, 148, 0.18);
}

.secondary-btn:hover,
.primary-btn:hover,
.submit-btn:hover {
  transform: translateY(-1px);
}

.form-panel {
  align-self: start;
}

.editor-form {
  display: grid;
  gap: 1rem;
}

.field-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.field-group label {
  color: var(--text-soft);
  font-size: 0.76rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.field-group ion-input,
.field-group ion-toggle {
  --background: #f8fafc;
  --border-radius: 12px;
  border: 1px solid var(--border);
  border-radius: 12px;
  min-height: 46px;
}

.switch-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.75rem;
  border: 1px solid var(--border);
  background: #f8fafc;
  border-radius: 12px;
  padding: 0.8rem 0.9rem;
}

.switch-row span {
  color: var(--text-main);
  font-weight: 600;
}

.message-banner {
  margin-top: 1rem;
  padding: 0.75rem 0.85rem;
  border-radius: 10px;
  background: rgba(16, 185, 129, 0.08);
  border: 1px solid rgba(16, 185, 129, 0.18);
  color: #047857;
  font-size: 0.8rem;
  font-weight: 700;
}

.empty-message {
  margin: 0;
  color: var(--text-soft);
  font-size: 0.9rem;
}

@media (max-width: 900px) {
  .management-layout {
    grid-template-columns: 1fr;
  }
}
</style>