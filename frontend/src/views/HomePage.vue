<template>
  <ion-page class="sportra-landing">
    <ion-content :fullscreen="true" class="sportra-content">
      
      <!-- Navbar Perfectamente Alineado -->
      <header class="navbar">
        <div class="navbar-container">
          <div class="logo" @click="limpiarFiltros" role="button" tabindex="0" title="Restablecer inicio">
            <div class="logo-icon" aria-hidden="true"></div>
            <span class="logo-text">Sportra</span>
          </div>

          <div class="navbar-actions">
            <button class="btn-owner" @click="irAlPanel" aria-label="Acceso Propietarios">
              <ion-icon name="person-circle-outline" class="btn-icon"></ion-icon>
              <span>Acceso propietarios</span>
            </button>
          </div>
        </div>
      </header>

      <!-- Hero Section con Layout Equilibrado -->
      <section class="hero-section">
        <div class="hero-bg-glow"></div>
        <div class="hero-content">
          
          <div class="hero-badge">
            <span class="pulse-dot"></span>
            <span>Reserva directa en Costa Rica</span>
          </div>

          <h1 class="hero-title">
            Encuentra y reserva <span class="highlight-text">tu cancha</span>
          </h1>
          <p class="hero-subtitle">
            Explora complejos deportivos verificados según tu ubicación y disponibilidad.
          </p>

          <!-- Tarjeta de Búsqueda Flotante e Impecable -->
          <div class="search-card">
            
            <!-- Buscador Global -->
            <div class="search-input-wrapper">
              <ion-icon name="search-outline" class="search-icon"></ion-icon>
              <input
                v-model="busquedaTexto"
                type="text"
                class="text-input"
                placeholder="Buscar complejo por nombre..."
              />
              <button
                v-if="busquedaTexto"
                class="btn-clear-text"
                @click="busquedaTexto = ''"
                aria-label="Limpiar texto"
              >
                <ion-icon name="close-circle"></ion-icon>
              </button>
            </div>

            <!-- Grid de Selectores Geográficos de Altura Unificada -->
            <div class="search-fields">
              
              <!-- Selector Provincia -->
              <div class="field-group">
                <label class="field-label">
                  <ion-icon name="map-outline" class="field-icon"></ion-icon>
                  <span>Provincia</span>
                </label>
                <div class="select-wrapper" :class="{ 'has-value': provinciaId }">
                  <ion-select
                    v-model="provinciaId"
                    interface="popover"
                    placeholder="Todas las provincias"
                    @ionChange="onProvinciaChange"
                  >
                    <ion-select-option :value="null">Todas las provincias</ion-select-option>
                    <ion-select-option v-for="p in provincias" :key="p.id" :value="p.id">
                      {{ p.nombre }}
                    </ion-select-option>
                  </ion-select>
                </div>
              </div>

              <!-- Selector Cantón -->
              <div class="field-group">
                <label class="field-label">
                  <ion-icon name="location-outline" class="field-icon"></ion-icon>
                  <span>Cantón</span>
                </label>
                <div class="select-wrapper" :class="{ 'has-value': cantonId, 'is-disabled': !provinciaId && cantones.length === 0 }">
                  <ion-select
                    v-model="cantonId"
                    interface="popover"
                    placeholder="Todos los cantones"
                    :disabled="!provinciaId && cantones.length === 0"
                    @ionChange="onCantonChange"
                  >
                    <ion-select-option :value="null">Todos los cantones</ion-select-option>
                    <ion-select-option v-for="c in cantones" :key="c.id" :value="c.id">
                      {{ c.nombre }}
                    </ion-select-option>
                  </ion-select>
                </div>
              </div>

              <!-- Selector Distrito -->
              <div class="field-group">
                <label class="field-label">
                  <ion-icon name="navigate-outline" class="field-icon"></ion-icon>
                  <span>Distrito</span>
                </label>
                <div class="select-wrapper" :class="{ 'has-value': distritoId, 'is-disabled': !cantonId && distritos.length === 0 }">
                  <ion-select
                    v-model="distritoId"
                    interface="popover"
                    placeholder="Todos los distritos"
                    :disabled="!cantonId && distritos.length === 0"
                    @ionChange="cargarComplejos"
                  >
                    <ion-select-option :value="null">Todos los distritos</ion-select-option>
                    <ion-select-option v-for="d in distritos" :key="d.id" :value="d.id">
                      {{ d.nombre }}
                    </ion-select-option>
                  </ion-select>
                </div>
              </div>

            </div>

            <!-- Footer Interno de la Tarjeta -->
            <div v-if="filtrosActivos" class="search-footer">
              <div class="active-badge">
                <span class="active-dot"></span>
                <span>Filtros aplicados</span>
              </div>
              <button class="btn-reset" @click="limpiarFiltros">
                <ion-icon name="refresh-outline"></ion-icon>
                <span>Restablecer ubicación</span>
              </button>
            </div>

          </div>
        </div>
      </section>

      <!-- Main Container: Grilla y Resultados -->
      <main class="main-container">
        
        <!-- Header de Resultados -->
        <div class="section-header">
          <div class="section-title-group">
            <h2>Complejos Deportivos</h2>
            <span class="results-badge" v-if="!cargando">
              {{ complejosFiltrados.length }} {{ complejosFiltrados.length === 1 ? 'disponible' : 'disponibles' }}
            </span>
          </div>

          <div class="sorting-controls" v-if="complejosFiltrados.length > 1">
            <span class="sort-label">Ordenar por:</span>
            <div class="sort-select-wrapper">
              <select id="sort-select" v-model="orden" class="sort-select">
                <option value="nombre">Nombre (A-Z)</option>
                <option value="precio-asc">Precio: Menor a Mayor</option>
                <option value="precio-desc">Precio: Mayor a Menor</option>
              </select>
              <ion-icon name="chevron-down-outline" class="sort-arrow"></ion-icon>
            </div>
          </div>
        </div>

        <!-- Estado 1: Loading Skeletons perfectamente estructurados -->
        <div v-if="cargando" class="courts-grid">
          <div v-for="i in 6" :key="i" class="court-card skeleton-card">
            <div class="skeleton-img"></div>
            <div class="card-body">
              <div class="skeleton-line title"></div>
              <div class="skeleton-line subtitle"></div>
              <div class="card-footer-skeleton">
                <div class="skeleton-line price"></div>
                <div class="skeleton-btn"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Estado 2: Tarjetas de Complejos Acomodadas -->
        <div v-else-if="complejosFiltrados.length > 0" class="courts-grid">
          <article
            v-for="complejo in complejosOrdenados"
            :key="complejo.id"
            class="court-card"
            @click="verDetalle(complejo.slug)"
          >
            <div class="card-img-wrapper">
              <img
                :src="complejo.logo_url || fallbackImage"
  :alt="complejo.nombre"
  loading="lazy"
  @error="handleImageError"
              />
              <div class="card-badges">
                <span class="courts-chip">
                  <ion-icon name="football-outline"></ion-icon>
                  {{ complejo.total_canchas }} {{ complejo.total_canchas === 1 ? 'cancha' : 'canchas' }}
                </span>
              </div>
            </div>

            <div class="card-body">
              <h3 class="card-title">{{ complejo.nombre }}</h3>
              <div class="card-location">
                <ion-icon name="location-sharp" class="loc-icon"></ion-icon>
                <span>{{ complejo.distrito }}, {{ complejo.canton }}</span>
              </div>

              <div class="card-footer">
                <div class="price-block">
                  <span class="price-caption">Precio desde</span>
                  <div class="price-amount">
                    <strong>₡{{ formatearPrecio(complejo.precio_desde) }}</strong>
                    <small>/hr</small>
                  </div>
                </div>

                <button class="btn-card-action">
                  <span>Ver complejo</span>
                  <ion-icon name="arrow-forward-outline"></ion-icon>
                </button>
              </div>
            </div>
          </article>
        </div>

        <!-- Estado 3: Sin Resultados -->
        <div v-else class="state-container empty-box">
          <div class="empty-icon-wrapper">
            <ion-icon name="search-outline"></ion-icon>
          </div>
          <h3>No se encontraron resultados</h3>
          <p v-if="busquedaTexto">No coinciden complejos con "<strong>{{ busquedaTexto }}</strong>".</p>
          <p v-else>No hay instalaciones disponibles en la ubicación seleccionada.</p>
          <button class="btn-clear-filters" @click="limpiarFiltros">
            <ion-icon name="refresh-outline"></ion-icon>
            <span>Restablecer filtros</span>
          </button>
        </div>

      </main>

      <!-- Footer Slim Alineado -->
      <footer class="footer">
        <div class="footer-content">
          <div class="footer-brand">
            <div class="logo-icon-sm"></div>
            <span class="footer-title">Sportra CR</span>
          </div>
          <span class="copyright">© 2026 Sportra. Todos los derechos reservados.</span>
        </div>
      </footer>

    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import {
  IonPage, IonContent, IonSelect, IonSelectOption, IonIcon
} from '@ionic/vue';
import complejosService from '@/services/complejos.service';
import geografiaService from '@/services/geografia.service';
import type { Complejo } from '@/types';

