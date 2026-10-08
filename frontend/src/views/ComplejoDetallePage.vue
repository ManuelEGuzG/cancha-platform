<template>
  <ion-page class="sportra-app">
    <!-- Header Principal Simplificado (Sin buscador, explorar ni propietarios) -->
    <header class="site-header">
      <div class="nav-shell">
        <button class="brand" type="button" @click="irAHome" aria-label="Ir al inicio">
          <span class="brand-copy">
            <strong>SPORTRA<span>.</span></strong>
            <small>RESERVA TU CANCHA</small>
          </span>
        </button>
      </div>
    </header>

    <ion-content :fullscreen="true" class="sportra-main-viewport">
      <!-- Loading State -->
      <div v-if="cargando" class="loading-container">
        <ion-spinner name="crescent" class="custom-spinner"></ion-spinner>
        <span>Cargando detalles del complejo...</span>
      </div>

      <!-- Contenido Principal cuando existe el Complejo -->
      <div v-else-if="complejo" class="detail-page-wrapper">
        
        <!-- HERO SECTION OSCURA -->
        <section class="complejo-hero-wrapper">
          <div class="hero-inner-container">
            
            <!-- Breadcrumb Navigation -->
            <div class="hero-top-nav">
              <button class="back-pill-btn" type="button" @click="irAHome">
                <ion-icon :icon="arrowBackOutline"></ion-icon>
                <span>Volver</span>
              </button>
              <span class="breadcrumb-text">Complejos / {{ complejo.nombre }}</span>
            </div>

            <!-- Card Banner Principal con Imagen de Fondo -->
            <div class="hero-banner-card">
              <img 
                :src="getComplejoImage(complejo)" 
                :alt="complejo.nombre"
                class="hero-img-bg"
                @error="handleImageError"
              />
              <div class="hero-gradient-overlay"></div>

              <!-- Badge Superior Derecha -->
              <div class="hero-top-badge">
                Complejo verificado
              </div>

              <!-- Contenido Superpuesto en el Banner -->
              <div class="hero-content-bottom">
                <div class="location-chip">
                  <ion-icon :icon="locationOutline"></ion-icon>
                  <span>{{ complejo.distrito }}, {{ complejo.canton }}</span>
                </div>

                <h1 class="complejo-title">{{ complejo.nombre }}</h1>
                <p class="complejo-description">
                  {{ complejo.descripcion || 'Instalaciones deportivas acondicionadas para la práctica de día y noche.' }}
                </p>
              </div>

              <!-- Botón Contacto Esquina Inferior Derecha -->
              <button class="view-photo-btn" type="button" @click="contactarWhatsApp">
                <ion-icon :icon="logoWhatsapp"></ion-icon>
                <span>Contacto directo</span>
              </button>
            </div>

          </div>
        </section>

        <!-- SECCIÓN INFERIOR CLARA (2 COLUMNAS) -->
        <section class="main-details-section">
          <div class="section-container">
            <div class="two-columns-layout">
              
              <!-- COLUMNA IZQUIERDA: INFORMACIÓN Y CANCHAS -->
              <div class="left-info-column">
                
                <!-- Card 1: Todo listo para jugar -->
                <article class="detail-white-card">
                  <h2>Todo listo para jugar</h2>
                  <p class="card-intro-p">
                    Espacios equipados con iluminación, vestuarios y comodidades para tu partido.
                  </p>

                  <div class="amenities-grid">
                    <div class="amenity-item">
                      <ion-icon :icon="optionsOutline"></ion-icon>
                      <span>Iluminación LED</span>
                    </div>
                    <div class="amenity-item">
                      <ion-icon :icon="footballOutline"></ion-icon>
                      <span>Vestuarios</span>
                    </div>
                    <div class="amenity-item">
                      <ion-icon :icon="optionsOutline"></ion-icon>
                      <span>Estacionamiento</span>
                    </div>
                    <div class="amenity-item">
                      <ion-icon :icon="refreshOutline"></ion-icon>
                      <span>Bebederos / Zona social</span>
                    </div>
                  </div>

                  <div class="info-footer-grid">
                    <div class="info-block">
                      <small>UBICACIÓN</small>
                      <strong v-if="enlaceGoogleMaps">
                        <a :href="enlaceGoogleMaps" target="_blank" rel="noopener noreferrer">
                          {{ complejo.distrito }}, {{ complejo.canton }}
                        </a>
                      </strong>
                      <strong v-else>{{ complejo.distrito }}, {{ complejo.canton }}</strong>
                    </div>

                    <div class="info-block">
                      <small>HORARIO DEL COMPLEJO</small>
                      <strong>Todos los días · 08:00–23:00</strong>
                    </div>
                  </div>
                </article>

                <!-- Card 2: Elige tu cancha -->
                <article class="detail-white-card">
                  <div class="card-header-flex">
                    <h2>Elige tu cancha</h2>
                    <span class="count-badge">{{ disponibilidad?.canchas?.length || 0 }} canchas</span>
                  </div>

                  <div class="courts-selector-list">
                    <div
                      v-for="cancha in disponibilidad?.canchas || []"
                      :key="cancha.cancha_id"
                      class="court-select-item"
                      :class="{ 'is-selected': canchaSeleccionadaId === cancha.cancha_id }"
                      @click="seleccionarCancha(cancha.cancha_id)"
                    >
                      <div class="court-item-left">
                        <div class="court-icon-circle">
                          <ion-icon :icon="footballOutline"></ion-icon>
                        </div>
                        <div class="court-item-details">
                          <h3>{{ cancha.nombre }} · Fútbol</h3>
                          <p>Exterior · Césped sintético · Iluminación</p>
                        </div>
                      </div>

                      <div class="court-item-right">
                        <div class="court-price-tag">
                          <strong>₡{{ formatearPrecio(cancha.precio_hora) }}</strong>
                          <small>/ hora</small>
                        </div>
                        <div class="radio-check-circle" :class="{ checked: canchaSeleccionadaId === cancha.cancha_id }">
                          <span v-if="canchaSeleccionadaId === cancha.cancha_id">✓</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <p class="disclaimer-note">
                    <ion-icon :icon="informationCircleOutline"></ion-icon>
                    Los horarios a la derecha corresponden a la cancha seleccionada.
                  </p>
                </article>

              </div>

              <!-- COLUMNA DERECHA: WIDGET DE HORARIOS Y RESERVA -->
              <div class="right-booking-column">
                <article class="booking-sticky-card">
                  
                  <div class="booking-card-header">
                    <h2>Encuentra tu horario</h2>
                    <span class="selected-court-name" v-if="canchaActual">
                      {{ canchaActual.nombre }}
                    </span>
                  </div>

                  <!-- Mes y Navegación Días -->
                  <div class="month-selector-bar">
                    <span>{{ mesAnioTexto }}</span>
                    <div class="month-nav-btns">
                      <button type="button" @click="moverSemana(-7)">&lt;</button>
                      <button type="button" @click="moverSemana(7)">&gt;</button>
                    </div>
                  </div>

                  <!-- Carrusel Semanal de Días -->
                  <div class="days-week-grid">
                    <button
                      v-for="dia in proximosDias"
                      :key="dia.iso"
                      type="button"
                      class="day-btn"
                      :class="{ 'is-active': dia.iso === fechaSeleccionada }"
                      @click="seleccionarFecha(dia.iso)"
                    >
                      <small>{{ dia.nombreDia }}</small>
                      <strong>{{ dia.numeroDia }}</strong>
                    </button>
                  </div>

                  <!-- Título de Sección Horarios -->
                  <div class="slots-header">
                    <span>Horarios disponibles</span>
                    <small v-if="bloquesSeleccionados.length">{{ bloquesSeleccionados.length }} seleccionado(s)</small>
                  </div>

                  <!-- Loader Horarios -->
                  <div v-if="cargandoDisponibilidad" class="slots-loading">
                    <ion-spinner name="crescent"></ion-spinner>
                    <span>Cargando disponibilidad...</span>
                  </div>

                  <!-- Grid de Horarios -->
                  <div v-else-if="canchaActual && canchaActual.bloques?.length" class="time-slots-grid">
                    <button
                      v-for="bloque in canchaActual.bloques"
                      :key="bloque.hora_inicio"
                      type="button"
                      class="time-pill"
                      :class="[
                        `status-${bloque.estado}`,
                        { 'is-selected': estaSeleccionado(canchaActual.cancha_id, bloque.hora_inicio) }
                      ]"
                      :disabled="bloque.estado !== 'disponible'"
                      @click="alternarBloque(canchaActual, bloque)"
                    >
                      <span>{{ bloque.hora_inicio }}</span>
                      <small v-if="bloque.estado !== 'disponible'">
                        {{ textoEstadoBloque(bloque.estado) }}
                      </small>
                    </button>
                  </div>

                  <!-- Sin Horarios -->
                  <div v-else class="empty-slots-box">
                    <span>No hay horarios configurados para esta fecha.</span>
                  </div>

                  <!-- Resumen de Pago y Confirmación -->
                  <div class="booking-summary-footer">
                    <div class="summary-time-range" v-if="bloquesSeleccionados.length">
                      <ion-icon :icon="calendarOutline"></ion-icon>
                      <span>{{ fechaFormateadaCorta }} · {{ resumenHorasSeleccionadas }}</span>
                    </div>

                    <div class="summary-price-row">
                      <span>Total por reserva</span>
                      <strong class="total-price">
                        ₡{{ formatearPrecio(totalCalculado) }}
                      </strong>
                    </div>

                    <button
                      type="button"
                      class="btn-checkout-primary"
                      :disabled="!bloquesSeleccionados.length"
                      @click="modalSolicitudAbierto = true"
                    >
                      <span>Continuar con la reserva</span>
                      <ion-icon :icon="arrowForwardOutline"></ion-icon>
                    </button>

                    <small class="terms-micro-text">
                      Revisa los detalles antes de confirmar.
                    </small>
                  </div>

                </article>
              </div>

            </div>
          </div>
        </section>

        <!-- FOOTER -->
        <footer class="site-footer">
          <div class="section-container">
            <div class="footer-bottom">
              <p>Vista ilustrativa: complejo, canchas, precios y horarios de ejemplo.</p>
              <p class="footer-sub">© 2026 SPORTRA. Todos los derechos reservados.</p>
            </div>
          </div>
        </footer>

      </div>

      <!-- Estado No Encontrado -->
      <div v-else class="not-found-container">
        <div class="empty-icon-circle">
          <ion-icon :icon="alertCircleOutline"></ion-icon>
        </div>
        <h2>No encontramos ese complejo</h2>
        <p>El centro deportivo solicitado no está disponible en este momento.</p>
        <button class="back-pill-btn" type="button" @click="irAHome">
          <span>Volver al inicio</span>
          <ion-icon :icon="arrowForwardOutline"></ion-icon>
        </button>
      </div>
    </ion-content>

    <!-- Modal de Reserva Estilizado -->
    <ion-modal :is-open="modalSolicitudAbierto" @didDismiss="cerrarReserva" class="modal-sportra-styled">
      <div class="modal-card-wrapper">
        <div class="modal-header">
          <div>
            <span class="eyebrow">RESERVA DIRECTA</span>
            <h2>Confirmar horarios</h2>
            <p class="modal-subtitle">{{ fechaFormateadaCorta }} · {{ resumenHorasSeleccionadas }}</p>
          </div>
          <button class="close-btn" type="button" aria-label="Cerrar" @click="cerrarReserva">
            <ion-icon :icon="closeOutline"></ion-icon>
          </button>
        </div>

        <div class="modal-body-form">
          <div aria-hidden="true" class="honeypot-field">
            <label for="website-check">Dejar vacío</label>
            <input id="website-check" v-model="solicitud.website" type="text" tabindex="-1" autocomplete="off" />
          </div>

          <div class="form-field">
            <label>Nombre completo <span>*</span></label>
            <input v-model="solicitud.nombre" type="text" placeholder="Ej: Juan Pérez" />
          </div>

          <div class="form-field">
            <label>Cédula o Identificación <span>*</span></label>
            <input v-model="solicitud.cedula" type="text" placeholder="Número de identificación" />
          </div>

          <div class="form-field">
            <label>Teléfono de contacto <span>*</span></label>
            <input v-model="solicitud.telefono" type="tel" placeholder="Ej: 8888-8888" />
          </div>

          <div class="form-field">
            <label>Comentario o solicitud especial <small>(Opcional)</small></label>
            <textarea v-model="solicitud.comentario" rows="3" placeholder="Añade cualquier detalle sobre tu reserva..."></textarea>
          </div>

          <div class="modal-notice-box">
            <ion-icon :icon="informationCircleOutline"></ion-icon>
            <p>Tus horas se mantendrán en trámite durante 15 minutos. Al completar, se abrirá WhatsApp para que confirmes el envío con la administración.</p>
          </div>

          <div v-if="turnstileSiteKey" ref="turnstileContainer" class="turnstile-container"></div>

          <p v-if="errorSolicitud" class="modal-error-text" role="alert">{{ errorSolicitud }}</p>

          <div class="modal-actions-footer">
            <button class="cancel-btn" type="button" @click="cerrarReserva">Cancelar</button>
            <button 
              class="submit-btn" 
              type="button"
              :disabled="enviandoSolicitud || !solicitud.nombre || !solicitud.cedula || !solicitud.telefono || Boolean(turnstileSiteKey && !captchaToken)"
              @click="enviarSolicitud"
            >
              <ion-icon :icon="logoWhatsapp"></ion-icon>
              <span>{{ enviandoSolicitud ? 'Procesando...' : 'Solicitar horas por WhatsApp' }}</span>
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
  IonPage, IonContent, IonIcon, IonModal, IonSpinner
} from '@ionic/vue';
import { addIcons } from 'ionicons';
import { 
  footballOutline, 
  arrowBackOutline, 
  arrowForwardOutline,
  locationOutline, 
  logoWhatsapp, 
  calendarOutline, 
  alertCircleOutline, 
  closeOutline, 
  informationCircleOutline,
  optionsOutline,
  refreshOutline
} from 'ionicons/icons';

