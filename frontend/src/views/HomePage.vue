<template>
  <ion-page class="sportra-app">
    <!-- Header / Navegación principal -->
    <header class="site-header">
      <div class="nav-shell">
        <button class="brand" type="button" @click="limpiarFiltros" aria-label="Ir al inicio">
          <span class="brand-copy">
            <strong>SPORTRA<span>.</span></strong>
            <small>RESERVA TU CANCHA</small>
          </span>
        </button>

        <div class="search-bar-header" @click="toggleDropdownSearch">
          <ion-icon :icon="searchOutline"></ion-icon>
          <span>Buscar una cancha · {{ ubicacionTextoSeleccionada }}</span>
        </div>

        <div class="nav-actions">
          <button class="nav-link" type="button" @click="scrollToCanchas">
            Explorar canchas
          </button>

          <button class="owner-button" type="button" @click="irAlPanel">
            <span>Propietarios</span>
            <ion-icon :icon="arrowForwardOutline"></ion-icon>
          </button>
        </div>
      </div>

      <!-- Panel de Búsqueda Desplegable / Filtros -->
      <transition name="search-panel">
        <div v-if="searchOpen" class="search-panel" @click.stop>
          <div class="panel-heading">
            <div>
              <span class="eyebrow">EXPLORA</span>
              <h2>Encuentra tu próxima cancha</h2>
            </div>
            <button class="icon-button" type="button" @click="searchOpen = false" aria-label="Cerrar">
              <ion-icon :icon="closeOutline"></ion-icon>
            </button>
          </div>

          <div class="search-main-field">
            <ion-icon :icon="searchOutline"></ion-icon>
            <input
              v-model="busquedaTexto"
              type="text"
              placeholder="Nombre de la instalación..."
              aria-label="Buscar por nombre"
            />
            <button
              v-if="busquedaTexto"
              class="input-clear"
              type="button"
              @click="busquedaTexto = ''"
              aria-label="Limpiar búsqueda"
            >
              <ion-icon :icon="closeCircle"></ion-icon>
            </button>
          </div>

          <div class="filter-grid">
            <div class="filter-field">
              <label>Provincia</label>
              <ion-select
                v-model="provinciaId"
                interface="popover"
                placeholder="Todas"
                class="modern-select"
                @ionChange="onProvinciaChange"
              >
                <ion-select-option :value="null">Todas</ion-select-option>
                <ion-select-option v-for="p in provincias" :key="p.id" :value="p.id">
                  {{ p.nombre }}
                </ion-select-option>
              </ion-select>
            </div>

            <div class="filter-field">
              <label>Cantón</label>
              <ion-select
                v-model="cantonId"
                interface="popover"
                placeholder="Todos"
                class="modern-select"
                :disabled="!provinciaId && cantones.length === 0"
                @ionChange="onCantonChange"
              >
                <ion-select-option :value="null">Todos</ion-select-option>
                <ion-select-option v-for="c in cantones" :key="c.id" :value="c.id">
                  {{ c.nombre }}
                </ion-select-option>
              </ion-select>
            </div>

            <div class="filter-field">
              <label>Distrito</label>
              <ion-select
                v-model="distritoId"
                interface="popover"
                placeholder="Todos"
                class="modern-select"
                :disabled="!cantonId && distritos.length === 0"
                @ionChange="cargarComplejos"
              >
                <ion-select-option :value="null">Todos</ion-select-option>
                <ion-select-option v-for="d in distritos" :key="d.id" :value="d.id">
                  {{ d.nombre }}
                </ion-select-option>
              </ion-select>
            </div>
          </div>

          <button class="nearby-button" type="button" @click="buscarCercanas">
            <span class="nearby-icon">
              <ion-icon :icon="locationOutline"></ion-icon>
            </span>
            <span class="nearby-text">
              <strong>Usar mi ubicación</strong>
              <small>Ordenar las canchas más cercanas primero</small>
            </span>
            <ion-icon :icon="arrowForwardOutline" class="nearby-arrow"></ion-icon>
          </button>

          <p v-if="errorUbicacion" class="location-error" role="alert">
            {{ errorUbicacion }}
          </p>

          <div class="panel-footer">
            <button class="reset-button" type="button" @click="limpiarFiltros">
              <ion-icon :icon="refreshOutline"></ion-icon>
              <span>Restablecer</span>
            </button>
            <button class="apply-button" type="button" @click="aplicarFiltrosYScroll">
              <span>Ver resultados</span>
              <ion-icon :icon="arrowForwardOutline"></ion-icon>
            </button>
          </div>
        </div>
      </transition>
    </header>

    <ion-content :fullscreen="true" class="sportra-main-viewport" ref="contentRef">
      <!-- Hero Section Principal -->
      <section class="hero-section">
        <div class="hero-container">
          <div class="hero-inner">
            <div class="hero-copy animate-fade-up">
              <div class="status-pill">
                <span class="status-dot"></span>
                MENOS VUELTAS. MÁS PARTIDOS.
              </div>

              <h1>
                Tu próximo<br />
                <span>partido empieza</span><br />
                aquí.
              </h1>

              <p class="hero-description">
                Encuentra tu cancha, elige tu horario y reúne al equipo.
              </p>

              <div class="hero-actions">
                <button class="primary-action" type="button" @click="scrollToCanchas">
                  <span>Explorar canchas</span>
                  <ion-icon :icon="arrowForwardOutline"></ion-icon>
                </button>
                <span class="hero-subtext">Tu deporte. Tu zona. Tu momento.</span>
              </div>

              <!-- Badges de Métricas -->
              <div class="hero-metrics">
                <div class="metric-item">
                  <strong>100+</strong>
                  <small>Canchas disponibles</small>
                </div>
                <div class="metric-divider"></div>
                <div class="metric-item">
                  <strong>Reserva instantánea</strong>
                  <small>Sin intermediarios</small>
                </div>
              </div>
            </div>

            <!-- Card Destacada Hero Banner -->
            <div
              v-if="complejosOrdenados.length > 0"
              class="featured-hero-card animate-fade-up-delay"
              @click="verDetalle(complejosOrdenados[0].slug)"
            >
              <div class="hero-card-badge">
                <span class="reload-icon">↻</span>
                Siempre hay tiempo para jugar
              </div>
              <img
                :src="getComplejoImage(complejosOrdenados[0])"
                :alt="complejosOrdenados[0].nombre"
                @error="handleImageError"
              />
              <div class="hero-card-overlay">
                <span class="hero-card-tag">ENCUENTRA TU LUGAR</span>
                <h3>De la rutina a la cancha.</h3>
              </div>
            </div>

            <!-- Fallback estático -->
            <div
              v-else
              class="featured-hero-card animate-fade-up-delay"
              @click="scrollToCanchas"
            >
              <div class="hero-card-badge">
                <span class="reload-icon">↻</span>
                Siempre hay tiempo para jugar
              </div>
              <img
                src="https://images.unsplash.com/photo-1529900748604-07564a03e7a6?auto=format&fit=crop&w=800&q=80"
                alt="Fútbol Costa Rica"
              />
              <div class="hero-card-overlay">
                <span class="hero-card-tag">ENCUENTRA TU LUGAR</span>
                <h3>De la rutina a la cancha.</h3>
              </div>
            </div>
          </div>

          <!-- Barra Horizontal de Búsqueda del Hero -->
          <div class="hero-search-card">
            <div class="search-fields-row">
              <div class="search-col" @click="toggleDropdownSearch">
                <div class="icon-circle">
                  <ion-icon :icon="locationOutline"></ion-icon>
                </div>
                <div class="col-text">
                  <small>¿DÓNDE?</small>
                  <strong>{{ ubicacionTextoSeleccionada }}</strong>
                </div>
              </div>

              <div class="search-col-divider"></div>

              <div class="search-col" @click="toggleDropdownSearch">
                <div class="icon-circle">
                  <ion-icon :icon="footballOutline"></ion-icon>
                </div>
                <div class="col-text">
                  <small>DEPORTE</small>
                  <strong>Fútbol</strong>
                </div>
              </div>

              <div class="search-col-divider"></div>

              <div class="search-col relative-date-picker">
                <div class="icon-circle">
                  <ion-icon :icon="calendarOutline"></ion-icon>
                </div>
                <div class="col-text">
                  <small>FECHA</small>
                  <strong>{{ fechaSeleccionadaTexto }}</strong>
                </div>
                <input
                  type="date"
                  v-model="fechaFiltro"
                  class="hidden-date-input"
                  aria-label="Seleccionar fecha"
                />
              </div>

              <button class="hero-search-btn" type="button" @click="aplicarFiltrosYScroll">
                <span>Buscar</span>
                <ion-icon :icon="searchOutline"></ion-icon>
              </button>
            </div>

            <div class="hero-chips-row">
              <span class="chips-label">Empieza por:</span>
              <button type="button" @click="buscarCercanas">Cerca de mí</button>
              <button type="button" @click="scrollToCanchas">Canchas techadas</button>
              <button type="button" @click="scrollToCanchas">Jugar de noche</button>
            </div>
          </div>
        </div>
      </section>

      <!-- Sección de Resultados / Lista de Canchas -->
      <main id="seccion-canchas" ref="seccionCanchasRef" class="courts-section">
        <div class="section-container">
          <div class="section-intro">
            <div>
              <span class="section-eyebrow">DESCUBRE TU PRÓXIMA CANCHA</span>
              <h2>El lugar perfecto para tu partido.</h2>
            </div>

            <button class="secondary-btn" type="button" @click="limpiarFiltros">
              Ver todas las canchas
              <ion-icon :icon="arrowForwardOutline"></ion-icon>
            </button>
          </div>

          <div class="results-toolbar">
            <div class="active-filters" v-if="filtrosActivos">
              <span class="filter-status">
                <span></span>
                Filtros activos
              </span>
              <button type="button" @click="limpiarFiltros">
                Limpiar
                <ion-icon :icon="closeOutline"></ion-icon>
              </button>
            </div>

            <div v-if="complejosFiltrados.length > 1" class="sort-control">
              <ion-icon :icon="swapVerticalOutline"></ion-icon>
              <select v-model="orden" aria-label="Ordenar resultados">
                <option value="nombre">Nombre A-Z</option>
                <option value="precio-asc">Precio menor a mayor</option>
                <option value="precio-desc">Precio mayor a menor</option>
                <option v-if="ubicacionUsuario" value="cercania">Más cercanas</option>
              </select>
            </div>
          </div>

          <transition name="fade-mode" mode="out-in">
            <!-- Loading Skeleton -->
            <div v-if="cargando" class="cards-grid" key="loading">
              <div v-for="i in 3" :key="i" class="court-card skeleton-card">
                <div class="skeleton-image shimmer"></div>
                <div class="skeleton-body">
                  <div class="skeleton-line skeleton-title shimmer"></div>
                  <div class="skeleton-line skeleton-location shimmer"></div>
                  <div class="skeleton-divider shimmer"></div>
                  <div class="skeleton-bottom">
                    <div class="skeleton-price shimmer"></div>
                    <div class="skeleton-button shimmer"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Resultados Grid -->
            <div v-else-if="complejosFiltrados.length > 0" class="cards-grid" key="results">
              <article
                v-for="(complejo, index) in complejosOrdenados"
                :key="complejo.id"
                class="court-card"
                :style="{ animationDelay: `${index * 0.05}s` }"
                @click="verDetalle(complejo.slug)"
              >
                <div class="court-image">
                  <span class="sport-badge">Fútbol</span>
                  <button class="fav-badge" type="button" aria-label="Guardar">
                    ♥
                  </button>
                  <img
                    :src="getComplejoImage(complejo)"
                    :alt="complejo.nombre"
                    loading="lazy"
                    @error="handleImageError"
                  />
                </div>

                <div class="court-content">
                  <h3>{{ complejo.nombre }}</h3>
                  <p class="court-location">
                    <ion-icon :icon="locationOutline"></ion-icon>
                    {{ complejo.distrito }}, {{ complejo.canton }}
                  </p>
                  <p class="court-features">
                    Césped sintético · Iluminación LED
                  </p>

                  <div class="court-slots">
                    <span class="slots-title">HORARIOS DISPONIBLES</span>
                    <div class="slots-pills">
                      <span class="slot-pill">18:00</span>
                      <span class="slot-pill">19:00</span>
                      <span class="slot-pill">20:00</span>
                    </div>
                  </div>

                  <div class="court-bottom">
                    <div class="court-price">
                      <strong>₡{{ formatearPrecio(complejo.precio_desde) }}</strong>
                      <span>/ hora</span>
                    </div>

                    <button class="card-link" type="button">
                      Ver cancha
                      <ion-icon :icon="arrowForwardOutline"></ion-icon>
                    </button>
                  </div>
                </div>
              </article>
            </div>

            <!-- Empty State / Sin resultados estilizado con Plus Jakarta Sans -->
            <div v-else class="empty-state" key="empty">
              <div class="empty-icon">
                <ion-icon :icon="searchOutline"></ion-icon>
              </div>
              <span class="empty-eyebrow">SIN RESULTADOS</span>
              <h3 class="empty-title">No encontramos esa cancha.</h3>
              <p v-if="busquedaTexto" class="empty-description">
                No hay instalaciones que coincidan con “<strong>{{ busquedaTexto }}</strong>”.
              </p>
              <p v-else class="empty-description">
                Prueba modificando la ubicación o eliminando alguno de los filtros.
              </p>
              <button class="empty-btn" type="button" @click="limpiarFiltros">
                <ion-icon :icon="refreshOutline"></ion-icon>
                <span>Restablecer filtros</span>
              </button>
            </div>
          </transition>

          <p class="disclaimer-text">
            Selección ilustrativa: nombres, precios y horarios de ejemplo.
          </p>
        </div>
      </main>

      <!-- Sección de Pasos -->
      <section class="steps-section">
        <div class="section-container">
          <span class="section-eyebrow">ALGO SENCILLO</span>
          <h2>Del "¿jugamos?" al "nos vemos ahí".</h2>

          <div class="steps-grid">
            <div class="step-card">
              <div class="step-header">
                <span class="step-icon">
                  <ion-icon :icon="searchOutline"></ion-icon>
                </span>
                <span class="step-number">01</span>
              </div>
              <h3>Encuentra tu cancha</h3>
              <p>Busca por provincia o cantón. Compara espacios y precios.</p>
            </div>

            <div class="step-card">
              <div class="step-header">
                <span class="step-icon">
                  <ion-icon :icon="optionsOutline"></ion-icon>
                </span>
                <span class="step-number">02</span>
              </div>
              <h3>Elige tu momento</h3>
              <p>Consulta la disponibilidad y selecciona tu horario ideal.</p>
            </div>

            <div class="step-card">
              <div class="step-header">
                <span class="step-icon">
                  <ion-icon :icon="footballOutline"></ion-icon>
                </span>
                <span class="step-number">03</span>
              </div>
              <h3>Que empiece el juego</h3>
              <p>Confirma por WhatsApp y llega con el equipo a darlo todo.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Banner Propietarios -->
      <section class="owner-banner-section">
        <div class="section-container">
          <div class="owner-card">
            <div class="owner-info">
              <span class="section-eyebrow">¿TIENES UN CENTRO DEPORTIVO?</span>
              <h2>Haz que cada cancha juegue a tu favor.</h2>
              <p>Acerca tus espacios a nuevos jugadores y organiza tus reservas en un mismo lugar.</p>
              <button class="owner-primary-btn" type="button" @click="irAlPanel">
                <span>Soy propietario</span>
                <ion-icon :icon="arrowForwardOutline"></ion-icon>
              </button>
            </div>

            <div class="owner-preview-box">
              <div class="preview-header">
                <div>
                  <strong>Tu centro, en orden.</strong>
                  <small>Vista de reservas · Hoy</small>
                </div>
                <ion-icon :icon="optionsOutline"></ion-icon>
              </div>
              <div class="preview-rows">
                <div class="preview-row">
                  <div class="row-info">
                    <strong>Cancha Fútbol 5 · Sintético</strong>
                    <small>18:00 - 19:00</small>
                  </div>
                  <span class="status-tag reserved">Reservada</span>
                </div>
                <div class="preview-row">
                  <div class="row-info">
                    <strong>Cancha Fútbol 7 · Techada</strong>
                    <small>19:00 - 20:00</small>
                  </div>
                  <span class="status-tag available">Disponible</span>
                </div>
                <div class="preview-row">
                  <div class="row-info">
                    <strong>Cancha Fútbol 5 · Iluminada</strong>
                    <small>20:00 - 21:00</small>
                  </div>
                  <span class="status-tag reserved">Reservada</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Footer Completo -->
      <footer class="site-footer">
        <div class="section-container">
          <div class="footer-top">
            <div class="footer-brand-col">
              <span class="footer-brand-title">SPORTRA<span>.</span></span>
              <p>Reserva tu cancha. Vive tu deporte.</p>
            </div>

            <div class="footer-links-col">
              <h4>Para jugadores</h4>
              <a @click="scrollToCanchas">Explorar canchas</a>
              <a @click="scrollToCanchas">Deportes</a>
              <a @click="scrollToCanchas">Cómo reservar</a>
            </div>

            <div class="footer-links-col">
              <h4>Para propietarios</h4>
              <a @click="irAlPanel">Publicar mi cancha</a>
              <a @click="irAlPanel">Gestión de reservas</a>
              <a @click="irAlPanel">Contactar</a>
            </div>

            <div class="footer-links-col">
              <h4>Te ayudamos</h4>
              <a href="#">Centro de ayuda</a>
              <a href="#">Condiciones de reserva</a>
              <a href="#">Privacidad</a>
            </div>
          </div>

          <div class="footer-bottom">
            <p>© 2026 SPORTRA. Todos los derechos reservados.</p>
            <p class="footer-sub">Hoy tampoco deberías quedarte sin jugar.</p>
          </div>
        </div>
      </footer>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { IonPage, IonContent, IonSelect, IonSelectOption, IonIcon } from '@ionic/vue';
