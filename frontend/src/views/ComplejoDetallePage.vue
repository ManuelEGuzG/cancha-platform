<template>
  <ion-page class="sportra-app">
    <!-- Navbar Flotante Neón Glass -->
    <header class="navbar-container">
      <div class="navbar-bar">
        <div class="brand-box" @click="irAHome" role="button" tabindex="0">
          <div class="brand-badge glow-pulse">
            <ion-icon name="football-outline" class="brand-icon"></ion-icon>
          </div>
          <span class="brand-name">SPORTRA<span class="neon-dot">.</span></span>
        </div>

        <div class="header-right">
          <button class="btn-portal-glow" @click="irAHome">
            <ion-icon name="arrow-back-outline"></ion-icon>
            <span>Volver</span>
          </button>
        </div>
      </div>
    </header>

    <ion-content :fullscreen="true" class="sportra-main-viewport">
      <div class="page-background-glow glow-float-1"></div>
      <div class="page-background-glow glow-float-2"></div>
      <div class="bg-grid"></div>

      <!-- Skeleton Loading General -->
      <div v-if="cargando" class="detail-wrapper loading-state">
        <ion-spinner name="crescent" class="lime-spinner"></ion-spinner>
        <span>Cargando detalles del complejo...</span>
      </div>

      <!-- Contenido Principal -->
      <div v-else-if="complejo" class="detail-wrapper">
        
        <!-- Hero Visual con Overlays Neón -->
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

        <main class="main-content-layout">
          
          <!-- Card de Información del Complejo -->
          <section class="glass-card info-header-card">
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
                <span class="eyebrow">Complejo Deportivo</span>
                <h1 class="complejo-title">{{ complejo.nombre }}</h1>
                <p class="complejo-address">
                  <ion-icon name="navigate-outline"></ion-icon>
                  {{ complejo.direccion_texto || 'Sin dirección exacta registrada' }}
                </p>
                <a v-if="enlaceGoogleMaps" :href="enlaceGoogleMaps" target="_blank" rel="noopener noreferrer" class="google-maps-link">
                  <ion-icon name="location-sharp"></ion-icon>
                  Ver ubicación en Google Maps
                </a>
              </div>
            </div>

            <div class="metrics-grid">
              <div class="metric-card">
                <span class="metric-label">Ubicación</span>
                <strong>{{ complejo.distrito }}, {{ complejo.canton }}</strong>
              </div>
              <div class="metric-card">
                <span class="metric-label">Canchas</span>
                <strong>{{ complejo.canchas?.length || 0 }} disponibles</strong>
              </div>
              <div class="metric-card">
                <span class="metric-label">Desde</span>
                <strong class="highlight-val">₡{{ formatearPrecio(complejo.canchas?.[0]?.precio_hora || 0) }}</strong>
              </div>
            </div>

            <div v-if="complejo.descripcion" class="description-box">
              <h3>Sobre este complejo</h3>
              <p>{{ complejo.descripcion }}</p>
            </div>

            <button 
              v-if="complejo.whatsapp_numero" 
              class="btn-whatsapp-full wave-effect" 
              @click="contactarWhatsApp"
            >
              <ion-icon name="logo-whatsapp" class="wa-icon"></ion-icon>
              <div class="wa-btn-text">
                <span>Consultar por WhatsApp</span>
                <small>Respuesta directa con la administración</small>
              </div>
              <ion-icon name="open-outline" class="open-icon"></ion-icon>
            </button>
          </section>

          <!-- Sección de Horarios y Disponibilidad -->
          <section class="disponibilidad-section">
            <div class="section-title-wrapper">
              <div>
                <span class="eyebrow">Reserva en línea</span>
                <h2>Horarios y Disponibilidad</h2>
                <p class="section-sub">Selecciona cualquier fecha para consultar canchas libres en tiempo real</p>
              </div>
            </div>

            <!-- Selector de Fecha Libre -->
            <div class="glass-card date-picker-container">
              
              <div class="date-header-row">
                <span class="selected-date-label">
                  <ion-icon name="calendar-outline"></ion-icon>
                  {{ fechaFormateadaCorta }}
                </span>

                <button class="btn-picker-trigger" id="open-date-modal">
                  <ion-icon name="calendar-sharp"></ion-icon>
                  <span>Elegir fecha</span>
                </button>
              </div>

              <!-- Modal Nativo Ionic de Datetime Estilizado -->
              <ion-modal trigger="open-date-modal" :keep-contents-mounted="true" class="dark-datetime-modal">
                <div class="date-modal-wrapper">
                  <ion-datetime
                    id="datetime"
                    presentation="date"
                    :value="fechaSeleccionada"
                    :min="fechaMinima"
                    @ionChange="onFechaModalChange($event)"
                    :show-default-buttons="true"
                    done-text="Seleccionar"
                    cancel-text="Cancelar"
                    class="custom-neon-datetime"
                  ></ion-datetime>
                </div>
              </ion-modal>

              <!-- Carrusel de Acceso Rápido Dinámico -->
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

            <!-- Leyenda de Estados -->
            <div class="glass-card legend-bar">
              <div class="legend-item"><span class="dot disponible"></span>Disponible</div>
              <div class="legend-item"><span class="dot reservado"></span>Reservado</div>
              <div class="legend-item"><span class="dot en-tramite"></span>En trámite</div>
              <div class="legend-item"><span class="dot cerrada"></span>Cerrada</div>
              <div class="legend-item"><span class="dot en-mantenimiento"></span>En mantenimiento</div>
            </div>

            <!-- Loading de Horarios -->
            <div v-if="cargandoDisponibilidad" class="dispo-loading-box">
              <ion-spinner name="crescent" class="lime-spinner"></ion-spinner>
              <span>Actualizando disponibilidad...</span>
            </div>

            <!-- Grilla de Canchas y Bloques de Horario -->
            <div v-else-if="disponibilidad?.canchas && disponibilidad.canchas.length > 0" class="canchas-list">
              
              <div 
                v-for="cancha in disponibilidad.canchas" 
                :key="cancha.cancha_id" 
                class="glass-card cancha-card"
              >
                <div class="cancha-card-header">
                  <div class="cancha-info">
                    <div class="cancha-icon-box">
                      <ion-icon name="football-outline"></ion-icon>
                    </div>
                    <div>
                      <h3>{{ cancha.nombre }}</h3>
                      <span class="deporte-subtitle">Fútbol</span>
                    </div>
                  </div>
                  <div class="cancha-price-badge">
                    <strong>₡{{ formatearPrecio(cancha.precio_hora) }}</strong>
                    <small>/ hora</small>
                  </div>
                </div>

                <div class="horarios-grid">
                  <button
                    v-for="bloque in cancha.bloques"
                    :key="bloque.hora_inicio"
                    class="slot-btn"
                    :class="[
                      `slot-${bloque.estado}`, 
                      {
                        'is-interactive': bloque.estado === 'disponible',
                        'is-selected': estaSeleccionado(cancha.cancha_id, bloque.hora_inicio)
                      }
                    ]"
                    :disabled="bloque.estado !== 'disponible'"
                    @click="alternarBloque(cancha, bloque)"
                  >
                    <span class="time-text">{{ bloque.hora_inicio }}</span>
                    <span class="status-indicator">
                      {{ statusLabel(bloque.estado) }}
                    </span>
                  </button>
                </div>

                <button
                  v-if="canchaSeleccionadaId === cancha.cancha_id && bloquesSeleccionados.length"
                  class="btn-modal-submit"
                  @click="modalSolicitudAbierto = true"
                >
                  Solicitar {{ bloquesSeleccionados.length }} {{ bloquesSeleccionados.length === 1 ? 'hora' : 'horas' }}
                </button>
              </div>

            </div>

            <!-- Estado Vacío -->
            <div v-else class="glass-card empty-dispo-box">
              <div class="empty-icon-glow">
                <ion-icon name="calendar-clear-outline"></ion-icon>
              </div>
              <h4>Sin disponibilidad para esta fecha</h4>
              <p>No se encontraron horarios configurados para el {{ fechaFormateadaCorta }}.</p>
            </div>

          </section>

          <!-- Galería de Instalaciones -->
          <section class="gallery-section">
            <div class="section-title-wrapper">
              <div>
                <span class="eyebrow">Instalaciones</span>
                <h2>Conoce el espacio</h2>
                <p class="section-sub">Espacios acondicionados para la práctica deportiva de día y noche.</p>
              </div>
            </div>
            <div class="gallery-grid">
              <div 
                v-for="(imagen, index) in galleryImages" 
                :key="`${imagen}-${index}`" 
                class="gallery-item-glass"
              >
                <img
                  :src="imagen"
                  :alt="`Instalaciones de ${complejo.nombre}, imagen ${index + 1}`"
                  @error="handleImageError"
                />
              </div>
            </div>
          </section>

        </main>
      </div>

      <!-- Error State -->
      <div v-else class="detail-wrapper empty-state-full">
        <div class="empty-icon-glow">
          <ion-icon name="alert-circle-outline"></ion-icon>
        </div>
        <h3>No se encontró el complejo</h3>
        <p>El complejo deportivo que buscas no existe o no se encuentra disponible actualmente.</p>
        <button class="btn-portal-glow" @click="irAHome">Volver al Inicio</button>
      </div>

    </ion-content>

    <!-- Modal de Reserva Neón Glass -->
    <ion-modal :is-open="modalSolicitudAbierto" @didDismiss="cerrarReserva" class="glass-modal-container">
      <div class="reservation-modal-glass">
        <div class="reservation-modal-header">
          <div>
            <span class="eyebrow">Solicitud instantánea</span>
            <h2>Reservar Cancha</h2>
            <p>{{ fechaFormateadaCorta }} · {{ resumenHorasSeleccionadas }}</p>
          </div>
          <button class="modal-close-btn" aria-label="Cerrar" @click="cerrarReserva">
            <ion-icon name="close-outline"></ion-icon>
          </button>
        </div>

        <div class="reservation-form">
          <div aria-hidden="true" style="position: absolute; left: -10000px;">
            <label for="website-check">Dejar vacío</label>
            <input id="website-check" v-model="solicitud.website" type="text" tabindex="-1" autocomplete="off" />
          </div>

          <div class="field-block">
            <label>Nombre completo <span>*</span></label>
            <div class="input-glow-box">
              <input v-model="solicitud.nombre" type="text" placeholder="Ej: Juan Pérez" />
            </div>
          </div>

          <div class="field-block">
            <label>Cédula <span>*</span></label>
            <div class="input-glow-box">
              <input v-model="solicitud.cedula" type="text" autocomplete="off" placeholder="Número de identificación" />
            </div>
          </div>

          <div class="field-block">
            <label>Teléfono <span>*</span></label>
            <div class="input-glow-box">
              <input v-model="solicitud.telefono" type="tel" placeholder="Ej: 8888-8888" />
            </div>
          </div>

          <div class="field-block">
            <label>Comentario <small>(opcional)</small></label>
            <div class="input-glow-box">
              <textarea v-model="solicitud.comentario" rows="3" placeholder="¿Algún requerimiento especial?"></textarea>
            </div>
          </div>

          <div class="reservation-note">
            <ion-icon name="information-circle-outline"></ion-icon>
            <p>Las horas quedarán en trámite por 15 minutos. Se abrirá WhatsApp con los datos; debes tocar Enviar para avisar al dueño.</p>
          </div>

          <div v-if="turnstileSiteKey" ref="turnstileContainer" class="turnstile-container"></div>

          <p v-if="errorSolicitud" class="reservation-note error-note" role="alert">{{ errorSolicitud }}</p>

          <div class="modal-actions">
            <button class="btn-modal-cancel" @click="cerrarReserva">Cancelar</button>
            <button 
              class="btn-modal-submit" 
              :disabled="enviandoSolicitud || !solicitud.nombre || !solicitud.cedula || !solicitud.telefono || Boolean(turnstileSiteKey && !captchaToken)"
              @click="enviarSolicitud"
            >
              <ion-icon name="logo-whatsapp"></ion-icon>
              {{ enviandoSolicitud ? 'Enviando solicitud...' : 'Solicitar horas' }}
            </button>
          </div>
        </div>
      </div>
    </ion-modal>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  IonPage, IonContent, IonIcon, IonModal, IonDatetime, IonSpinner
} from '@ionic/vue';
import { addIcons } from 'ionicons';
import { 
  footballOutline, 
  arrowBackOutline, 
  locationSharp, 
  navigateOutline, 
  logoWhatsapp, 
  openOutline, 
  calendarOutline, 
  calendarSharp, 
  calendarClearOutline, 
  alertCircleOutline, 
  closeOutline, 
  informationCircleOutline 
} from 'ionicons/icons';
import complejosService from '@/services/complejos.service';
import echo from '@/services/echo';
import type { ComplejoDetalle, Disponibilidad } from '@/types';

