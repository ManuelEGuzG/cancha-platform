<template>
  <ion-page>
    <ion-header>
      <ion-toolbar>
        <ion-title>Iniciar sesión</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content class="ion-padding">
      <ion-item>
        <ion-label position="stacked">Correo</ion-label>
        <ion-input v-model="email" type="email"></ion-input>
      </ion-item>

      <ion-item>
        <ion-label position="stacked">Contraseña</ion-label>
        <ion-input v-model="password" type="password"></ion-input>
      </ion-item>

      <ion-button expand="block" class="ion-margin-top" @click="ingresar" :disabled="cargando">
        {{ cargando ? 'Ingresando...' : 'Ingresar' }}
      </ion-button>

      <p v-if="error" style="color: red">{{ error }}</p>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonItem, IonLabel, IonInput, IonButton } from '@ionic/vue';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const email = ref('');
const password = ref('');
const cargando = ref(false);
const error = ref('');

async function ingresar() {
  cargando.value = true;
  error.value = '';
  try {
    await authStore.login(email.value, password.value);
    router.push('/panel');
  } catch (e) {
    error.value = 'Correo o contraseña incorrectos.';
  } finally {
    cargando.value = false;
  }
}
</script>