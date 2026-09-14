<template>
  <ion-page class="sportra-admin-page">
    <!-- Navbar Flotante -->
    <header class="navbar-container">
      <div class="navbar-bar">
        <div class="brand-box">
          <div class="brand-badge glow-pulse">
            <ion-icon :icon="shieldCheckmarkOutline" class="brand-icon"></ion-icon>
          </div>
          <span class="brand-name">SPORTRA<span class="neon-dot">.</span> <small class="role-badge">ADMIN</small></span>
        </div>

        <div class="header-right">
          <button class="btn-logout" @click="cerrarSesion">
            <ion-icon :icon="logOutOutline"></ion-icon>
            <span>Cerrar Sesión</span>
          </button>
        </div>
      </div>
    </header>

    <ion-content class="sportra-main-viewport">
      <div class="admin-wrapper">
        <!-- Banner de Encabezado -->
        <section class="admin-hero">
          <div class="hero-text">
            <h1 class="page-title">Panel <span class="lime-gradient-text">Administrativo Global</span></h1>
            <p class="page-subtitle">Control de suscripciones, gestión de sedes, accesos e historial de reservas.</p>
          </div>

          <!-- Métricas Rápidas -->
          <div class="metrics-row">
            <div class="metric-card">
              <span class="metric-label">Complejos</span>
              <span class="metric-value">{{ facturacion.length }}</span>
            </div>
            <div class="metric-card">
              <span class="metric-label">Al Día</span>
              <span class="metric-value text-lime">{{ complejosAlDia }}</span>
            </div>
            <div class="metric-card">
              <span class="metric-label">Usuarios</span>
              <span class="metric-value">{{ usuarios.length }}</span>
            </div>
          </div>
        </section>

        <!-- Navegación por Segmentos -->
        <div class="tab-menu">
          <button 
            :class="['tab-btn', { active: vista === 'complejos' }]" 
            @click="vista = 'complejos'"
          >
            <ion-icon :icon="businessOutline"></ion-icon>
            <span>Complejos & Pagos</span>
          </button>
          <button 
            :class="['tab-btn', { active: vista === 'usuarios' }]" 
            @click="vista = 'usuarios'"
          >
            <ion-icon :icon="peopleOutline"></ion-icon>
            <span>Usuarios & Permisos</span>
          </button>
          <button 
            :class="['tab-btn', { active: vista === 'movimientos' }]" 
            @click="vista = 'movimientos'"
          >
            <ion-icon :icon="receiptOutline"></ion-icon>
            <span>Movimientos Globales</span>
          </button>
        </div>

        <!-- Banner de Notificación -->
        <transition name="fade">
          <div v-if="mensaje" class="notification-toast">
            <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
            <span>{{ mensaje }}</span>
            <ion-icon :icon="closeCircleOutline" class="btn-close-toast" @click="mensaje = ''"></ion-icon>
          </div>
        </transition>

        <!-- PESTAÑA 1: COMPLEJOS Y SUSCRIPCIONES -->
        <section v-if="vista === 'complejos'" class="tab-content animate-fade-in">
          <div class="admin-grid-layout">
            <!-- Tabla de Complejos -->
            <div class="section-card main-col">
              <div class="card-header">
                <h3>Estado de Complejos y Suscripciones</h3>
              </div>

              <div class="table-responsive">
                <table class="neon-table">
                  <thead>
                    <tr>
                      <th>Complejo</th>
                      <th>Suscripción</th>
                      <th>Canchas</th>
                      <th>Reservas/Mes</th>
                      <th>Ingreso Est.</th>
                      <th class="text-right">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="c in facturacion" :key="c.complejo_id">
                      <td class="font-bold">
                        {{ c.nombre }}
                        <span v-if="!c.activo" class="tag-blocked">BLOQUEADO</span>
                      </td>
                      <td>
                        <span :class="['status-pill', getBadgeClass(c)]">
                          {{ getBadgeText(c) }}
                        </span>
                      </td>
                      <td>{{ c.total_canchas }}</td>
                      <td>{{ c.reservas_mes }}</td>
                      <td class="text-lime font-bold">₡{{ c.ingreso_estimado_mes ? c.ingreso_estimado_mes.toLocaleString('es-CR') : 0 }}</td>
                      <td>
                        <div class="action-buttons-cell">
                          <button class="btn-action-sm btn-pay" @click="abrirPago(c)" title="Registrar Pago">
                            <ion-icon :icon="cardOutline"></ion-icon> Pago
                          </button>
                          <button 
                            :class="['btn-action-sm', c.activo ? 'btn-danger' : 'btn-success']" 
                            @click="toggleComplejo(c)"
                          >
                            <ion-icon :icon="c.activo ? lockClosedOutline : lockOpenOutline"></ion-icon>
                            {{ c.activo ? 'Bloquear' : 'Activar' }}
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="facturacion.length === 0">
                      <td colspan="6" class="text-center py-4">No hay complejos registrados.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Formulario Crear Complejo -->
            <div class="section-card side-col">
              <div class="card-header">
                <h3>Crear Complejo</h3>
              </div>

              <form @submit.prevent="crearComplejo" class="neon-form">
                <div class="field-block">
                  <label>Provincia</label>
                  <ion-select v-model="provinciaId" interface="popover" placeholder="Seleccione..." class="neon-select" @ionChange="onProvinciaChange">
                    <ion-select-option v-for="p in provincias" :key="p.id" :value="p.id">{{ p.nombre }}</ion-select-option>
                  </ion-select>
                </div>

                <div class="field-block">
                  <label>Cantón</label>
                  <ion-select v-model="cantonId" interface="popover" placeholder="Seleccione..." class="neon-select" :disabled="!provinciaId" @ionChange="onCantonChange">
                    <ion-select-option v-for="c in cantones" :key="c.id" :value="c.id">{{ c.nombre }}</ion-select-option>
                  </ion-select>
                </div>

                <div class="field-block">
                  <label>Distrito</label>
                  <ion-select v-model="nuevoComplejo.distrito_id" interface="popover" placeholder="Seleccione..." class="neon-select" :disabled="!cantonId">
                    <ion-select-option v-for="d in distritos" :key="d.id" :value="d.id">{{ d.nombre }}</ion-select-option>
                  </ion-select>
                </div>

                <div class="field-block">
                  <label>Nombre del Complejo</label>
                  <input v-model="nuevoComplejo.nombre" type="text" placeholder="Ej: Complejo Camp Nou" required class="neon-input" />
                </div>

                <div class="field-block">
                  <label>WhatsApp</label>
                  <input v-model="nuevoComplejo.whatsapp_numero" type="text" placeholder="Ej: 88888888" required class="neon-input" />
                </div>

                <button type="submit" class="btn-submit-neon" :disabled="!nuevoComplejo.distrito_id || !nuevoComplejo.nombre">
                  <ion-icon :icon="addCircleOutline"></ion-icon> Crear Complejo
                </button>
              </form>
            </div>
          </div>
        </section>

        <!-- PESTAÑA 2: USUARIOS Y ACTIVIDAD -->
        <section v-if="vista === 'usuarios'" class="tab-content animate-fade-in">
          <div class="admin-grid-layout">
            <!-- Tabla Usuarios -->
            <div class="section-card main-col">
              <div class="card-header">
                <h3>Usuarios Existentes</h3>
              </div>

              <div class="table-responsive">
                <table class="neon-table">
                  <thead>
                    <tr>
                      <th>Usuario</th>
                      <th>Email</th>
                      <th>Complejos Asignados</th>
                      <th class="text-right">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="u in usuarios" :key="u.id">
                      <td class="font-bold">
                        {{ u.name }}
                        <span v-if="!u.activo" class="tag-blocked">BLOQUEADO</span>
                      </td>
                      <td>{{ u.email }}</td>
                      <td>
                        <span class="complexes-tag">
                          {{ u.complejos?.map((c: any) => c.nombre).join(', ') || 'Sin asignación' }}
                        </span>
                      </td>
                      <td>
                        <div class="action-buttons-cell">
                          <button class="btn-action-sm btn-info" @click="verActividad(u)" title="Ver Historial">
                            <ion-icon :icon="eyeOutline"></ion-icon> Actividad
                          </button>
                          <button 
                            :class="['btn-action-sm', u.activo ? 'btn-danger' : 'btn-success']" 
                            @click="toggleUsuario(u)"
                          >
                            <ion-icon :icon="u.activo ? lockClosedOutline : lockOpenOutline"></ion-icon>
                            {{ u.activo ? 'Bloquear' : 'Activar' }}
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Formulario Crear Usuario -->
            <div class="section-card side-col">
              <div class="card-header">
                <h3>Crear Propietario / Encargado</h3>
              </div>

              <form @submit.prevent="crearUsuario" class="neon-form">
                <div class="field-block">
                  <label>Nombre Completo</label>
                  <input v-model="nuevoUsuario.name" type="text" placeholder="Ej: Carlos Ruiz" required class="neon-input" />
                </div>

                <div class="field-block">
                  <label>Correo Electrónico</label>
                  <input v-model="nuevoUsuario.email" type="email" placeholder="carlos@ejemplo.com" required class="neon-input" />
                </div>

                <div class="field-block">
                  <label>Contraseña</label>
                  <input v-model="nuevoUsuario.password" type="password" placeholder="••••••••" required class="neon-input" />
                </div>

                <div class="field-block">
                  <label>Asignar Complejo</label>
                  <ion-select v-model="nuevoUsuario.complejo_id" interface="popover" placeholder="Seleccione..." class="neon-select">
                    <ion-select-option v-for="c in facturacion" :key="c.complejo_id" :value="c.complejo_id">{{ c.nombre }}</ion-select-option>
                  </ion-select>
                </div>

                <div class="field-block">
                  <label>Rol asignado</label>
                  <ion-select v-model="nuevoUsuario.rol" interface="popover" class="neon-select">
                    <ion-select-option value="propietario">Propietario</ion-select-option>
                    <ion-select-option value="encargado">Encargado</ion-select-option>
                  </ion-select>
                </div>

                <button type="submit" class="btn-submit-neon" :disabled="!nuevoUsuario.email || !nuevoUsuario.password">
                  <ion-icon :icon="personAddOutline"></ion-icon> Crear Usuario
                </button>
              </form>
            </div>
          </div>
        </section>

        <!-- PESTAÑA 3: MOVIMIENTOS / RESERVAS GLOBALES -->
        <section v-if="vista === 'movimientos'" class="tab-content animate-fade-in">
          <div class="section-card">
            <div class="card-header header-with-filter">
              <div>
                <h3>Historial Global de Reservas</h3>
                <p>Auditoría de reservas realizadas en todos los complejos de la plataforma.</p>
              </div>

              <div class="filter-box">
                <ion-select 
                  v-model="filtroComplejo" 
                  interface="popover" 
                  placeholder="Filtrar por complejo" 
                  class="neon-select filter-select"
                  @ionChange="cargarMovimientos"
                >
                  <ion-select-option :value="null">Todos los Complejos</ion-select-option>
                  <ion-select-option v-for="c in facturacion" :key="c.complejo_id" :value="c.complejo_id">{{ c.nombre }}</ion-select-option>
                </ion-select>
              </div>
            </div>

            <div class="table-responsive">
              <table class="neon-table">
                <thead>
                  <tr>
                    <th>Fecha & Hora</th>
                    <th>Complejo / Cancha</th>
                    <th>Cliente</th>
                    <th>Estado</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="m in movimientos" :key="m.id">
                    <td class="font-bold text-lime">{{ m.fecha }} <small class="text-muted">({{ m.hora_inicio }} - {{ m.hora_fin }})</small></td>
                    <td>{{ m.cancha?.complejo?.nombre }} — <span class="text-white">{{ m.cancha?.nombre }}</span></td>
                    <td>{{ m.nombre_cliente }}</td>
                    <td>
                      <span :class="['status-pill', m.estado === 'confirmada' ? 'status-active' : 'status-pending']">
                        {{ m.estado }}
                      </span>
                    </td>
                  </tr>
                  <tr v-if="movimientos.length === 0">
                    <td colspan="4" class="text-center py-4 text-muted">No se encontraron movimientos registrados.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

      </div>
    </ion-content>

    <!-- MODAL REGISTRAR PAGO -->
    <div v-if="complejoPago" class="modal-backdrop">
      <div class="modal-box animate-pop">
        <div class="modal-header">
          <h3>Registrar Pago — <span class="text-lime">{{ complejoPago.nombre }}</span></h3>
          <button class="btn-close-modal" @click="complejoPago = null">
            <ion-icon :icon="closeCircleOutline"></ion-icon>
          </button>
        </div>

        <form @submit.prevent="guardarPago" class="neon-form modal-body">
          <div class="field-block">
            <label>Monto Pago (₡)</label>
            <input v-model.number="nuevoPago.monto" type="number" required class="neon-input" />
          </div>

          <div class="form-grid-2">
            <div class="field-block">
              <label>Vigencia Desde</label>
              <input v-model="nuevoPago.periodo_desde" type="date" required class="neon-input" />
            </div>
            <div class="field-block">
              <label>Vigencia Hasta</label>
              <input v-model="nuevoPago.periodo_hasta" type="date" required class="neon-input" />
            </div>
          </div>

          <div class="form-grid-2">
            <div class="field-block">
              <label>Fecha del Pago</label>
              <input v-model="nuevoPago.fecha_pago" type="date" required class="neon-input" />
            </div>
            <div class="field-block">
              <label>Método de Pago</label>
              <ion-select v-model="nuevoPago.metodo" interface="popover" class="neon-select">
                <ion-select-option value="sinpe">SINPE Móvil</ion-select-option>
                <ion-select-option value="transferencia">Transferencia Bancaria</ion-select-option>
                <ion-select-option value="efectivo">Efectivo</ion-select-option>
              </ion-select>
            </div>
          </div>

          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="complejoPago = null">Cancelar</button>
            <button type="submit" class="btn-submit-neon">Guardar y Extender Suscripción</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL ACTIVIDAD DE USUARIO -->
    <div v-if="usuarioActividad" class="modal-backdrop">
      <div class="modal-box animate-pop">
        <div class="modal-header">
          <h3>Bitácora de Actividad — <span class="text-lime">{{ usuarioActividad.name }}</span></h3>
          <button class="btn-close-modal" @click="usuarioActividad = null">
            <ion-icon :icon="closeCircleOutline"></ion-icon>
          </button>
        </div>

        <div class="modal-body user-logs-container">
          <div v-for="log in actividad" :key="log.id" class="log-row">
            <div class="log-time">{{ log.created_at }}</div>
            <div class="log-details">
              <strong>{{ log.log_name }}</strong>: {{ log.description }}
            </div>
          </div>
          <div v-if="actividad.length === 0" class="text-center py-4 text-muted">
            Este usuario no registra actividad reciente.
          </div>
        </div>
      </div>
    </div>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { IonPage, IonContent, IonSelect, IonSelectOption, IonIcon } from '@ionic/vue';