import complejosService from '@/services/complejos.service';
import echo from '@/services/echo';
import type { ComplejoDetalle, Disponibilidad } from '@/types';

addIcons({
  'football-outline': footballOutline,
  'arrow-back-outline': arrowBackOutline,
  'arrow-forward-outline': arrowForwardOutline,
  'location-outline': locationOutline,
  'logo-whatsapp': logoWhatsapp,
  'calendar-outline': calendarOutline,
  'alert-circle-outline': alertCircleOutline,
  'close-outline': closeOutline,
  'information-circle-outline': informationCircleOutline,
  'options-outline': optionsOutline,
  'refresh-outline': refreshOutline
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

const DIAS_A_MOSTRAR = 7;

const proximosDias = computed<DiaItem[]>(() => {
  const lista: DiaItem[] = [];
  const base = new Date();

  for (let i = 0; i < DIAS_A_MOSTRAR; i++) {
    const d = new Date(base.getFullYear(), base.getMonth(), base.getDate() + i);
    lista.push(formatearObjetoDia(d));
  }
  return lista;
});

function formatearObjetoDia(d: Date): DiaItem {
  const year = d.getFullYear();
  const monthStr = String(d.getMonth() + 1).padStart(2, '0');
  const dayStr = String(d.getDate()).padStart(2, '0');
  const iso = `${year}-${monthStr}-${dayStr}`;

  const nombreDia = d.toLocaleDateString('es-CR', { weekday: 'short' }).replace('.', '').toUpperCase();

  return {
    iso,
    nombreDia,
    numeroDia: d.getDate(),
    mes: d.toLocaleDateString('es-CR', { month: 'short' }).replace('.', '').toUpperCase(),
  };
}

const mesAnioTexto = computed(() => {
  if (!fechaSeleccionada.value) return 'Octubre de 2026';
  const [year, month, day] = fechaSeleccionada.value.split('-').map(Number);
  const dateObj = new Date(year, month - 1, day);
  const mesStr = dateObj.toLocaleDateString('es-CR', { month: 'long' });
  return `${mesStr.charAt(0).toUpperCase() + mesStr.slice(1)} de ${year}`;
});

const fechaFormateadaCorta = computed(() => {
  if (!fechaSeleccionada.value) return '';
  const [year, month, day] = fechaSeleccionada.value.split('-').map(Number);
  const dateObj = new Date(year, month - 1, day);
  
  const texto = dateObj.toLocaleDateString('es-CR', {
    weekday: 'short',
    day: 'numeric',
    month: 'short'
  });

  return texto;
});

const enlaceGoogleMaps = computed(() => {
  if (!complejo.value?.latitud || !complejo.value?.longitud) return '';
  return `https://www.google.com/maps/search/?api=1&query=${complejo.value.latitud},${complejo.value.longitud}`;
});

const canchaActual = computed(() => {
  if (!disponibilidad.value?.canchas || disponibilidad.value.canchas.length === 0) return null;
  if (!canchaSeleccionadaId.value) return disponibilidad.value.canchas[0];
  return disponibilidad.value.canchas.find(c => c.cancha_id === canchaSeleccionadaId.value) || disponibilidad.value.canchas[0];
});

const totalCalculado = computed(() => {
  if (!canchaActual.value || !bloquesSeleccionados.value.length) return 0;
  return canchaActual.value.precio_hora * bloquesSeleccionados.value.length;
});

function textoEstadoBloque(estado: string): string {
  if (estado === 'reservado' || estado === 'ocupado') return 'Ocupado';
  if (estado === 'en_tramite') return 'En trámite';
  return 'No disponible';
}

function seleccionarCancha(id: number) {
  canchaSeleccionadaId.value = id;
  bloquesSeleccionados.value = [];
}

function seleccionarFecha(iso: string) {
  if (fechaSeleccionada.value !== iso) {
    limpiarSeleccion();
    fechaSeleccionada.value = iso;
    cargarDisponibilidad();
  }
}

function moverSemana(dias: number) {
  const [year, month, day] = fechaSeleccionada.value.split('-').map(Number);
  const nuevaFecha = new Date(year, month - 1, day + dias);
  const yearStr = nuevaFecha.getFullYear();
  const monthStr = String(nuevaFecha.getMonth() + 1).padStart(2, '0');
  const dayStr = String(nuevaFecha.getDate()).padStart(2, '0');
  seleccionarFecha(`${yearStr}-${monthStr}-${dayStr}`);
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
    if (disponibilidad.value?.canchas?.length && !canchaSeleccionadaId.value) {
      canchaSeleccionadaId.value = disponibilidad.value.canchas[0].cancha_id;
    }
  } catch (error) {
    console.error('Error cargando disponibilidad:', error);
  } finally {
    cargandoDisponibilidad.value = false;
  }
}

const resumenHorasSeleccionadas = computed(() => bloquesSeleccionados.value
  .map((bloque) => `${bloque.hora_inicio}`)
  .join(', '));

function estaSeleccionado(canchaId: number, horaInicio: string): boolean {
  return bloquesSeleccionados.value.some((bloque) => bloque.cancha_id === canchaId && bloque.hora_inicio === horaInicio);
}

function alternarBloque(cancha: { cancha_id: number; nombre: string }, bloque: { hora_inicio: string; hora_fin: string; estado: string }) {
  if (bloque.estado !== 'disponible') return;
  canchaSeleccionadaId.value = cancha.cancha_id;

  if (estaSeleccionado(cancha.cancha_id, bloque.hora_inicio)) {
    bloquesSeleccionados.value = bloquesSeleccionados.value.filter((item) => item.hora_inicio !== bloque.hora_inicio);
  } else {
    bloquesSeleccionados.value.push({ cancha_id: cancha.cancha_id, nombre: cancha.nombre, ...bloque });
  }
}

function cerrarReserva() {
  modalSolicitudAbierto.value = false;
  errorSolicitud.value = '';
}

function limpiarSeleccion() {
  bloquesSeleccionados.value = [];
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
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

/* =========================================================
   VARIABLES Y BASE
   ========================================================= */

.sportra-app {
  --bg-dark: #060e2d;
  --bg-card: #0c1743;
  --blue-accent: #7b96ff;
  --blue-hover: #6281f7;
  --text-white: #ffffff;
  --text-muted: #8e9cc0;
  --text-dark: #091133;
  --border-dark: rgba(255, 255, 255, 0.08);
  --bg-light: #f4f6fc;

  background: var(--bg-dark);
  color: var(--text-white);
  font-family: 'Plus Jakarta Sans', sans-serif;
}

ion-content.sportra-main-viewport {
  --background: var(--bg-dark);
  --color: var(--text-white);
  font-family: 'Plus Jakarta Sans', sans-serif;
}

button, input, select, textarea {
  font-family: 'Plus Jakarta Sans', sans-serif;
}

.section-container {
  max-width: 1240px;
  margin: 0 auto;
  width: 100%;
}

/* =========================================================
   HEADER PRINCIPAL
   ========================================================= */

.site-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  padding: 16px 48px;
  background: rgba(6, 14, 45, 0.92);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border-bottom: 1px solid var(--border-dark);
}

.nav-shell {
  max-width: 1240px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: flex-start;
}

.brand {
  border: 0;
  background: transparent;
  color: #fff;
  cursor: pointer;
  text-align: left;
  padding: 0;
}

.brand-copy strong {
  font-size: 20px;
  font-weight: 800;
  letter-spacing: -0.03em;
}

.brand-copy strong span {
  color: var(--blue-accent);
}

.brand-copy small {
  display: block;
  font-size: 8px;
  color: var(--text-muted);
  letter-spacing: 0.12em;
  margin-top: 1px;
  font-weight: 700;
}

/* =========================================================
   HERO BANNER COMPLEJO (ZONA OSCURA)
   ========================================================= */

.complejo-hero-wrapper {
  padding-top: 85px;
  padding-bottom: 30px;
  background: var(--bg-dark);
}

.hero-inner-container {
  max-width: 1240px;
  margin: 0 auto;
  padding: 0 24px;
}

.hero-top-nav {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 16px;
}

.back-pill-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid var(--border-dark);
  border-radius: 8px;
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
}

