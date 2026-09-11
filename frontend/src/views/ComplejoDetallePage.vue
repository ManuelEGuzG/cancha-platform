<template>
  <ion-page class="complejo-detail-page">
    <!-- Top Bar Elegante -->
    <ion-header class="ion-no-border navbar-header">
      <ion-toolbar class="custom-toolbar">
        <ion-buttons slot="start">
          <ion-back-button default-href="/home" text="" class="custom-back-btn"></ion-back-button>
        </ion-buttons>
        <ion-title class="nav-title">
          {{ complejo?.nombre || 'Detalle del Complejo' }}
        </ion-title>
        <ion-buttons slot="end" v-if="complejo?.whatsapp_numero">
          <button class="btn-icon-wa" @click="contactarWhatsApp" title="Contactar por WhatsApp">
            <ion-icon name="logo-whatsapp"></ion-icon>
          </button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true" class="page-content">
      
      <!-- Skeleton Loading General -->
      <div v-if="cargando" class="container padding-responsive">
        <div class="skeleton-hero"></div>
        <div class="skeleton-line title"></div>
        <div class="skeleton-line subtitle"></div>
        <div class="skeleton-card mt-4"></div>
      </div>

      <!-- Contenido Principal cuando está cargado -->
      <div v-else-if="complejo" class="main-wrapper">
        
        <!-- Banner / Hero de Presentación -->
        <div class="complejo-hero">
          <img 
            :src="getComplejoImage(complejo)" 
            :alt="complejo.nombre"
            class="hero-image"
            @error="handleImageError"
          />
          <div class="hero-overlay"></div>
          <div class="hero-badge">
            <ion-icon name="location-sharp"></ion-icon>
            <span>{{ complejo.distrito }}, {{ complejo.canton }}</span>
          </div>
        </div>

        <div class="container padding-responsive">
          
          <!-- Encabezado y Dirección -->
          <section class="info-header-card">
            <div class="title-row">
              <div class="logo-wrapper" v-if="complejo.logo_url || fallbackImage">
                <img
                  :src="getComplejoImage(complejo)"
                  :alt="`${complejo.nombre} logo`"
                  class="logo-image"
                  @error="handleImageError"
                />
              </div>
              <div class="title-copy">
                <h1 class="complejo-title">{{ complejo.nombre }}</h1>
                <p class="complejo-address">
                  <ion-icon name="navigate-outline"></ion-icon>
                  {{ complejo.direccion_texto || 'Sin dirección exacta registrada' }}
                </p>
              </div>
            </div>

            <div class="metrics-grid">
              <div class="metric-card">
                <span class="metric-label">Ubicación</span>
                <strong>{{ complejo.distrito }}, {{ complejo.canton }}</strong>
              </div>
              <div class="metric-card">
                <span class="metric-label">Canchas</span>
                <strong>{{ complejo.canchas?.length || 0 }}</strong>
              </div>
              <div class="metric-card">
                <span class="metric-label">Desde</span>
                <strong>₡{{ formatearPrecio(complejo.canchas?.[0]?.precio_hora || 0) }}</strong>
              </div>
            </div>

            <div v-if="complejo.descripcion" class="description-box">
              <h3>Descripción</h3>
              <p>{{ complejo.descripcion }}</p>
            </div>

            <button 
              v-if="complejo.whatsapp_numero" 
              class="btn-whatsapp-full" 
              @click="contactarWhatsApp"
            >
              <ion-icon name="logo-whatsapp" class="wa-icon"></ion-icon>
              <div class="wa-btn-text">
                <span>Consultar por WhatsApp</span>
                <small>Respuesta directa con el complejo</small>
              </div>
              <ion-icon name="open-outline" class="open-icon"></ion-icon>
            </button>
          </section>

          <!-- Sección de Disponibilidad con Calendario Libre -->
          <section class="disponibilidad-section">
            <div class="section-title-wrapper">
              <div>
                <h2>Horarios y Disponibilidad</h2>
                <p class="section-sub">Selecciona cualquier fecha para consultar canchas libres</p>
              </div>
            </div>

            <!-- Selector de Fecha Libre: Carrusel + Datetime Modal -->
            <div class="date-picker-container">
              
              <div class="date-header-row">
                <span class="selected-date-label">
                  <ion-icon name="calendar-outline"></ion-icon>
                  {{ fechaFormateadaCorta }}
                </span>

                <!-- Botón de Calendario para Elegir Cualquier Fecha -->
                <button class="btn-picker-trigger" id="open-date-modal">
                  <ion-icon name="calendar-sharp"></ion-icon>
                  <span>Elegir fecha</span>
                </button>
              </div>

              <!-- Modal Nativo de Ionic con Datetime -->
              <ion-modal trigger="open-date-modal" :keep-contents-mounted="true">
                <ion-datetime
                  id="datetime"
                  presentation="date"
                  :value="fechaSeleccionada"
                  :min="fechaMinima"
                  @ionChange="onFechaModalChange($event)"
                  :show-default-buttons="true"
                  done-text="Seleccionar"
                  cancel-text="Cancelar"
                ></ion-datetime>
              </ion-modal>

              <!-- Carrusel de Acceso Rápido (Próximos Días) -->
              <div class="date-chips-scroll">
                <button
                  v-for="dia in proximosDias"
                  :key="dia.iso"
                  class="date-chip-btn"
                  :class="{ 'is-selected': dia.iso === fechaSeleccionada }"
                  @click="seleccionarFecha(dia.iso)"
                >
                  <span class="chip-day-name">{{ dia.nombreDia }}</span>
                  <span class="chip-day-number">{{ dia.numeroDia }}</span>
                  <span class="chip-month">{{ dia.mes }}</span>
                </button>
              </div>

            </div>

            <!-- Leyenda de Estados de Horario -->
            <div class="legend-bar">
              <div class="legend-item"><span class="dot disponible"></span>Disponible</div>
              <div class="legend-item"><span class="dot ocupada"></span>Ocupado</div>
              <div class="legend-item"><span class="dot pendiente"></span>Pendiente</div>
              <div class="legend-item"><span class="dot bloqueada"></span>Bloqueado</div>
            </div>

            <!-- Skeletons durante la carga -->
            <div v-if="cargandoDisponibilidad" class="canchas-skeleton-list mt-3">
              <div v-for="i in 2" :key="i" class="cancha-card-skeleton">
                <div class="skeleton-line c-title"></div>
                <div class="grid-skeleton">
                  <div v-for="j in 6" :key="j" class="skeleton-chip"></div>
                </div>
              </div>
            </div>

            <!-- Grilla de Canchas y Bloques de Horario -->
            <div v-else-if="disponibilidad?.canchas && disponibilidad.canchas.length > 0" class="canchas-list">
              
              <div 
                v-for="cancha in disponibilidad.canchas" 
                :key="cancha.cancha_id" 
                class="cancha-card"
              >
                <div class="cancha-card-header">
                  <div class="cancha-info">
                    <ion-icon name="football-outline" class="cancha-icon"></ion-icon>
                    <h3>{{ cancha.nombre }}</h3>
                  </div>
                  <div class="cancha-price-badge">
                    <strong>₡{{ formatearPrecio(cancha.precio_hora) }}</strong>
                    <small>/hora</small>
                  </div>
                </div>

                <div class="horarios-grid">
                  <div
                    v-for="bloque in cancha.bloques"
                    :key="bloque.hora_inicio"
                    class="slot-btn"
                    :class="[
                      `slot-${bloque.estado}`, 
                      { 'is-interactive': bloque.estado === 'disponible' }
                    ]"
                    :disabled="bloque.estado !== 'disponible'"
                    @click="seleccionarBloque(cancha, bloque)"
                  >
                    <span class="time-text">{{ bloque.hora_inicio }}</span>
                    <span class="status-indicator">
                      {{ statusLabel(bloque.estado) }}
                    </span>
                  </div>
                </div>
              </div>

            </div>

            <!-- Estado Vacío en Disponibilidad -->
            <div v-else class="empty-dispo-box">
              <ion-icon name="calendar-clear-outline" class="empty-icon"></ion-icon>
              <h4>Sin disponibilidad para esta fecha</h4>
              <p>No se encontraron horarios para el {{ fechaFormateadaCorta }}.</p>
            </div>

          </section>

          <section class="gallery-section" aria-labelledby="gallery-title">
            <div class="section-heading-row">
              <div>
                <span class="eyebrow">El espacio</span>
                <h2 id="gallery-title">Conoce la cancha</h2>
                <p>Un espacio preparado para disfrutar tus partidos de día y de noche.</p>
              </div>
              <ion-icon name="images-outline" class="section-heading-icon"></ion-icon>
            </div>
            <div class="gallery-grid">
              <img
                v-for="(imagen, index) in galleryImages"
                :key="`${imagen}-${index}`"
                :src="imagen"
                :alt="`Instalaciones de ${complejo.nombre}, imagen ${index + 1}`"
                @error="handleImageError"
              />
            </div>
          </section>

        </div>
      </div>

      <!-- Error State -->
      <div v-else class="container empty-state-full">
        <ion-icon name="alert-circle-outline" class="error-icon"></ion-icon>
        <h3>No se encontró el complejo</h3>
        <p>El complejo deportivo que buscas no existe o no está activo actualmente.</p>
        <ion-button router-link="/home" fill="clear">Volver al Inicio</ion-button>
      </div>

    </ion-content>

    <ion-modal :is-open="Boolean(bloqueSeleccionado)" @didDismiss="cerrarReserva">
      <div class="reservation-modal">
        <div class="reservation-modal-header">
          <div>
            <span class="eyebrow">Solicitud de reserva</span>
            <h2>Reservar cancha</h2>
            <p>{{ fechaFormateadaCorta }} · {{ bloqueSeleccionado?.hora_inicio }} - {{ bloqueSeleccionado?.hora_fin }}</p>
          </div>
          <button class="modal-close" aria-label="Cerrar" @click="cerrarReserva">
            <ion-icon name="close-outline"></ion-icon>
          </button>
        </div>

        <div class="reservation-form">
          <label>Nombre completo <span>*</span></label>
          <ion-input v-model="solicitud.nombre" fill="outline" placeholder="Juan Pérez"></ion-input>

          <label>Teléfono <span>*</span></label>
          <ion-input v-model="solicitud.telefono" fill="outline" type="tel" placeholder="8888-8888"></ion-input>

          <label>Comentario <small>(opcional)</small></label>
          <ion-textarea v-model="solicitud.comentario" fill="outline" :auto-grow="true" placeholder="¿Algo que debamos saber?"></ion-textarea>

          <div class="reservation-note">
            <ion-icon name="information-circle-outline"></ion-icon>
            <p>Te contactaremos por WhatsApp para confirmar la disponibilidad y completar la reserva.</p>
          </div>

          <div class="modal-actions">
            <button class="btn-secondary" @click="cerrarReserva">Cancelar</button>
            <button class="btn-primary" :disabled="!solicitud.nombre || !solicitud.telefono" @click="enviarSolicitud">
              <ion-icon name="logo-whatsapp"></ion-icon>
              Solicitar por WhatsApp
            </button>
          </div>
        </div>
      </div>
    </ion-modal>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonButtons, IonBackButton,
  IonIcon, IonButton, IonModal, IonDatetime, IonInput, IonTextarea
} from '@ionic/vue';
import complejosService from '@/services/complejos.service';
import echo from '@/services/echo';
import type { ComplejoDetalle, Disponibilidad } from '@/types';