import { addIcons } from 'ionicons';
import {
  shieldCheckmarkOutline, logOutOutline, businessOutline, peopleOutline,
  receiptOutline, checkmarkCircleOutline, closeCircleOutline, cardOutline,
  lockClosedOutline, lockOpenOutline, addCircleOutline, eyeOutline, personAddOutline
} from 'ionicons/icons';

import { useAuthStore } from '@/stores/auth';
import adminService from '@/services/admin.service';
import geografiaService from '@/services/geografia.service';

addIcons({
  'shield-checkmark-outline': shieldCheckmarkOutline,
  'log-out-outline': logOutOutline,
  'business-outline': businessOutline,
  'people-outline': peopleOutline,
  'receipt-outline': receiptOutline,
  'checkmark-circle-outline': checkmarkCircleOutline,
  'close-circle-outline': closeCircleOutline,
  'card-outline': cardOutline,
  'lock-closed-outline': lockClosedOutline,
  'lock-open-outline': lockOpenOutline,
  'add-circle-outline': addCircleOutline,
  'eye-outline': eyeOutline,
  'person-add-outline': personAddOutline
});

const router = useRouter();
const authStore = useAuthStore();

const vista = ref<'complejos' | 'usuarios' | 'movimientos'>('complejos');

const facturacion = ref<any[]>([]);
const usuarios = ref<any[]>([]);
const movimientos = ref<any[]>([]);
const mensaje = ref('');