.breadcrumb-text {
  font-size: 12px;
  color: var(--text-muted);
}

.hero-banner-card {
  position: relative;
  width: 100%;
  height: clamp(300px, 35vh, 380px);
  border-radius: 20px;
  overflow: hidden;
  border: 1px solid var(--border-dark);
}

.hero-img-bg {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.hero-gradient-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(6, 14, 45, 0.95) 15%, rgba(6, 14, 45, 0.25) 60%, transparent 100%);
}

.hero-top-badge {
  position: absolute;
  top: 16px;
  right: 16px;
  background: rgba(6, 14, 45, 0.75);
  backdrop-filter: blur(8px);
  padding: 6px 14px;
  border-radius: 99px;
  font-size: 11px;
  font-weight: 600;
  color: var(--text-muted);
  border: 1px solid var(--border-dark);
}

.hero-content-bottom {
  position: absolute;
  bottom: 24px;
  left: 24px;
  max-width: 600px;
}

.location-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 12px;
  background: rgba(123, 150, 255, 0.2);
  border-radius: 99px;
  font-size: 11px;
  font-weight: 700;
  color: var(--blue-accent);
  margin-bottom: 10px;
}

.complejo-title {
  font-size: 38px;
  font-weight: 800;
  margin: 0 0 8px;
  color: #fff;
  letter-spacing: -0.03em;
}