const router = useRouter();
const fallbackImage = 'https://images.unsplash.com/photo-1529900748604-07564a03e7a6?auto=format&fit=crop&w=800&q=80';

const provincias = ref<any[]>([]);
const cantones = ref<any[]>([]);
const distritos = ref<any[]>([]);

const provinciaId = ref<number | null>(null);
const cantonId = ref<number | null>(null);
const distritoId = ref<number | null>(null);

const busquedaTexto = ref('');
const orden = ref<'nombre' | 'precio-asc' | 'precio-desc'>('nombre');

const complejos = ref<Complejo[]>([]);
const cargando = ref(true);

const filtrosActivos = computed(() => {
  return provinciaId.value !== null || cantonId.value !== null || distritoId.value !== null || busquedaTexto.value !== '';
});

const complejosFiltrados = computed(() => {
  if (!busquedaTexto.value.trim()) return complejos.value;
  const query = busquedaTexto.value.toLowerCase().trim();
  return complejos.value.filter(c =>
    c.nombre.toLowerCase().includes(query) ||
    c.canton.toLowerCase().includes(query) ||
    c.distrito.toLowerCase().includes(query)
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

function handleImageError(event: Event) {
  (event.target as HTMLImageElement).src = fallbackImage;
}

onMounted(cargarProvincias);
</script>

<style scoped>
/* Reset & CSS Custom Properties */
.sportra-landing {
  --primary-color: #1D5C94;
  --primary-hover: #113D80;
  --primary-light: #edf9ff;
  --primary-border: #bfe4f6;
  --text-primary: #0f172a;
  --text-secondary: #64748b;
  --bg-page: #f4fbff;
  --border-light: #dfeaf7;
  --accent-cyan: #66E3DA;
  --accent-glow: rgba(102, 227, 218, 0.25);
}

ion-content.sportra-content {
  --background: var(--bg-page);
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

/* Header Navbar Centrado y Ajustado */
.navbar {
  background: #ffffff;
  border-bottom: 1px solid var(--border-light);
  position: sticky;
  top: 0;
  z-index: 100;
  backdrop-filter: blur(8px);
}

.navbar-container {
  max-width: 1140px;
  margin: 0 auto;
  padding: 0.85rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo {
  display: inline-flex;
  align-items: center;
  gap: 0.65rem;
  cursor: pointer;
  user-select: none;
}

.logo-icon {
  width: 2.2rem;
  height: 2.2rem;
  background-color: var(--primary-color);
  -webkit-mask: url('/Sportra_Logo.svg') no-repeat center / contain;
  mask: url('/Sportra_Logo.svg') no-repeat center / contain;
  transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.logo:hover .logo-icon {
  transform: scale(1.08);
}

.logo-text {
  font-weight: 800;
  font-size: 1.8rem;
  color: var(--primary-color);
  letter-spacing: -0.03em;
  line-height: 1;
}

.btn-owner {
  background: var(--primary-light);
  border: 1px solid var(--primary-border);
  border-radius: 8px;
  padding: 0.5rem 0.9rem;
  color: var(--primary-color);
  font-size: 0.85rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  cursor: pointer;
  transition: all 0.2s ease;
  height: 38px;
}

.btn-owner:hover {
  background: var(--primary-color);
  color: #ffffff;
  border-color: var(--primary-color);
  box-shadow: 0 4px 12px rgba(0, 102, 255, 0.2);
}

.btn-icon {
  font-size: 1.15rem;
}

/* Hero Section */
.hero-section {
  position: relative;
  background: linear-gradient(135deg, #66E3DA 0%, #3DA2BB 30%, #1D5C94 68%, #08226C 100%);
  color: #ffffff;
  padding: 3.5rem 1.5rem 4.5rem;
  overflow: hidden;
}

.hero-bg-glow {
  position: absolute;
  top: -40%;
  left: 50%;
  transform: translateX(-50%);
  width: 700px;
  height: 700px;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.12) 0%, rgba(255, 255, 255, 0) 70%);
  pointer-events: none;
}

.hero-content {
  position: relative;
  max-width: 820px;
  margin: 0 auto;
  text-align: center;
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(255, 255, 255, 0.15);
  padding: 0.35rem 0.85rem;
  border-radius: 20px;
  font-size: 0.775rem;
  font-weight: 600;
  margin-bottom: 1.25rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.pulse-dot {
  width: 7px;
  height: 7px;
  background-color: #4ade80;
  border-radius: 50%;
  box-shadow: 0 0 8px #4ade80;
}

.hero-title {
  font-size: clamp(2rem, 4vw, 2.6rem);
  font-weight: 800;
  margin: 0 0 0.6rem 0;
  letter-spacing: -0.03em;
  line-height: 1.2;
}

.highlight-text {
  color: #93c5fd;
}

.hero-subtitle {
  font-size: 1rem;
  color: #e0f2fe;
  margin: 0 0 2.25rem 0;
  line-height: 1.5;
}

/* Search Card e Inputs Alineados */
.search-card {
  background: #ffffff;
  border-radius: 16px;
  padding: 1.25rem;
  box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.12);
  text-align: left;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
}

.search-icon {
  position: absolute;
  left: 1rem;
  font-size: 1.15rem;
  color: #94a3b8;
  pointer-events: none;
}

.text-input {
  width: 100%;
  height: 46px;
  border: 1px solid #cbd5e1;
  background: #f8fafc;
  border-radius: 10px;
  padding: 0 2.5rem 0 2.75rem;
  font-size: 0.925rem;
  color: var(--text-primary);
  outline: none;
  transition: all 0.2s ease;
}

.text-input:focus {
  border-color: var(--primary-color);
  background: #ffffff;
  box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.1);
}

.btn-clear-text {
  position: absolute;
  right: 0.75rem;
  background: transparent;
  border: none;
  color: #94a3b8;
  font-size: 1.25rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  padding: 0.2rem;
}

.btn-clear-text:hover {
  color: var(--text-primary);
}

.search-fields {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.85rem;
}

.field-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.field-label {
  font-size: 0.725rem;
  font-weight: 700;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.04em;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}

.field-icon {
  font-size: 0.85rem;
  color: var(--primary-color);
}

.select-wrapper {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  padding: 0 0.5rem;
  height: 46px;
  display: flex;
  align-items: center;
  transition: all 0.2s ease;
}

.select-wrapper.has-value {
  border-color: var(--primary-color);
  background: var(--primary-light);
}

.select-wrapper.is-disabled {
  background: #f1f5f9;
  opacity: 0.65;
}

.select-wrapper ion-select {
  --padding-start: 0;
  --padding-top: 0;
  --padding-bottom: 0;
  width: 100%;
  color: var(--text-primary);
  font-size: 0.875rem;
  font-weight: 600;
}

.search-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1rem;
  padding-top: 0.85rem;
  border-top: 1px solid #f1f5f9;
}

.active-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.775rem;
  color: #0284c7;
  background: #e0f2fe;
  padding: 0.25rem 0.65rem;
  border-radius: 6px;
  font-weight: 700;
}

.active-dot {
  width: 6px;
  height: 6px;
  background-color: #0284c7;
  border-radius: 50%;
}

.btn-reset {
  background: transparent;
  border: none;
  color: var(--text-secondary);
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  transition: color 0.2s ease;
}

.btn-reset:hover {
  color: #ef4444;
}

/* Grilla de Complejos y Contenido Principal */
.main-container {
  max-width: 1140px;
  margin: 0 auto;
  padding: 2.5rem 1.5rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.75rem;
}

.section-title-group {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.section-title-group h2 {
  font-size: 1.4rem;
  font-weight: 800;
  color: var(--text-primary);
  margin: 0;
}

.results-badge {
  background: #e0f2fe;
  color: #0284c7;
  font-size: 0.775rem;
  font-weight: 700;
  padding: 0.2rem 0.6rem;
  border-radius: 12px;
}

.sort-label {
  font-size: 0.825rem;
  color: var(--text-secondary);
  margin-right: 0.4rem;
  font-weight: 600;
}

.sort-select-wrapper {
  position: relative;
  display: inline-flex;
  align-items: center;
}

.sort-select {
  appearance: none;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 0.45rem 2rem 0.45rem 0.75rem;
  background: #ffffff;
  color: var(--text-primary);
  font-size: 0.825rem;
  font-weight: 600;
  outline: none;
  cursor: pointer;
}

.sort-arrow {
  position: absolute;
  right: 0.6rem;
  pointer-events: none;
  color: var(--text-secondary);
  font-size: 0.85rem;
}

/* Layout de Tarjetas (Equal Height & Dynamic Stretch) */
.courts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
  gap: 1.5rem;
}

.court-card {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid var(--border-light);
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.court-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px -6px rgba(0, 0, 0, 0.08);
  border-color: var(--primary-border);
}

.card-img-wrapper {
  position: relative;
  height: 180px;
  background-color: #e2e8f0;
  overflow: hidden;
}

.card-img-wrapper img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.35s ease;
}

.court-card:hover .card-img-wrapper img {
  transform: scale(1.05);
}

.card-badges {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  z-index: 2;
}

.courts-chip {
  background: rgba(15, 23, 42, 0.75);
  backdrop-filter: blur(4px);
  color: #ffffff;
  padding: 0.3rem 0.6rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}

.card-body {
  padding: 1.15rem;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.card-title {
  margin: 0 0 0.4rem 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1.35;
}

.card-location {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  color: var(--text-secondary);
  font-size: 0.825rem;
  margin-bottom: 1.25rem;
}

.loc-icon {
  color: var(--primary-color);
  font-size: 0.95rem;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
  padding-top: 0.85rem;
  border-top: 1px solid #f1f5f9;
}

.price-caption {
  display: block;
  font-size: 0.7rem;
  color: var(--text-secondary);
  text-transform: uppercase;
  font-weight: 700;
}

.price-amount strong {
  font-size: 1.15rem;
  color: var(--primary-color);
  font-weight: 800;
}

.price-amount small {
  color: var(--text-secondary);
}

.btn-card-action {
  background: var(--primary-light);
  color: var(--primary-color);
  border: none;
  padding: 0.5rem 0.85rem;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  transition: all 0.2s ease;
}

.court-card:hover .btn-card-action {
  background: var(--primary-color);
  color: #ffffff;
}

/* Skeletons Animados */
.skeleton-card {
  pointer-events: none;
}

.skeleton-img {
  height: 180px;
  background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-line {
  height: 0.9rem;
  background: #e2e8f0;
  border-radius: 4px;
  margin-bottom: 0.6rem;
}

.skeleton-line.title { width: 70%; height: 1.1rem; }
.skeleton-line.subtitle { width: 50%; }
.card-footer-skeleton {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
  padding-top: 0.85rem;
  border-top: 1px solid #f1f5f9;
}

.skeleton-line.price { width: 35%; margin: 0; }
.skeleton-btn { width: 85px; height: 32px; background: #e2e8f0; border-radius: 8px; }

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* Empty State */
.state-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3.5rem 1rem;
  text-align: center;
}

.empty-box {
  background: #ffffff;
  border-radius: 14px;
  border: 1px dashed #cbd5e1;
}

.empty-icon-wrapper {
  background: var(--primary-light);
  color: var(--primary-color);
  width: 54px;
  height: 54px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  margin-bottom: 0.85rem;
}

.empty-box h3 {
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0 0 0.4rem 0;
}

.empty-box p {
  color: var(--text-secondary);
  font-size: 0.875rem;
  margin: 0 0 1.25rem 0;
}

.btn-clear-filters {
  background: var(--primary-color);
  color: #ffffff;
  border: none;
  padding: 0.6rem 1.2rem;
  border-radius: 8px;
  font-weight: 700;
  font-size: 0.825rem;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  transition: background-color 0.2s ease;
}

.btn-clear-filters:hover {
  background: var(--primary-hover);
}

/* Footer Slim */
.footer {
  background: #ffffff;
  border-top: 1px solid var(--border-light);
  padding: 1.25rem 1.5rem;
}

.footer-content {
  max-width: 1140px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.footer-brand {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.logo-icon-sm {
  width: 1.25rem;
  height: 1.25rem;
  background-color: var(--primary-color);
  -webkit-mask: url('/Sportra_Logo.svg') no-repeat center / contain;
  mask: url('/Sportra_Logo.svg') no-repeat center / contain;
}

.footer-title {
  font-weight: 800;
  color: var(--text-primary);
  font-size: 0.9rem;
}

.copyright {
  font-size: 0.8rem;
  color: var(--text-secondary);
}

/* Responsividad Fina */
@media (max-width: 768px) {
  .search-fields {
    grid-template-columns: 1fr;
  }
  
  .footer-content {
    flex-direction: column;
    gap: 0.75rem;
    text-align: center;
  }
}
</style>