const filtroComplejo = ref<number | null>(null);

// Geografía
const provincias = ref<any[]>([]);
const cantones = ref<any[]>([]);
const distritos = ref<any[]>([]);
const provinciaId = ref<number | null>(null);
const cantonId = ref<number | null>(null);

const complejoPago = ref<any>(null);
const nuevoPago = ref({ 
  monto: 0, 
  periodo_desde: '', 
  periodo_hasta: '', 
  fecha_pago: new Date().toISOString().split('T')[0], 
  metodo: 'sinpe' 
});

const usuarioActividad = ref<any>(null);
const actividad = ref<any[]>([]);

// Tipado explícito para evitar errores con TypeScript al pasar objeto a adminService.crearComplejo
const nuevoComplejo = ref<{ distrito_id: number | null; nombre: string; whatsapp_numero: string }>({ 
  distrito_id: null, 
  nombre: '', 
  whatsapp_numero: '' 
});

const nuevoUsuario = ref({ name: '', email: '', password: '', complejo_id: null as number | null, rol: 'propietario' });

const complejosAlDia = computed(() => facturacion.value.filter(c => c.estado_suscripcion === 'al_dia').length);

async function cargarDatos() {
  try {
    const [factRes, userRes] = await Promise.all([
      adminService.resumenFacturacion(),
      adminService.usuarios(),
    ]);
    facturacion.value = factRes.data.data;
    usuarios.value = userRes.data.data;
  } catch (error) {
    console.error('Error cargando datos principales:', error);
  }
}