.complejo-description {
  font-size: 14px;
  color: var(--text-muted);
  margin: 0;
  line-height: 1.5;
}

.view-photo-btn {
  position: absolute;
  bottom: 24px;
  right: 24px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: rgba(6, 14, 45, 0.85);
  backdrop-filter: blur(10px);
  border: 1px solid var(--border-dark);
  border-radius: 10px;
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
}

/* =========================================================
   SECCIÓN PRINCIPAL CLARA (LAYOUT 2 COLUMNAS)
   ========================================================= */

.main-details-section {
  background: var(--bg-light);
  color: var(--text-dark);
  padding: 40px 24px 80px;
}

.two-columns-layout {
  display: grid;
  grid-template-columns: 1.25fr 0.85fr;
  gap: 28px;
  align-items: start;
}

/* CARDS IZQUIERDAS (BLANCAS) */

.detail-white-card {
  background: #ffffff;
  border-radius: 20px;
  padding: 28px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
  margin-bottom: 24px;
}

.detail-white-card h2 {
  font-size: 20px;
  font-weight: 800;
  margin: 0 0 8px;
  color: var(--text-dark);
}

.card-intro-p {
  font-size: 13px;
  color: #64748b;
  margin: 0 0 20px;
}

.amenities-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  margin-bottom: 24px;
  padding-bottom: 20px;
  border-bottom: 1px solid #f1f5f9;
}

