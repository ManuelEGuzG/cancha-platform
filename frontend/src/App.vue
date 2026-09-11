<template>
  <ion-app>
    <ion-router-outlet />
  </ion-app>
</template>

<script setup lang="ts">
import { IonApp, IonRouterOutlet } from '@ionic/vue';
</script>

<style>
  :root {
    color-scheme: light;
  }

  html, body, #app {
    min-height: 100%;
    background: linear-gradient(180deg, #f8fdff 0%, #edf7ff 100%);
  }

  * {
    box-sizing: border-box;
  }
</style>