addIcons({
  'football-outline': footballOutline,
  'arrow-back-outline': arrowBackOutline,
  'location-sharp': locationSharp,
  'navigate-outline': navigateOutline,
  'logo-whatsapp': logoWhatsapp,
  'open-outline': openOutline,
  'calendar-outline': calendarOutline,
  'calendar-sharp': calendarSharp,
  'calendar-clear-outline': calendarClearOutline,
  'alert-circle-outline': alertCircleOutline,
  'close-outline': closeOutline,
  'information-circle-outline': informationCircleOutline
});

const route = useRoute();
const router = useRouter();
const slug = route.params.slug as string;
const fallbackImage = 'https://images.unsplash.com/photo-1529900748604-07564a03e7a6?auto=format&fit=crop&w=800&q=80';

const complejo = ref<ComplejoDetalle | null>(null);
const disponibilidad = ref<Disponibilidad | null>(null);
const cargando = ref(true);
const cargandoDisponibilidad = ref(false);
const canchaSeleccionadaId = ref<number | null>(null);
const bloquesSeleccionados = ref<{ cancha_id: number; nombre: string; hora_inicio: string; hora_fin: string }[]>([]);
const modalSolicitudAbierto = ref(false);
const enviandoSolicitud = ref(false);
const errorSolicitud = ref('');
const captchaToken = ref('');
const turnstileContainer = ref<HTMLElement | null>(null);
const turnstileSiteKey = import.meta.env.VITE_TURNSTILE_SITE_KEY || '';
const solicitud = ref({ nombre: '', cedula: '', telefono: '', comentario: '', website: '' });
let turnstileWidgetId: string | undefined;