.amenity-item {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
  font-weight: 600;
  color: #334155;
}

.amenity-item ion-icon {
  color: #64748b;
  font-size: 18px;
}

.info-footer-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.info-block small {
  display: block;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.08em;
  color: #94a3b8;
  margin-bottom: 4px;
}

.info-block strong {
  font-size: 13px;
  color: var(--text-dark);
}

.info-block strong a {
  color: var(--blue-accent);
  text-decoration: none;
}

.card-header-flex {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.count-badge {
  font-size: 12px;
  color: #94a3b8;
  font-weight: 600;
}

.courts-selector-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 16px;
}

.court-select-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  cursor: pointer;
  transition: 0.2s ease;
}

.court-select-item.is-selected {
  background: #eef2ff;
  border-color: var(--blue-accent);
}

.court-item-left {
  display: flex;
  align-items: center;
  gap: 14px;
}

.court-icon-circle {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: #e2e8f0;
  display: grid;
  place-items: center;
  color: #64748b;
}

.court-select-item.is-selected .court-icon-circle {
  background: var(--blue-accent);
  color: #060e2d;
}

.court-item-details h3 {
  margin: 0;
  font-size: 15px;
  font-weight: 800;
  color: var(--text-dark);
}

.court-item-details p {
  margin: 2px 0 0;
  font-size: 11px;
  color: #64748b;
}

