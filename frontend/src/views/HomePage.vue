<template>
  <ion-page>
    <ion-header>
      <ion-toolbar>
        <ion-title>Encuentra una cancha</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content class="ion-padding">
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
  IonSpinner,
} from '@ionic/vue';
import complejosService from '@/services/complejos.service';
import type { Complejo } from '@/types';

const router = useRouter();
const complejos = ref<Complejo[]>([]);
const cargando = ref(true);

async function cargarComplejos() {
  cargando.value = true;
  try {
    const { data } = await complejosService.listar();
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

function formatearPrecio(precio: number): string {
  return precio.toLocaleString('es-CR');
}

onMounted(cargarComplejos);
</script>