<template>
  <ion-page>
    <ion-content :fullscreen="true">
      <!-- Navbar Limpiado y Elegante -->
      <header class="navbar">
        <div class="navbar-container">
          <div class="logo" @click="limpiarFiltros">
            <div class="logo-icon-wrapper">
              <ion-icon name="football-outline" class="logo-icon"></ion-icon>
            </div>
            <span class="logo-text">Sportra</span>
          </div>
          <div class="navbar-actions">
            <button class="btn-owner" @click="irAlPanel">
              <ion-icon name="person-circle-outline"></ion-icon>
              <span>Acceso propietarios</span>
            </button>
          </div>
        </div>
      </header>

      <!-- Hero Section (Fondo Azul Sólido Vibrante) -->
      <section class="hero-section">
        <div class="hero-content">
          <div class="hero-badge">
            <span class="pulse-dot"></span>
            Reserva directa en Costa Rica
          </div>
          <h1 class="hero-title">Encuentra y reserva tu cancha</h1>
          <p class="hero-subtitle">Explora complejos deportivos verificados según tu ubicación y disponibilidad.</p>

          <!-- Tarjeta de Búsqueda Flotante -->
          <div class="search-card">
            <!-- Buscador general por texto -->
            <div class="search-input-wrapper">
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
              >
                <ion-icon name="close-circle-outline"></ion-icon>
              </button>
            </div>

            <!-- Grid de Selectores Ubicación -->
            <div class="search-fields">
              <!-- Selector Provincia -->
              <div class="field-group">
                <label class="field-label">Provincia</label>
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
                <label class="field-label">Cantón</label>
                <div class="select-wrapper" :class="{ 'has-value': cantonId }">
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
                <label class="field-label">Distrito</label>
                <div class="select-wrapper" :class="{ 'has-value': distritoId }">
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

            <!-- Footer interno de la tarjeta -->
            <div v-if="filtrosActivos" class="search-footer">
              <span class="active-filters-tag">Filtros aplicados</span>
              <button class="btn-reset" @click="limpiarFiltros">
                Restablecer ubicación
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Sección de Resultados / Complejos -->
      <main class="main-container">
        <div class="section-header">
          <div class="section-title-group">
            <h2>Complejos Deportivos</h2>
            <span class="results-badge" v-if="!cargando">
              {{ complejosFiltrados.length }} {{ complejosFiltrados.length === 1 ? 'disponible' : 'disponibles' }}
            </span>
          </div>

          <div class="sorting-controls" v-if="complejosFiltrados.length > 1">
            <span class="sort-label">Ordenar por:</span>
            <select v-model="orden" class="sort-select">
              <option value="nombre">Nombre (A-Z)</option>
              <option value="precio-asc">Precio: Menor a Mayor</option>
              <option value="precio-desc">Precio: Mayor a Menor</option>
            </select>
          </div>
        </div>

        <!-- Estado: Cargando -->
        <div v-if="cargando" class="state-container">
          <ion-spinner name="crescent" color="primary"></ion-spinner>
          <p class="state-text">Buscando complejos disponibles...</p>
        </div>

        <!-- Estado: Tarjetas de Complejos -->
        <div v-else-if="complejosFiltrados.length > 0" class="courts-grid">
          <article
            v-for="complejo in complejosOrdenados"
            :key="complejo.id"
            class="court-card"
            @click="verDetalle(complejo.slug)"
          >
            <div class="card-img-wrapper">
              <img
                :src="complejo.imagen_url || 'https://images.unsplash.com/photo-1529900748604-07564a03e7a6?auto=format&fit=crop&w=800&q=80'"
                :alt="complejo.nombre"
                loading="lazy"
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
                <ion-icon name="location-outline"></ion-icon>
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

        <!-- Estado: Sin Resultados -->
        <div v-else class="state-container empty-box">
          <div class="empty-icon-wrapper">
            <ion-icon name="search-outline"></ion-icon>
          </div>
          <h3>No se encontraron resultados</h3>
          <p v-if="busquedaTexto">No coinciden complejos con "{{ busquedaTexto }}".</p>
          <p v-else>No hay instalaciones disponibles en la ubicación seleccionada.</p>
          <button class="btn-clear-filters" @click="limpiarFiltros">
            Restablecer filtros
          </button>
        </div>
      </main>

      <!-- Footer Limpio -->
      <footer class="footer">
        <div class="footer-content">
          <span>© 2026 Sportra CR. Todos los derechos reservados.</span>
        </div>
      </footer>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import {
  IonPage, IonContent, IonSpinner, IonSelect, IonSelectOption, IonIcon
} from '@ionic/vue';
import complejosService from '@/services/complejos.service';
import geografiaService from '@/services/geografia.service';
import type { Complejo } from '@/types';

const router = useRouter();

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

onMounted(cargarProvincias);
</script>