const route = useRoute();
const slug = route.params.slug as string;
const fallbackImage = 'https://images.unsplash.com/photo-1529900748604-07564a03e7a6?auto=format&fit=crop&w=800&q=80';

const complejo = ref<ComplejoDetalle | null>(null);
const disponibilidad = ref<Disponibilidad | null>(null);
const cargando = ref(true);
const cargandoDisponibilidad = ref(false);
const bloqueSeleccionado = ref<{ hora_inicio: string; hora_fin: string } | null>(null);
const canchaSeleccionada = ref<{ nombre: string } | null>(null);
const solicitud = ref({ nombre: '', telefono: '', comentario: '' });

// Fecha seleccionada en formato ISO 'YYYY-MM-DD'
const fechaSeleccionada = ref<string>(obtenerFechaHoyISO());
const fechaMinima = obtenerFechaHoyISO();

function obtenerFechaHoyISO(): string {
  const hoy = new Date();
  return hoy.toISOString().split('T')[0];
}

// Genera los próximos 7 días para el acceso rápido horizontal
interface DiaItem {
  iso: string;
  nombreDia: string;
  numeroDia: number;
  mes: string;
}

const proximosDias = computed<DiaItem[]>(() => {
  const lista: DiaItem[] = [];
  const base = new Date();

  for (let i = 0; i < 7; i++) {
    const d = new Date(base);
    d.setDate(base.getDate() + i);
    const iso = d.toISOString().split('T')[0];

    const esHoy = i === 0;
    const esManana = i === 1;

    let nombreDia = d.toLocaleDateString('es-CR', { weekday: 'short' }).replace('.', '');
    if (esHoy) nombreDia = 'Hoy';
    else if (esManana) nombreDia = 'Mañ.';

    lista.push({
      iso,
      nombreDia: nombreDia.toUpperCase(),
      numeroDia: d.getDate(),
      mes: d.toLocaleDateString('es-CR', { month: 'short' }).replace('.', '').toUpperCase(),
    });
  }

  return lista;
});