async function cargarProvincias() {
  try {
    const { data } = await geografiaService.provincias();
    provincias.value = data.data;
  } catch (error) {
    console.error('Error cargando provincias:', error);
  }
}

async function onProvinciaChange() {
  cantones.value = [];
  distritos.value = [];
  cantonId.value = null;
  nuevoComplejo.value.distrito_id = null;

  if (provinciaId.value) {
    const { data } = await geografiaService.cantones(provinciaId.value);
    cantones.value = data.data;
  }
}

async function onCantonChange() {
  distritos.value = [];
  nuevoComplejo.value.distrito_id = null;

  if (cantonId.value) {
    const { data } = await geografiaService.distritos(cantonId.value);
    distritos.value = data.data;
  }
}

async function cargarMovimientos() {
  try {
    const { data } = await adminService.movimientos({ complejo_id: filtroComplejo.value ?? undefined });
    movimientos.value = data.data.data;
  } catch (error) {
    console.error('Error cargando movimientos:', error);
  }
}

function abrirPago(complejo: any) {
  complejoPago.value = complejo;
}

async function guardarPago() {
  try {
    await adminService.registrarPago(complejoPago.value.complejo_id, nuevoPago.value);
    mensaje.value = 'Pago registrado y suscripción renovada exitosamente.';
    complejoPago.value = null;
    nuevoPago.value = { monto: 0, periodo_desde: '', periodo_hasta: '', fecha_pago: new Date().toISOString().split('T')[0], metodo: 'sinpe' };
    await cargarDatos();
  } catch (error) {
    console.error('Error al registrar pago:', error);
  }
}