import { addIcons } from 'ionicons';
import { 
  searchOutline, 
  closeOutline, 
  optionsOutline, 
  refreshOutline, 
  closeCircle, 
  swapVerticalOutline, 
  footballOutline, 
  locationOutline, 
  arrowForwardOutline,
  calendarOutline
} from 'ionicons/icons';

import complejosService from '@/services/complejos.service';
import geografiaService from '@/services/geografia.service';
import type { Complejo } from '@/types';

addIcons({
  'search-outline': searchOutline,
  'close-outline': closeOutline,
  'options-outline': optionsOutline,
  'refresh-outline': refreshOutline,
  'close-circle': closeCircle,
  'swap-vertical-outline': swapVerticalOutline,
  'football-outline': footballOutline,
  'location-outline': locationOutline,
  'arrow-forward-outline': arrowForwardOutline,
  'calendar-outline': calendarOutline
});

const router = useRouter();
const fallbackImage = 'https://images.unsplash.com/photo-1529900748604-07564a03e7a6?auto=format&fit=crop&w=800&q=80';

const contentRef = ref<any>(null);
const seccionCanchasRef = ref<HTMLElement | null>(null);

const provincias = ref<any[]>([]);
const cantones = ref<any[]>([]);
const distritos = ref<any[]>([]);
const provinciaId = ref<number | null>(null);
const cantonId = ref<number | null>(null);
const distritoId = ref<number | null>(null);
const busquedaTexto = ref('');
const fechaFiltro = ref<string>('');
const orden = ref<'nombre' | 'precio-asc' | 'precio-desc' | 'cercania'>('nombre');
const ubicacionUsuario = ref<{ latitud: number; longitud: number } | null>(null);
const errorUbicacion = ref('');
const searchOpen = ref(false);

