<template>
  <ion-page class="sportra-landing">
    <!-- Header Sticky con Buscador Flotante -->
    <header class="navbar">
      <div class="navbar-container">
        <!-- Logo Sportra -->
        <div class="logo" @click="limpiarFiltros" role="button" tabindex="0">
          <div class="logo-icon" aria-hidden="true"></div>
          <span class="logo-text">Sportra</span>
        </div>

        <!-- Search Capsule Central -->
        <div class="search-capsule" @click="toggleDropdownSearch">
          <div class="capsule-section">
            <span class="capsule-label">Dónde</span>
            <span class="capsule-value">{{ ubicacionTextoSeleccionada }}</span>
          </div>

          <div class="capsule-divider"></div>

          <div class="capsule-section">
            <span class="capsule-label">Búsqueda</span>
            <span class="capsule-value">{{ busquedaTexto || 'Nombre del complejo...' }}</span>
          </div>

          <button class="capsule-search-btn" aria-label="Buscar">
            <ion-icon name="search-outline"></ion-icon>
          </button>

          <!-- Dropdown Flotante -->
          <div v-if="searchOpen" class="search-popover" @click.stop>
            <div class="popover-header">
              <span>Filtros de Ubicación</span>
              <button class="btn-close-popover" @click="searchOpen = false">
                <ion-icon name="close-outline"></ion-icon>
              </button>
            </div>

            <div class="popover-field">
              <label>Buscar por Nombre</label>
              <div class="popover-input-wrapper">
                <ion-icon name="search-outline"></ion-icon>
                <input 
                  v-model="busquedaTexto" 
                  type="text" 
                  placeholder="Ej: Complejo Maracaná..." 
                />
                <ion-icon 
                  v-if="busquedaTexto" 
                  name="close-circle" 
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
              <button class="btn-text-reset" @click="limpiarFiltros">Restablecer</button>
              <button class="btn-apply" @click="searchOpen = false">Aplicar Búsqueda</button>
            </div>
          </div>
        </div>

        <div class="navbar-actions">
          <button class="btn-owner" @click="irAlPanel">
            <ion-icon name="person-circle-outline" class="btn-icon"></ion-icon>
            <span class="btn-owner-text">Acceso propietarios</span>
          </button>
        </div>
      </div>
    </header>

    <ion-content :fullscreen="true" class="sportra-content">
      <main class="main-container">
        
        <section class="results-section">
          <div class="results-header">
            <div class="results-meta">
              <h1 class="results-title">
                {{ complejosFiltrados.length }} {{ complejosFiltrados.length === 1 ? 'complejo deportivo' : 'complejos deportivos' }}
              </h1>
              <div v-if="filtrosActivos" class="active-filter-badge">
                <span class="dot"></span> Filtros aplicados
              </div>
            </div>

            <div class="sorting-controls" v-if="complejosFiltrados.length > 1">
              <select v-model="orden" class="sort-select">
                <option value="nombre">Ordenar por nombre</option>
                <option value="precio-asc">Precio: Menor a Mayor</option>
                <option value="precio-desc">Precio: Mayor a Menor</option>
              </select>
            </div>
          </div>

          <!-- Loading Skeleton -->
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
                :src="complejo.imagen_url || fallbackImage"
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
                    </div>
                  </div>

                  <button class="btn-card-action">
                    <span>Ver cancha</span>
                  </button>
                </div>
              </div>
            </article>
          </div>

          <!-- Sin resultados -->
          <div v-else class="empty-box">
            <div class="empty-icon-wrapper">
              <ion-icon name="search-outline"></ion-icon>
            </div>
            <h3>Sin resultados coincidentes</h3>
            <p v-if="busquedaTexto">No se encontraron complejos para "{{ busquedaTexto }}".</p>
            <p v-else>No hay complejos disponibles en esta zona.</p>
            <button class="btn-clear-filters" @click="limpiarFiltros">
              <ion-icon name="refresh-outline"></ion-icon>
              <span>Restablecer filtros</span>
            </button>
          </div>

          <footer class="footer">
            <div class="footer-brand">
              <div class="logo-icon-sm"></div>
              <span class="footer-title">Sportra CR</span>
            </div>
            <span class="copyright">© 2026 Sportra. Todos los derechos reservados.</span>
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

import complejosService from '@/services/complejos.service';
import geografiaService from '@/services/geografia.service';
import type { Complejo } from '@/types';

const router = useRouter();
const fallbackImage = 'https://images.unsplash.com/photo-1529900748604-07564a03e7a6?auto=format&fit=crop&w=800&q=80';

// Estado de UI y Filtros
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
  return 'Toda Costa Rica';
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

function handleImageError(event: Event) {
  (event.target as HTMLImageElement).src = fallbackImage;
}