type TurnstileApi = {
  render: (container: HTMLElement, options: {
    sitekey: string;
    action: string;
    callback: (token: string) => void;
    'expired-callback': () => void;
  }) => string;
  reset: (widgetId?: string) => void;
  remove: (widgetId: string) => void;
};

const fechaSeleccionada = ref<string>(obtenerFechaHoyISO());
const fechaMinima = obtenerFechaHoyISO();
const enlaceGoogleMaps = computed(() => {
  if (!complejo.value?.latitud || !complejo.value?.longitud) return '';
  return `https://www.google.com/maps/search/?api=1&query=${complejo.value.latitud},${complejo.value.longitud}`;
});

function obtenerFechaHoyISO(): string {
  const hoy = new Date();
  const year = hoy.getFullYear();
  const month = String(hoy.getMonth() + 1).padStart(2, '0');
  const day = String(hoy.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

interface DiaItem {
  iso: string;
  nombreDia: string;
  numeroDia: number;
  mes: string;
}

const DIAS_A_MOSTRAR = 14;

const proximosDias = computed<DiaItem[]>(() => {
  const lista: DiaItem[] = [];
  const base = new Date();

  for (let i = 0; i < DIAS_A_MOSTRAR; i++) {
    const d = new Date(base.getFullYear(), base.getMonth(), base.getDate() + i);
    lista.push(formatearObjetoDia(d));
  }

  if (fechaSeleccionada.value) {
    const existe = lista.some(dia => dia.iso === fechaSeleccionada.value);
    if (!existe) {
      const [year, month, day] = fechaSeleccionada.value.split('-').map(Number);
      const fechaEspecial = new Date(year, month - 1, day);
      lista.push(formatearObjetoDia(fechaEspecial));
    }
  }

  return lista.sort((a, b) => a.iso.localeCompare(b.iso));
});

function formatearObjetoDia(d: Date): DiaItem {
  const hoy = new Date();
  const manana = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate() + 1);

  const year = d.getFullYear();
  const monthStr = String(d.getMonth() + 1).padStart(2, '0');
  const dayStr = String(d.getDate()).padStart(2, '0');
  const iso = `${year}-${monthStr}-${dayStr}`;

  const hoyIso = obtenerFechaHoyISO();
  const mananaIso = `${manana.getFullYear()}-${String(manana.getMonth() + 1).padStart(2, '0')}-${String(manana.getDate()).padStart(2, '0')}`;

  const esHoy = iso === hoyIso;
  const esManana = iso === mananaIso;

  let nombreDia = d.toLocaleDateString('es-CR', { weekday: 'short' }).replace('.', '');
  if (esHoy) nombreDia = 'Hoy';
  else if (esManana) nombreDia = 'Mañ.';

  return {
    iso,
    nombreDia: nombreDia.toUpperCase(),
    numeroDia: d.getDate(),
    mes: d.toLocaleDateString('es-CR', { month: 'short' }).replace('.', '').toUpperCase(),
  };
}

const galleryImages = computed(() => [
  ...((complejo.value?.canchas || []).flatMap((cancha) => cancha.fotos || []).map((foto) => foto.url)),
].length
  ? (complejo.value?.canchas || []).flatMap((cancha) => cancha.fotos || []).map((foto) => foto.url)
  : [getComplejoImage(complejo.value), '/Images/Soccer_center.jpeg', '/Images/oij.jpeg', fallbackImage]);

// Parseo explicito en hora local para evitar el desajuste UTC
const fechaFormateadaCorta = computed(() => {
  if (!fechaSeleccionada.value) return '';
  const [year, month, day] = fechaSeleccionada.value.split('-').map(Number);
  const dateObj = new Date(year, month - 1, day);
  
  const texto = dateObj.toLocaleDateString('es-CR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });

  return texto.charAt(0).toUpperCase() + texto.slice(1);
});

function seleccionarFecha(iso: string) {
  if (fechaSeleccionada.value !== iso) {
    limpiarSeleccion();
    fechaSeleccionada.value = iso;
    cargarDisponibilidad();
  }
}

function onFechaModalChange(event: CustomEvent) {
  const val = event.detail.value;
  if (val) {
    const iso = Array.isArray(val) ? val[0].split('T')[0] : val.split('T')[0];
    limpiarSeleccion();
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
    disponible: 'Disponible',
    reservado: 'Reservado',
    en_tramite: 'En trámite',
    cerrada: 'Cerrada',
    en_mantenimiento: 'En mantenimiento',
  };
  return labels[estado] || estado;
}

const resumenHorasSeleccionadas = computed(() => bloquesSeleccionados.value
  .map((bloque) => `${bloque.hora_inicio} - ${bloque.hora_fin}`)
  .join(', '));

function estaSeleccionado(canchaId: number, horaInicio: string): boolean {
  return bloquesSeleccionados.value.some((bloque) => bloque.cancha_id === canchaId && bloque.hora_inicio === horaInicio);
}

function alternarBloque(cancha: { cancha_id: number; nombre: string }, bloque: { hora_inicio: string; hora_fin: string; estado: string }) {
  if (bloque.estado !== 'disponible') return;
  if (canchaSeleccionadaId.value !== null && canchaSeleccionadaId.value !== cancha.cancha_id) {
    bloquesSeleccionados.value = [];
  }
  canchaSeleccionadaId.value = cancha.cancha_id;

  if (estaSeleccionado(cancha.cancha_id, bloque.hora_inicio)) {
    bloquesSeleccionados.value = bloquesSeleccionados.value.filter((item) => item.hora_inicio !== bloque.hora_inicio);
  } else {
    bloquesSeleccionados.value.push({ cancha_id: cancha.cancha_id, nombre: cancha.nombre, ...bloque });
  }

  if (bloquesSeleccionados.value.length === 0) canchaSeleccionadaId.value = null;
}

function cerrarReserva() {
  modalSolicitudAbierto.value = false;
  errorSolicitud.value = '';
}

function limpiarSeleccion() {
  bloquesSeleccionados.value = [];
  canchaSeleccionadaId.value = null;
  modalSolicitudAbierto.value = false;
}

async function enviarSolicitud() {
  if (!complejo.value || canchaSeleccionadaId.value === null || bloquesSeleccionados.value.length === 0) return;

  errorSolicitud.value = '';
  enviandoSolicitud.value = true;
  const ventanaWhatsApp = window.open('about:blank', '_blank');

  try {
    const { data } = await complejosService.crearSolicitud(slug, {
      cancha_id: canchaSeleccionadaId.value,
      nombre_cliente: solicitud.value.nombre,
      cedula_cliente: solicitud.value.cedula,
      telefono_cliente: solicitud.value.telefono,
      fecha: fechaSeleccionada.value,
      horas: bloquesSeleccionados.value.map((bloque) => bloque.hora_inicio),
      observaciones: solicitud.value.comentario || undefined,
      website: solicitud.value.website,
      captcha_token: captchaToken.value || undefined,
    });

    if (ventanaWhatsApp) {
      ventanaWhatsApp.location.href = data.data.whatsapp_url;
      ventanaWhatsApp.opener = null;
    } else {
      window.location.assign(data.data.whatsapp_url);
    }

    modalSolicitudAbierto.value = false;
    bloquesSeleccionados.value = [];
    canchaSeleccionadaId.value = null;
    solicitud.value = { nombre: '', cedula: '', telefono: '', comentario: '', website: '' };
    restablecerCaptcha();
    await cargarDisponibilidad();
  } catch (error: any) {
    ventanaWhatsApp?.close();
    errorSolicitud.value = error.response?.data?.message || 'No se pudo registrar la solicitud. Revisa las horas e inténtalo de nuevo.';
    restablecerCaptcha();
  } finally {
    enviandoSolicitud.value = false;
  }
}

function restablecerCaptcha() {
  captchaToken.value = '';
  if (turnstileWidgetId) (window as Window & { turnstile?: TurnstileApi }).turnstile?.reset(turnstileWidgetId);
}

function cargarTurnstile() {
  if (!turnstileSiteKey || !turnstileContainer.value) return;

  const render = () => {
    const turnstile = (window as Window & { turnstile?: TurnstileApi }).turnstile;
    if (!turnstile || !turnstileContainer.value) return;

    turnstileWidgetId = turnstile.render(turnstileContainer.value, {
      sitekey: turnstileSiteKey,
      action: 'reserva',
      callback: (token) => { captchaToken.value = token; },
      'expired-callback': () => { captchaToken.value = ''; },
    });
  };

  const existente = document.getElementById('sportra-turnstile-script') as HTMLScriptElement | null;
  if ((window as Window & { turnstile?: TurnstileApi }).turnstile) {
    render();
  } else if (!existente) {
    const script = document.createElement('script');
    script.id = 'sportra-turnstile-script';
    script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
    script.async = true;
    script.defer = true;
    script.onload = render;
    script.onerror = () => { errorSolicitud.value = 'No se pudo cargar la verificación anti-spam.'; };
    document.head.append(script);
  } else {
    existente.addEventListener('load', render, { once: true });
  }
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

function irAHome() {
  router.push('/home');
}

let canalSuscrito: any = null;

onMounted(async () => {
  await cargarComplejo();
  await cargarDisponibilidad();
  cargarTurnstile();

  if (complejo.value) {
    canalSuscrito = echo.channel(`complejo.${complejo.value.id}.disponibilidad`);
    canalSuscrito.listen('.disponibilidad.actualizada', () => {
      cargarDisponibilidad();
    });
  }
});

onUnmounted(() => {
  if (turnstileWidgetId) (window as Window & { turnstile?: TurnstileApi }).turnstile?.remove(turnstileWidgetId);
  if (complejo.value) {
    echo.leaveChannel(`complejo.${complejo.value.id}.disponibilidad`);
  }
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700;800&family=Inter:wght@400;500;600;700;800&display=swap');

/* BASE LAYOUT */
ion-content.sportra-main-viewport {
  --background: #030712;
  font-family: 'Inter', -apple-system, sans-serif;
  color: #f3f4f6;
}

/* NAVBAR NEON GLASS */
.navbar-container {
  position: fixed;
  top: 1.25rem;
  left: 0;
  right: 0;
  z-index: 1000;
  padding: 0 1.5rem;
}

.navbar-bar {
  max-width: 1280px;
  margin: 0 auto;
  background: rgba(11, 15, 25, 0.75);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 99px;
  padding: 0.5rem 0.6rem 0.5rem 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8), 0 0 1px rgba(132, 204, 22, 0.2);
}

.brand-box {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  cursor: pointer;
  user-select: none;
}

.brand-badge {
  width: 38px;
  height: 38px;
  background: rgba(132, 204, 22, 0.15);
  border: 1px solid rgba(132, 204, 22, 0.4);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 15px rgba(132, 204, 22, 0.2);
}

.brand-icon {
  font-size: 1.25rem;
  color: #84cc16;
}

.brand-name {
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 800;
  font-size: 1.35rem;
  color: #ffffff;
  letter-spacing: -0.04em;
}

.neon-dot {
  color: #84cc16;
  text-shadow: 0 0 8px rgba(132, 204, 22, 0.8);
}

.btn-portal-glow {
  background: #ffffff;
  color: #030712;
  border: none;
  padding: 0.5rem 1.1rem;
  border-radius: 99px;
  font-weight: 800;
  font-size: 0.825rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.btn-portal-glow:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(255, 255, 255, 0.25);
}

/* BACKGROUND GLOW EFFECTS */
.page-background-glow {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
  filter: blur(90px);
}

.glow-float-1 {
  top: 5%;
  left: 10%;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(132, 204, 22, 0.12) 0%, rgba(3, 7, 18, 0) 70%);
}

.glow-float-2 {
  bottom: 10%;
  right: 10%;
  width: 450px;
  height: 450px;
  background: radial-gradient(circle, rgba(163, 230, 53, 0.08) 0%, rgba(3, 7, 18, 0) 70%);
}

.bg-grid {
  position: absolute;
  inset: 0;
  background-image: linear-gradient(to right, rgba(255,255,255,0.02) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(255,255,255,0.02) 1px, transparent 1px);
  background-size: 48px 48px;
  pointer-events: none;
  mask-image: radial-gradient(circle at center, black 40%, transparent 80%);
}

/* WRAPPER PRINCIPAL */
.detail-wrapper {
  position: relative;
  z-index: 2;
  max-width: 1140px;
  margin: 0 auto;
  padding: 7rem 1.5rem 5rem;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 10rem 0;
  gap: 1.25rem;
  color: #94a3b8;
  font-weight: 600;
}

.lime-spinner {
  color: #84cc16;
  width: 46px;
  height: 46px;
}

/* HERO VISUAL */
.complejo-hero {
  position: relative;
  height: 320px;
  width: 100%;
  border-radius: 28px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8);
}

.hero-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(3, 7, 18, 0.95) 0%, rgba(3, 7, 18, 0.2) 60%, transparent 100%);
}