const galleryImages = computed(() => [
  getComplejoImage(complejo.value),
  '/Images/Soccer_center.jpeg',
  '/Images/oij.jpeg',
  fallbackImage,
]);

// Etiqueta legible de la fecha seleccionada
const fechaFormateadaCorta = computed(() => {
  if (!fechaSeleccionada.value) return '';
  const [year, month, day] = fechaSeleccionada.value.split('-').map(Number);
  const dateObj = new Date(year, month - 1, day);
  
  return dateObj.toLocaleDateString('es-CR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
});

function seleccionarFecha(iso: string) {
  if (fechaSeleccionada.value !== iso) {
    fechaSeleccionada.value = iso;
    cargarDisponibilidad();
  }
}

function onFechaModalChange(event: CustomEvent) {
  const val = event.detail.value;
  if (val) {
    const iso = val.split('T')[0];
    fechaSeleccionada.value = iso;
    cargarDisponibilidad();
  }
}

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
    const { data } = await complejosService.disponibilidad(slug, fechaSeleccionada.value);
    disponibilidad.value = data.data;
  } catch (error) {
    console.error('Error cargando disponibilidad:', error);
  } finally {
    cargandoDisponibilidad.value = false;
  }
}

function statusLabel(estado: string): string {
  const labels: Record<string, string> = {
    disponible: 'Libre',
    ocupada: 'Ocupado',
    pendiente: 'Pendiente',
    bloqueada: 'No disp.'
  };
  return labels[estado] || estado;
}

