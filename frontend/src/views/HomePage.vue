<template>
  <ion-page>
    <ion-header>
      <ion-toolbar>
        <ion-title>Encuentra una cancha</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content class="ion-padding">
      <ion-item>
        <ion-label>Provincia</ion-label>
        <ion-select v-model="provinciaId" @ionChange="onProvinciaChange">
          <ion-select-option v-for="p in provincias" :key="p.id" :value="p.id">{{ p.nombre }}</ion-select-option>
        </ion-select>
      </ion-item>

      <ion-item v-if="cantones.length">
        <ion-label>Cantón</ion-label>
        <ion-select v-model="cantonId" @ionChange="onCantonChange">
          <ion-select-option v-for="c in cantones" :key="c.id" :value="c.id">{{ c.nombre }}</ion-select-option>
        </ion-select>
      </ion-item>

      <ion-item v-if="distritos.length">
        <ion-label>Distrito</ion-label>
        <ion-select v-model="distritoId" @ionChange="cargarComplejos">
          <ion-select-option :value="null">Todos</ion-select-option>
          <ion-select-option v-for="d in distritos" :key="d.id" :value="d.id">{{ d.nombre }}</ion-select-option>
        </ion-select>
      </ion-item>

      <ion-button fill="clear" size="small" @click="irAlPanel">Ingresar como propietario</ion-button>

      <ion-spinner v-if="cargando" name="crescent"></ion-spinner>

      <ion-list v-else>
        <ion-card v-for="complejo in complejos" :key="complejo.id" button @click="verDetalle(complejo.slug)">
          <ion-card-header>
            <ion-card-title>{{ complejo.nombre }}</ion-card-title>
            <ion-card-subtitle>{{ complejo.distrito }}, {{ complejo.canton }}</ion-card-subtitle>
          </ion-card-header>
          <ion-card-content>
            <p>{{ complejo.total_canchas }} cancha(s)</p>
            <p>Desde ₡{{ formatearPrecio(complejo.precio_desde) }}/hora</p>
          </ion-card-content>
        </ion-card>
      </ion-list>

      <p v-if="!cargando && complejos.length === 0">No se encontraron complejos.</p>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent,
  IonList, IonCard, IonCardHeader, IonCardTitle, IonCardSubtitle, IonCardContent,
  IonSpinner, IonItem, IonLabel, IonSelect, IonSelectOption, IonButton,
} from '@ionic/vue';
import complejosService from '@/services/complejos.service';
import geografiaService from '@/services/geografia.service';
import type { Complejo } from '@/types';

const router = useRouter();

const provincias = ref<any[]>([]);
const cantones = ref<any[]>([]);
const distritos = ref<any[]>([]);

const provinciaId = ref<number | null>(null);
const cantonId = ref<number | null>(null);
const distritoId = ref<number | null>(null);

const complejos = ref<Complejo[]>([]);
const cargando = ref(true);

async function cargarProvincias() {
  const { data } = await geografiaService.provincias();
  provincias.value = data.data;
  if (provincias.value.length > 0) {
    provinciaId.value = provincias.value[0].id;
    await onProvinciaChange();
  }
}

async function onProvinciaChange() {
  cantones.value = [];
  distritos.value = [];
  cantonId.value = null;
  distritoId.value = null;
  if (!provinciaId.value) return;

  const { data } = await geografiaService.cantones(provinciaId.value);
  cantones.value = data.data;
  if (cantones.value.length > 0) {
    cantonId.value = cantones.value[0].id;
    await onCantonChange();
  }
}

async function onCantonChange() {
  distritos.value = [];
  distritoId.value = null;
  if (!cantonId.value) return;

  const { data } = await geografiaService.distritos(cantonId.value);
  distritos.value = data.data;
  await cargarComplejos();
}

async function cargarComplejos() {
  cargando.value = true;
  try {
    const { data } = await complejosService.listar({
      canton_id: cantonId.value ?? undefined,
      distrito_id: distritoId.value ?? undefined,
    });
    complejos.value = data.data;
  } catch (error) {
    console.error('Error cargando complejos:', error);
  } finally {
    cargando.value = false;
  }
}

function verDetalle(slug: string) {
  router.push(`/complejo/${slug}`);
}

function irAlPanel() {
  router.push('/login');
}

function formatearPrecio(precio: number): string {
  return precio.toLocaleString('es-CR');
}

onMounted(cargarProvincias);
</script>