const complejos = ref<Complejo[]>([]);
const cargando = ref(true);

const filtrosActivos = computed(() => {
  return provinciaId.value !== null || cantonId.value !== null || distritoId.value !== null
    || busquedaTexto.value !== '' || fechaFiltro.value !== '' || ubicacionUsuario.value !== null;
});

const ubicacionTextoSeleccionada = computed(() => {
  if (distritoId.value) {
    const d = distritos.value.find(item => item.id === distritoId.value);
    if (d) return d.nombre;
  }
  if (cantonId.value) {
    const c = cantones.value.find(item => item.id === cantonId.value);
    if (c) return c.nombre;
  }
  if (provinciaId.value) {
    const p = provincias.value.find(item => item.id === provinciaId.value);
    if (p) return p.nombre;
  }
  return 'Costa Rica';
});

const fechaSeleccionadaTexto = computed(() => {
  if (!fechaFiltro.value) return 'Elegir fecha';
  const [year, month, day] = fechaFiltro.value.split('-');
  return `${day}/${month}/${year}`;
});

const complejosFiltrados = computed(() => {
  if (!busquedaTexto.value.trim()) return complejos.value;
  const query = busquedaTexto.value.toLowerCase().trim();
  return complejos.value.filter(c =>
    c.nombre.toLowerCase().includes(query) ||
    (c.provincia && c.provincia.toLowerCase().includes(query)) ||
    (c.canton && c.canton.toLowerCase().includes(query)) ||
    (c.distrito && c.distrito.toLowerCase().includes(query))
  );
});