function seleccionarBloque(cancha: { nombre: string }, bloque: { hora_inicio: string; hora_fin: string; estado: string }) {
  if (bloque.estado !== 'disponible') return;
  canchaSeleccionada.value = cancha;
  bloqueSeleccionado.value = bloque;
}

function cerrarReserva() {
  bloqueSeleccionado.value = null;
  canchaSeleccionada.value = null;
}

function enviarSolicitud() {
  if (!complejo.value || !bloqueSeleccionado.value || !solicitud.value.nombre || !solicitud.value.telefono) return;

  const mensaje = [
    `Hola, quisiera reservar ${canchaSeleccionada.value?.nombre || 'la cancha'} en ${complejo.value.nombre}.`,
    `Fecha: ${fechaFormateadaCorta.value}.`,
    `Horario: ${bloqueSeleccionado.value.hora_inicio} - ${bloqueSeleccionado.value.hora_fin}.`,
    `Nombre: ${solicitud.value.nombre}.`,
    `Teléfono: ${solicitud.value.telefono}.`,
    solicitud.value.comentario ? `Comentario: ${solicitud.value.comentario}.` : '',
  ].filter(Boolean).join('\n');

  const numero = complejo.value.whatsapp_numero?.replace(/\D/g, '');
  if (numero) window.open(`https://wa.me/${numero}?text=${encodeURIComponent(mensaje)}`, '_blank');
  cerrarReserva();
}