.court-item-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.court-price-tag strong {
  font-size: 16px;
  font-weight: 800;
  color: var(--text-dark);
}

.court-price-tag small {
  font-size: 11px;
  color: #64748b;
}

.radio-check-circle {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  border: 2px solid #cbd5e1;
  display: grid;
  place-items: center;
  font-size: 12px;
  color: #fff;
}

.radio-check-circle.checked {
  background: var(--blue-accent);
  border-color: var(--blue-accent);
}

.disclaimer-note {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  color: #94a3b8;
  margin: 0;
}

/* COLUMNA DERECHA: WIDGET DE RESERVAS */

.booking-sticky-card {
  background: #ffffff;
  border-radius: 20px;
  padding: 24px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
  position: sticky;
  top: 90px;
}

.booking-card-header {
  margin-bottom: 16px;
}

.booking-card-header h2 {
  font-size: 20px;
  font-weight: 800;
  margin: 0;
  color: var(--text-dark);
}

.selected-court-name {
  font-size: 12px;
  color: #64748b;
}

.month-selector-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  font-size: 13px;
  font-weight: 700;
  color: #334155;
}

.month-nav-btns button {
  background: transparent;
  border: 0;
  font-weight: 800;
  color: #64748b;
  cursor: pointer;
  padding: 0 6px;
}