const complejosOrdenados = computed(() => {
  const lista = [...complejosFiltrados.value];
  if (orden.value === 'precio-asc') {
    return lista.sort((a, b) => a.precio_desde - b.precio_desde);
  }
  if (orden.value === 'precio-desc') {
    return lista.sort((a, b) => b.precio_desde - a.precio_desde);
  }
  if (orden.value === 'cercania' && ubicacionUsuario.value) {
    return lista.sort((a, b) => distanciaKm(a, ubicacionUsuario.value!) - distanciaKm(b, ubicacionUsuario.value!));
  }
  return lista.sort((a, b) => a.nombre.localeCompare(b.nombre));
});

function distanciaKm(complejo: Complejo, origen: { latitud: number; longitud: number }): number {
  if (complejo.latitud === null || complejo.longitud === null) return Number.POSITIVE_INFINITY;
  const radianes = Math.PI / 180;
  const deltaLatitud = (Number(complejo.latitud) - origen.latitud) * radianes;
  const deltaLongitud = (Number(complejo.longitud) - origen.longitud) * radianes;
  const a = Math.sin(deltaLatitud / 2) ** 2
    + Math.cos(origen.latitud * radianes) * Math.cos(Number(complejo.latitud) * radianes)
    * Math.sin(deltaLongitud / 2) ** 2;

  return 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function buscarCercanas() {
  errorUbicacion.value = '';
  if (!navigator.geolocation) {
    errorUbicacion.value = 'Este dispositivo no permite consultar la ubicación.';
    return;
  }

  navigator.geolocation.getCurrentPosition(({ coords }) => {
    ubicacionUsuario.value = { latitud: coords.latitude, longitud: coords.longitude };
    orden.value = 'cercania';
    searchOpen.value = false;
    scrollToCanchas();
  }, () => {
    errorUbicacion.value = 'No se pudo obtener la ubicación. Revisa los permisos del navegador.';
  }, { enableHighAccuracy: false, maximumAge: 300000, timeout: 10000 });
}

function scrollToCanchas() {
  if (contentRef.value && seccionCanchasRef.value) {
    const yOffset = seccionCanchasRef.value.offsetTop - 80;
    contentRef.value.$el.scrollToPoint(0, yOffset, 600);
  } else if (seccionCanchasRef.value) {
    seccionCanchasRef.value.scrollIntoView({ behavior: 'smooth' });
  }
}

function aplicarFiltrosYScroll() {
  searchOpen.value = false;
  scrollToCanchas();
}

function toggleDropdownSearch() {
  searchOpen.value = !searchOpen.value;
}

async function cargarProvincias() {
  try {
    const { data } = await geografiaService.provincias();
    provincias.value = data.data;
    await cargarComplejos();
  } catch (error) {
    console.error('Error cargando provincias:', error);
    cargando.value = false;
  }
}

async function onProvinciaChange() {
  cantones.value = [];
  distritos.value = [];
  cantonId.value = null;
  distritoId.value = null;

  if (provinciaId.value) {
    const { data } = await geografiaService.cantones(provinciaId.value);
    cantones.value = data.data;
  }
  await cargarComplejos();
}

async function onCantonChange() {
  distritos.value = [];
  distritoId.value = null;

  if (cantonId.value) {
    const { data } = await geografiaService.distritos(cantonId.value);
    distritos.value = data.data;
  }
  await cargarComplejos();
}

async function cargarComplejos() {
  cargando.value = true;
  try {
    const { data } = await complejosService.listar({
      provincia_id: provinciaId.value ?? undefined,
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

async function limpiarFiltros() {
  busquedaTexto.value = '';
  fechaFiltro.value = '';
  provinciaId.value = null;
  cantonId.value = null;
  distritoId.value = null;
  ubicacionUsuario.value = null;
  errorUbicacion.value = '';
  orden.value = 'nombre';
  cantones.value = [];
  distritos.value = [];
  await cargarComplejos();
}

function verDetalle(slug: string) {
  router.push(`/complejo/${slug}`);
}

function irAlPanel() {
  router.push('/login');
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

onMounted(async () => {
  await cargarProvincias();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

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

button, input, select {
  font-family: 'Plus Jakarta Sans', sans-serif;
}

.section-container {
  max-width: 1360px;
  margin: 0 auto;
  width: 100%;
}

/* =========================================================
   HEADER
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
  max-width: 1360px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
}

.brand {
  border: 0;
  background: transparent;
  color: #fff;
  cursor: pointer;
  text-align: left;
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

.search-bar-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 18px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid var(--border-dark);
  border-radius: 99px;
  font-size: 13px;
  color: var(--text-muted);
  cursor: pointer;
  min-width: 320px;
  transition: 0.2s ease;
}

.search-bar-header:hover {
  background: rgba(255, 255, 255, 0.08);
  color: #fff;
}

.nav-actions {
  display: flex;
  align-items: center;
  gap: 24px;
}

.nav-link {
  border: 0;
  background: transparent;
  color: var(--text-muted);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}

.nav-link:hover {
  color: #fff;
}

.owner-button {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 18px;
  border: 0;
  border-radius: 99px;
  background: var(--blue-accent);
  color: #060e2d;
  font-size: 13px;
  font-weight: 800;
  cursor: pointer;
  transition: 0.2s ease;
}

.owner-button:hover {
  background: var(--blue-hover);
}

/* =========================================================
   PANEL DESPLEGABLE DE FILTROS ORDENADO Y BLANCO
   ========================================================= */

.search-panel {
  position: absolute;
  top: calc(100% + 12px);
  left: 50%;
  transform: translateX(-50%);
  width: min(620px, calc(100vw - 32px));
  background: #0c1743;
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 20px;
  padding: 24px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
  color: #fff;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.panel-heading {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
}

.eyebrow {
  display: block;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.12em;
  color: #ffffff !important;
  margin-bottom: 2px;
}

.panel-heading h2 {
  margin: 0;
  font-size: 18px;
  font-weight: 800;
  color: #ffffff;
}

.icon-button {
  background: rgba(255, 255, 255, 0.1);
  border: 0;
  color: #fff;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  cursor: pointer;
  transition: 0.2s ease;
}

.icon-button:hover {
  background: rgba(255, 255, 255, 0.2);
}

.search-main-field {
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid var(--border-dark);
  border-radius: 12px;
  padding: 0 14px;
}

.search-main-field ion-icon {
  color: var(--text-muted);
  font-size: 18px;
}

.search-main-field input {
  width: 100%;
  height: 44px;
  background: transparent;
  border: 0;
  outline: 0;
  color: #fff;
  padding-left: 10px;
  font-size: 14px;
}

.input-clear {
  background: transparent;
  border: 0;
  color: var(--text-muted);
  cursor: pointer;
}

.filter-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}

.filter-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.filter-field label {
  font-size: 11px;
  color: var(--text-muted);
  font-weight: 700;
}

.modern-select {
  --background: rgba(255, 255, 255, 0.06);
  --color: #fff;
  --padding-start: 12px;
  border: 1px solid var(--border-dark);
  border-radius: 10px;
  min-height: 42px;
  font-size: 13px;
  width: 100%;
}

.nearby-button {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  background: rgba(123, 150, 255, 0.12);
  border: 1px solid rgba(123, 150, 255, 0.25);
  border-radius: 14px;
  color: #fff;
  cursor: pointer;
  text-align: left;
  transition: 0.2s ease;
}

.nearby-button:hover {
  background: rgba(123, 150, 255, 0.2);
}

.nearby-icon {
  width: 36px;
  height: 36px;
  background: var(--blue-accent);
  border-radius: 10px;
  display: grid;
  place-items: center;
  color: #060e2d;
  font-size: 18px;
  flex-shrink: 0;
}

.nearby-text {
  display: flex;
  flex-direction: column;
}

.nearby-text strong {
  font-size: 13px;
  color: #fff;
}

.nearby-text small {
  font-size: 11px;
  color: var(--text-muted);
}

.nearby-arrow {
  margin-left: auto;
  font-size: 16px;
  color: var(--blue-accent);
}

.location-error {
  color: #ff6b6b;
  font-size: 12px;
  margin: 0;
}

.panel-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 12px;
  border-top: 1px solid var(--border-dark);
}

.reset-button {
  background: transparent;
  border: 0;
  color: var(--text-muted);
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: 0.2s ease;
}

.reset-button:hover {
  color: #fff;
}

.apply-button {
  background: var(--blue-accent);
  border: 0;
  color: #060e2d;
  padding: 12px 24px;
  border-radius: 12px;
  font-weight: 800;
  font-size: 13px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: 0.2s ease;
}

.apply-button:hover {
  background: var(--blue-hover);
}

/* =========================================================
   HERO SECTION
   ========================================================= */

.hero-section {
  min-height: 100vh;
  min-height: 100dvh;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  box-sizing: border-box;
  padding: 115px 48px 36px;
  background: radial-gradient(circle at 82% 25%, rgba(40, 75, 190, 0.28), transparent 45%), var(--bg-dark);
}

.hero-container {
  max-width: 1360px;
  margin: 0 auto;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  flex: 1;
}

.hero-inner {
  display: grid;
  grid-template-columns: 1.15fr 0.85fr;
  gap: 50px;
  align-items: center;
  margin: auto 0;
  padding: 10px 0;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 16px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 99px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.08em;
  color: var(--text-muted);
  margin-bottom: 22px;
}

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--blue-accent);
  box-shadow: 0 0 10px var(--blue-accent);
}

.hero-copy h1 {
  font-size: clamp(52px, 5vw, 76px);
  font-weight: 800;
  line-height: 1.04;
  letter-spacing: -0.04em;
  margin: 0 0 20px;
  color: #ffffff;
}

.hero-copy h1 span {
  color: #8fa5ff;
}

.hero-description {
  font-size: 17px;
  color: var(--text-muted);
  margin: 0 0 32px;
  max-width: 580px;
  line-height: 1.5;
}

.hero-actions {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 32px;
}

.primary-action {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 14px 28px;
  background: var(--blue-accent);
  border: 0;
  border-radius: 12px;
  color: #060e2d;
  font-size: 15px;
  font-weight: 800;
  cursor: pointer;
  transition: 0.2s ease;
  box-shadow: 0 4px 16px rgba(123, 150, 255, 0.25);
}

.primary-action:hover {
  background: var(--blue-hover);
  transform: translateY(-2px);
}

.hero-subtext {
  font-size: 13px;
  color: var(--text-muted);
}

.hero-metrics {
  display: flex;
  align-items: center;
  gap: 24px;
  padding-top: 12px;
}

.metric-item {
  display: flex;
  flex-direction: column;
}

.metric-item strong {
  color: #ffffff;
  font-size: 16px;
  font-weight: 800;
}

.metric-item small {
  color: var(--text-muted);
  font-size: 12px;
}

.metric-divider {
  width: 1px;
  height: 28px;
  background: rgba(255, 255, 255, 0.1);
}

.featured-hero-card {
  position: relative;
  width: 100%;
  height: clamp(340px, 46vh, 460px);
  border-radius: 20px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.1);
  cursor: pointer;
  box-shadow: 0 24px 50px rgba(0, 0, 0, 0.45);
}

.featured-hero-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}

.featured-hero-card:hover img {
  transform: scale(1.03);
}

.hero-card-badge {
  position: absolute;
  top: 18px;
  left: 18px;
  z-index: 2;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: rgba(6, 14, 45, 0.75);
  backdrop-filter: blur(10px);
  border-radius: 99px;
  font-size: 12px;
  font-weight: 600;
  border: 1px solid rgba(255, 255, 255, 0.12);
}

.reload-icon {
  font-size: 12px;
  color: var(--blue-accent);
}

.hero-card-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(6, 14, 45, 0.95) 12%, transparent 65%);
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 28px;
}