async function toggleComplejo(complejo: any) {
  try {
    await adminService.toggleEstadoComplejo(complejo.complejo_id);
    await cargarDatos();
  } catch (error) {
    console.error('Error cambiando estado del complejo:', error);
  }
}

async function toggleUsuario(usuario: any) {
  try {
    await adminService.toggleEstadoUsuario(usuario.id);
    await cargarDatos();
  } catch (error) {
    console.error('Error cambiando estado del usuario:', error);
  }
}

async function verActividad(usuario: any) {
  usuarioActividad.value = usuario;
  try {
    const { data } = await adminService.actividadUsuario(usuario.id);
    actividad.value = data.data.data;
  } catch (error) {
    console.error('Error cargando bitácora de actividad:', error);
  }
}

async function crearComplejo() {
  if (!nuevoComplejo.value.distrito_id) return;

  try {
    // Se usa el 'as any' para el argumento del servicio si la firma requiere tipos más restringidos
    await adminService.crearComplejo(nuevoComplejo.value as any);
    mensaje.value = 'Nuevo complejo registrado con éxito.';
    nuevoComplejo.value = { distrito_id: null, nombre: '', whatsapp_numero: '' };
    provinciaId.value = null;
    cantonId.value = null;
    await cargarDatos();
  } catch (error) {
    console.error('Error al crear complejo:', error);
  }
}

async function crearUsuario() {
  try {
    await adminService.crearUsuario(nuevoUsuario.value as any);
    mensaje.value = 'Usuario registrado y asignado correctamente.';
    nuevoUsuario.value = { name: '', email: '', password: '', complejo_id: null, rol: 'propietario' };
    await cargarDatos();
  } catch (error) {
    console.error('Error al crear usuario:', error);
  }
}

async function cerrarSesion() {
  await authStore.logout();
  router.push('/login');
}

function getBadgeClass(c: any) {
  if (c.estado_suscripcion === 'vencida') return 'status-danger';
  if (c.estado_suscripcion === 'por_vencer') return 'status-warning';
  if (c.estado_suscripcion === 'al_dia') return 'status-active';
  return 'status-neutral';
}

