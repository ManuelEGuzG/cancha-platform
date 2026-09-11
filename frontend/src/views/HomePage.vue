<template>
  <ion-page class="sportra-landing">
    <!-- Header Flotante Glassmorphism -->
    <header class="navbar-wrapper">
      <div class="navbar-glass">
        <!-- Logo Sportra Verde Liso -->
        <div class="logo" @click="limpiarFiltros" role="button" tabindex="0">
          <ion-icon :icon="footballOutline" class="logo-icon"></ion-icon>
          <span class="logo-text">Sportra</span>
        </div>

        <!-- Search Capsule Central -->
        <div class="search-capsule" @click="toggleDropdownSearch" :class="{ 'is-active': searchOpen }">
          <div class="capsule-section">
            <span class="capsule-label">Ubicación</span>
            <span class="capsule-value">{{ ubicacionTextoSeleccionada }}</span>
          </div>

          <div class="capsule-divider"></div>

          <div class="capsule-section">
            <span class="capsule-label">Complejo</span>
            <span class="capsule-value">{{ busquedaTexto || 'Buscar instalación...' }}</span>
          </div>

          <!-- Botón circular verde con ícono de lupa -->
          <button class="capsule-search-btn" aria-label="Buscar">
            <ion-icon :icon="searchOutline"></ion-icon>
          </button>

          <!-- Dropdown Flotante / Filtros de Búsqueda -->
          <transition name="popover-fade">
            <div v-if="searchOpen" class="search-popover" @click.stop>
              <div class="popover-header">
                <div class="popover-title">
                  <ion-icon :icon="optionsOutline" class="title-icon"></ion-icon>
                  <span>Filtros de Búsqueda</span>
                </div>
                <button class="btn-close-popover" @click="searchOpen = false" aria-label="Cerrar">
                  <ion-icon :icon="closeOutline"></ion-icon>
                </button>
              </div>

              <div class="popover-field">
                <label>Nombre del complejo</label>
                <div class="popover-input-wrapper">
                  <ion-icon :icon="searchOutline" class="input-icon"></ion-icon>
                  <input 
                    v-model="busquedaTexto" 
                    type="text" 
                    placeholder="Ej: Soccer Center, Maracaná..." 
                  />
                  <ion-icon 
                    v-if="busquedaTexto" 
                    :icon="closeCircle" 
                    class="clear-icon" 
                    @click="busquedaTexto = ''"
                  ></ion-icon>
                </div>
              </div>

              <div class="popover-grid">
                <div class="field-group">
                  <label>Provincia</label>
                  <ion-select
                    v-model="provinciaId"
                    interface="popover"
                    placeholder="Todas"
                    class="popover-select"
                    @ionChange="onProvinciaChange"
                  >
                    <ion-select-option :value="null">Todas</ion-select-option>
                    <ion-select-option v-for="p in provincias" :key="p.id" :value="p.id">
                      {{ p.nombre }}
                    </ion-select-option>
                  </ion-select>
                </div>

                <div class="field-group">
                  <label>Cantón</label>
                  <ion-select
                    v-model="cantonId"
                    interface="popover"
                    placeholder="Todos"
                    class="popover-select"
                    :disabled="!provinciaId && cantones.length === 0"
                    @ionChange="onCantonChange"
                  >
                    <ion-select-option :value="null">Todos</ion-select-option>
                    <ion-select-option v-for="c in cantones" :key="c.id" :value="c.id">
                      {{ c.nombre }}
                    </ion-select-option>
                  </ion-select>
                </div>

                <div class="field-group">
                  <label>Distrito</label>
                  <ion-select
                    v-model="distritoId"
                    interface="popover"
                    placeholder="Todos"
                    class="popover-select"
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

              <div class="popover-footer">
                <button class="btn-text-reset" @click="limpiarFiltros">
                  <ion-icon :icon="refreshOutline"></ion-icon> Restablecer
                </button>
                <button class="btn-apply" @click="aplicarFiltrosYScroll">Aplicar Filtros</button>
              </div>
            </div>
          </transition>
        </div>

        <nav class="hero-nav-links" aria-label="Navegación principal">
          <button @click="scrollToCanchas">Canchas</button>
          <button @click="scrollToCanchas">Horarios</button>
          <button @click="irAlPanel">Propietarios</button>
        </nav>

        <!-- Acciones del Navbar -->
        <div class="navbar-actions">
          <button class="btn-owner-pill" @click="irAlPanel">
            <span>Portal Propietarios</span>
            <div class="pill-arrow">
              <ion-icon :icon="arrowForwardOutline"></ion-icon>
            </div>
          </button>
        </div>
      </div>
    </header>

    <ion-content :fullscreen="true" class="sportra-content" ref="contentRef">
      <!-- Hero Section con Fondo de Fútbol Difuminado -->
      <section class="hero-section">
        <div class="hero-bg-image"></div>
        <div class="hero-bg-overlay"></div>
        
        <div class="hero-container">
          <div class="hero-main-content">
            <div class="hero-badge">La red de canchas de Costa Rica</div>
            
            <!-- Título Principal en Blanco Liso -->
            <h1 class="hero-title">
              Tu pasión vive en la cancha <br />
              <span class="font-serif-italic">Reserva tu próximo partido</span>
            </h1>
            
            <p class="hero-subtitle">
              Encuentra y reserva la mejor cancha sintética para tu mejenga. Horarios en tiempo real, instalaciones verificadas y confirmación inmediata sin llamadas.
            </p>

            <div class="hero-cta-group">
              <button class="btn-join-now" @click="scrollToCanchas">
                <span>Armar Mejenga</span>
                <div class="circle-icon-btn">
                  <ion-icon :icon="arrowForwardOutline"></ion-icon>
                </div>
              </button>

              <div class="community-avatars">
                <div class="avatar-stack">
                  <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80" alt="Jugador" />
                  <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=100&q=80" alt="Jugador" />
                  <img src="https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?auto=format&fit=crop&w=100&q=80" alt="Jugador" />
                  <span class="avatar-more">+2k</span>
                </div>
              </div>
            </div>
          </div>

          <aside class="hero-feature-card" v-if="complejosOrdenados.length > 0">
            <div class="feature-card-topline">
              <span>Disponible cerca de ti</span>
              <ion-icon :icon="footballOutline"></ion-icon>
            </div>
            <div class="feature-image-wrap">
              <img :src="getComplejoImage(complejosOrdenados[0])" :alt="complejosOrdenados[0].nombre" @error="handleImageError" />
              <span class="feature-live"><i></i> En tiempo real</span>
            </div>
            <div class="feature-card-body">
              <div>
                <h2>{{ complejosOrdenados[0].nombre }}</h2>
                <p><ion-icon :icon="locationOutline"></ion-icon>{{ complejosOrdenados[0].distrito }}, {{ complejosOrdenados[0].canton }}</p>
              </div>
              <strong>₡{{ formatearPrecio(complejosOrdenados[0].precio_desde) }}<small>/hora</small></strong>
            </div>
            <button class="feature-card-link" @click="verDetalle(complejosOrdenados[0].slug)">
              Ver disponibilidad <ion-icon :icon="arrowForwardOutline"></ion-icon>
            </button>
          </aside>
        </div>
      </section>

      <!-- Main Container de Resultados -->
      <main class="main-container" id="seccion-canchas" ref="seccionCanchasRef">
        <section class="results-section">
          <div class="results-header">
            <div class="results-meta">
              <h2 class="results-title">
                {{ complejosFiltrados.length }} {{ complejosFiltrados.length === 1 ? 'Cancha Disponible' : 'Canchas Disponibles' }}
              </h2>
              <div v-if="filtrosActivos" class="active-filter-badge">
                <span class="dot"></span> Filtros Activos
              </div>
            </div>

            <div class="sorting-controls" v-if="complejosFiltrados.length > 1">
              <div class="select-wrapper">
                <ion-icon :icon="swapVerticalOutline" class="sort-icon"></ion-icon>
                <select v-model="orden" class="sort-select">
                  <option value="nombre">Nombre (A-Z)</option>
                  <option value="precio-asc">Precio: Menor a Mayor</option>
                  <option value="precio-desc">Precio: Mayor a Menor</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Skeleton Loading -->
          <div v-if="cargando" class="courts-grid">
            <div v-for="i in 6" :key="i" class="court-card skeleton-card">
              <div class="skeleton-img shimmer"></div>
              <div class="card-body">
                <div class="skeleton-line title shimmer"></div>
                <div class="skeleton-line subtitle shimmer"></div>
                <div class="card-footer-skeleton">
                  <div class="skeleton-line price shimmer"></div>
                  <div class="skeleton-btn shimmer"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Grilla de Tarjetas -->
          <div v-else-if="complejosFiltrados.length > 0" class="courts-grid">
            <article
              v-for="complejo in complejosOrdenados"
              :key="complejo.id"
              class="court-card"
              @click="verDetalle(complejo.slug)"
            >
              <div class="card-img-wrapper">
                <img
                  :src="getComplejoImage(complejo)"
                  :alt="complejo.nombre"
                  loading="lazy"
                  @error="handleImageError"
                />
                <div class="card-badges">
                  <span class="courts-chip">
                    <ion-icon :icon="footballOutline"></ion-icon>
                    {{ complejo.total_canchas }} {{ complejo.total_canchas === 1 ? 'Cancha' : 'Canchas' }}
                  </span>
                </div>
              </div>

              <div class="card-body">
                <div class="card-header-info">
                  <h3 class="card-title">{{ complejo.nombre }}</h3>
                  <div class="card-location">
                    <ion-icon :icon="locationOutline" class="loc-icon"></ion-icon>
                    <span>{{ complejo.distrito }}, {{ complejo.canton }}</span>
                  </div>
                </div>

                <div class="card-footer">
                  <div class="price-block">
                    <span class="price-caption">Precio por hora</span>
                    <div class="price-amount">
                      <strong>₡{{ formatearPrecio(complejo.precio_desde) }}</strong>
                    </div>
                  </div>

                  <button class="btn-card-action">
                    <span>Reservar Hora</span>
                    <ion-icon :icon="arrowForwardOutline"></ion-icon>
                  </button>
                </div>
              </div>
            </article>
          </div>

          <!-- Empty State -->
          <div v-else class="empty-box">
            <div class="empty-icon-wrapper">
              <ion-icon :icon="searchOutline"></ion-icon>
            </div>
            <h3>Sin Resultados de Canchas</h3>
            <p v-if="busquedaTexto">No se encontraron complejos deportivos para "{{ busquedaTexto }}".</p>
            <p v-else>No hay canchas disponibles con los filtros aplicados en esta zona.</p>
            <button class="btn-clear-filters" @click="limpiarFiltros">
              <ion-icon :icon="refreshOutline"></ion-icon>
              <span>Restablecer Filtros</span>
            </button>
          </div>

          <!-- Footer Estilizado -->
          <footer class="footer">
            <div class="footer-brand">
              <ion-icon :icon="footballOutline" class="footer-logo-icon"></ion-icon>
              <span class="footer-title">Sportra</span>
            </div>
            <span class="copyright">© 2026 Sportra Costa Rica. La mejor app para tu mejenga.</span>
          </footer>
        </section>
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
    c.canton?.toLowerCase().includes(query) ||
    c.distrito?.toLowerCase().includes(query)
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
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@1,600;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