.hero-card-tag {
  font-size: 10px;
  letter-spacing: 0.12em;
  color: var(--blue-accent);
  font-weight: 800;
  margin-bottom: 6px;
}

.hero-card-overlay h3 {
  margin: 0;
  font-size: 26px;
  font-weight: 800;
  color: #fff;
}

/* BARRA DE BÚSQUEDA HERO */
.hero-search-card {
  background: #0d173d;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 18px;
  padding: 18px 24px;
  box-shadow: 0 16px 40px rgba(0,0,0,0.35);
  margin-top: auto;
}

.search-fields-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 14px;
}

.search-col {
  display: flex;
  align-items: center;
  gap: 14px;
  cursor: pointer;
  flex: 1;
}

.relative-date-picker {
  position: relative;
}

.hidden-date-input {
  position: absolute;
  inset: 0;
  opacity: 0;
  cursor: pointer;
  width: 100%;
  height: 100%;
}

.icon-circle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.05);
  display: grid;
  place-items: center;
  color: var(--text-muted);
  font-size: 18px;
}

.col-text small {
  display: block;
  font-size: 9px;
  letter-spacing: 0.08em;
  color: var(--text-muted);
  font-weight: 800;
  margin-bottom: 2px;
}

.col-text strong {
  font-size: 14px;
  color: #ffffff;
  font-weight: 700;
}

