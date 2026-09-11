<template>
  <ion-page>
    <ion-header>
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-back-button default-href="/home"></ion-back-button>
        </ion-buttons>
        <ion-title>{{ complejo?.nombre || 'Cargando...' }}</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content class="ion-padding">
      <ion-spinner v-if="cargando" name="crescent"></ion-spinner>

      <div v-else-if="complejo">
        <p>{{ complejo.direccion_texto || 'Sin dirección registrada' }}</p>
        <p>{{ complejo.distrito }}, {{ complejo.canton }}, {{ complejo.provincia }}</p>

        <ion-button v-if="complejo.whatsapp_numero" expand="block" color="success" @click="contactarWhatsApp">
          Contactar por WhatsApp
        </ion-button>

        <h2>Disponibilidad — {{ fechaSeleccionada }}</h2>

        <ion-segment v-model="fechaSeleccionada" @ionChange="cargarDisponibilidad">
          <ion-segment-button value="hoy">
            <ion-label>Hoy</ion-label>
          </ion-segment-button>
          <ion-segment-button value="manana">
            <ion-label>Mañana</ion-label>
          </ion-segment-button>
        </ion-segment>

        <ion-spinner v-if="cargandoDisponibilidad" name="crescent"></ion-spinner>

        <div v-else v-for="cancha in disponibilidad?.canchas" :key="cancha.cancha_id" class="cancha-bloque">
          <h3>{{ cancha.nombre }} — ₡{{ formatearPrecio(cancha.precio_hora) }}/hora</h3>

          <div class="horarios">
            <ion-chip
              v-for="bloque in cancha.bloques"
              :key="bloque.hora_inicio"
              :color="colorEstado(bloque.estado)"
            >
              {{ bloque.hora_inicio }} {{ emojiEstado(bloque.estado) }}
            </ion-chip>
          </div>
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonButtons, IonBackButton,
  IonButton, IonSpinner, IonSegment, IonSegmentButton, IonLabel, IonChip,
} from '@ionic/vue';
import complejosService from '@/services/complejos.service';
import echo from '@/services/echo';
import type { ComplejoDetalle, Disponibilidad } from '@/types';

const route = useRoute();
const slug = route.params.slug as string;

const complejo = ref<ComplejoDetalle | null>(null);
const disponibilidad = ref<Disponibilidad | null>(null);
const cargando = ref(true);
const cargandoDisponibilidad = ref(false);
const fechaSeleccionada = ref<'hoy' | 'manana'>('hoy');

async function cargarComplejo() {
  cargando.value = true;
  try {
    const { data } = await complejosService.detalle(slug);
    complejo.value = data.data;
  } catch (error) {
    console.error('Error cargando complejo:', error);
  } finally {
    cargando.value = false;
  }
}

async function cargarDisponibilidad() {
  cargandoDisponibilidad.value = true;
  try {
    const fecha = calcularFecha(fechaSeleccionada.value);
    const { data } = await complejosService.disponibilidad(slug, fecha);
    disponibilidad.value = data.data;
  } catch (error) {
    console.error('Error cargando disponibilidad:', error);
  } finally {
    cargandoDisponibilidad.value = false;
  }
}

function calcularFecha(opcion: 'hoy' | 'manana'): string {
  const fecha = new Date();
  if (opcion === 'manana') fecha.setDate(fecha.getDate() + 1);
  return fecha.toISOString().split('T')[0];
}

function colorEstado(estado: string): string {
  const colores: Record<string, string> = {
    disponible: 'success',
    ocupada: 'danger',
    pendiente: 'warning',
    bloqueada: 'dark',
  };
  return colores[estado] || 'medium';
}

function emojiEstado(estado: string): string {
  const emojis: Record<string, string> = {
    disponible: '🟢',
    ocupada: '🔴',
    pendiente: '🟡',
    bloqueada: '⚫',
  };
  return emojis[estado] || '';
}

function formatearPrecio(precio: number): string {
  return precio.toLocaleString('es-CR');
}

async function contactarWhatsApp() {
  try {
    const { data } = await complejosService.enlaceWhatsApp(slug);
    window.open(data.data.enlace, '_blank');
  } catch (error) {
    console.error('Error generando enlace de WhatsApp:', error);
  }
}

let canalSuscrito: any = null;

onMounted(async () => {
  await cargarComplejo();
  await cargarDisponibilidad();

  if (complejo.value) {
    canalSuscrito = echo.channel(`complejo.${complejo.value.id}.disponibilidad`);
    canalSuscrito.listen('.disponibilidad.actualizada', () => {
      cargarDisponibilidad();
    });
  }
});

onUnmounted(() => {
  if (complejo.value) {
    echo.leaveChannel(`complejo.${complejo.value.id}.disponibilidad`);
  }
});
</script>

<style scoped>
.cancha-bloque {
  margin-bottom: 24px;
}
.horarios {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}
</style>