.hero-badge {
  position: absolute;
  bottom: 1.25rem;
  left: 1.25rem;
  background: rgba(11, 15, 25, 0.75);
  backdrop-filter: blur(12px);
  color: #84cc16;
  padding: 0.4rem 0.9rem;
  border-radius: 99px;
  font-size: 0.825rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  border: 1px solid rgba(132, 204, 22, 0.3);
}

/* CARDS GLASS COMUNES */
.glass-card {
  background: rgba(11, 15, 25, 0.85);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 24px;
  padding: 1.75rem;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8);
}

.main-content-layout {
  display: flex;
  flex-direction: column;
  gap: 2.5rem;
}

/* CARD INFO HEAD */
.info-header-card {
  margin-top: -3.5rem;
  position: relative;
  z-index: 10;
}

.title-row {
  display: flex;
  align-items: center;
  gap: 1.25rem;
}

.logo-wrapper {
  width: 82px;
  height: 82px;
  min-width: 82px;
  border-radius: 22px;
  padding: 3px;
  background: rgba(132, 204, 22, 0.2);
  border: 1px solid rgba(132, 204, 22, 0.4);
  box-shadow: 0 0 20px rgba(132, 204, 22, 0.15);
}

.logo-image {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 18px;
}

.eyebrow {
  display: inline-block;
  font-size: 0.68rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-weight: 800;
  color: #84cc16;
}