.search-col-divider {
  width: 1px;
  height: 36px;
  background: rgba(255, 255, 255, 0.08);
  margin: 0 20px;
}

.hero-search-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--blue-accent);
  border: 0;
  color: #060e2d;
  padding: 12px 28px;
  border-radius: 12px;
  font-weight: 800;
  font-size: 14px;
  cursor: pointer;
  transition: 0.2s ease;
}

.hero-search-btn:hover {
  background: var(--blue-hover);
}

.hero-chips-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding-top: 14px;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.chips-label {
  font-size: 12px;
  color: var(--text-muted);
  margin-right: 4px;
}

.hero-chips-row button {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 99px;
  padding: 6px 16px;
  color: var(--text-muted);
  font-size: 12px;
  cursor: pointer;
  transition: 0.2s ease;
}

.hero-chips-row button:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
}

/* =========================================================
   SECCIÓN CANCHAS
   ========================================================= */

.courts-section {
  background: var(--bg-light);
  color: var(--text-dark);
  padding: 90px 48px;
}

.section-intro {
  margin-bottom: 28px;
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
}

.section-eyebrow {
  display: block;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.12em;
  color: var(--blue-accent);
  margin-bottom: 6px;
}

.section-intro h2 {
  margin: 0;
  font-size: 36px;
  font-weight: 800;
  letter-spacing: -0.03em;
  color: var(--text-dark);
}

