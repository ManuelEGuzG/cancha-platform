<template>
  <ion-page class="sportra-app">
    <!-- Navbar Flotante Neón Glass -->
    <header class="navbar-container">
      <div class="navbar-bar">
        <!-- Logo con Glow Neón -->
        <div class="brand-box" @click="limpiarFiltros" role="button" tabindex="0">
          <div class="brand-badge glow-pulse">
            <ion-icon :icon="footballOutline" class="brand-icon"></ion-icon>
          </div>
          <span class="brand-name">SPORTRA<span class="neon-dot">.</span></span>
        </div>

        <!-- Central Search Capsule -->
        <div 
          class="hero-search-bar" 
          @click="toggleDropdownSearch" 
          :class="{ 'bar-active': searchOpen }"
        >
          <div class="search-item">
            <span class="item-tag">Zona</span>
            <span class="item-text">{{ ubicacionTextoSeleccionada }}</span>
          </div>

          <div class="search-divider"></div>

          <div class="search-item">
            <span class="item-tag">Instalación</span>
            <span class="item-text text-truncate">{{ busquedaTexto || '¿Dónde jugamos?' }}</span>
          </div>

          <button class="search-btn-neon" aria-label="Buscar">
            <ion-icon :icon="searchOutline"></ion-icon>
          </button>

          <!-- Popover Modal de Filtros -->
          <transition name="pop-scale">
            <div v-if="searchOpen" class="filter-modal" @click.stop>
              <div class="modal-top">
                <div class="modal-title">
                  <ion-icon :icon="optionsOutline" class="title-lime"></ion-icon>
                  <span>Filtrar instalaciones</span>
                </div>
                <button class="btn-close-modal" @click="searchOpen = false" aria-label="Cerrar">
                  <ion-icon :icon="closeOutline"></ion-icon>
                </button>
              </div>

              <div class="modal-body">
                <div class="field-block">
                  <label>Buscar por nombre</label>
                  <div class="input-glow-box">
                    <ion-icon :icon="searchOutline" class="field-icon"></ion-icon>
                    <input 
                      v-model="busquedaTexto" 
                      type="text" 
                      placeholder="Ej: Soccer Center, Maracaná..." 
                    />
                    <ion-icon 
                      v-if="busquedaTexto" 
                      :icon="closeCircle" 
                      class="clear-btn" 
                      @click="busquedaTexto = ''"
                    ></ion-icon>
                  </div>
                </div>

                <div class="selects-row">
                  <div class="field-block">
                    <label>Provincia</label>
                    <ion-select
                      v-model="provinciaId"
                      interface="popover"
                      placeholder="Todas"
                      class="neon-select"
                      @ionChange="onProvinciaChange"
                    >
                      <ion-select-option :value="null">Todas</ion-select-option>
                      <ion-select-option v-for="p in provincias" :key="p.id" :value="p.id">
                        {{ p.nombre }}
                      </ion-select-option>
                    </ion-select>
                  </div>

                  <div class="field-block">
                    <label>Cantón</label>
                    <ion-select
                      v-model="cantonId"
                      interface="popover"
                      placeholder="Todos"
                      class="neon-select"
                      :disabled="!provinciaId && cantones.length === 0"
                      @ionChange="onCantonChange"
                    >
                      <ion-select-option :value="null">Todos</ion-select-option>
                      <ion-select-option v-for="c in cantones" :key="c.id" :value="c.id">
                        {{ c.nombre }}
                      </ion-select-option>
                    </ion-select>
                  </div>

                  <div class="field-block">
                    <label>Distrito</label>
                    <ion-select
                      v-model="distritoId"
                      interface="popover"
                      placeholder="Todos"
                      class="neon-select"
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
              </div>

              <div class="modal-footer">
                <button class="btn-clear" @click="limpiarFiltros">
                  <ion-icon :icon="refreshOutline"></ion-icon> Restablecer
                </button>
                <button class="btn-submit-neon wave-effect" @click="aplicarFiltrosYScroll">
                  Buscar Canchas
                </button>
              </div>
            </div>
          </transition>
        </div>

        <!-- Accesos rápidos sin Horarios -->
        <div class="header-right">
          <nav class="top-nav">
            <button @click="scrollToCanchas">Canchas</button>
          </nav>

          <button class="btn-portal-glow" @click="irAlPanel">
            <span>Propietarios</span>
            <div class="portal-arrow">
              <ion-icon :icon="arrowForwardOutline"></ion-icon>
            </div>
          </button>
        </div>
      </div>
    </header>

    <ion-content :fullscreen="true" class="sportra-main-viewport" ref="contentRef">
      <!-- HERO LANDING -->
      <section class="hero-wrapper">
        <div class="hero-bg-glow glow-float-1"></div>
        <div class="hero-bg-glow glow-float-2"></div>
        <div class="hero-bg-grid"></div>

        <div class="hero-content-grid">
          <div class="hero-text-side animate-fade-up">
            <div class="pill-tag">
              <span class="beacon"></span>
              <span>RESERVAS AL INSTANTE EN COSTA RICA</span>
            </div>

            <h1 class="hero-title-main">
              DOMINA LA <br />
              <span class="lime-gradient-text glow-text-shadow">CANCHA HOY</span>
            </h1>

            <p class="hero-lead">
              La plataforma definitiva para encontrar y alquilar canchas sintéticas. Precios transparentes, disponibilidad en vivo y confirmación inmediata.
            </p>

            <div class="hero-actions-row">
              <button class="btn-cta-lime glow-hover" @click="scrollToCanchas">
                <span>Reservar Partido</span>
                <div class="cta-circle">
                  <ion-icon :icon="arrowForwardOutline"></ion-icon>
                </div>
              </button>

              <div class="live-community-badge">
                <div class="avatars-cluster">
                  <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80" alt="Jugador" />
                  <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=100&q=80" alt="Jugador" />
                  <img src="https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?auto=format&fit=crop&w=100&q=80" alt="Jugador" />
                </div>
                <div class="community-stats">
                  <strong>+2,000</strong>
                  <span>Mejengueros listos</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Tarjeta Destacada Neón -->
          <div class="hero-card-side animate-fade-up-delay" v-if="complejosOrdenados.length > 0">
            <div class="spotlight-card border-gradient-glow">
              <div class="spotlight-header">
                <span class="spotlight-badge">DESTACADO EN TU ZONA</span>
                <ion-icon :icon="footballOutline" class="spotlight-icon-pulse"></ion-icon>
              </div>

              <div class="spotlight-img-box">
                <img :src="getComplejoImage(complejosOrdenados[0])" :alt="complejosOrdenados[0].nombre" @error="handleImageError" />
                <span class="badge-live-now">
                  <span class="dot-ping"></span> DISPONIBLE
                </span>
              </div>

              <div class="spotlight-info">
                <div>
                  <h3 class="spotlight-name">{{ complejosOrdenados[0].nombre }}</h3>
                  <p class="spotlight-location">
                    <ion-icon :icon="locationOutline"></ion-icon>
                    <span>{{ complejosOrdenados[0].distrito }}, {{ complejosOrdenados[0].canton }}</span>
                  </p>
                </div>
                <div class="spotlight-price">
                  <span class="price-val">₡{{ formatearPrecio(complejosOrdenados[0].precio_desde) }}</span>
                  <span class="price-unit">/hora</span>
                </div>
              </div>

              <button class="spotlight-btn" @click="verDetalle(complejosOrdenados[0].slug)">
                <span>Reservar esta cancha</span>
                <ion-icon :icon="arrowForwardOutline" class="btn-icon-slide"></ion-icon>
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- SECCIÓN DE CANCHAS & RESULTADOS -->
      <main class="courts-section" id="seccion-canchas" ref="seccionCanchasRef">
        <div class="section-header-bar">
          <div class="header-title-box">
            <h2 class="main-section-title">
              Canchas Encontradas
              <span class="counter-pill">{{ complejosFiltrados.length }}</span>
            </h2>
            <div v-if="filtrosActivos" class="active-filter-tag">
              <span class="pulse-lime"></span> Filtros aplicados
            </div>
          </div>

          <div class="sort-box" v-if="complejosFiltrados.length > 1">
            <ion-icon :icon="swapVerticalOutline" class="sort-icon-lime"></ion-icon>
            <select v-model="orden" class="neon-sort-select">
              <option value="nombre">Ordenar por: Nombre (A-Z)</option>
              <option value="precio-asc">Precio: Menor a Mayor</option>
              <option value="precio-desc">Precio: Mayor a Menor</option>
            </select>
          </div>
        </div>

        <!-- Skeleton Loader -->
        <transition name="fade-mode" mode="out-in">
          <div v-if="cargando" class="cards-grid">
            <div v-for="i in 6" :key="i" class="card-item skeleton-box">
              <div class="sk-image shimmer-effect"></div>
              <div class="sk-body">
                <div class="sk-line title shimmer-effect"></div>
                <div class="sk-line subtitle shimmer-effect"></div>
                <div class="sk-footer">
                  <div class="sk-line price shimmer-effect"></div>
                  <div class="sk-btn shimmer-effect"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Grid de Canchas -->
          <div v-else-if="complejosFiltrados.length > 0" class="cards-grid">
            <article
              v-for="(complejo, index) in complejosOrdenados"
              :key="complejo.id"
              class="card-item interactive-card"
              :style="{ animationDelay: `${index * 0.08}s` }"
              @click="verDetalle(complejo.slug)"
            >
              <div class="card-img-container">
                <img
                  :src="getComplejoImage(complejo)"
                  :alt="complejo.nombre"
                  loading="lazy"
                  @error="handleImageError"
                />
                <div class="card-img-overlay"></div>
                <div class="card-top-badges">
                  <span class="chip-count">
                    <ion-icon :icon="footballOutline"></ion-icon>
                    {{ complejo.total_canchas }} {{ complejo.total_canchas === 1 ? 'Cancha' : 'Canchas' }}
                  </span>
                </div>
              </div>

              <div class="card-body-container">
                <h3 class="card-court-title">{{ complejo.nombre }}</h3>
                <p class="card-court-loc">
                  <ion-icon :icon="locationOutline" class="loc-lime"></ion-icon>
                  <span>{{ complejo.distrito }}, {{ complejo.canton }}</span>
                </p>

                <div class="card-footer-box">
                  <div class="price-display">
                    <span class="price-lbl">POR HORA</span>
                    <div class="price-num">₡{{ formatearPrecio(complejo.precio_desde) }}</div>
                  </div>

                  <button class="btn-card-reserve">
                    <span>Ver Hora</span>
                    <ion-icon :icon="arrowForwardOutline" class="btn-icon-slide"></ion-icon>
                  </button>
                </div>
              </div>
            </article>
          </div>

          <!-- Empty State -->
          <div v-else class="empty-results-box animate-fade-in">
            <div class="empty-glow-icon glow-pulse">
              <ion-icon :icon="searchOutline"></ion-icon>
            </div>
            <h3>Sin canchas disponibles</h3>
            <p v-if="busquedaTexto">No se hallaron instalaciones para "{{ busquedaTexto }}".</p>
            <p v-else>No coinciden instalaciones con la búsqueda actual.</p>
            <button class="btn-reset-main glow-hover" @click="limpiarFiltros">
              <ion-icon :icon="refreshOutline"></ion-icon>
              <span>Restablecer Filtros</span>
            </button>
          </div>
        </transition>

        <!-- Footer estilizado -->
        <footer class="app-footer">
          <div class="footer-left">
            <div class="footer-logo glow-pulse">
              <ion-icon :icon="footballOutline"></ion-icon>
            </div>
            <span class="footer-brand">SPORTRA<span class="neon-dot">.</span></span>
          </div>
          <span class="footer-copyright">© 2026 Sportra Costa Rica. Reservas de fútbol en tiempo real.</span>
        </footer>
      </main>
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
  arrowForwardOutline
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
  'arrow-forward-outline': arrowForwardOutline
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
const orden = ref<'nombre' | 'precio-asc' | 'precio-desc'>('nombre');
const searchOpen = ref(false);