.days-week-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 6px;
  margin-bottom: 20px;
}

.day-btn {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 8px 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  cursor: pointer;
  transition: 0.2s ease;
}

.day-btn small {
  font-size: 9px;
  color: #64748b;
  font-weight: 700;
}

.day-btn strong {
  font-size: 14px;
  font-weight: 800;
  color: var(--text-dark);
}

.day-btn.is-active {
  background: #0c1743;
  border-color: #0c1743;
}

.day-btn.is-active small,
.day-btn.is-active strong {
  color: #ffffff;
}

.slots-header {
  display: flex;
  justify-content: space-between;
  font-size: 11px;
  color: #94a3b8;
  font-weight: 700;
  margin-bottom: 12px;
}

.slots-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 30px 0;
  color: #64748b;
  font-size: 12px;
}

.time-slots-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
  margin-bottom: 24px;
}

.time-pill {
  height: 48px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 700;
  color: var(--text-dark);
  cursor: pointer;
  transition: 0.2s ease;
}

.time-pill.status-disponible:hover {
  border-color: var(--blue-accent);
  background: #eef2ff;
}

.time-pill.is-selected {
  background: #0c1743 !important;
  color: #ffffff !important;
  border-color: #0c1743 !important;
}

/* OCUPADO / RESERVADO - ROJO */
.time-pill.status-reservado,
.time-pill.status-ocupado {
  background: #fef2f2 !important;
  border-color: #fca5a5 !important;
  color: #dc2626 !important;
  cursor: not-allowed;
  opacity: 0.9;
}

.time-pill.status-reservado small,
.time-pill.status-ocupado small {
  color: #dc2626 !important;
  font-weight: 700;
}

/* EN TRÁMITE - AMARILLO */
.time-pill.status-en_tramite {
  background: #fffbeb !important;
  border-color: #fde68a !important;
  color: #d97706 !important;
  cursor: not-allowed;
  opacity: 0.95;
}