.secondary-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #fff;
  border: 1px solid #dce2ee;
  padding: 12px 24px;
  border-radius: 99px;
  font-size: 13px;
  font-weight: 700;
  color: var(--text-dark);
  cursor: pointer;
}

.results-toolbar {
  margin-bottom: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.active-filters {
  display: flex;
  align-items: center;
  gap: 12px;
}

.filter-status {
  font-size: 12px;
  font-weight: 700;
  color: var(--blue-accent);
  display: flex;
  align-items: center;
  gap: 6px;
}

.filter-status span {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--blue-accent);
}

.active-filters button {
  background: transparent;
  border: 0;
  color: #64748b;
  font-size: 12px;
  cursor: pointer;
}

.sort-control {
  display: flex;
  align-items: center;
  gap: 8px;
  position: relative;
}

.sort-control ion-icon {
  position: absolute;
  left: 10px;
  color: #64748b;
}

.sort-control select {
  padding: 8px 12px 8px 32px;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  background: #fff;
  font-size: 12px;
  font-weight: 600;
  color: var(--text-dark);
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 28px;
}

.court-card {
  background: #ffffff;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
  border: 1px solid #e2e8f0;
  cursor: pointer;
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.court-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
}

.court-image {
  position: relative;
  height: 200px;
}

.court-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.sport-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  background: rgba(6, 14, 45, 0.8);
  color: #fff;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
}

.fav-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  background: #fff;
  border: 0;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  font-size: 12px;
  color: #94a3b8;
  cursor: pointer;
}

.court-content {
  padding: 20px;
}

.court-content h3 {
  margin: 0 0 6px;
  font-size: 18px;
  font-weight: 800;
  color: var(--text-dark);
}

.court-location {
  margin: 0 0 8px;
  font-size: 12px;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 4px;
}

.court-features {
  font-size: 11px;
  color: #94a3b8;
  margin: 0 0 16px;
}

.court-slots {
  background: #f8fafc;
  border-radius: 10px;
  padding: 10px 12px;
  margin-bottom: 16px;
}

.slots-title {
  display: block;
  font-size: 9px;
  font-weight: 800;
  letter-spacing: 0.08em;
  color: #94a3b8;
  margin-bottom: 6px;
}

.slots-pills {
  display: flex;
  gap: 8px;
}

.slot-pill {
  background: #eef2ff;
  color: #4b6bfe;
  font-weight: 700;
  font-size: 11px;
  padding: 4px 10px;
  border-radius: 6px;
}

.court-bottom {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid #f1f5f9;
  padding-top: 14px;
}

.court-price strong {
  font-size: 20px;
  font-weight: 800;
  color: var(--text-dark);
}

.court-price span {
  font-size: 12px;
  color: #64748b;
}

.card-link {
  background: transparent;
  border: 0;
  color: #4b6bfe;
  font-weight: 700;
  font-size: 12px;
  display: flex;
  align-items: center;
  gap: 4px;
  cursor: pointer;
}

.disclaimer-text {
  margin-top: 28px;
  font-size: 11px;
  color: #94a3b8;
  text-align: center;
}

/* =========================================================
   EMPTY STATE (SIN RESULTADOS) - TIPOGRAFÍA BASE MEJORADA
   ========================================================= */

