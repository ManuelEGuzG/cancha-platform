<template>
  <ion-page>
    <ion-header>
      <ion-toolbar>
        <ion-title>{{ complejoActual?.nombre || 'Panel' }}</ion-title>
        <ion-buttons slot="end">
          <ion-button @click="cerrarSesion">Salir</ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content class="ion-padding">
      <ion-spinner v-if="cargandoInicial" name="crescent"></ion-spinner>

      <div v-else>
        <ion-select v-if="misComplejos.length > 1" v-model="complejoIdSeleccionado" @ionChange="cargarAgenda">
          <ion-select-option v-for="c in misComplejos" :key="c.id" :value="c.id">{{ c.nombre }}</ion-select-option>
        </ion-select>

        <h2>Estadísticas rápidas</h2>
        <p v-if="estadisticas">
          Canchas: {{ estadisticas.total_canchas }} |
          Reservas hoy: {{ estadisticas.reservas_hoy }} |
          Reservas este mes: {{ estadisticas.reservas_mes.total }}
        </p>

        <h2>Agenda de hoy</h2>
        <div v-for="cancha in agenda?.canchas" :key="cancha.cancha_id" class="cancha-agenda">
          <h3>{{ cancha.nombre }} ({{ cancha.deporte }})</h3>

          <ion-item v-for="r in cancha.reservas" :key="r.id">
            <ion-label>
              {{ r.hora_inicio }} - {{ r.hora_fin }} | {{ r.nombre_cliente }} ({{ r.estado }})
            </ion-label>
            <ion-button slot="end" color="danger" size="small" @click="cancelar(r.id)">Cancelar</ion-button>
          </ion-item>

          <ion-item v-for="b in cancha.bloqueos" :key="'b' + b.id">
            <ion-label>⚫ {{ b.hora_inicio }} - {{ b.hora_fin }} | {{ b.motivo }}</ion-label>
          </ion-item>

          <p v-if="!cancha.reservas.length && !cancha.bloqueos.length">Sin reservas ni bloqueos hoy.</p>
        </div>

        <h2>Nueva reserva manual</h2>
        <ion-item>
          <ion-label position="stacked">Cancha</ion-label>
          <ion-select v-model="nuevaReserva.cancha_id">
            <ion-select-option v-for="cancha in agenda?.canchas" :key="cancha.cancha_id" :value="cancha.cancha_id">
              {{ cancha.nombre }}
            </ion-select-option>
          </ion-select>
        </ion-item>
        <ion-item>
          <ion-label position="stacked">Nombre del cliente</ion-label>
          <ion-input v-model="nuevaReserva.nombre_cliente"></ion-input>
        </ion-item>
        <ion-item>
          <ion-label position="stacked">Teléfono</ion-label>
          <ion-input v-model="nuevaReserva.telefono_cliente"></ion-input>
        </ion-item>
        <ion-item>
          <ion-label position="stacked">Fecha</ion-label>
          <ion-input v-model="nuevaReserva.fecha" type="date"></ion-input>
        </ion-item>
        <ion-item>
          <ion-label position="stacked">Hora inicio</ion-label>
          <ion-input v-model="nuevaReserva.hora_inicio" type="time"></ion-input>
        </ion-item>
        <ion-item>
          <ion-label position="stacked">Hora fin</ion-label>
          <ion-input v-model="nuevaReserva.hora_fin" type="time"></ion-input>
        </ion-item>

        <ion-button expand="block" class="ion-margin-top" @click="crearReserva">Guardar reserva</ion-button>

        <p v-if="mensajeReserva" :style="{ color: errorReserva ? 'red' : 'green' }">{{ mensajeReserva }}</p>
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
.cancha-agenda {
  margin-bottom: 20px;
}
</style>