function formatearPrecio(precio: number): string {
  return precio ? precio.toLocaleString('es-CR') : '0';
}

function getComplejoImage(complejo: any): string {
  const nombre = (complejo?.nombre || '').toLowerCase();

  if (complejo?.logo_url) {
    return complejo.logo_url;
  }

  if (nombre.includes('oij')) {
    return '/Images/oij.jpeg';
  }

  if (nombre.includes('soccer') || nombre.includes('center') || nombre.includes('soccer_center')) {
    return '/Images/Soccer_center.jpeg';
  }

  return fallbackImage;
}

function handleImageError(event: Event) {
  const target = event.target as HTMLImageElement;
  // Evita loop infinito si el fallback también falla
  if (target.src === fallbackImage) return;
  target.src = fallbackImage;
}

async function contactarWhatsApp() {
  try {
    const { data } = await complejosService.enlaceWhatsApp(slug);
    if (data?.data?.enlace) {
      window.open(data.data.enlace, '_blank');
    }
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
/* Custom Properties */
.complejo-detail-page {
  --primary: #21439a;
  --primary-hover: #173477;
  --bg-page: #f7f9fc;
  --text-main: #0f172a;
  --text-muted: #64748b;
  --border-color: #dfeaf7;
  --accent: #66E3DA;
}

.page-content {
  --background: var(--bg-page);
  font-family: "Avenir Next", "Segoe UI", sans-serif;
}

.container {
  max-width: 1060px;
  margin: 0 auto;
}

.padding-responsive {
  padding: 1.75rem 1.25rem 3.5rem;
}

/* Header Navbar */
.navbar-header {
  background: #ffffff;
  border-bottom: 1px solid var(--border-color);
}

.custom-toolbar {
  --background: #ffffff;
  --border-width: 0;
}

.nav-title {
  font-weight: 700;
  font-size: 1.05rem;
  color: var(--text-main);
}

.btn-icon-wa {
  background: #25d366;
  color: #ffffff;
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  cursor: pointer;
  margin-right: 0.5rem;
}

/* Hero Visual */
.complejo-hero {
  position: relative;
  height: 270px;
  width: 100%;
  background: #000;
  overflow: hidden;
}

.hero-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(15, 23, 42, 0.75) 0%, transparent 60%);
}