.empty-state {
  text-align: center;
  padding: 56px 28px;
  background: #ffffff;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
  max-width: 520px;
  margin: 0 auto;
  font-family: 'Plus Jakarta Sans', sans-serif;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.empty-icon {
  width: 56px;
  height: 56px;
  background: #eef2ff;
  color: var(--blue-accent, #7b96ff);
  border-radius: 50%;
  display: grid;
  place-items: center;
  font-size: 24px;
  margin-bottom: 20px;
}

.empty-eyebrow {
  display: block;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.1em;
  color: #6281f7;
  margin-bottom: 8px;
  text-transform: uppercase;
}

.empty-title {
  margin: 0 0 10px;
  font-size: 24px;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: #091133;
  line-height: 1.2;
}

.empty-description {
  font-size: 14px;
  color: #64748b;
  line-height: 1.5;
  margin: 0 0 24px;
  max-width: 420px;
  font-weight: 500;
}

.empty-description strong {
  color: #091133;
}

.empty-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 24px;
  background: #7b96ff;
  border: 0;
  border-radius: 12px;
  color: #060e2d;
  font-size: 13px;
  font-weight: 800;
  font-family: 'Plus Jakarta Sans', sans-serif;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 14px rgba(123, 150, 255, 0.3);
}

.empty-btn:hover {
  background: #6281f7;
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(98, 129, 247, 0.4);
}

.empty-btn ion-icon {
  font-size: 16px;
}

/* Skeletons */
.skeleton-card {
  height: 380px;
  background: #fff;
}
.skeleton-image {
  height: 200px;
  background: #e2e8f0;
}
.skeleton-body {
  padding: 20px;
}
.skeleton-line {
  height: 16px;
  background: #f1f5f9;
  border-radius: 4px;
  margin-bottom: 10px;
}
.skeleton-title { width: 70%; }
.skeleton-location { width: 40%; }
.skeleton-divider { height: 1px; background: #f1f5f9; margin: 20px 0; }
.skeleton-bottom { display: flex; justify-content: space-between; }
.skeleton-price { width: 30%; height: 20px; background: #f1f5f9; }
.skeleton-button { width: 20%; height: 20px; background: #f1f5f9; }

/* =========================================================
   SECCIÓN PASOS
   ========================================================= */

.steps-section {
  padding: 100px 48px;
}

.steps-section h2 {
  font-size: 36px;
  font-weight: 800;
  margin: 0 0 50px;
}

.steps-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 28px;
}

.step-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid var(--border-dark);
  border-radius: 20px;
  padding: 30px;
}

.step-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.step-icon {
  width: 44px;
  height: 44px;
  background: rgba(123, 150, 255, 0.12);
  color: var(--blue-accent);
  border-radius: 12px;
  display: grid;
  place-items: center;
  font-size: 20px;
}

.step-number {
  font-size: 28px;
  font-weight: 800;
  color: rgba(255, 255, 255, 0.12);
}

.step-card h3 {
  font-size: 18px;
  font-weight: 800;
  margin: 0 0 10px;
}

.step-card p {
  font-size: 13px;
  color: var(--text-muted);
  line-height: 1.6;
  margin: 0;
}

/* =========================================================
   BANNER PROPIETARIOS
   ========================================================= */

.owner-banner-section {
  padding: 0 48px 100px;
}

.owner-card {
  background: linear-gradient(135deg, #0c1743 0%, #060e2d 100%);
  border: 1px solid var(--border-dark);
  border-radius: 24px;
  padding: 50px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 50px;
  align-items: center;
}

.owner-info h2 {
  font-size: 36px;
  font-weight: 800;
  margin: 0 0 16px;
  letter-spacing: -0.03em;
  line-height: 1.1;
}

.owner-info p {
  color: var(--text-muted);
  font-size: 15px;
  margin: 0 0 32px;
  line-height: 1.6;
}

.owner-primary-btn {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: var(--blue-accent);
  border: 0;
  color: #060e2d;
  padding: 12px 24px;
  border-radius: 10px;
  font-weight: 800;
  font-size: 14px;
  cursor: pointer;
  transition: 0.2s ease;
}

.owner-primary-btn:hover {
  background: var(--blue-hover);
}

.owner-preview-box {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--border-dark);
  border-radius: 16px;
  padding: 20px;
}

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.preview-header strong {
  display: block;
  font-size: 14px;
}

.preview-header small {
  font-size: 11px;
  color: var(--text-muted);
}

.preview-rows {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.preview-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(255, 255, 255, 0.03);
  padding: 12px 16px;
  border-radius: 10px;
}

.row-info strong {
  display: block;
  font-size: 12px;
}

.row-info small {
  font-size: 10px;
  color: var(--text-muted);
}

.status-tag {
  font-size: 10px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 6px;
}

.status-tag.reserved {
  background: rgba(123, 150, 255, 0.2);
  color: #a5bbfd;
}

.status-tag.available {
  background: rgba(255, 255, 255, 0.08);
  color: var(--text-muted);
}

/* =========================================================
   FOOTER
   ========================================================= */

.site-footer {
  border-top: 1px solid var(--border-dark);
  padding: 60px 48px 40px;
}

.footer-top {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 40px;
  margin-bottom: 40px;
}

.footer-brand-title {
  font-size: 22px;
  font-weight: 800;
}

.footer-brand-title span {
  color: var(--blue-accent);
}

.footer-brand-col p {
  color: var(--text-muted);
  font-size: 13px;
  margin: 8px 0 0;
}

.footer-links-col h4 {
  font-size: 13px;
  font-weight: 700;
  margin: 0 0 16px;
  color: #fff;
}

.footer-links-col a {
  display: block;
  color: var(--text-muted);
  font-size: 13px;
  margin-bottom: 10px;
  cursor: pointer;
  text-decoration: none;
}

.footer-links-col a:hover {
  color: #fff;
}

.footer-bottom {
  border-top: 1px solid var(--border-dark);
  padding-top: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12px;
  color: var(--text-muted);
}

.footer-sub {
  margin: 0;
}

/* RESPONSIVE */

@media (max-width: 1100px) {
  .hero-section {
    min-height: auto;
    height: auto;
    padding-top: 110px;
  }
  .hero-inner {
    grid-template-columns: 1fr;
  }
  .featured-hero-card {
    height: 320px;
  }
  .cards-grid, .steps-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .owner-card {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .site-header {
    padding: 12px 20px;
  }
  .search-bar-header {
    display: none;
  }
  .hero-section {
    padding: 100px 20px 30px;
  }
  .hero-copy h1 {
    font-size: 38px;
  }
  .search-fields-row {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
  }
  .search-col-divider {
    display: none;
  }
  .hero-chips-row {
    flex-wrap: wrap;
  }
  .courts-section {
    padding: 60px 20px;
  }
  .cards-grid, .steps-grid {
    grid-template-columns: 1fr;
  }
  .footer-top {
    grid-template-columns: 1fr;
  }
  .footer-bottom {
    flex-direction: column;
    gap: 10px;
    text-align: center;
  }
}
</style>