.complejo-title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 1.85rem;
  font-weight: 800;
  color: #ffffff;
  margin: 0.1rem 0 0.35rem 0;
  letter-spacing: -0.03em;
}

.complejo-address {
  font-size: 0.875rem;
  color: #94a3b8;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.complejo-address ion-icon {
  color: #84cc16;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
  margin: 1.5rem 0;
}

.metric-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 16px;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.metric-label {
  font-size: 0.68rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #64748b;
  font-weight: 800;
}

.metric-card strong {
  font-size: 0.95rem;
  color: #ffffff;
  font-weight: 800;
}

.metric-card .highlight-val {
  color: #84cc16;
  font-size: 1.1rem;
}

.description-box {
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 1.25rem;
  margin-bottom: 1.5rem;
}

.description-box h3 {
  margin: 0 0 0.4rem;
  font-size: 0.75rem;
  text-transform: uppercase;
  color: #84cc16;
  letter-spacing: 0.08em;
  font-weight: 800;
}

.description-box p {
  margin: 0;
  color: #cbd5e1;
  line-height: 1.6;
  font-size: 0.9rem;
}

/* BOTÓN WHATSAPP */
.btn-whatsapp-full {
  width: 100%;
  background: #25d366;
  color: #030712;
  border: none;
  border-radius: 16px;
  padding: 0.85rem 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.85rem;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 0 10px 30px rgba(37, 211, 102, 0.25);
}