<style scoped>
ion-content {
  --background: #f8fafc;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

/* Header Navbar */
.navbar {
  background: #ffffff;
  border-bottom: 1px solid #e2e8f0;
  position: sticky;
  top: 0;
  z-index: 100;
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
  display: flex;
  align-items: center;
  gap: 0.6rem;
  cursor: pointer;
}

.logo-icon-wrapper {
  background: #0066ff;
  color: #ffffff;
  padding: 0.45rem;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-icon {
  font-size: 1.3rem;
}

.logo-text {
  font-weight: 800;
  font-size: 1.35rem;
  color: #0066ff;
  letter-spacing: -0.03em;
}

.btn-owner {
  background: #f0f7ff;
  border: 1px solid #c7d2fe;
  border-radius: 8px;
  padding: 0.5rem 0.9rem;
  color: #0066ff;
  font-size: 0.85rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-owner:hover {
  background: #0066ff;
  color: #ffffff;
  border-color: #0066ff;
}

/* Hero Section */
.hero-section {
  background: #0066ff;
  color: #ffffff;
  padding: 3.5rem 1.5rem 4.5rem;
  text-align: center;
}

.hero-content {
  max-width: 820px;
  margin: 0 auto;
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
}

.pulse-dot {
  width: 7px;
  height: 7px;
  background-color: #4ade80;
  border-radius: 50%;
}

.hero-title {
  font-size: 2.5rem;
  font-weight: 800;
  margin: 0 0 0.6rem 0;
  letter-spacing: -0.03em;
}

.hero-subtitle {
  font-size: 1rem;
  color: #e0f2fe;
  margin: 0 0 2.25rem 0;
}

/* Card Búsqueda Integrada */
.search-card {
  background: #ffffff;
  border-radius: 14px;
  padding: 1.25rem;
  box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.15);
  text-align: left;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
}

.text-input {
  width: 100%;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  border-radius: 8px;
  padding: 0.75rem 1rem;
  font-size: 0.925rem;
  color: #0f172a;
  outline: none;
  transition: border-color 0.2s;
}

.text-input:focus {
  border-color: #0066ff;
  background: #ffffff;
}

.btn-clear-text {
  position: absolute;
  right: 0.75rem;
  background: transparent;
  border: none;
  color: #94a3b8;
  font-size: 1.2rem;
  cursor: pointer;
}

.search-fields {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.85rem;
}

.field-group {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.field-label {
  font-size: 0.725rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.select-wrapper {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 0.15rem 0.5rem;
}

.select-wrapper.has-value {
  border-color: #0066ff;
}

.select-wrapper ion-select {
  --padding-start: 0;
  width: 100%;
  color: #0f172a;
  font-size: 0.875rem;
  font-weight: 500;
}

.search-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1rem;
  padding-top: 0.85rem;
  border-top: 1px solid #f1f5f9;
}

.active-filters-tag {
  font-size: 0.775rem;
  color: #0284c7;
  background: #e0f2fe;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
  font-weight: 600;
}

.btn-reset {
  background: transparent;
  border: none;
  color: #64748b;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
}

.btn-reset:hover {
  color: #ef4444;
}

/* Grid Complejos */
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
  color: #0f172a;
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
  color: #64748b;
  margin-right: 0.4rem;
}

.sort-select {
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 0.4rem 0.75rem;
  background: #ffffff;
  color: #0f172a;
  font-size: 0.825rem;
  font-weight: 600;
  outline: none;
}

.courts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
  gap: 1.5rem;
}

.court-card {
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  display: flex;
  flex-direction: column;
}

.court-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.08);
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
  top: 0.75rem;
  right: 0.75rem;
}

.courts-chip {
  background: rgba(15, 23, 42, 0.75);
  color: #ffffff;
  padding: 0.3rem 0.6rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
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
  color: #0f172a;
}

.card-location {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  color: #64748b;
  font-size: 0.825rem;
  margin-bottom: 1.25rem;
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
  color: #64748b;
  text-transform: uppercase;
  font-weight: 600;
}

.price-amount strong {
  font-size: 1.1rem;
  color: #0066ff;
  font-weight: 800;
}

.btn-card-action {
  background: #f0f7ff;
  color: #0066ff;
  border: none;
  padding: 0.45rem 0.85rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.court-card:hover .btn-card-action {
  background: #0066ff;
  color: #ffffff;
}

/* States */
.state-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 1rem;
  text-align: center;
}

.state-text {
  color: #64748b;
  font-size: 0.9rem;
  margin-top: 0.85rem;
}

.empty-box {
  background: #ffffff;
  border-radius: 12px;
  border: 1px dashed #cbd5e1;
}

.empty-icon-wrapper {
  background: #f0f7ff;
  color: #0066ff;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  margin-bottom: 0.85rem;
}

.btn-clear-filters {
  background: #0066ff;
  color: #ffffff;
  border: none;
  padding: 0.55rem 1.15rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.825rem;
  cursor: pointer;
}

/* Footer Slim */
.footer {
  background: #ffffff;
  border-top: 1px solid #e2e8f0;
  padding: 1.25rem 1.5rem;
}

.footer-content {
  max-width: 1140px;
  margin: 0 auto;
  text-align: center;
  font-size: 0.8rem;
  color: #64748b;
}

@media (max-width: 768px) {
  .search-fields {
    grid-template-columns: 1fr;
  }
}
</style>