onMounted(async () => {
  await cargarProvincias();
});
</script>

<style scoped>
/* Variables de Marca Sportra */
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
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

/* NAVBAR */
.navbar {
  background: #ffffff;
  border-bottom: 1px solid var(--border-light);
  position: sticky;
  top: 0;
  z-index: 1000;
  height: 76px;
  display: flex;
  align-items: center;
}

.navbar-container {
  width: 100%;
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  cursor: pointer;
}

.logo-icon {
  width: 2.2rem;
  height: 2.2rem;
  background-color: var(--primary-color);
  -webkit-mask: url('/Sportra_Logo.svg') no-repeat center / contain;
  mask: url('/Sportra_Logo.svg') no-repeat center / contain;
}

.logo-text {
  font-weight: 800;
  font-size: 1.5rem;
  color: var(--primary-color);
  letter-spacing: -0.03em;
}

/* SEARCH CAPSULE (Pill) */
.search-capsule {
  position: relative;
  display: flex;
  align-items: center;
  background: #ffffff;
  border: 1px solid var(--border-light);
  border-radius: 40px;
  padding: 0.35rem 0.5rem 0.35rem 1.25rem;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
  cursor: pointer;
  transition: all 0.2s ease;
}

.search-capsule:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
  border-color: #cbd5e1;
}

.capsule-section {
  display: flex;
  flex-direction: column;
  padding-right: 1rem;
}

.btn-icon {
  font-size: 1.15rem;
}

/* Hero Section */
.hero-section {
  position: relative;
  background: linear-gradient(135deg, #0066ff 0%, #004bbb 100%);
  color: #ffffff;
  padding: 3.5rem 1.5rem 4.5rem;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.2;
}

.capsule-divider {
  width: 1px;
  height: 22px;
  background-color: var(--border-light);
  margin-right: 1rem;
}

.capsule-search-btn {
  background: var(--primary-color);
  color: #ffffff;
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}

/* POPOVER DE BÚSQUEDA */
.search-popover {
  position: absolute;
  top: 52px;
  left: 50%;
  transform: translateX(-50%);
  width: 440px;
  background: #ffffff;
  border-radius: 16px;
  padding: 1.25rem;
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
  border: 1px solid var(--border-light);
  z-index: 1001;
}

.popover-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: 700;
  font-size: 0.9rem;
  margin-bottom: 0.85rem;
  color: var(--text-primary);
}

.btn-close-popover {
  background: transparent;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  color: var(--text-secondary);
}

.popover-field { margin-bottom: 0.85rem; }
.popover-field label, .field-group label {
  font-size: 0.725rem;
  font-weight: 700;
  color: var(--text-secondary);
  display: block;
  margin-bottom: 0.3rem;
}

.popover-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.popover-input-wrapper ion-icon {
  position: absolute;
  left: 0.75rem;
  color: var(--text-secondary);
}

.popover-input-wrapper input {
  width: 100%;
  height: 38px;
  border: 1px solid var(--border-light);
  border-radius: 8px;
  padding: 0 2rem 0 2.25rem;
  font-size: 0.85rem;
  outline: none;
}

.clear-icon { left: auto !important; right: 0.75rem; cursor: pointer; }

.popover-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.popover-select {
  border: 1px solid var(--border-light);
  border-radius: 8px;
  height: 38px;
  font-size: 0.8rem;
  --padding-start: 0.5rem;
}

.popover-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 0.75rem;
  border-top: 1px solid var(--border-light);
}

.btn-text-reset {
  background: transparent;
  border: none;
  color: var(--text-secondary);
  font-weight: 600;
  font-size: 0.8rem;
  cursor: pointer;
}

.btn-apply {
  background: var(--primary-color);
  color: #ffffff;
  border: none;
  padding: 0.5rem 0.9rem;
  border-radius: 8px;
  font-weight: 700;
  font-size: 0.8rem;
  cursor: pointer;
}

/* BOTÓN PROPIETARIOS */
.btn-owner {
  background: #f1f5f9;
  border: 1px solid var(--border-light);
  border-radius: 8px;
  padding: 0.5rem 0.85rem;
  color: var(--text-primary);
  font-size: 0.85rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-owner:hover {
  background: var(--primary-light);
  color: var(--primary-color);
  border-color: var(--primary-border);
}

/* MAIN CONTAINER CENTRADO */
.main-container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 1.5rem 1.5rem 3rem;
}

.results-section {
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid var(--border-light);
  padding: 2rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}

.results-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.results-title {
  font-size: 1.35rem;
  font-weight: 800;
  color: var(--text-primary);
  margin: 0;
}