.btn-whatsapp-full:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 35px rgba(37, 211, 102, 0.4);
}

.wa-icon { font-size: 1.85rem; }

.wa-btn-text {
  display: flex;
  flex-direction: column;
  text-align: left;
  flex: 1;
}

.wa-btn-text span { font-weight: 800; font-size: 0.95rem; }
.wa-btn-text small { font-size: 0.75rem; opacity: 0.85; font-weight: 600; }
.open-icon { font-size: 1.2rem; }

/* SECCIÓN DISPONIBILIDAD */
.disponibilidad-section {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.section-title-wrapper h2 {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 1.6rem;
  font-weight: 800;
  color: #ffffff;
  margin: 0.1rem 0 0.25rem 0;
}

.section-sub {
  font-size: 0.85rem;
  color: #94a3b8;
  margin: 0;
}

.date-picker-container {
  padding: 1.25rem;
}

.date-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.85rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.selected-date-label {
  font-size: 0.9rem;
  font-weight: 800;
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}

.selected-date-label ion-icon {
  color: #84cc16;
}

.btn-picker-trigger {
  background: rgba(132, 204, 22, 0.12);
  border: 1px solid rgba(132, 204, 22, 0.3);
  color: #84cc16;
  padding: 0.45rem 0.85rem;
  border-radius: 99px;
  font-size: 0.8rem;
  font-weight: 800;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-picker-trigger:hover {
  background: #84cc16;
  color: #030712;
}

/* CARRUSEL DIAS DINÁMICO */
.date-chips-scroll {
  display: flex;
  gap: 0.75rem;
  overflow-x: auto;
  padding: 0.25rem 0.25rem 0.5rem 0.25rem;
  scrollbar-width: thin;
  scrollbar-color: rgba(132, 204, 22, 0.3) transparent;
  -webkit-overflow-scrolling: touch;
}

.date-chips-scroll::-webkit-scrollbar {
  height: 6px;
}

.date-chips-scroll::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.02);
  border-radius: 10px;
}