ion-content.sportra-content {
  --background: #07140d;
  font-family: 'Plus Jakarta Sans', sans-serif;
}

/* NAVBAR FLOTANTE GLASSMORPHISM */
.navbar-wrapper {
  position: fixed;
  top: 1rem;
  left: 0;
  right: 0;
  z-index: 1000;
  padding: 0 1.5rem;
}

.navbar-glass {
  max-width: 1200px;
  margin: 0 auto;
  background: rgba(20, 47, 29, 0.72);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(198, 243, 212, 0.26);
  border-radius: 99px;
  padding: 0.6rem 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}

/* LOGO SPORTRA VERDE LISO */
.logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.logo-icon {
  font-size: 1.6rem;
  color: #10b981;
}

.logo-text {
  font-weight: 800;
  font-size: 1.35rem;
  color: #10b981;
  letter-spacing: -0.02em;
}

/* SEARCH CAPSULE CENTRAL */
.search-capsule {
  position: relative;
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(16, 185, 129, 0.3);
  border-radius: 99px;
  padding: 0.25rem 0.4rem 0.25rem 1.1rem;
  cursor: pointer;
  transition: all 0.25s ease;
}

.search-capsule:hover, .search-capsule.is-active {
  background: rgba(16, 185, 129, 0.15);
  border-color: #10b981;
}