.active-filter-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.75rem;
  color: var(--primary-color);
  background: var(--primary-light);
  padding: 0.2rem 0.5rem;
  border-radius: 6px;
  font-weight: 700;
  margin-top: 0.25rem;
}

.active-filter-badge .dot {
  width: 6px;
  height: 6px;
  background-color: var(--primary-color);
  border-radius: 50%;
}

.sort-select {
  border: 1px solid var(--border-light);
  border-radius: 8px;
  padding: 0.45rem 0.75rem;
  font-size: 0.85rem;
  outline: none;
  background-color: #ffffff;
  color: var(--text-primary);
  font-weight: 600;
  cursor: pointer;
}

/* GRILLA Y TARJETAS EN PANTALLA COMPLETA */
.courts-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
  margin-bottom: 2.5rem;
}

.court-card {
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid var(--border-light);
  overflow: hidden;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  flex-direction: column;
}

.court-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 22px rgba(0, 0, 0, 0.08);
  border-color: var(--primary-color);
}

.card-img-wrapper {
  position: relative;
  height: 180px;
  background-color: #e2e8f0;
}

.card-img-wrapper img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.card-badges {
  position: absolute;
  top: 0.65rem;
  right: 0.65rem;
}

.courts-chip {
  background: rgba(15, 23, 42, 0.75);
  backdrop-filter: blur(4px);
  color: #ffffff;
  padding: 0.25rem 0.55rem;
  border-radius: 6px;
  font-size: 0.725rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}

.card-body {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.card-title {
  margin: 0 0 0.35rem 0;
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--text-primary);
}

.card-location {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  color: var(--text-secondary);
  font-size: 0.825rem;
  margin-bottom: 1rem;
}

.loc-icon { color: var(--primary-color); }

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
  padding-top: 0.75rem;
  border-top: 1px solid #f1f5f9;
}

.price-caption {
  display: block;
  font-size: 0.65rem;
  color: var(--text-secondary);
  text-transform: uppercase;
  font-weight: 700;
}

.price-amount strong {
  font-size: 1.1rem;
  color: var(--primary-color);
  font-weight: 800;
}

.btn-card-action {
  background: var(--primary-light);
  color: var(--primary-color);
  border: none;
  padding: 0.4rem 0.8rem;
  border-radius: 6px;
  font-size: 0.775rem;
  font-weight: 700;
  transition: background 0.2s;
}

.court-card:hover .btn-card-action {
  background: var(--primary-color);
  color: #ffffff;
}

/* SKELETONS & EMPTY */
.skeleton-card { pointer-events: none; }
.skeleton-img { height: 180px; background: #e2e8f0; }
.skeleton-line { height: 0.85rem; background: #e2e8f0; border-radius: 4px; margin-bottom: 0.5rem; }
.skeleton-line.title { width: 70%; height: 1.1rem; }
.skeleton-line.subtitle { width: 50%; }
.card-footer-skeleton { display: flex; justify-content: space-between; align-items: center; margin-top: auto; }
.skeleton-line.price { width: 35%; margin: 0; }
.skeleton-btn { width: 80px; height: 30px; background: #e2e8f0; border-radius: 6px; }

.empty-box {
  background: #ffffff;
  border-radius: 12px;
  border: 1px dashed #cbd5e1;
  padding: 3rem 1.5rem;
  text-align: center;
  margin-bottom: 2.5rem;
}

.empty-icon-wrapper {
  background: var(--primary-light);
  color: var(--primary-color);
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  margin: 0 auto 0.85rem;
}

.btn-clear-filters {
  background: var(--primary-color);
  color: #ffffff;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 700;
  font-size: 0.825rem;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}

/* FOOTER */
.footer {
  margin-top: 1rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border-light);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.footer-brand { display: flex; align-items: center; gap: 0.4rem; }
.logo-icon-sm {
  width: 1.1rem;
  height: 1.1rem;
  background-color: var(--primary-color);
  -webkit-mask: url('/Sportra_Logo.svg') no-repeat center / contain;
  mask: url('/Sportra_Logo.svg') no-repeat center / contain;
}
.footer-title { font-weight: 800; color: var(--text-primary); font-size: 0.85rem; }
.copyright { font-size: 0.775rem; color: var(--text-secondary); }

/* RESPONSIVIDAD */
@media (max-width: 1024px) {
  .courts-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .navbar-container { padding: 0 1rem; }
  .btn-owner-text { display: none; }
  .search-capsule { padding: 0.35rem 0.5rem; }
  .capsule-section { padding-right: 0.5rem; }
  .capsule-divider { margin-right: 0.5rem; }
  .search-popover { width: 90vw; }
  .courts-grid {
    grid-template-columns: 1fr;
  }
  .results-section {
    padding: 1rem;
  }
}
</style>