const complejos = ref<Complejo[]>([]);
const cargando = ref(true);

const filtrosActivos = computed(() => {
  return provinciaId.value !== null || cantonId.value !== null || distritoId.value !== null || busquedaTexto.value !== '';
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
  return lista.sort((a, b) => a.nombre.localeCompare(b.nombre));
});

function scrollToCanchas() {
  if (contentRef.value && seccionCanchasRef.value) {
    const yOffset = seccionCanchasRef.value.offsetTop - 90;
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
  provinciaId.value = null;
  cantonId.value = null;
  distritoId.value = null;
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
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700;800&family=Inter:wght@400;500;600;700;800&display=swap');

/* BASE & LAYOUT */
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
  transition: border-color 0.3s ease;
}

.navbar-bar:hover {
  border-color: rgba(132, 204, 22, 0.25);
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
  transition: transform 0.3s ease;
}

.brand-box:hover .brand-badge {
  transform: rotate(15deg) scale(1.08);
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

/* SEARCH BAR CAPSULE */
.hero-search-bar {
  position: relative;
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 99px;
  padding: 0.3rem 0.4rem 0.3rem 1.25rem;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.hero-search-bar:hover,
.hero-search-bar.bar-active {
  background: rgba(132, 204, 22, 0.08);
  border-color: rgba(132, 204, 22, 0.5);
  box-shadow: 0 0 25px rgba(132, 204, 22, 0.2);
}

.search-item {
  display: flex;
  flex-direction: column;
  padding-right: 0.85rem;
}

.item-tag {
  font-size: 0.58rem;
  font-weight: 800;
  text-transform: uppercase;
  color: #84cc16;
  letter-spacing: 0.06em;
}

.item-text {
  font-size: 0.825rem;
  color: #ffffff;
  font-weight: 600;
  max-width: 140px;
}

.text-truncate {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.search-divider {
  width: 1px;
  height: 22px;
  background-color: rgba(255, 255, 255, 0.12);
  margin-right: 0.85rem;
}

.search-btn-neon {
  background: #84cc16;
  color: #030712;
  border: none;
  width: 38px;
  height: 38px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.15rem;
  cursor: pointer;
  box-shadow: 0 0 15px rgba(132, 204, 22, 0.4);
  transition: transform 0.25s ease, background-color 0.25s ease, box-shadow 0.25s ease;
}

.hero-search-bar:hover .search-btn-neon {
  transform: scale(1.08);
  box-shadow: 0 0 22px rgba(132, 204, 22, 0.6);
}

/* MODAL DE FILTROS CORREGIDO */
.filter-modal {
  position: absolute;
  top: calc(100% + 16px);
  left: 50%;
  transform: translateX(-50%);
  width: 520px;
  background: rgba(11, 15, 25, 0.95);
  backdrop-filter: blur(30px);
  -webkit-backdrop-filter: blur(30px);
  border-radius: 28px;
  padding: 1.6rem;
  box-shadow: 0 30px 70px rgba(0, 0, 0, 0.95), 0 0 30px rgba(132, 204, 22, 0.15);
  border: 1px solid rgba(132, 204, 22, 0.35);
  z-index: 1001;
}

.modal-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
}

.modal-title {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  font-weight: 700;
  font-size: 1.05rem;
  color: #ffffff;
}

.title-lime { color: #84cc16; }

.btn-close-modal {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ffffff;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-close-modal:hover { 
  background: rgba(255, 255, 255, 0.2); 
  transform: rotate(90deg);
}

.field-block label {
  font-size: 0.68rem;
  font-weight: 800;
  color: #a3e635;
  display: block;
  margin-bottom: 0.4rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.input-glow-box {
  position: relative;
  display: flex;
  align-items: center;
}

.input-glow-box .field-icon {
  position: absolute;
  left: 0.9rem;
  color: #64748b;
  font-size: 1.1rem;
}

.input-glow-box input {
  width: 100%;
  height: 44px;
  background: #030712;
  border: 1px solid rgba(132, 204, 22, 0.25);
  border-radius: 14px;
  padding: 0 2.5rem;
  font-size: 0.875rem;
  color: #ffffff;
  outline: none;
  transition: all 0.25s ease;
}

.input-glow-box input:focus {
  border-color: #84cc16;
  box-shadow: 0 0 15px rgba(132, 204, 22, 0.25);
}

.clear-btn {
  position: absolute;
  right: 0.9rem;
  color: #64748b;
  cursor: pointer;
  transition: color 0.2s;
}

.clear-btn:hover { color: #ffffff; }

.selects-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.75rem;
  margin: 1.25rem 0 1.5rem;
}

/* FIX PARA ION-SELECTS EN MODAL */
.neon-select {
  --background: #030712;
  --color: #ffffff;
  --placeholder-color: #94a3b8;
  --placeholder-opacity: 1;
  --padding-start: 0.85rem;
  --padding-end: 0.85rem;
  --padding-top: 0;
  --padding-bottom: 0;
  background: #030712;
  border: 1px solid rgba(132, 204, 22, 0.25);
  border-radius: 14px;
  height: 44px;
  font-size: 0.825rem;
  font-weight: 600;
  color: #ffffff;
  display: flex;
  align-items: center;
  transition: all 0.25s ease;
}

.neon-select:hover {
  border-color: rgba(132, 204, 22, 0.6);
  box-shadow: 0 0 12px rgba(132, 204, 22, 0.15);
}

.neon-select::part(icon) {
  color: #84cc16;
  opacity: 1;
}

.neon-select::part(text) {
  color: #ffffff;
  font-weight: 600;
}

.neon-select::part(placeholder) {
  color: #94a3b8;
}

.modal-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1.1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.btn-clear {
  background: transparent;
  border: none;
  color: #94a3b8;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  transition: color 0.2s;
}

.btn-clear:hover { color: #ffffff; }

.btn-submit-neon {
  background: #84cc16;
  color: #030712;
  border: none;
  padding: 0.7rem 1.5rem;
  border-radius: 14px;
  font-weight: 800;
  font-size: 0.875rem;
  cursor: pointer;
  box-shadow: 0 0 20px rgba(132, 204, 22, 0.3);
  transition: all 0.25s ease;
}

.btn-submit-neon:hover { 
  background: #a3e635; 
  transform: translateY(-2px);
  box-shadow: 0 5px 25px rgba(132, 204, 22, 0.5);
}

/* HEADER RIGHT */
.header-right {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.top-nav {
  display: flex;
  gap: 1.25rem;
}

.top-nav button {
  background: transparent;
  border: none;
  color: #94a3b8;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: color 0.2s ease;
}

.top-nav button:hover { color: #ffffff; }

.btn-portal-glow {
  background: #ffffff;
  color: #030712;
  border: none;
  padding: 0.45rem 0.5rem 0.45rem 1.1rem;
  border-radius: 99px;
  font-weight: 800;
  font-size: 0.825rem;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.btn-portal-glow:hover {
  transform: translateY(-2px) scale(1.02);
  box-shadow: 0 8px 25px rgba(255, 255, 255, 0.25);
}

.portal-arrow {
  background: #030712;
  color: #ffffff;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  transition: transform 0.25s ease;
}

.btn-portal-glow:hover .portal-arrow {
  transform: translateX(3px);
}

/* HERO LANDING SECTION */
.hero-wrapper {
  position: relative;
  min-height: 880px;
  padding: 10rem 1.5rem 6rem;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.hero-bg-glow {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
  filter: blur(90px);
}

.glow-float-1 {
  top: -10%;
  left: 15%;
  width: 550px;
  height: 550px;
  background: radial-gradient(circle, rgba(132, 204, 22, 0.18) 0%, rgba(3, 7, 18, 0) 70%);
  animation: floatGlow 12s infinite alternate ease-in-out;
}

.glow-float-2 {
  bottom: 10%;
  right: 10%;
  width: 450px;
  height: 450px;
  background: radial-gradient(circle, rgba(163, 230, 53, 0.12) 0%, rgba(3, 7, 18, 0) 70%);
  animation: floatGlow 16s infinite alternate-reverse ease-in-out;
}

.hero-bg-grid {
  position: absolute;
  inset: 0;
  background-image: linear-gradient(to right, rgba(255,255,255,0.025) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(255,255,255,0.025) 1px, transparent 1px);
  background-size: 48px 48px;
  pointer-events: none;
  mask-image: radial-gradient(circle at center, black 40%, transparent 80%);
}

.hero-content-grid {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 1280px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 400px;
  align-items: center;
  gap: 4rem;
}

.pill-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  background: rgba(132, 204, 22, 0.1);
  border: 1px solid rgba(132, 204, 22, 0.3);
  padding: 0.4rem 1.1rem;
  border-radius: 99px;
  font-size: 0.75rem;
  font-weight: 800;
  color: #a3e635;
  letter-spacing: 0.05em;
  margin-bottom: 1.75rem;
  box-shadow: 0 0 15px rgba(132, 204, 22, 0.1);
}

.beacon {
  width: 8px;
  height: 8px;
  background: #84cc16;
  border-radius: 50%;
  box-shadow: 0 0 10px #84cc16;
  animation: pulseBeacon 2s infinite;
}

.hero-title-main {
  font-family: 'Space Grotesk', sans-serif;
  font-size: clamp(3.2rem, 5.8vw, 5.4rem);
  font-weight: 800;
  color: #ffffff;
  line-height: 1.02;
  letter-spacing: -0.04em;
  margin: 0 0 1.5rem;
}

.lime-gradient-text {
  background: linear-gradient(135deg, #84cc16 0%, #ecfccb 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.glow-text-shadow {
  filter: drop-shadow(0 0 25px rgba(132, 204, 22, 0.3));
}

.hero-lead {
  color: #94a3b8;
  font-size: 1.1rem;
  max-width: 560px;
  line-height: 1.65;
  margin-bottom: 2.75rem;
}

.hero-actions-row {
  display: flex;
  align-items: center;
  gap: 2.5rem;
}

.btn-cta-lime {
  background: #84cc16;
  color: #030712;
  border: none;
  padding: 0.55rem 0.65rem 0.55rem 1.75rem;
  border-radius: 99px;
  font-weight: 800;
  font-size: 1rem;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  cursor: pointer;
  box-shadow: 0 0 30px rgba(132, 204, 22, 0.4);
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.btn-cta-lime:hover {
  transform: translateY(-3px) scale(1.03);
  box-shadow: 0 10px 40px rgba(132, 204, 22, 0.6);
  background: #a3e635;
}

.cta-circle {
  background: #030712;
  color: #ffffff;
  width: 42px;
  height: 42px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  transition: transform 0.3s ease;
}

.btn-cta-lime:hover .cta-circle {
  transform: translateX(4px);
}

.live-community-badge {
  display: flex;
  align-items: center;
  gap: 0.85rem;
}

.avatars-cluster {
  display: flex;
}

.avatars-cluster img {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  border: 2px solid #030712;
  margin-left: -14px;
  object-fit: cover;
  transition: transform 0.2s ease;
}

.avatars-cluster img:first-child { margin-left: 0; }
.avatars-cluster img:hover { transform: translateY(-4px) scale(1.1); z-index: 5; }

.community-stats {
  display: flex;
  flex-direction: column;
}

.community-stats strong {
  font-size: 1.05rem;
  font-weight: 800;
  color: #ffffff;
}

.community-stats span {
  font-size: 0.75rem;
  color: #64748b;
}

/* SPOTLIGHT CARD side */
.border-gradient-glow {
  position: relative;
  background: rgba(15, 23, 42, 0.85);
  backdrop-filter: blur(24px);
  border: 1px solid rgba(132, 204, 22, 0.3);
  border-radius: 26px;
  padding: 1.25rem;
  box-shadow: 0 30px 60px rgba(0, 0, 0, 0.85), 0 0 40px rgba(132, 204, 22, 0.12);
  display: flex;
  flex-direction: column;
  gap: 1.1rem;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.border-gradient-glow:hover {
  transform: translateY(-5px);
  box-shadow: 0 35px 70px rgba(0, 0, 0, 0.9), 0 0 50px rgba(132, 204, 22, 0.2);
}

.spotlight-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.7rem;
  font-weight: 800;
  color: #a3e635;
  letter-spacing: 0.05em;
}

.spotlight-icon-pulse {
  font-size: 1.1rem;
  animation: pulseBeacon 2s infinite;
}

.spotlight-img-box {
  position: relative;
  height: 195px;
  border-radius: 18px;
  overflow: hidden;
}

.spotlight-img-box img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.border-gradient-glow:hover .spotlight-img-box img {
  transform: scale(1.05);
}

.badge-live-now {
  position: absolute;
  top: 0.75rem;
  left: 0.75rem;
  background: rgba(3, 7, 18, 0.85);
  backdrop-filter: blur(8px);
  padding: 0.35rem 0.75rem;
  border-radius: 99px;
  font-size: 0.65rem;
  font-weight: 800;
  color: #ffffff;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  border: 1px solid rgba(132, 204, 22, 0.3);
}

.dot-ping {
  width: 6px;
  height: 6px;
  background: #84cc16;
  border-radius: 50%;
  box-shadow: 0 0 8px #84cc16;
  animation: pulseBeacon 1.5s infinite;
}

.spotlight-info {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
}

.spotlight-name {
  margin: 0 0 0.25rem;
  font-size: 1.15rem;
  font-weight: 800;
  color: #ffffff;
}

.spotlight-location {
  margin: 0;
  font-size: 0.8rem;
  color: #94a3b8;
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.spotlight-location ion-icon { color: #84cc16; }

.spotlight-price { text-align: right; }

.spotlight-price .price-val {
  display: block;
  font-size: 1.25rem;
  font-weight: 800;
  color: #84cc16;
}

.spotlight-price .price-unit {
  font-size: 0.65rem;
  color: #64748b;
}

.spotlight-btn {
  background: rgba(132, 204, 22, 0.12);
  border: 1px solid rgba(132, 204, 22, 0.35);
  color: #ffffff;
  padding: 0.8rem 1.1rem;
  border-radius: 16px;
  font-size: 0.85rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  transition: all 0.25s ease;
}

.spotlight-btn:hover {
  background: #84cc16;
  color: #030712;
  box-shadow: 0 0 25px rgba(132, 204, 22, 0.4);
}

.btn-icon-slide {
  transition: transform 0.25s ease;
}

.spotlight-btn:hover .btn-icon-slide,
.btn-card-reserve:hover .btn-icon-slide {
  transform: translateX(4px);
}

/* SECCIÓN RESULTADOS */
.courts-section {
  max-width: 1280px;
  margin: 0 auto;
  padding: 3rem 1.5rem 6rem;
}

.section-header-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2.5rem;
  padding-bottom: 1.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.header-title-box {
  display: flex;
  align-items: center;
  gap: 1.25rem;
}

.main-section-title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 1.65rem;
  font-weight: 800;
  color: #ffffff;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.counter-pill {
  background: rgba(132, 204, 22, 0.15);
  color: #84cc16;
  font-size: 0.85rem;
  padding: 0.2rem 0.65rem;
  border-radius: 99px;
  font-weight: 800;
  border: 1px solid rgba(132, 204, 22, 0.3);
}

.active-filter-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.75rem;
  color: #a3e635;
  background: rgba(132, 204, 22, 0.1);
  padding: 0.25rem 0.75rem;
  border-radius: 99px;
  font-weight: 700;
}

.pulse-lime {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #84cc16;
  animation: pulseBeacon 1.5s infinite;
}

.sort-box {
  position: relative;
  display: flex;
  align-items: center;
}

.sort-icon-lime {
  position: absolute;
  left: 0.9rem;
  color: #84cc16;
  pointer-events: none;
}

.neon-sort-select {
  background: #0b0f19;
  border: 1px solid rgba(132, 204, 22, 0.25);
  border-radius: 14px;
  padding: 0.65rem 1.25rem 0.65rem 2.5rem;
  font-size: 0.825rem;
  font-weight: 600;
  color: #ffffff;
  outline: none;
  cursor: pointer;
  appearance: none;
  transition: border-color 0.25s ease;
}

.neon-sort-select:hover {
  border-color: #84cc16;
}

/* CARDS GRID & INTERACTIVE CARDS */
.cards-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
}

.interactive-card {
  opacity: 0;
  animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.card-item {
  background: #0b0f19;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 24px;
  overflow: hidden;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.card-item:hover {
  transform: translateY(-8px);
  border-color: rgba(132, 204, 22, 0.6);
  box-shadow: 0 20px 45px rgba(0, 0, 0, 0.85), 0 0 25px rgba(132, 204, 22, 0.15);
}

.card-img-container {
  position: relative;
  aspect-ratio: 16 / 9;
  background: #030712;
  overflow: hidden;
}

.card-img-container img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

.card-img-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(11, 15, 25, 0.9) 0%, transparent 60%);
  opacity: 0.6;
  transition: opacity 0.3s ease;
}

.card-item:hover .card-img-overlay {
  opacity: 0.3;
}

.card-item:hover .card-img-container img {
  transform: scale(1.08);
}

.card-top-badges {
  position: absolute;
  top: 0.85rem;
  right: 0.85rem;
  z-index: 2;
}

.chip-count {
  background: rgba(3, 7, 18, 0.85);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #ffffff;
  padding: 0.35rem 0.75rem;
  border-radius: 99px;
  font-size: 0.725rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}

.card-body-container {
  padding: 1.4rem;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.card-court-title {
  margin: 0 0 0.35rem;
  font-size: 1.2rem;
  font-weight: 800;
  color: #ffffff;
  transition: color 0.2s ease;
}

.card-item:hover .card-court-title {
  color: #84cc16;
}

.card-court-loc {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  color: #94a3b8;
  font-size: 0.825rem;
  margin: 0 0 1.5rem;
}

.loc-lime { color: #84cc16; }

.card-footer-box {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-top: auto;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.price-lbl {
  display: block;
  font-size: 0.625rem;
  text-transform: uppercase;
  color: #64748b;
  font-weight: 800;
  letter-spacing: 0.05em;
}

.price-num {
  font-size: 1.35rem;
  font-weight: 800;
  color: #84cc16;
}

.btn-card-reserve {
  background: rgba(255, 255, 255, 0.05);
  color: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.12);
  padding: 0.6rem 1.1rem;
  border-radius: 14px;
  font-size: 0.825rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.25s ease;
}

.card-item:hover .btn-card-reserve {
  background: #84cc16;
  color: #030712;
  border-color: #84cc16;
  box-shadow: 0 0 15px rgba(132, 204, 22, 0.4);
}

/* SKELETON LOADERS */
.sk-image { height: 180px; background: rgba(255, 255, 255, 0.05); }
.sk-body { padding: 1.25rem; }
.sk-line { background: rgba(255, 255, 255, 0.05); border-radius: 6px; margin-bottom: 0.8rem; }
.sk-line.title { height: 20px; width: 70%; }
.sk-line.subtitle { height: 14px; width: 45%; }
.sk-footer { display: flex; justify-content: space-between; margin-top: 1.5rem; }
.sk-line.price { height: 24px; width: 35%; }
.sk-btn { height: 32px; width: 80px; background: rgba(255, 255, 255, 0.05); border-radius: 10px; }

.shimmer-effect {
  position: relative;
  overflow: hidden;
}

.shimmer-effect::after {
  position: absolute;
  top: 0; right: 0; bottom: 0; left: 0;
  transform: translateX(-100%);
  background-image: linear-gradient(
    90deg,
    rgba(255, 255, 255, 0) 0,
    rgba(255, 255, 255, 0.05) 20%,
    rgba(255, 255, 255, 0.1) 60%,
    rgba(255, 255, 255, 0)
  );
  animation: shimmer 1.8s infinite;
  content: '';
}

@keyframes shimmer {
  100% { transform: translateX(100%); }
}

/* EMPTY STATE */
.empty-results-box {
  text-align: center;
  padding: 5rem 2rem;
  background: #0b0f19;
  border: 1px dashed rgba(132, 204, 22, 0.3);
  border-radius: 28px;
}

.empty-glow-icon {
  width: 68px;
  height: 68px;
  background: rgba(132, 204, 22, 0.12);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
  color: #84cc16;
  font-size: 2.2rem;
}

.empty-results-box h3 {
  font-size: 1.35rem;
  color: #ffffff;
  margin: 0 0 0.5rem;
  font-weight: 700;
}

.empty-results-box p {
  color: #94a3b8;
  font-size: 0.95rem;
  margin: 0 0 1.75rem;
}

.btn-reset-main {
  background: #84cc16;
  color: #030712;
  border: none;
  padding: 0.75rem 1.6rem;
  border-radius: 14px;
  font-weight: 800;
  font-size: 0.875rem;
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  cursor: pointer;
  box-shadow: 0 0 20px rgba(132, 204, 22, 0.3);
  transition: all 0.25s ease;
}

.btn-reset-main:hover {
  background: #a3e635;
  transform: translateY(-2px);
}

/* FOOTER */
.app-footer {
  margin-top: 5rem;
  padding-top: 2rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.footer-left {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.footer-logo {
  width: 32px;
  height: 32px;
  background: rgba(132, 204, 22, 0.15);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #84cc16;
}

.footer-brand {
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 800;
  color: #ffffff;
  font-size: 1.15rem;
}

.footer-copyright {
  font-size: 0.8rem;
  color: #64748b;
}

/* ANIMACIONES KEYFRAMES & TRANSICIONES */
@keyframes floatGlow {
  0% { transform: translate(0, 0) scale(1); }
  100% { transform: translate(30px, -40px) scale(1.1); }
}

@keyframes pulseBeacon {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.4; transform: scale(0.85); }
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-up {
  animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.animate-fade-up-delay {
  animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.15s forwards;
  opacity: 0;
}

.animate-fade-in {
  animation: fadeInUp 0.4s ease forwards;
}

.pop-scale-enter-active,
.pop-scale-leave-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.pop-scale-enter-from,
.pop-scale-leave-to {
  opacity: 0;
  transform: translate(-50%, 15px) scale(0.95);
}

.fade-mode-enter-active,
.fade-mode-leave-active {
  transition: opacity 0.3s ease;
}

.fade-mode-enter-from,
.fade-mode-leave-to {
  opacity: 0;
}

/* RESPONSIVE */
@media (max-width: 1024px) {
  .hero-content-grid { grid-template-columns: 1fr; gap: 3rem; }
  .spotlight-card { max-width: 440px; margin: 0 auto; }
  .cards-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
  .navbar-container { padding: 0 0.75rem; }
  .navbar-bar { flex-direction: column; gap: 0.75rem; border-radius: 24px; padding: 0.75rem; }
  .header-right { display: none; }
  .hero-search-bar { width: 100%; justify-content: space-between; }
  .filter-modal { width: 92vw; }
  .selects-row { grid-template-columns: 1fr; }
  .hero-wrapper { padding: 12rem 1.25rem 4rem; }
  .hero-actions-row { flex-direction: column; align-items: flex-start; gap: 1.5rem; }
  .cards-grid { grid-template-columns: 1fr; }
  .section-header-bar { flex-direction: column; align-items: flex-start; gap: 1rem; }
  .app-footer { flex-direction: column; gap: 1rem; text-align: center; }
}
</style>