.date-chips-scroll::-webkit-scrollbar-thumb {
  background: rgba(132, 204, 22, 0.3);
  border-radius: 10px;
}

.date-chip-btn {
  flex: 0 0 86px;
  height: 76px;
  background: #030712;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.chip-day-name {
  font-size: 0.65rem;
  font-weight: 800;
  color: #64748b;
}

.chip-day-number {
  font-size: 1.25rem;
  font-weight: 800;
  color: #ffffff;
  line-height: 1.1;
}

.chip-month {
  font-size: 0.6rem;
  color: #64748b;
  font-weight: 700;
}

.date-chip-btn:hover {
  border-color: rgba(132, 204, 22, 0.4);
}

.date-chip-btn.is-selected {
  background: #84cc16;
  border-color: #84cc16;
  box-shadow: 0 0 20px rgba(132, 204, 22, 0.4);
}

.date-chip-btn.is-selected .chip-day-name,
.date-chip-btn.is-selected .chip-day-number,
.date-chip-btn.is-selected .chip-month {
  color: #030712;
}

/* LEYENDA */
.legend-bar {
  display: flex;
  gap: 1.25rem;
  flex-wrap: wrap;
  padding: 0.85rem 1.25rem;
  font-size: 0.78rem;
  color: #94a3b8;
  font-weight: 700;
}

