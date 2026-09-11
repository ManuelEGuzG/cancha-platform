<template>
  <ion-page>
    <ion-header>
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-back-button default-href="/panel"></ion-back-button>
        </ion-buttons>
        <ion-title>Horarios y bloqueos</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content class="ion-padding">
      <ion-spinner v-if="cargando" name="crescent"></ion-spinner>

      <div v-else>
        <h2>Horario regular (todos los días)</h2>
        <p>Configura la misma apertura/cierre para toda la semana:</p>
        <ion-item>
          <ion-label position="stacked">Hora apertura</ion-label>
          <ion-input v-model="horaApertura" type="time"></ion-input>
        </ion-item>
        <ion-item>
          <ion-label position="stacked">Hora cierre</ion-label>
          <ion-input v-model="horaCierre" type="time"></ion-input>
        </ion-item>
        <ion-button expand="block" @click="guardarHorarioRegular">Guardar horario semanal</ion-button>

        <h2>Excepciones (feriados, eventos)</h2>
        <ion-item v-for="ex in horariosExcepcion" :key="ex.id">
          <ion-label>
            {{ ex.fecha }} — {{ ex.hora_apertura ? `${ex.hora_apertura} a ${ex.hora_cierre}` : 'Cerrado todo el día' }}
            ({{ ex.motivo }})
          </ion-label>
          <ion-button slot="end" color="danger" size="small" @click="eliminarExcepcion(ex.id)">Eliminar</ion-button>
        </ion-item>

        <ion-item>
          <ion-label position="stacked">Fecha</ion-label>
          <ion-input v-model="nuevaExcepcion.fecha" type="date"></ion-input>
        </ion-item>
        <ion-item>
          <ion-label position="stacked">Hora apertura (vacío = cerrado todo el día)</ion-label>
          <ion-input v-model="nuevaExcepcion.hora_apertura" type="time"></ion-input>
        </ion-item>
        <ion-item>
          <ion-label position="stacked">Hora cierre</ion-label>
          <ion-input v-model="nuevaExcepcion.hora_cierre" type="time"></ion-input>
        </ion-item>
        <ion-item>
          <ion-label position="stacked">Motivo</ion-label>
          <ion-input v-model="nuevaExcepcion.motivo"></ion-input>
        </ion-item>
        <ion-button expand="block" @click="crearExcepcion">Agregar excepción</ion-button>

        <h2>Bloquear horario</h2>
        <ion-item>
          <ion-label position="stacked">Fecha</ion-label>
          <ion-input v-model="nuevoBloqueo.fecha" type="date"></ion-input>
        </ion-item>
        <ion-item>
          <ion-label position="stacked">Hora inicio</ion-label>
          <ion-input v-model="nuevoBloqueo.hora_inicio" type="time"></ion-input>
        </ion-item>
        <ion-item>
          <ion-label position="stacked">Hora fin</ion-label>
          <ion-input v-model="nuevoBloqueo.hora_fin" type="time"></ion-input>
        </ion-item>
        <ion-item>
          <ion-label position="stacked">Motivo</ion-label>
          <ion-select v-model="nuevoBloqueo.motivo">
            <ion-select-option value="mantenimiento">Mantenimiento</ion-select-option>
            <ion-select-option value="evento">Evento</ion-select-option>
            <ion-select-option value="reparacion">Reparación</ion-select-option>
            <ion-select-option value="uso_interno">Uso interno</ion-select-option>
            <ion-select-option value="otro">Otro</ion-select-option>
          </ion-select>
        </ion-item>
        <ion-button expand="block" color="dark" @click="crearBloqueo">Bloquear horario</ion-button>

        <p v-if="mensaje">{{ mensaje }}</p>
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