.hero-badge {
  position: absolute;
  bottom: 1rem;
  left: 1rem;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(8px);
  color: #ffffff;
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

/* Card Principal de Información */
.info-header-card {
  background: #ffffff;
  border-radius: 16px;
  padding: 1.25rem;
  margin-top: -2.25rem;
  position: relative;
  z-index: 10;
  border: 1px solid var(--border-color);
  box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
}

.title-row {
  display: flex;
  align-items: center;
  gap: 0.9rem;
}

.logo-wrapper {
  width: 76px;
  height: 76px;
  min-width: 76px;
  border-radius: 22px;
  padding: 4px;
  background: linear-gradient(135deg, rgba(0, 102, 255, 0.12), rgba(16, 185, 129, 0.12));
  border: 2px solid rgba(0, 102, 255, 0.15);
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
}

.logo-image {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 18px;
}

.title-copy {
  flex: 1;
  min-width: 0;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.75rem;
  margin: 1rem 0 1.1rem;
}

.metric-card {
  background: linear-gradient(180deg, #f8fbff 0%, #eef6ff 100%);
  border: 1px solid rgba(0, 102, 255, 0.12);
  border-radius: 12px;
  padding: 0.85rem 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.metric-label {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--text-muted);
  font-weight: 700;
}

.metric-card strong {
  font-size: 0.9rem;
  color: var(--text-main);
  font-weight: 800;
}

.description-box {
  background: #f8fafc;
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 0.9rem 1rem;
  margin-bottom: 1rem;
}

.description-box h3 {
  margin: 0 0 0.4rem;
  font-size: 0.8rem;
  text-transform: uppercase;
  color: var(--text-muted);
  letter-spacing: 0.08em;
}

.description-box p {
  margin: 0;
  color: var(--text-main);
  line-height: 1.6;
  font-size: 0.9rem;
}

.complejo-title {
  font-size: 1.5rem;
  font-weight: 800;
  color: var(--text-main);
  margin: 0 0 0.35rem 0;
  letter-spacing: -0.02em;
}

.complejo-address {
  font-size: 0.875rem;
  color: var(--text-muted);
  margin: 0 0 1.25rem 0;
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.complejo-address ion-icon {
  color: var(--primary);
}

/* Botón WhatsApp */
.btn-whatsapp-full {
  width: 100%;
  background: #25d366;
  color: #ffffff;
  border: none;
  border-radius: 12px;
  padding: 0.75rem 1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
}

.wa-icon { font-size: 1.75rem; }

.wa-btn-text {
  display: flex;
  flex-direction: column;
  text-align: left;
  flex: 1;
}

.wa-btn-text span { font-weight: 700; font-size: 0.95rem; }
.wa-btn-text small { font-size: 0.75rem; opacity: 0.9; }

/* Componente de Selección de Fechas */
.disponibilidad-section {
  margin-top: 3rem;
}

.section-title-wrapper h2 {
  font-size: 1.55rem;
  font-weight: 800;
  color: var(--text-main);
  margin: 0 0 0.2rem 0;
}

.section-sub {
  font-size: 0.825rem;
  color: var(--text-muted);
  margin: 0 0 1rem 0;
}

.date-picker-container {
  background: #ffffff;
  border: 1px solid var(--border-color);
  border-radius: 14px;
  padding: 1rem;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.04);
  margin-bottom: 1rem;
}

.date-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.85rem;
  padding-bottom: 0.6rem;
  border-bottom: 1px solid #f1f5f9;
}

.selected-date-label {
  font-size: 0.875rem;
  font-weight: 700;
  color: var(--text-main);
  text-transform: capitalize;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}

.selected-date-label ion-icon {
  color: var(--primary);
}

.btn-picker-trigger {
  background: #f0f7ff;
  border: 1px solid #c7d2fe;
  color: var(--primary);
  padding: 0.35rem 0.75rem;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-picker-trigger:hover {
  background: var(--primary);
  color: #ffffff;
}

/* Carrusel Horizontal de Días */
.date-chips-scroll {
  display: flex;
  gap: 0.5rem;
  overflow-x: auto;
  padding: 0.2rem 0.1rem 0.35rem;
  scrollbar-width: thin;
}

.date-chip-btn {
  flex: 0 0 auto;
  width: 88px;
  height: 76px;
  background: #f8fafc;
  border: 1px solid var(--border-color);
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.chip-day-name {
  font-size: 0.65rem;
  font-weight: 700;
  color: var(--text-muted);
}

.chip-day-number {
  font-size: 1.15rem;
  font-weight: 800;
  color: var(--text-main);
  line-height: 1.1;
}

.chip-month {
  font-size: 0.6rem;
  color: var(--text-muted);
}

.date-chip-btn.is-selected {
  background: var(--primary);
  border-color: var(--primary);
  box-shadow: 0 8px 18px rgba(33, 67, 154, 0.25);
}

.date-chip-btn.is-selected .chip-day-name,
.date-chip-btn.is-selected .chip-day-number,
.date-chip-btn.is-selected .chip-month {
  color: #ffffff;
}

/* Leyenda */
.legend-bar {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  margin-bottom: 1.25rem;
  background: #ffffff;
  padding: 0.6rem 0.85rem;
  border-radius: 10px;
  border: 1px solid var(--border-color);
  font-size: 0.75rem;
  color: var(--text-muted);
  font-weight: 600;
}

.legend-item { display: flex; align-items: center; gap: 0.35rem; }
.dot { width: 8px; height: 8px; border-radius: 50%; }
.dot.disponible { background-color: #10b981; }
.dot.ocupada { background-color: #ef4444; }
.dot.pendiente { background-color: #f59e0b; }
.dot.bloqueada { background-color: #64748b; }

/* Tarjetas de Canchas */
.canchas-list {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.cancha-card {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid var(--border-color);
  padding: 1.25rem;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
}

.cancha-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px dashed var(--border-color);
}

.cancha-info { display: flex; align-items: center; gap: 0.5rem; }
.cancha-icon { color: var(--primary); font-size: 1.2rem; }

.cancha-info h3 {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--text-main);
  margin: 0;
}

.cancha-price-badge strong { color: var(--primary); font-size: 1.1rem; font-weight: 800; }
.cancha-price-badge small { color: var(--text-muted); font-size: 0.75rem; }

/* Horarios Grid */
.horarios-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(85px, 1fr));
  gap: 0.6rem;
}

.slot-btn {
  width: 100%;
  min-height: 68px;
  font: inherit;
  border-radius: 10px;
  padding: 0.5rem 0.25rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border: 1px solid transparent;
  transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.time-text { font-size: 0.85rem; font-weight: 700; }
.status-indicator { font-size: 0.65rem; font-weight: 600; text-transform: uppercase; margin-top: 2px; }

.slot-disponible { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }
.slot-disponible.is-interactive:hover {
  background: #10b981;
  color: #ffffff;
  cursor: pointer;
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(16, 185, 129, 0.22);
}

.slot-ocupada { background: #fef2f2; border-color: #fecaca; color: #991b1b; opacity: 0.7; text-decoration: line-through; }
.slot-pendiente { background: #fffbeb; border-color: #fde68a; color: #92400e; }
.slot-bloqueada { background: #f1f5f9; border-color: #e2e8f0; color: #94a3b8; opacity: 0.6; }

/* Empty States & Skeleton Helpers */
.empty-dispo-box, .empty-state-full {
  text-align: center;
  background: #ffffff;
  padding: 2.5rem 1rem;
  border-radius: 14px;
  border: 1px dashed var(--border-color);
  color: var(--text-muted);
}

.empty-icon, .error-icon { font-size: 2.5rem; margin-bottom: 0.5rem; }
.skeleton-hero { height: 200px; background: #e2e8f0; border-radius: 16px; margin-bottom: 1rem; }
.skeleton-line { background: #e2e8f0; border-radius: 4px; margin-bottom: 0.5rem; }
.skeleton-line.title { width: 60%; height: 24px; }
.skeleton-line.subtitle { width: 40%; height: 16px; }
.skeleton-card { height: 120px; background: #e2e8f0; border-radius: 14px; }
.skeleton-chip { height: 48px; background: #e2e8f0; border-radius: 10px; }

.grid-skeleton {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.5rem;
  margin-top: 0.75rem;
}

.cancha-card-skeleton {
  background: #ffffff;
  padding: 1rem;
  border-radius: 14px;
  margin-bottom: 1rem;
}

.skeleton-line.c-title { width: 35%; height: 18px; }
.mt-3 { margin-top: 0.75rem; }
.mt-4 { margin-top: 1rem; }

/* Galería y solicitud */
.gallery-section {
  margin-top: 3.5rem;
}

.section-heading-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 1rem;
  margin-bottom: 1rem;
}

.eyebrow {
  display: block;
  margin-bottom: 0.35rem;
  color: var(--primary);
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.section-heading-row h2 {
  margin: 0;
  color: var(--text-main);
  font-size: 1.55rem;
}

.section-heading-row p {
  margin: 0.35rem 0 0;
  color: var(--text-muted);
  font-size: 0.9rem;
}

.section-heading-icon {
  color: var(--primary);
  font-size: 1.75rem;
}

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0.75rem;
}

.gallery-grid img {
  width: 100%;
  aspect-ratio: 1.25 / 1;
  border-radius: 12px;
  object-fit: cover;
  box-shadow: 0 6px 16px rgba(15, 23, 42, 0.12);
}

.reservation-modal {
  max-width: 520px;
  margin: 0 auto;
  overflow: hidden;
  background: #ffffff;
  color: var(--text-main);
}

.reservation-modal-header {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  padding: 1.5rem 1.5rem 1.35rem;
  background: #188443;
  color: #ffffff;
}

.reservation-modal-header .eyebrow { color: #b8f4cc; }
.reservation-modal-header h2 { margin: 0; font-size: 1.35rem; }
.reservation-modal-header p { margin: 0.35rem 0 0; color: #d5f8df; font-size: 0.9rem; }

.modal-close {
  align-self: flex-start;
  border: 0;
  background: transparent;
  color: #d5f8df;
  cursor: pointer;
  font-size: 1.55rem;
}

.reservation-form { padding: 1.5rem; }
.reservation-form label { display: block; margin: 0.95rem 0 0.45rem; color: #334155; font-weight: 700; }
.reservation-form label:first-child { margin-top: 0; }
.reservation-form label span { color: #dc2626; }
.reservation-form label small { color: #94a3b8; font-weight: 500; }
.reservation-form ion-input, .reservation-form ion-textarea { --border-radius: 9px; --border-color: #cbd5e1; --highlight-color: var(--primary); }

.reservation-note {
  display: flex;
  gap: 0.6rem;
  margin-top: 1.25rem;
  padding: 0.8rem;
  border: 1px solid #fde68a;
  border-radius: 9px;
  background: #fffbeb;
  color: #92400e;
  font-size: 0.8rem;
}

.reservation-note ion-icon { flex: 0 0 auto; font-size: 1.1rem; }
.reservation-note p { margin: 0; line-height: 1.45; }
.modal-actions { display: grid; grid-template-columns: 1fr 1.25fr; gap: 0.75rem; margin-top: 1.4rem; }
.btn-secondary, .btn-primary { min-height: 44px; border-radius: 9px; border: 1px solid #cbd5e1; font-weight: 800; cursor: pointer; }
.btn-secondary { background: #ffffff; color: #334155; }
.btn-primary { display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; border-color: #16a34a; background: #16a34a; color: #ffffff; }
.btn-primary:disabled { cursor: not-allowed; opacity: 0.5; }

@media (max-width: 640px) {
  .complejo-hero { height: 205px; }
  .metrics-grid { grid-template-columns: 1fr; }
  .gallery-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .section-heading-row { align-items: flex-start; }
  .section-heading-icon { display: none; }
  .reservation-modal-header, .reservation-form { padding-left: 1.15rem; padding-right: 1.15rem; }
}
</style>