.legend-item { display: flex; align-items: center; gap: 0.4rem; }
.dot { width: 8px; height: 8px; border-radius: 50%; }
.dot.disponible { background-color: #84cc16; box-shadow: 0 0 8px #84cc16; }
.dot.reservado { background-color: #ef4444; }
.dot.en-tramite { background-color: #f59e0b; }
.dot.cerrada { background-color: #64748b; }
.dot.en-mantenimiento { background-color: #f97316; }

.dispo-loading-box {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 3rem;
  color: #94a3b8;
  font-weight: 600;
}

/* LISTA DE CANCHAS */
.canchas-list {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.cancha-card {
  padding: 1.5rem;
}

.cancha-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
  padding-bottom: 1rem;
  border-bottom: 1px dashed rgba(255, 255, 255, 0.1);
}

.cancha-info { display: flex; align-items: center; gap: 0.75rem; }

.cancha-icon-box {
  width: 40px;
  height: 40px;
  background: rgba(132, 204, 22, 0.12);
  border: 1px solid rgba(132, 204, 22, 0.3);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #84cc16;
  font-size: 1.15rem;
}

.cancha-info h3 {
  font-size: 1.1rem;
  font-weight: 800;
  color: #ffffff;
  margin: 0;
}

.deporte-subtitle {
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 600;
}

.cancha-price-badge strong { color: #84cc16; font-size: 1.2rem; font-weight: 800; }
.cancha-price-badge small { color: #64748b; font-size: 0.75rem; }

/* HORARIOS GRID */
.horarios-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(95px, 1fr));
  gap: 0.75rem;
}

.slot-btn {
  width: 100%;
  min-height: 70px;
  font: inherit;
  border-radius: 14px;
  padding: 0.6rem 0.3rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border: 1px solid transparent;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.time-text { font-size: 0.9rem; font-weight: 800; }
.status-indicator { font-size: 0.65rem; font-weight: 800; text-transform: uppercase; margin-top: 3px; letter-spacing: 0.04em; }

.slot-disponible { 
  background: rgba(132, 204, 22, 0.08); 
  border-color: rgba(132, 204, 22, 0.3); 
  color: #a3e635; 
}

.slot-disponible.is-interactive:hover {
  background: #84cc16;
  color: #030712;
  cursor: pointer;
  transform: translateY(-2px);
  box-shadow: 0 0 20px rgba(132, 204, 22, 0.4);
}

.slot-reservado { background: rgba(239, 68, 68, 0.08); border-color: rgba(239, 68, 68, 0.2); color: #f87171; opacity: 0.6; text-decoration: line-through; }
.slot-en_tramite { background: rgba(245, 158, 11, 0.08); border-color: rgba(245, 158, 11, 0.2); color: #fbbf24; }
.slot-cerrada { background: rgba(255, 255, 255, 0.03); border-color: rgba(255, 255, 255, 0.08); color: #64748b; opacity: 0.5; }
.slot-en_mantenimiento { background: rgba(249, 115, 22, 0.08); border-color: rgba(249, 115, 22, 0.25); color: #fb923c; }
.slot-btn.is-selected { background: rgba(132, 204, 22, 0.18); border-color: #84cc16; color: #d9f99d; box-shadow: 0 0 16px rgba(132, 204, 22, 0.24); }

/* EMPTY STATES */
.empty-dispo-box, .empty-state-full {
  text-align: center;
  padding: 3.5rem 1.5rem;
}

.empty-icon-glow {
  width: 60px;
  height: 60px;
  background: rgba(132, 204, 22, 0.12);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.25rem;
  color: #84cc16;
  font-size: 1.85rem;
}

.empty-dispo-box h4, .empty-state-full h3 {
  color: #ffffff;
  font-family: 'Space Grotesk', sans-serif;
  font-size: 1.25rem;
  margin: 0 0 0.5rem;
}

.empty-dispo-box p, .empty-state-full p {
  color: #94a3b8;
  font-size: 0.875rem;
  margin: 0 0 1.5rem;
}

/* GALERIA */
.gallery-section {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 1rem;
}

.gallery-item-glass {
  border-radius: 20px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.08);
  aspect-ratio: 1.25 / 1;
  background: rgba(255, 255, 255, 0.02);
}

.gallery-item-glass img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}

.gallery-item-glass:hover img {
  transform: scale(1.08);
}

/* MODAL DE RESERVA NEÓN GLASS */
.glass-modal-container {
  --background: transparent;
  --backdrop-opacity: 0.8;
}

.reservation-modal-glass {
  max-width: 520px;
  margin: auto;
  background: rgba(11, 15, 25, 0.95);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid rgba(132, 204, 22, 0.3);
  border-radius: 28px;
  overflow: hidden;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.9), 0 0 30px rgba(132, 204, 22, 0.15);
  color: #ffffff;
}

.reservation-modal-header {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  padding: 1.75rem 1.75rem 1.25rem;
  background: rgba(132, 204, 22, 0.08);
  border-bottom: 1px solid rgba(132, 204, 22, 0.2);
}

.reservation-modal-header h2 { 
  margin: 0.2rem 0 0; 
  font-family: 'Space Grotesk', sans-serif; 
  font-size: 1.45rem; 
  color: #ffffff; 
}

.reservation-modal-header p { 
  margin: 0.35rem 0 0; 
  color: #a3e635; 
  font-size: 0.875rem; 
  font-weight: 600; 
}

.modal-close-btn {
  align-self: flex-start;
  border: none;
  background: rgba(255, 255, 255, 0.05);
  color: #ffffff;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 1.4rem;
  transition: background 0.2s ease;
}

.modal-close-btn:hover {
  background: rgba(255, 255, 255, 0.15);
}

.reservation-form { 
  padding: 1.75rem; 
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}

.field-block label { 
  font-size: 0.68rem; 
  font-weight: 800; 
  color: #84cc16; 
  display: block; 
  margin-bottom: 0.4rem; 
  text-transform: uppercase; 
  letter-spacing: 0.04em; 
}

.field-block label span { color: #ef4444; }
.field-block label small { color: #64748b; text-transform: none; }

.input-glow-box input,
.input-glow-box textarea {
  width: 100%;
  background: #030712;
  border: 1px solid rgba(132, 204, 22, 0.25);
  border-radius: 14px;
  padding: 0.75rem 1rem;
  font-size: 0.875rem;
  color: #ffffff;
  outline: none;
  font-family: inherit;
  transition: all 0.25s ease;
}

.input-glow-box input:focus,
.input-glow-box textarea:focus {
  border-color: #84cc16;
  box-shadow: 0 0 15px rgba(132, 204, 22, 0.25);
}

.reservation-note {
  display: flex;
  gap: 0.6rem;
  padding: 0.85rem 1rem;
  border: 1px solid rgba(132, 204, 22, 0.3);
  border-radius: 14px;
  background: rgba(132, 204, 22, 0.05);
  color: #cbd5e1;
  font-size: 0.8rem;
  align-items: center;
}

.reservation-note ion-icon { flex: 0 0 auto; font-size: 1.2rem; color: #84cc16; }
.reservation-note p { margin: 0; line-height: 1.45; }
.error-note { color: #fecaca; border-color: rgba(248, 113, 113, 0.35); }

.modal-actions { 
  display: grid; 
  grid-template-columns: 1fr 1.35fr; 
  gap: 0.85rem; 
  margin-top: 0.5rem; 
}

.btn-modal-cancel {
  background: transparent;
  color: #94a3b8;
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 0.8rem 1rem;
  border-radius: 14px;
  font-weight: 800;
  cursor: pointer;
}

.btn-modal-cancel:hover {
  color: #ffffff;
  border-color: rgba(255, 255, 255, 0.25);
}

.btn-modal-submit {
  background: #25d366;
  color: #030712;
  border: none;
  padding: 0.8rem 1rem;
  border-radius: 14px;
  font-weight: 800;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  cursor: pointer;
  box-shadow: 0 0 20px rgba(37, 211, 102, 0.3);
}

.btn-modal-submit:disabled {
  opacity: 0.4;
  cursor: not-allowed;
  box-shadow: none;
}

/* FIX ION-DATETIME DARK NEON */
.dark-datetime-modal {
  --width: auto;
  --height: auto;
  --background: transparent;
  --backdrop-opacity: 0.75;
}

.date-modal-wrapper {
  margin: auto;
  border-radius: 24px;
  overflow: hidden;
  border: 1px solid rgba(132, 204, 22, 0.3);
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.9), 0 0 25px rgba(132, 204, 22, 0.2);
  background: #0b0f19;
  display: flex;
  justify-content: center;
  align-items: center;
}

.custom-neon-datetime {
  --background: #0b0f19;
  --color: #ffffff;
  --color-accent: #84cc16;
  --color-accent-text: #030712;
  border-radius: 24px;
}

.custom-neon-datetime::part(button) {
  color: #84cc16;
  font-weight: 800;
  text-transform: uppercase;
}

/* RESPONSIVE */
@media (max-width: 768px) {
  .complejo-hero { height: 240px; }
  .metrics-grid { grid-template-columns: 1fr; }
  .gallery-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .title-row { flex-direction: column; align-items: flex-start; }
}

@media (max-width: 480px) {
  .detail-wrapper { padding: 8rem 1rem 3rem; }
  .modal-actions { grid-template-columns: 1fr; }
}
</style>