.hero-nav-links {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  margin-left: auto;
  margin-right: 1.25rem;
}

.hero-nav-links button {
  border: 0;
  padding: 0;
  background: transparent;
  color: rgba(255, 255, 255, 0.78);
  font-size: 0.72rem;
  cursor: pointer;
  transition: color 0.2s ease;
}

.hero-nav-links button:hover { color: #ffffff; }

.capsule-section {
  display: flex;
  flex-direction: column;
  padding-right: 0.85rem;
}

.capsule-label {
  font-size: 0.6rem;
  font-weight: 800;
  text-transform: uppercase;
  color: #10b981;
}

.capsule-value {
  font-size: 0.8rem;
  color: #ffffff;
  font-weight: 600;
  max-width: 120px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.capsule-divider {
  width: 1px;
  height: 20px;
  background-color: rgba(255, 255, 255, 0.15);
  margin-right: 0.85rem;
}

.capsule-search-btn {
  background: #16a34a;
  color: #ffffff;
  border: none;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: transform 0.2s, background 0.2s;
}

.capsule-search-btn:hover {
  background: #10b981;
  transform: scale(1.08);
}

/* BOTÓN PORTAL PROPIETARIOS */
.btn-owner-pill {
  background: #ffffff;
  color: #06120b;
  border: none;
  padding: 0.45rem 0.5rem 0.45rem 1.1rem;
  border-radius: 99px;
  font-weight: 700;
  font-size: 0.825rem;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-owner-pill:hover {
  background: #e2e8f0;
  transform: translateY(-1px);
}

.pill-arrow {
  background: #06120b;
  color: #ffffff;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
}

/* POPOVER FILTROS */
.search-popover {
  position: absolute;
  top: calc(100% + 16px);
  left: 50%;
  transform: translateX(-50%);
  width: 480px;
  background: #0a1f13;
  border-radius: 20px;
  padding: 1.5rem;
  box-shadow: 0 25px 50px rgba(0,0,0,0.85);
  border: 1px solid rgba(16, 185, 129, 0.3);
  z-index: 1001;
  color: #ffffff;
}

.popover-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
}

.popover-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 700;
  font-size: 0.95rem;
}

.title-icon { color: #10b981; }

.btn-close-popover {
  background: rgba(255,255,255,0.08);
  border: none;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ffffff;
  cursor: pointer;
}

.popover-field label, .field-group label {
  font-size: 0.68rem;
  font-weight: 800;
  color: #a7f3d0;
  display: block;
  margin-bottom: 0.4rem;
  text-transform: uppercase;
}

.popover-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.popover-input-wrapper .input-icon {
  position: absolute;
  left: 0.85rem;
  color: #64748b;
}

.popover-input-wrapper input {
  width: 100%;
  height: 42px;
  background: #06120b;
  border: 1px solid rgba(16, 185, 129, 0.3);
  border-radius: 10px;
  padding: 0 2.25rem 0 2.5rem;
  font-size: 0.85rem;
  color: #ffffff;
  outline: none;
}

.popover-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.65rem;
  margin: 1rem 0 1.5rem;
}

.popover-select {
  --background: #06120b;
  --color: #ffffff;
  border: 1px solid rgba(16, 185, 129, 0.3);
  border-radius: 10px;
  height: 42px;
  font-size: 0.825rem;
}

.popover-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.btn-text-reset {
  background: transparent;
  border: none;
  color: #94a3b8;
  font-size: 0.85rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.btn-apply {
  background: #16a34a;
  color: #ffffff;
  border: none;
  padding: 0.65rem 1.35rem;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.85rem;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-apply:hover {
  background: #10b981;
}

/* HERO SECTION CON FONDO VERDE FUTBOL */
.hero-section {
  position: relative;
  min-height: 790px;
  margin: 0;
  border: 0;
  border-radius: 0;
  padding: 10rem max(4rem, calc((100% - 1200px) / 2)) 7rem;
  display: flex;
  align-items: center;
  overflow: hidden;
  box-shadow: none;
}

.hero-bg-image {
  position: absolute;
  inset: -20px;
  background-image: url('https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=1920&q=80');
  background-size: cover;
  background-position: center center;
  filter: brightness(0.7) saturate(0.9);
  transform: scale(1.05);
  z-index: 0;
}

.hero-bg-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    90deg,
    rgba(4, 18, 10, 0.88) 0%,
    rgba(4, 18, 10, 0.52) 48%,
    rgba(4, 18, 10, 0.1) 100%
  );
  z-index: 1;
}

.hero-container {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: minmax(0, 1fr) 300px;
  align-items: center;
  gap: 5rem;
}

.hero-badge {
  display: inline-block;
  background: rgba(16, 185, 129, 0.15);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(16, 185, 129, 0.4);
  border-radius: 99px;
  padding: 0.4rem 1.1rem;
  font-size: 0.78rem;
  font-weight: 700;
  color: #a7f3d0;
  margin-bottom: 1.75rem;
  letter-spacing: 0.02em;
}

/* TÍTULO PRINCIPAL - BLANCO LISO */
.hero-title {
  max-width: 700px;
  font-size: clamp(2.8rem, 5.2vw, 5rem);
  font-weight: 800;
  color: #ffffff;
  line-height: 1.08;
  letter-spacing: -0.03em;
  margin: 0 0 1.25rem;
}

.font-serif-italic {
  font-family: 'Playfair Display', Georgia, serif;
  font-style: italic;
  font-weight: 600;
  color: #ffffff;
}

.hero-subtitle {
  color: rgba(248, 250, 252, 0.9);
  font-size: 1.1rem;
  max-width: 530px;
  line-height: 1.6;
  margin-bottom: 2.25rem;
}

.hero-feature-card {
  align-self: end;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 16px;
  background: rgba(20, 48, 29, 0.88);
  backdrop-filter: blur(14px);
  box-shadow: 0 18px 34px rgba(0, 0, 0, 0.28);
}

.feature-card-topline, .feature-card-body, .feature-card-link {
  display: flex;
  align-items: center;
}

.feature-card-topline {
  justify-content: space-between;
  padding: 0.8rem 1rem;
  color: #d7f7df;
  font-size: 0.68rem;
  font-weight: 700;
}

.feature-card-topline ion-icon { color: #f8ce3a; font-size: 1.1rem; }
.feature-image-wrap { position: relative; height: 142px; }
.feature-image-wrap img { width: 100%; height: 100%; object-fit: cover; }
.feature-live { position: absolute; left: 0.75rem; bottom: 0.7rem; padding: 0.3rem 0.5rem; border-radius: 5px; background: rgba(4, 18, 10, 0.78); color: #c8f8d5; font-size: 0.62rem; font-weight: 700; }
.feature-live i { display: inline-block; width: 6px; height: 6px; margin-right: 0.3rem; border-radius: 50%; background: #32d583; }
.feature-card-body { justify-content: space-between; gap: 0.6rem; padding: 0.9rem 1rem 0.5rem; }
.feature-card-body h2 { margin: 0 0 0.3rem; color: #ffffff; font-size: 0.95rem; }
.feature-card-body p { display: flex; align-items: center; gap: 0.25rem; margin: 0; color: #a9c5af; font-size: 0.67rem; }
.feature-card-body p ion-icon { color: #55d38c; }
.feature-card-body strong { color: #bdf4c9; font-size: 0.9rem; white-space: nowrap; }
.feature-card-body small { color: #9cbaa4; font-size: 0.6rem; font-weight: 500; }
.feature-card-link { justify-content: space-between; width: calc(100% - 2rem); margin: 0.45rem 1rem 1rem; padding: 0.65rem 0.75rem; border: 1px solid rgba(101, 225, 151, 0.45); border-radius: 8px; background: rgba(16, 185, 129, 0.14); color: #ffffff; font-size: 0.7rem; font-weight: 700; cursor: pointer; }
.feature-card-link ion-icon { color: #83e6a7; }

.hero-cta-group {
  display: flex;
  align-items: center;
  gap: 1.75rem;
}

.btn-join-now {
  background: #ffffff;
  color: #06120b;
  border: none;
  padding: 0.5rem 0.6rem 0.5rem 1.4rem;
  border-radius: 99px;
  font-weight: 800;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.85rem;
  cursor: pointer;
  transition: transform 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.btn-join-now:hover {
  transform: scale(1.04);
}

.circle-icon-btn {
  background: #16a34a;
  color: #ffffff;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* AVATARES */
.avatar-stack {
  display: flex;
  align-items: center;
}

.avatar-stack img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 2px solid #06120b;
  margin-left: -12px;
  object-fit: cover;
}

.avatar-stack img:first-child { margin-left: 0; }

.avatar-more {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(16, 185, 129, 0.25);
  backdrop-filter: blur(8px);
  border: 2px solid #06120b;
  margin-left: -12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 800;
  color: #ffffff;
}

/* MAIN CONTAINER & GRILLA DE COMPLEJOS */
.main-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem 5rem;
  scroll-margin-top: 80px;
}

.results-section {
  background: transparent;
  border: 0;
  border-radius: 0;
  padding: 3rem 0 0;
}

.results-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.results-title {
  font-size: 1.3rem;
  font-weight: 800;
  color: #ffffff;
  margin: 0;
}

.active-filter-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.75rem;
  color: #10b981;
  background: rgba(16, 185, 129, 0.15);
  padding: 0.2rem 0.55rem;
  border-radius: 6px;
  font-weight: 700;
  margin-top: 0.25rem;
}

.sort-select {
  background: #06120b;
  border: 1px solid rgba(16, 185, 129, 0.3);
  border-radius: 10px;
  padding: 0.55rem 1rem 0.55rem 2.2rem;
  font-size: 0.825rem;
  font-weight: 600;
  color: #ffffff;
  outline: none;
}

/* COURTS GRID */
.courts-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.75rem;
  margin-bottom: 3rem;
}

.court-card {
  background: #06120b;
  border-radius: 16px;
  border: 1px solid rgba(16, 185, 129, 0.2);
  overflow: hidden;
  cursor: pointer;
  transition: all 0.25s ease;
  display: flex;
  flex-direction: column;
}

.court-card:hover {
  transform: translateY(-4px);
  border-color: #10b981;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.6);
}

.card-img-wrapper {
  position: relative;
  aspect-ratio: 16 / 9;
  background-color: #0f2d1c;
  overflow: hidden;
}

.card-img-wrapper img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.card-badges {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
}

.courts-chip {
  background: rgba(6, 18, 11, 0.85);
  backdrop-filter: blur(8px);
  color: #ffffff;
  padding: 0.3rem 0.65rem;
  border-radius: 6px;
  font-size: 0.725rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

.card-body {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.card-title {
  margin: 0 0 0.35rem 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: #ffffff;
}

.card-location {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  color: #94a3b8;
  font-size: 0.825rem;
  margin-bottom: 1.25rem;
}

.loc-icon { color: #10b981; }

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-top: auto;
  padding-top: 0.85rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.price-caption {
  display: block;
  font-size: 0.68rem;
  color: #64748b;
  text-transform: uppercase;
  font-weight: 700;
}

.price-amount strong {
  font-size: 1.2rem;
  color: #10b981;
  font-weight: 800;
}

.btn-card-action {
  background: rgba(255, 255, 255, 0.08);
  color: #ffffff;
  border: none;
  padding: 0.5rem 0.9rem;
  border-radius: 8px;
  font-size: 0.775rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.35rem;
  transition: background 0.2s;
}

.court-card:hover .btn-card-action {
  background: #16a34a;
}

/* FOOTER */
.footer {
  margin-top: 1rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.footer-brand { display: flex; align-items: center; gap: 0.5rem; }
.footer-logo-icon { font-size: 1.2rem; color: #10b981; }
.footer-title { font-weight: 800; color: #ffffff; font-size: 0.85rem; }
.copyright { font-size: 0.775rem; color: #64748b; }

/* RESPONSIVE */
@media (max-width: 1024px) {
  .courts-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
  .hero-section { min-height: 760px; margin: 0; padding: 8rem 1.25rem 3rem; border-radius: 0; }
  .navbar-wrapper { padding: 0 0.65rem; }
  .navbar-glass { flex-direction: column; gap: 0.75rem; border-radius: 20px; }
  .hero-nav-links { display: none; }
  .search-capsule { width: 100%; justify-content: space-between; }
  .hero-container { grid-template-columns: 1fr; gap: 2rem; align-content: center; }
  .hero-title { font-size: clamp(2.6rem, 12vw, 4rem); }
  .hero-subtitle { font-size: 0.95rem; }
  .hero-feature-card { width: min(100%, 330px); justify-self: center; }
  .courts-grid { grid-template-columns: 1fr; }
}
</style>