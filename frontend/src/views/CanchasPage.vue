<template>
  <ion-page>
    <ion-header>
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-back-button default-href="/panel"></ion-back-button>
        </ion-buttons>
        <ion-title>Mis canchas</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content class="ion-padding">
      <ion-spinner v-if="cargando" name="crescent"></ion-spinner>

      <ion-list v-else>
        <ion-item v-for="cancha in canchas" :key="cancha.id">
          <ion-label>
            <h2>{{ cancha.nombre }} ({{ cancha.deporte.nombre }})</h2>
            <p>₡{{ cancha.precio_hora }}/hora — {{ cancha.activa ? 'Activa' : 'Inactiva' }}</p>
          </ion-label>
          <ion-button slot="end" size="small" @click="editar(cancha)">Editar</ion-button>
          <ion-button slot="end" size="small" @click="verHorarios(cancha.id)">Horarios</ion-button>
        </ion-item>
      </ion-list>

      <h2>Editar cancha</h2>
      <div v-if="canchaEditando">
        <ion-item>
          <ion-label position="stacked">Nombre</ion-label>
          <ion-input v-model="formEdicion.nombre"></ion-input>
        </ion-item>
        <ion-item>
          <ion-label position="stacked">Precio por hora</ion-label>
          <ion-input v-model.number="formEdicion.precio_hora" type="number"></ion-input>
        </ion-item>
        <ion-item>
          <ion-label>Activa</ion-label>
          <ion-toggle v-model="formEdicion.activa"></ion-toggle>
        </ion-item>
        <ion-button expand="block" @click="guardarEdicion">Guardar cambios</ion-button>
      </div>

      <h2>Nueva cancha</h2>
      <ion-item>
        <ion-label position="stacked">Nombre</ion-label>
        <ion-input v-model="nuevaCancha.nombre"></ion-input>
      </ion-item>
      <ion-item>
        <ion-label position="stacked">Precio por hora</ion-label>
        <ion-input v-model.number="nuevaCancha.precio_hora" type="number"></ion-input>
      </ion-item>
      <ion-button expand="block" class="ion-margin-top" @click="crearCancha">Crear cancha</ion-button>

      <p v-if="mensaje">{{ mensaje }}</p>
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