.time-pill.status-en_tramite small {
  color: #d97706 !important;
  font-weight: 700;
}

.time-pill:disabled {
  cursor: not-allowed;
}

.time-pill small {
  font-size: 8px;
  font-weight: 600;
  margin-top: 1px;
}

.empty-slots-box {
  text-align: center;
  padding: 24px;
  background: #f8fafc;
  border-radius: 12px;
  color: #64748b;
  font-size: 12px;
  margin-bottom: 24px;
}

.booking-summary-footer {
  border-top: 1px solid #f1f5f9;
  padding-top: 16px;
}

.summary-time-range {
  font-size: 12px;
  color: var(--blue-accent);
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 12px;
}

.summary-price-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.summary-price-row span {
  font-size: 12px;
  color: #64748b;
}

.total-price {
  font-size: 24px;
  font-weight: 800;
  color: var(--text-dark);
}

.btn-checkout-primary {
  width: 100%;
  padding: 14px;
  background: var(--blue-accent);
  border: 0;
  border-radius: 12px;
  color: #060e2d;
  font-size: 14px;
  font-weight: 800;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: 0.2s ease;
}

.btn-checkout-primary:hover:not(:disabled) {
  background: var(--blue-hover);
}

.btn-checkout-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.terms-micro-text {
  display: block;
  text-align: center;
  font-size: 10px;
  color: #94a3b8;
  margin-top: 10px;
}

/* FOOTER */

.site-footer {
  background: var(--bg-dark);
  padding: 40px 24px;
  color: var(--text-muted);
  font-size: 12px;
  border-top: 1px solid var(--border-dark);
}

.footer-bottom {
  display: flex;
  justify-content: space-between;
}

/* MODAL DE RESERVA */

.modal-sportra-styled {
  --background: transparent;
  --backdrop-opacity: 0.75;
}

.modal-card-wrapper {
  max-width: 500px;
  margin: auto;
  background: #0c1743;
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 24px;
  padding: 28px;
  color: #fff;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
}

.modal-header h2 {
  font-size: 20px;
  font-weight: 800;
  margin: 4px 0;
}

.eyebrow {
  font-size: 9px;
  font-weight: 800;
  letter-spacing: 0.1em;
  color: var(--blue-accent);
}

.modal-subtitle {
  font-size: 12px;
  color: var(--text-muted);
  margin: 0;
}

.close-btn {
  background: rgba(255, 255, 255, 0.08);
  border: 0;
  color: #fff;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  cursor: pointer;
}

.modal-body-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.honeypot-field {
  position: absolute;
  left: -10000px;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-field label {
  font-size: 11px;
  font-weight: 700;
  color: var(--text-muted);
}

.form-field input,
.form-field textarea {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--border-dark);
  border-radius: 10px;
  padding: 10px 14px;
  color: #fff;
  font-size: 13px;
  outline: none;
}

.modal-notice-box {
  display: flex;
  gap: 10px;
  background: rgba(123, 150, 255, 0.08);
  border: 1px solid rgba(123, 150, 255, 0.2);
  border-radius: 12px;
  padding: 12px;
  font-size: 11px;
  color: var(--text-muted);
}

.modal-actions-footer {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: 10px;
}

.cancel-btn {
  background: transparent;
  border: 1px solid var(--border-dark);
  color: var(--text-muted);
  border-radius: 10px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
}

.submit-btn {
  background: #25d366;
  border: 0;
  color: #060e2d;
  padding: 12px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  cursor: pointer;
}

.loading-container, .not-found-container {
  min-height: 80vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
  text-align: center;
  padding: 40px 20px;
}

.custom-spinner {
  color: var(--blue-accent);
  width: 42px;
  height: 42px;
}

.empty-icon-circle {
  width: 60px;
  height: 60px;
  background: rgba(123, 150, 255, 0.1);
  color: var(--blue-accent);
  border-radius: 50%;
  display: grid;
  place-items: center;
  font-size: 28px;
}

/* RESPONSIVE */

@media (max-width: 960px) {
  .site-header { padding: 12px 20px; }
  .two-columns-layout { grid-template-columns: 1fr; }
  .booking-sticky-card { position: static; }
  .footer-bottom { flex-direction: column; gap: 10px; text-align: center; }
}
</style>