function getBadgeText(c: any) {
  if (c.estado_suscripcion === 'vencida') return '🔴 Vencida';
  if (c.estado_suscripcion === 'por_vencer') return `🟡 Vence pronto (${c.suscripcion_vence_en})`;
  if (c.estado_suscripcion === 'al_dia') return `🟢 Al día (${c.suscripcion_vence_en})`;
  return '⚪ Sin registro';
}

watch(vista, (nuevaVista) => {
  if (nuevaVista === 'movimientos') cargarMovimientos();
});

onMounted(async () => {
  await Promise.all([cargarDatos(), cargarProvincias()]);
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700;800&family=Inter:wght@400;500;600;700;800&display=swap');

ion-content.sportra-main-viewport {
  --background: #030712;
  font-family: 'Inter', sans-serif;
  color: #f3f4f6;
}

/* NAVBAR */
.navbar-container {
  position: fixed;
  top: 1rem;
  left: 0;
  right: 0;
  z-index: 1000;
  padding: 0 1.5rem;
}

.navbar-bar {
  max-width: 1280px;
  margin: 0 auto;
  background: rgba(11, 15, 25, 0.85);
  backdrop-filter: blur(24px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 99px;
  padding: 0.6rem 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.brand-box { display: flex; align-items: center; gap: 0.6rem; }

.brand-badge {
  width: 36px;
  height: 36px;
  background: rgba(132, 204, 22, 0.15);
  border: 1px solid rgba(132, 204, 22, 0.4);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.brand-icon { font-size: 1.2rem; color: #84cc16; }
.brand-name { font-family: 'Space Grotesk', sans-serif; font-weight: 800; font-size: 1.25rem; color: #ffffff; }
.neon-dot { color: #84cc16; }

.role-badge {
  font-size: 0.65rem;
  background: rgba(132, 204, 22, 0.2);
  color: #84cc16;
  padding: 0.15rem 0.5rem;
  border-radius: 6px;
  margin-left: 0.4rem;
}

.btn-logout {
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #f87171;
  padding: 0.45rem 1rem;
  border-radius: 99px;
  font-weight: 700;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  cursor: pointer;
}

.btn-logout:hover { background: #ef4444; color: #ffffff; }

/* CONTAINER LAYOUT */
.admin-wrapper {
  max-width: 1280px;
  margin: 0 auto;
  padding: 7.5rem 1.5rem 4rem;
}

.admin-hero {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1.5rem;
}

.page-title { font-family: 'Space Grotesk', sans-serif; font-size: 2.2rem; font-weight: 800; margin: 0 0 0.4rem; }

.lime-gradient-text {
  background: linear-gradient(135deg, #84cc16 0%, #ecfccb 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.page-subtitle { color: #94a3b8; font-size: 0.95rem; margin: 0; }

.metrics-row { display: flex; gap: 1rem; }

.metric-card {
  background: #0b0f19;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 0.85rem 1.25rem;
  display: flex;
  flex-direction: column;
  min-width: 120px;
}

.metric-label { font-size: 0.7rem; color: #64748b; font-weight: 700; text-transform: uppercase; }
.metric-value { font-size: 1.4rem; font-weight: 800; color: #ffffff; }
.text-lime { color: #84cc16; }

/* TABS MENU */
.tab-menu {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  padding-bottom: 0.85rem;
}

.tab-btn {
  background: transparent;
  border: none;
  color: #94a3b8;
  padding: 0.6rem 1.1rem;
  border-radius: 12px;
  font-weight: 700;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.25s ease;
}

.tab-btn.active {
  background: rgba(132, 204, 22, 0.15);
  color: #84cc16;
  border: 1px solid rgba(132, 204, 22, 0.3);
}

/* CARDS & TABLES */
.admin-grid-layout {
  display: grid;
  grid-template-columns: 2.2fr 1fr;
  gap: 1.5rem;
}

.section-card {
  background: #0b0f19;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  padding: 1.5rem;
}

.card-header h3 { margin: 0 0 0.25rem; font-size: 1.2rem; font-weight: 800; color: #ffffff; }
.card-header p { margin: 0; font-size: 0.85rem; color: #94a3b8; }

.header-with-filter {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.filter-select { min-width: 200px; }

.table-responsive { overflow-x: auto; margin-top: 1rem; }

.neon-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.875rem;
}

.neon-table th {
  padding: 0.85rem 1rem;
  color: #64748b;
  font-size: 0.725rem;
  text-transform: uppercase;
  font-weight: 800;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.neon-table td {
  padding: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  color: #cbd5e1;
}

.status-pill {
  padding: 0.25rem 0.65rem;
  border-radius: 99px;
  font-size: 0.75rem;
  font-weight: 700;
}

.status-active { background: rgba(132, 204, 22, 0.15); color: #84cc16; }
.status-warning { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
.status-danger { background: rgba(239, 68, 68, 0.15); color: #f87171; }
.status-neutral { background: rgba(255, 255, 255, 0.08); color: #94a3b8; }

.tag-blocked {
  background: rgba(239, 68, 68, 0.2);
  color: #f87171;
  font-size: 0.65rem;
  padding: 0.1rem 0.4rem;
  border-radius: 4px;
  margin-left: 0.4rem;
}

/* ACTION BUTTONS */
.action-buttons-cell { display: flex; gap: 0.4rem; justify-content: flex-end; }

.btn-action-sm {
  border: none;
  padding: 0.35rem 0.65rem;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-pay { background: rgba(132, 204, 22, 0.15); color: #84cc16; }
.btn-pay:hover { background: #84cc16; color: #030712; }

.btn-info { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
.btn-info:hover { background: #3b82f6; color: #ffffff; }

.btn-danger { background: rgba(239, 68, 68, 0.15); color: #f87171; }
.btn-danger:hover { background: #ef4444; color: #ffffff; }

.btn-success { background: rgba(34, 197, 94, 0.15); color: #4ade80; }
.btn-success:hover { background: #22c55e; color: #ffffff; }

/* FORMS */
.neon-form { display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem; }

.field-block label {
  display: block;
  font-size: 0.725rem;
  font-weight: 800;
  color: #84cc16;
  text-transform: uppercase;
  margin-bottom: 0.35rem;
}

.neon-input {
  width: 100%;
  height: 42px;
  background: #030712;
  border: 1px solid rgba(132, 204, 22, 0.25);
  border-radius: 10px;
  padding: 0 0.85rem;
  color: #ffffff;
  outline: none;
  font-size: 0.875rem;
}

.neon-select {
  --background: #030712;
  --color: #ffffff;
  --padding-start: 0.85rem;
  background: #030712;
  border: 1px solid rgba(132, 204, 22, 0.25);
  border-radius: 10px;
  height: 42px;
  font-size: 0.875rem;
  color: #ffffff;
}

.btn-submit-neon {
  background: #84cc16;
  color: #030712;
  border: none;
  padding: 0.75rem 1.25rem;
  border-radius: 10px;
  font-weight: 800;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  cursor: pointer;
  margin-top: 0.5rem;
}

.btn-submit-neon:hover:not(:disabled) {
  background: #a3e635;
  box-shadow: 0 0 15px rgba(132, 204, 22, 0.3);
}

.btn-submit-neon:disabled { opacity: 0.5; cursor: not-allowed; }

/* MODALS */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(3, 7, 18, 0.8);
  backdrop-filter: blur(12px);
  z-index: 2000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
}

.modal-box {
  background: #0b0f19;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 20px;
  width: 100%;
  max-width: 520px;
  padding: 1.5rem;
}

.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.modal-header h3 { margin: 0; font-size: 1.15rem; font-weight: 800; color: #ffffff; }

.btn-close-modal { background: transparent; border: none; font-size: 1.5rem; color: #64748b; cursor: pointer; }

.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; }

.modal-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem; }

.btn-cancel {
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #94a3b8;
  padding: 0.6rem 1rem;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
}

.user-logs-container { display: flex; flex-direction: column; gap: 0.75rem; max-height: 350px; overflow-y: auto; }

.log-row {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  padding: 0.75rem;
  border-radius: 10px;
  font-size: 0.8rem;
}

.log-time { color: #84cc16; font-size: 0.7rem; font-weight: 700; margin-bottom: 0.2rem; }
.log-details { color: #cbd5e1; }

/* TOAST */
.notification-toast {
  background: rgba(132, 204, 22, 0.15);
  border: 1px solid #84cc16;
  color: #ffffff;
  padding: 0.85rem 1.25rem;
  border-radius: 14px;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.btn-close-toast { margin-left: auto; cursor: pointer; color: #94a3b8; }

@media (max-width: 900px) {
  .admin-grid-layout { grid-template-columns: 1fr; }
  .admin-hero { flex-direction: column; align-items: flex-start; }
}
</style>