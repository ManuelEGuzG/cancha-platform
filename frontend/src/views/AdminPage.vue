Tienes toda la razón. Viendo la captura, se aprecian graves problemas de legibilidad:

1. **Texto invisible en el Hero**: El título «Panel Administrativo» y la descripción están en verde oscuro/gris sobre azul marino.


2. **Cards de métricas rotas**: Las cajas de "COMPLEJOS" y "USUARIOS" salieron blancas sin adaptar el texto, y "AL DÍA" quedó en un tono oscuro illegible.


3. **Contraste de las tablas y formularios**: Las tarjetas blancas (`.section-card`) tienen textos y placeholders en gris extremadamente claro (`#cbd5e1` o `#8e9cc0`), provocando que casi no se puedan leer.


4. **Desconexión con la vista de detalle**: La vista de detalle usaba un fondo claro suave (`#f4f6fc`) en la parte inferior con tarjetas blancas puras, textos en azul oscuro (`#091133`), encabezados oscuros y bordes `#e2e8f0`.



Aquí tienes el código completo y corregido de **`AdminPage.vue`**. Se separó la interfaz exactamente como en la vista de detalle: **Hero oscuro arriba con texto blanco puro** y **Sección inferior clara con excelente contraste y tarjetas bien estructuradas**.

---

### `AdminPage.vue`

```vue
<template>
  <ion-page class="sportra-app">
    <!-- Navbar Flotante con Marca Limpia -->
    <header class="site-header">
      <div class="nav-shell">
        <div class="brand">
          <span class="brand-copy">
            <strong>SPORTRA<span>.</span></strong>
            <small>ADMINISTRACIÓN GLOBAL</small>
          </span>
        </div>

        <div class="header-right">
          <button class="btn-logout" type="button" @click="cerrarSesion">
            <ion-icon :icon="logOutOutline"></ion-icon>
            <span>Cerrar Sesión</span>
          </button>
        </div>
      </div>
    </header>

    <ion-content class="sportra-main-viewport" :fullscreen="true">
      <div class="detail-page-wrapper">
        
        <!-- HERO SECTION OSCURA (Encabezado y Métricas) -->
        <section class="complejo-hero-wrapper">
          <div class="hero-inner-container">
            <div class="admin-hero-card">
              <div class="hero-left-content">
                <div class="location-chip">
                  <span class="pulse-dot"></span>
                  <span>Plataforma Central</span>
                </div>
                <h1 class="page-title">Panel Administrativo</h1>
                <p class="page-subtitle">
                  Control de suscripciones, gestión de sedes, accesos e historial de reservas.
                </p>
              </div>

              <!-- Métricas Rápidas con Contraste Correcto -->
              <div class="metrics-row">
                <div class="metric-card">
                  <span class="metric-label">COMPLEJOS</span>
                  <strong class="metric-value">{{ facturacion.length }}</strong>
                </div>
                <div class="metric-card highlight">
                  <span class="metric-label">AL DÍA</span>
                  <strong class="metric-value">{{ complejosAlDia }}</strong>
                </div>
                <div class="metric-card">
                  <span class="metric-label">USUARIOS</span>
                  <strong class="metric-value">{{ usuarios.length }}</strong>
                </div>
              </div>
            </div>

            <!-- Navegación por Pestañas / Segmentos -->
            <div class="tab-menu-bar">
              <button 
                :class="['tab-btn', { active: vista === 'complejos' }]" 
                type="button"
                @click="vista = 'complejos'"
              >
                <ion-icon :icon="businessOutline"></ion-icon>
                <span>Complejos & Pagos</span>
              </button>
              <button 
                :class="['tab-btn', { active: vista === 'usuarios' }]" 
                type="button"
                @click="vista = 'usuarios'"
              >
                <ion-icon :icon="peopleOutline"></ion-icon>
                <span>Usuarios & Permisos</span>
              </button>
              <button 
                :class="['tab-btn', { active: vista === 'movimientos' }]" 
                type="button"
                @click="vista = 'movimientos'"
              >
                <ion-icon :icon="receiptOutline"></ion-icon>
                <span>Movimientos Globales</span>
              </button>
              <button
                :class="['tab-btn', { active: vista === 'verificacion' }]"
                type="button"
                @click="vista = 'verificacion'"
              >
                <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
                <span>Verificación</span>
              </button>
              <button
                :class="['tab-btn', { active: vista === 'reporte' }]"
                type="button"
                @click="vista = 'reporte'"
              >
                <ion-icon :icon="receiptOutline"></ion-icon>
                <span>Reporte Mensual</span>
              </button>
              <button :class="['tab-btn', { active: vista === 'fotos' }]" type="button" @click="vista = 'fotos'">
                <ion-icon :icon="imagesOutline"></ion-icon>
                <span>Fotos</span>
              </button>
              <button :class="['tab-btn', { active: vista === 'seguridad' }]" type="button" @click="vista = 'seguridad'">
                <ion-icon :icon="keyOutline"></ion-icon>
                <span>Seguridad</span>
              </button>
            </div>
          </div>
        </section>

        <!-- SECCIÓN INFERIOR CLARA (Contenido de Tablas y Formulario) -->
        <section class="main-details-section">
          <div class="section-container">

            <!-- Notificación Toast -->
            <transition name="fade">
              <div v-if="mensaje" class="notification-toast">
                <ion-icon :icon="checkmarkCircleOutline" class="toast-icon"></ion-icon>
                <span>{{ mensaje }}</span>
                <button type="button" class="btn-close-toast" @click="mensaje = ''">
                  <ion-icon :icon="closeCircleOutline"></ion-icon>
                </button>
              </div>
            </transition>

            <!-- PESTAÑA 1: COMPLEJOS Y SUSCRIPCIONES -->
            <div v-if="vista === 'complejos'" class="tab-content animate-fade-in">
              <div class="admin-grid-layout">
                <!-- Tabla de Complejos -->
                <div class="detail-white-card main-col">
                  <div class="card-header-flex">
                    <h2>Estado de Complejos y Suscripciones</h2>
                  </div>

                  <div class="table-responsive">
                    <table class="sportra-table">
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
                          <td class="font-bold cell-title">
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
                          <td class="price-highlight font-bold">₡{{ c.ingreso_estimado_mes ? c.ingreso_estimado_mes.toLocaleString('es-CR') : 0 }}</td>
                          <td>
                            <div class="action-buttons-cell">
                              <button class="btn-action-sm btn-pay" type="button" @click="abrirPago(c)" title="Registrar Pago">
                                <ion-icon :icon="cardOutline"></ion-icon>
                                <span>Pago</span>
                              </button>
                              <button 
                                type="button"
                                :class="['btn-action-sm', c.activo ? 'btn-danger' : 'btn-success']" 
                                @click="toggleComplejo(c)"
                              >
                                <ion-icon :icon="c.activo ? lockClosedOutline : lockOpenOutline"></ion-icon>
                                <span>{{ c.activo ? 'Bloquear' : 'Activar' }}</span>
                              </button>
                            </div>
                          </td>
                        </tr>
                        <tr v-if="facturacion.length === 0">
                          <td colspan="6" class="text-center py-4 text-muted">No hay complejos registrados.</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Formulario Crear Complejo -->
                <div class="detail-white-card side-col">
                  <div class="card-header-flex">
                    <h2>Crear Complejo</h2>
                  </div>

                  <form @submit.prevent="crearComplejo" class="sportra-form">
                    <div class="field-block">
                      <label>Provincia</label>
                      <ion-select v-model="provinciaId" interface="popover" placeholder="Seleccione..." class="sportra-select" @ionChange="onProvinciaChange">
                        <ion-select-option v-for="p in provincias" :key="p.id" :value="p.id">{{ p.nombre }}</ion-select-option>
                      </ion-select>
                    </div>

                    <div class="field-block">
                      <label>Cantón</label>
                      <ion-select v-model="cantonId" interface="popover" placeholder="Seleccione..." class="sportra-select" :disabled="!provinciaId" @ionChange="onCantonChange">
                        <ion-select-option v-for="c in cantones" :key="c.id" :value="c.id">{{ c.nombre }}</ion-select-option>
                      </ion-select>
                    </div>

                    <div class="field-block">
                      <label>Distrito</label>
                      <ion-select v-model="nuevoComplejo.distrito_id" interface="popover" placeholder="Seleccione..." class="sportra-select" :disabled="!cantonId">
                        <ion-select-option v-for="d in distritos" :key="d.id" :value="d.id">{{ d.nombre }}</ion-select-option>
                      </ion-select>
                    </div>

                    <div class="field-block">
                      <label>Nombre del Complejo</label>
                      <input v-model="nuevoComplejo.nombre" type="text" placeholder="Ej: Complejo Camp Nou" required class="sportra-input" />
                    </div>

                    <div class="field-block">
                      <label>WhatsApp</label>
                      <input v-model="nuevoComplejo.whatsapp_numero" type="text" placeholder="Ej: 88888888" required class="sportra-input" />
                    </div>

                    <button type="submit" class="btn-primary-sportra" :disabled="!nuevoComplejo.distrito_id || !nuevoComplejo.nombre">
                      <ion-icon :icon="addCircleOutline"></ion-icon>
                      <span>Crear Complejo</span>
                    </button>
                  </form>
                </div>
              </div>
            </div>

            <!-- PESTAÑA 2: USUARIOS Y ACTIVIDAD -->
            <div v-if="vista === 'usuarios'" class="tab-content animate-fade-in">
              <div class="admin-grid-layout">
                <!-- Tabla Usuarios -->
                <div class="detail-white-card main-col">
                  <div class="card-header-flex">
                    <h2>Usuarios Existentes</h2>
                  </div>

                  <div class="table-responsive">
                    <table class="sportra-table">
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
                          <td class="font-bold cell-title">
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
                              <button class="btn-action-sm btn-info" type="button" @click="verActividad(u)" title="Ver Historial">
                                <ion-icon :icon="eyeOutline"></ion-icon>
                                <span>Actividad</span>
                              </button>
                              <button 
                                type="button"
                                :class="['btn-action-sm', u.activo ? 'btn-danger' : 'btn-success']" 
                                @click="toggleUsuario(u)"
                              >
                                <ion-icon :icon="u.activo ? lockClosedOutline : lockOpenOutline"></ion-icon>
                                <span>{{ u.activo ? 'Bloquear' : 'Activar' }}</span>
                              </button>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Formulario Crear Usuario -->
                <div class="detail-white-card side-col">
                  <div class="card-header-flex">
                    <h2>Crear Encargado</h2>
                  </div>

                  <form @submit.prevent="crearUsuario" class="sportra-form">
                    <div class="field-block">
                      <label>Nombre Completo</label>
                      <input v-model="nuevoUsuario.name" type="text" placeholder="Ej: Carlos Ruiz" required class="sportra-input" />
                    </div>

                    <div class="field-block">
                      <label>Correo Electrónico</label>
                      <input v-model="nuevoUsuario.email" type="email" placeholder="carlos@ejemplo.com" required class="sportra-input" />
                    </div>

                    <div class="field-block">
                      <label>Contraseña</label>
                      <input v-model="nuevoUsuario.password" type="password" placeholder="••••••••" required class="sportra-input" />
                    </div>

                    <div class="field-block">
                      <label>Asignar Complejo</label>
                      <ion-select v-model="nuevoUsuario.complejo_id" interface="popover" placeholder="Seleccione..." class="sportra-select">
                        <ion-select-option v-for="c in facturacion" :key="c.complejo_id" :value="c.complejo_id">{{ c.nombre }}</ion-select-option>
                      </ion-select>
                    </div>

                    <div class="field-block">
                      <label>Rol asignado</label>
                      <ion-select v-model="nuevoUsuario.rol" interface="popover" class="sportra-select">
                        <ion-select-option value="propietario">Propietario</ion-select-option>
                        <ion-select-option value="encargado">Encargado</ion-select-option>
                      </ion-select>
                    </div>

                    <button type="submit" class="btn-primary-sportra" :disabled="!nuevoUsuario.email || !nuevoUsuario.password">
                      <ion-icon :icon="personAddOutline"></ion-icon>
                      <span>Crear Usuario</span>
                    </button>
                  </form>
                </div>
              </div>
            </div>

            <!-- VERIFICACIÓN DE CANCHAS -->
            <div v-if="vista === 'verificacion'" class="tab-content animate-fade-in">
              <div class="detail-white-card">
                <div class="card-header-flex">
                  <h2>Canchas pendientes de verificación</h2>
                  <span class="count-badge">{{ canchasRevision.length }} pendientes</span>
                </div>
                <div class="table-responsive">
                  <table class="sportra-table">
                    <thead>
                      <tr><th>Cancha</th><th>Complejo</th><th>Deporte</th><th>Observaciones</th><th>Revisión</th></tr>
                    </thead>
                    <tbody>
                      <tr v-for="cancha in canchasRevision" :key="cancha.id">
                        <td class="font-bold cell-title">{{ cancha.nombre }}</td>
                        <td>{{ cancha.complejo?.nombre }}</td>
                        <td>{{ cancha.deporte?.nombre }}</td>
                        <td>
                          <input
                            v-model="observacionesRechazo[cancha.id]"
                            class="sportra-input"
                            type="text"
                            maxlength="1000"
                            placeholder="Motivo si se rechaza"
                          />
                        </td>
                        <td>
                          <div class="action-buttons-cell">
                            <button class="btn-action-sm btn-success" type="button" @click="resolverVerificacion(cancha, 'aprobada')">Aprobar</button>
                            <button
                              type="button"
                              class="btn-action-sm btn-danger"
                              :disabled="!observacionesRechazo[cancha.id]?.trim()"
                              @click="resolverVerificacion(cancha, 'rechazada')"
                            >Rechazar</button>
                          </div>
                        </td>
                      </tr>
                      <tr v-if="canchasRevision.length === 0">
                        <td colspan="5" class="text-center py-4 text-muted">No hay canchas pendientes de revisión.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- REPORTE MENSUAL -->
            <div v-if="vista === 'reporte'" class="tab-content animate-fade-in">
              <div class="detail-white-card">
                <div class="card-header-flex header-with-filter">
                  <div>
                    <h2>Actividad mensual por cancha</h2>
                    <p class="subtitle-text">El ingreso bruto es referencial según reservas registradas.</p>
                  </div>
                  <div class="action-buttons-cell filter-row">
                    <input v-model="mesReporte" type="month" class="sportra-input input-month" />
                    <button class="btn-action-sm btn-info" type="button" @click="cargarReporteMensual">Consultar</button>
                    <button class="btn-action-sm btn-success" type="button" @click="descargarReporteMensual">Exportar CSV</button>
                  </div>
                </div>
                <div class="table-responsive">
                  <table class="sportra-table">
                    <thead>
                      <tr>
                        <th>Complejo</th><th>Cancha</th><th>Solicitudes</th><th>Aceptadas</th><th>Rechazadas</th>
                        <th>Vencidas</th><th>Horas confirmadas</th><th>Ingreso bruto ref.</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="fila in reporteMensual" :key="fila.cancha_id">
                        <td class="font-bold cell-title">{{ fila.complejo }}</td>
                        <td>{{ fila.cancha }}</td>
                        <td>{{ fila.solicitudes_recibidas }}</td>
                        <td>{{ fila.aceptadas }}</td>
                        <td>{{ fila.rechazadas }}</td>
                        <td>{{ fila.vencidas }}</td>
                        <td>{{ fila.horas_confirmadas }}</td>
                        <td class="price-highlight font-bold">₡{{ Number(fila.ingreso_bruto_reservas).toLocaleString('es-CR') }}</td>
                      </tr>
                      <tr v-if="reporteMensual.length === 0">
                        <td colspan="8" class="text-center py-4 text-muted">No hay datos para este mes.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- FOTOS -->
            <div v-if="vista === 'fotos'" class="tab-content animate-fade-in">
              <div class="detail-white-card">
                <div class="card-header-flex">
                  <h2>Fotos pendientes de revisión</h2>
                  <span class="count-badge">{{ fotosRevision.length }} pendientes</span>
                </div>
                <div class="table-responsive">
                  <table class="sportra-table">
                    <thead><tr><th>Foto</th><th>Cancha</th><th>Complejo</th><th>Descripción</th><th>Observaciones</th><th>Moderación</th></tr></thead>
                    <tbody>
                      <tr v-for="foto in fotosRevision" :key="foto.id">
                        <td><img :src="foto.url" :alt="foto.caption || 'Foto de cancha'" class="admin-photo-preview" loading="lazy" /></td>
                        <td>{{ foto.cancha?.nombre }}</td>
                        <td>{{ foto.cancha?.complejo?.nombre }}</td>
                        <td>{{ foto.caption || 'Sin descripción' }}</td>
                        <td><input v-model="observacionesFoto[foto.id]" class="sportra-input" maxlength="1000" placeholder="Motivo rechazo..." /></td>
                        <td>
                          <div class="action-buttons-cell">
                            <button class="btn-action-sm btn-success" type="button" @click="resolverFoto(foto, 'aprobada')">Aprobar</button>
                            <button class="btn-action-sm btn-danger" type="button" :disabled="!observacionesFoto[foto.id]?.trim()" @click="resolverFoto(foto, 'rechazada')">Rechazar</button>
                          </div>
                        </td>
                      </tr>
                      <tr v-if="fotosRevision.length === 0"><td colspan="6" class="text-center py-4 text-muted">No hay fotos pendientes.</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- SEGURIDAD (2FA) -->
            <div v-if="vista === 'seguridad'" class="tab-content animate-fade-in">
              <div class="detail-white-card security-card">
                <div class="card-header-flex">
                  <div>
                    <h2>Verificación en dos pasos</h2>
                    <p class="subtitle-text">Protege la cuenta administradora mediante aplicaciones TOTP.</p>
                  </div>
                  <span :class="['status-pill', twoFactorActivo ? 'status-active' : 'status-warning']">
                    {{ twoFactorActivo ? 'Activa' : 'Inactiva' }}
                  </span>
                </div>

                <div v-if="!twoFactorActivo" class="security-form">
                  <form class="sportra-form" @submit.prevent="iniciarDosFactores">
                    <div class="field-block">
                      <label>Contraseña actual</label>
                      <input v-model="passwordDosFactores" type="password" autocomplete="current-password" class="sportra-input" required />
                    </div>
                    <button type="submit" class="btn-primary-sportra" :disabled="cargandoDosFactores">
                      <span>{{ cargandoDosFactores ? 'Preparando...' : 'Configurar aplicación' }}</span>
                    </button>
                  </form>

                  <div v-if="configuracionDosFactores" class="totp-enrollment">
                    <img v-if="codigoQr" :src="codigoQr" alt="Código QR de configuración TOTP" class="totp-qr" />
                    <div class="field-block">
                      <label>Clave manual</label>
                      <code class="totp-secret">{{ configuracionDosFactores.secret }}</code>
                    </div>
                    <form class="sportra-form" @submit.prevent="confirmarDosFactores">
                      <div class="field-block">
                        <label>Código de 6 dígitos</label>
                        <input v-model="codigoDosFactores" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" class="sportra-input" required />
                      </div>
                      <button type="submit" class="btn-primary-sportra" :disabled="cargandoDosFactores">
                        <span>Confirmar y activar</span>
                      </button>
                    </form>
                  </div>
                </div>

                <div v-else class="security-form">
                  <div v-if="codigosRecuperacion.length" class="recovery-codes-panel" role="status">
                    <h4>Códigos de recuperación</h4>
                    <p>Guárdalos ahora. Cada código se puede usar una sola vez y no volverán a mostrarse.</p>
                    <div class="codes-grid">
                      <code v-for="codigo in codigosRecuperacion" :key="codigo" class="recovery-code">{{ codigo }}</code>
                    </div>
                    <label class="recovery-confirmation">
                      <input v-model="recoveryCodesSaved" type="checkbox" />
                      <span>Guardé los códigos en un lugar seguro</span>
                    </label>
                  </div>

                  <form class="sportra-form" @submit.prevent="desactivarDosFactores">
                    <div class="field-block">
                      <label>Contraseña actual</label>
                      <input v-model="passwordDesactivar2FA" type="password" autocomplete="current-password" class="sportra-input" required />
                    </div>
                    <div class="field-block">
                      <label>Código TOTP o de recuperación</label>
                      <input v-model="codigoDesactivar2FA" type="text" autocomplete="one-time-code" class="sportra-input" required />
                    </div>
                    <button type="submit" class="btn-action-sm btn-danger btn-block" :disabled="cargandoDosFactores">Desactivar 2FA</button>
                  </form>
                </div>

                <p v-if="mensajeDosFactores" class="message-banner" role="status">{{ mensajeDosFactores }}</p>
                <p v-if="errorDosFactores" class="message-banner error" role="alert">{{ errorDosFactores }}</p>
                <button v-if="codigosRecuperacion.length" class="btn-primary-sportra" :disabled="!recoveryCodesSaved" @click="cerrarSesionTras2FA">
                  <span>Volver a iniciar sesión</span>
                </button>
              </div>
            </div>

            <!-- MOVIMIENTOS / RESERVAS GLOBALES -->
            <div v-if="vista === 'movimientos'" class="tab-content animate-fade-in">
              <div class="detail-white-card">
                <div class="card-header-flex header-with-filter">
                  <div>
                    <h2>Historial Global de Reservas</h2>
                    <p class="subtitle-text">Auditoría de reservas en todos los complejos registrados.</p>
                  </div>

                  <div class="filter-box">
                    <ion-select 
                      v-model="filtroComplejo" 
                      interface="popover" 
                      placeholder="Filtrar por complejo" 
                      class="sportra-select filter-select"
                      @ionChange="cargarMovimientos"
                    >
                      <ion-select-option :value="null">Todos los Complejos</ion-select-option>
                      <ion-select-option v-for="c in facturacion" :key="c.complejo_id" :value="c.complejo_id">{{ c.nombre }}</ion-select-option>
                    </ion-select>

                    <ion-select
                      v-model="filtroCancha"
                      interface="popover"
                      placeholder="Filtrar por cancha"
                      class="sportra-select filter-select"
                      @ionChange="cargarMovimientos"
                    >
                      <ion-select-option :value="null">Todas las canchas</ion-select-option>
                      <ion-select-option v-for="cancha in canchasTodas" :key="cancha.id" :value="cancha.id">
                        {{ cancha.complejo?.nombre }} — {{ cancha.nombre }}
                      </ion-select-option>
                    </ion-select>
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="sportra-table">
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
                        <td class="font-bold price-highlight">{{ m.fecha }} <small class="text-muted">({{ m.hora_inicio }} - {{ m.hora_fin }})</small></td>
                        <td>{{ m.cancha?.complejo?.nombre }} — <span class="cell-title font-bold">{{ m.cancha?.nombre }}</span></td>
                        <td>{{ m.nombre_cliente }}</td>
                        <td>
                          <span :class="['status-pill', m.estado === 'confirmada' ? 'status-active' : 'status-tramite']">
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
            </div>

          </div>
        </section>

        <!-- FOOTER -->
        <footer class="site-footer">
          <div class="section-container">
            <div class="footer-bottom">
              <p>Módulo de administración central SPORTRA.</p>
              <p class="footer-sub">© 2026 SPORTRA. Todos los derechos reservados.</p>
            </div>
          </div>
        </footer>

      </div>
    </ion-content>

    <!-- MODAL REGISTRAR PAGO -->
    <div v-if="complejoPago" class="modal-backdrop">
      <div class="modal-card-wrapper animate-pop">
        <div class="modal-header">
          <div>
            <span class="eyebrow">PAGO DE SUSCRIPCIÓN</span>
            <h2>{{ complejoPago.nombre }}</h2>
          </div>
          <button class="close-btn" type="button" @click="complejoPago = null" aria-label="Cerrar">
            <ion-icon :icon="closeOutline"></ion-icon>
          </button>
        </div>

        <form @submit.prevent="guardarPago" class="modal-body-form">
          <div class="form-field">
            <label>Monto Pago (₡)</label>
            <input v-model.number="nuevoPago.monto" type="number" required />
          </div>

          <div class="form-grid-2">
            <div class="form-field">
              <label>Vigencia Desde</label>
              <input v-model="nuevoPago.periodo_desde" type="date" required />
            </div>
            <div class="form-field">
              <label>Vigencia Hasta</label>
              <input v-model="nuevoPago.periodo_hasta" type="date" required />
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-field">
              <label>Fecha del Pago</label>
              <input v-model="nuevoPago.fecha_pago" type="date" required />
            </div>
            <div class="form-field">
              <label>Método de Pago</label>
              <ion-select v-model="nuevoPago.metodo" interface="popover" class="sportra-modal-select">
                <ion-select-option value="sinpe">SINPE Móvil</ion-select-option>
                <ion-select-option value="transferencia">Transferencia Bancaria</ion-select-option>
                <ion-select-option value="efectivo">Efectivo</ion-select-option>
              </ion-select>
            </div>
          </div>

          <div class="modal-actions-footer">
            <button type="button" class="cancel-btn" @click="complejoPago = null">Cancelar</button>
            <button type="submit" class="submit-btn">
              <span>Guardar Pago</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL ACTIVIDAD DE USUARIO -->
    <div v-if="usuarioActividad" class="modal-backdrop">
      <div class="modal-card-wrapper animate-pop">
        <div class="modal-header">
          <div>
            <span class="eyebrow">BITÁCORA DE ACTIVIDAD</span>
            <h2>{{ usuarioActividad.name }}</h2>
          </div>
          <button class="close-btn" type="button" @click="usuarioActividad = null" aria-label="Cerrar">
            <ion-icon :icon="closeOutline"></ion-icon>
          </button>
        </div>

        <div class="modal-body-form user-logs-container">
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
import QRCode from 'qrcode';
import {
  shieldCheckmarkOutline, logOutOutline, businessOutline, peopleOutline,
  receiptOutline, checkmarkCircleOutline, closeCircleOutline, cardOutline,
  lockClosedOutline, lockOpenOutline, addCircleOutline, eyeOutline, personAddOutline,
  imagesOutline, keyOutline, closeOutline
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
  'person-add-outline': personAddOutline,
  'images-outline': imagesOutline,
  'key-outline': keyOutline,
  'close-outline': closeOutline
});

const router = useRouter();
const authStore = useAuthStore();

const vista = ref<'complejos' | 'usuarios' | 'movimientos' | 'verificacion' | 'reporte' | 'fotos' | 'seguridad'>('complejos');

const facturacion = ref<any[]>([]);
const usuarios = ref<any[]>([]);
const movimientos = ref<any[]>([]);
const canchasTodas = ref<any[]>([]);
const canchasRevision = ref<any[]>([]);
const fotosRevision = ref<any[]>([]);
const observacionesFoto = ref<Record<number, string>>({});
const observacionesRechazo = ref<Record<number, string>>({});
const reporteMensual = ref<any[]>([]);
const mensaje = ref('');
const fechaActual = new Date();
const mesReporte = ref(`${fechaActual.getFullYear()}-${String(fechaActual.getMonth() + 1).padStart(2, '0')}`);
const twoFactorActivo = ref(false);
const passwordDosFactores = ref('');
const passwordDesactivar2FA = ref('');
const codigoDosFactores = ref('');
const codigoDesactivar2FA = ref('');
const codigoQr = ref('');
const configuracionDosFactores = ref<{ secret: string; otpauth_url: string } | null>(null);
const codigosRecuperacion = ref<string[]>([]);
const recoveryCodesSaved = ref(false);
const cargandoDosFactores = ref(false);
const mensajeDosFactores = ref('');
const errorDosFactores = ref('');

const filtroComplejo = ref<number | null>(null);
const filtroCancha = ref<number | null>(null);

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
    const { data } = await adminService.movimientos({
      complejo_id: filtroComplejo.value ?? undefined,
      cancha_id: filtroCancha.value ?? undefined,
    });
    movimientos.value = data.data.data;
  } catch (error) {
    console.error('Error cargando movimientos:', error);
  }
}

async function cargarCanchasTodas() {
  try {
    const { data } = await adminService.canchas();
    canchasTodas.value = data.data.data;
  } catch (error) {
    console.error('Error cargando canchas para el historial:', error);
  }
}

async function cargarVerificaciones() {
  try {
    const { data } = await adminService.canchasPorVerificar();
    canchasRevision.value = data.data.data;
  } catch (error) {
    console.error('Error cargando canchas pendientes:', error);
  }
}

async function cargarFotosRevision() {
  try {
    const { data } = await adminService.fotosPendientes();
    fotosRevision.value = data.data.data;
  } catch {
    mensaje.value = 'No se pudieron cargar las fotos pendientes.';
  }
}

async function resolverFoto(foto: any, estado_verificacion: 'aprobada' | 'rechazada') {
  try {
    await adminService.verificarFoto(foto.id, {
      estado_verificacion,
      observaciones_admin: estado_verificacion === 'rechazada' ? observacionesFoto.value[foto.id]?.trim() : undefined,
    });
    mensaje.value = estado_verificacion === 'aprobada' ? 'Foto aprobada.' : 'Foto rechazada.';
    await cargarFotosRevision();
  } catch (error: any) {
    mensaje.value = error.response?.data?.message || 'No se pudo moderar la foto.';
  }
}

async function iniciarDosFactores() {
  cargandoDosFactores.value = true;
  errorDosFactores.value = '';
  mensajeDosFactores.value = '';
  try {
    const { data } = await adminService.iniciar2FA(passwordDosFactores.value);
    configuracionDosFactores.value = data.data;
    codigoQr.value = await QRCode.toDataURL(data.data.otpauth_url, { width: 240, margin: 2, errorCorrectionLevel: 'M' });
  } catch (error: any) {
    errorDosFactores.value = error.response?.data?.message || 'No se pudo iniciar la configuración 2FA.';
  } finally {
    cargandoDosFactores.value = false;
  }
}

async function confirmarDosFactores() {
  cargandoDosFactores.value = true;
  errorDosFactores.value = '';
  try {
    const { data } = await adminService.confirmar2FA(codigoDosFactores.value);
    codigosRecuperacion.value = data.data.recovery_codes;
    recoveryCodesSaved.value = false;
    twoFactorActivo.value = true;
    configuracionDosFactores.value = null;
    codigoQr.value = '';
    if (authStore.usuario) authStore.usuario.two_factor_enabled = true;
    mensajeDosFactores.value = '2FA activado con éxito.';
  } catch (error: any) {
    errorDosFactores.value = error.response?.data?.message || 'El código no es válido o ya fue utilizado.';
  } finally {
    cargandoDosFactores.value = false;
  }
}

async function desactivarDosFactores() {
  cargandoDosFactores.value = true;
  errorDosFactores.value = '';
  try {
    await adminService.desactivar2FA({ password: passwordDesactivar2FA.value, code: codigoDesactivar2FA.value });
    twoFactorActivo.value = false;
    if (authStore.usuario) authStore.usuario.two_factor_enabled = false;
    mensajeDosFactores.value = '2FA desactivado.';
    passwordDesactivar2FA.value = '';
    codigoDesactivar2FA.value = '';
  } catch (error: any) {
    errorDosFactores.value = error.response?.data?.message || 'No se pudo desactivar 2FA.';
  } finally {
    cargandoDosFactores.value = false;
  }
}

async function cerrarSesionTras2FA() {
  try {
    await authStore.logout();
  } catch {
    localStorage.removeItem('token');
  }
  router.replace('/login');
}

async function resolverVerificacion(cancha: any, estado_verificacion: 'aprobada' | 'rechazada') {
  try {
    await adminService.verificarCancha(cancha.id, {
      estado_verificacion,
      observaciones_admin: observacionesRechazo.value[cancha.id],
    });
    mensaje.value = estado_verificacion === 'aprobada' ? 'Cancha aprobada.' : 'Cancha rechazada con observaciones.';
    await cargarVerificaciones();
  } catch (error: any) {
    mensaje.value = error.response?.data?.message || 'No se pudo guardar la revisión.';
  }
}

async function cargarReporteMensual() {
  try {
    const { data } = await adminService.reporteMensual(mesReporte.value);
    reporteMensual.value = data.data;
  } catch (error) {
    console.error('Error cargando reporte mensual:', error);
  }
}

async function descargarReporteMensual() {
  try {
    const { data } = await adminService.exportarReporteMensual(mesReporte.value);
    const url = URL.createObjectURL(new Blob([data], { type: 'text/csv;charset=utf-8' }));
    const enlace = document.createElement('a');
    enlace.href = url;
    enlace.download = `sportra-reporte-${mesReporte.value}.csv`;
    enlace.click();
    URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error exportando reporte mensual:', error);
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
  } catch (error: any) {
    mensaje.value = error.response?.data?.message || 'No se pudo registrar el pago. Revisa los datos.';
  }
}

async function toggleComplejo(complejo: any) {
  try {
    await adminService.toggleEstadoComplejo(complejo.complejo_id);
    mensaje.value = complejo.activo ? 'Complejo bloqueado.' : 'Complejo activado.';
    await cargarDatos();
  } catch (error: any) {
    mensaje.value = error.response?.data?.message || 'No se pudo cambiar el estado del complejo.';
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
  if (c.estado_suscripcion === 'vencida') return 'Vencida';
  if (c.estado_suscripcion === 'por_vencer') return `Vence pronto (${c.suscripcion_vence_en})`;
  if (c.estado_suscripcion === 'al_dia') return `Al día (${c.suscripcion_vence_en})`;
  return 'Sin registro';
}

watch(vista, (nuevaVista) => {
  if (nuevaVista === 'movimientos') {
    cargarMovimientos();
    cargarCanchasTodas();
  }
  if (nuevaVista === 'verificacion') cargarVerificaciones();
  if (nuevaVista === 'reporte') cargarReporteMensual();
  if (nuevaVista === 'fotos') cargarFotosRevision();
  if (nuevaVista === 'seguridad') {
    twoFactorActivo.value = Boolean(authStore.usuario?.two_factor_enabled);
    mensajeDosFactores.value = '';
    errorDosFactores.value = '';
  }
});

onMounted(async () => {
  try {
    await authStore.cargarUsuario();
    if (!authStore.usuario?.is_platform_admin) {
      router.replace('/panel');
      return;
    }
    await Promise.all([cargarDatos(), cargarProvincias()]);
  } catch {
    router.replace('/login');
  }
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

/* =========================================================
   VARIABLES Y ESTILO BASE
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
   HEADER & NAVBAR
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
  justify-content: space-between;
}

.brand-copy strong {
  font-size: 20px;
  font-weight: 800;
  letter-spacing: -0.03em;
  color: #fff;
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

.btn-logout {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid var(--border-dark);
  color: #fff;
  padding: 8px 16px;
  border-radius: 10px;
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  transition: 0.2s ease;
}

.btn-logout:hover {
  background: rgba(123, 150, 255, 0.15);
  border-color: var(--blue-accent);
}

/* =========================================================
   HERO BANNER OSCURO
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

.admin-hero-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  background: var(--bg-card);
  border: 1px solid var(--border-dark);
  border-radius: 20px;
  padding: 28px 32px;
  margin-bottom: 20px;
}

.location-chip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 4px 12px;
  background: rgba(123, 150, 255, 0.15);
  border-radius: 99px;
  font-size: 11px;
  font-weight: 700;
  color: var(--blue-accent);
  margin-bottom: 12px;
}

.pulse-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: var(--blue-accent);
  box-shadow: 0 0 0 0 rgba(123, 150, 255, 0.7);
  animation: pulse 2s infinite;
}

.page-title {
  font-size: 34px;
  font-weight: 800;
  margin: 0 0 6px;
  color: #ffffff !important;
  letter-spacing: -0.02em;
}

.page-subtitle {
  font-size: 13px;
  color: var(--text-muted) !important;
  margin: 0;
}

.metrics-row {
  display: flex;
  gap: 12px;
}

.metric-card {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid var(--border-dark);
  border-radius: 14px;
  padding: 14px 20px;
  min-width: 110px;
  display: flex;
  flex-direction: column;
}

.metric-card.highlight {
  background: rgba(123, 150, 255, 0.1);
  border-color: rgba(123, 150, 255, 0.3);
}

.metric-label {
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.08em;
  color: var(--text-muted);
  margin-bottom: 4px;
}

.metric-value {
  font-size: 26px;
  font-weight: 800;
  color: #ffffff;
}

/* MENU TAB EN HERO */

.tab-menu-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.tab-btn {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid var(--border-dark);
  color: var(--text-muted);
  padding: 10px 18px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: 0.2s ease;
}

.tab-btn:hover {
  background: rgba(255, 255, 255, 0.08);
  color: #fff;
}

.tab-btn.active {
  background: var(--blue-accent);
  color: #060e2d;
  border-color: var(--blue-accent);
}

/* =========================================================
   SECCIÓN INFERIOR CLARA
   ========================================================= */

.main-details-section {
  background: var(--bg-light);
  color: var(--text-dark);
  padding: 40px 24px 80px;
  min-height: 60vh;
}

.admin-grid-layout {
  display: grid;
  grid-template-columns: 1.4fr 0.85fr;
  gap: 28px;
  align-items: start;
}

/* CARDS BLANCAS DE ALTO CONTRAS TE */

.detail-white-card {
  background: #ffffff;
  border-radius: 20px;
  padding: 28px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.card-header-flex {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.card-header-flex h2 {
  font-size: 20px;
  font-weight: 800;
  margin: 0;
  color: var(--text-dark);
}

.subtitle-text {
  font-size: 12px;
  color: #64748b;
  margin: 4px 0 0;
}

.count-badge {
  font-size: 12px;
  color: #64748b;
  font-weight: 700;
}

/* =========================================================
   TABLAS (TEXTO OSCURO LEGIBLE)
   ========================================================= */

.table-responsive {
  width: 100%;
  overflow-x: auto;
}

.sportra-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 13px;
}

.sportra-table th {
  padding: 12px 14px;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #64748b;
  border-bottom: 1px solid #e2e8f0;
  background: #f8fafc;
}

.sportra-table td {
  padding: 14px 14px;
  border-bottom: 1px solid #f1f5f9;
  color: #334155;
  vertical-align: middle;
}

.sportra-table tbody tr:hover {
  background: #f8fafc;
}

.cell-title {
  color: var(--text-dark) !important;
  font-weight: 700;
}

.price-highlight {
  color: #0c1743 !important;
  font-weight: 800;
}

.complexes-tag {
  display: inline-block;
  padding: 4px 10px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 11px;
  color: #475569;
  font-weight: 600;
}

/* ESTADOS (BADGES) EN CLARO */

.status-pill {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 99px;
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.status-active {
  background: #dcfce7;
  color: #15803d;
  border: 1px solid #bbf7d0;
}

.status-warning {
  background: #fef3c7;
  color: #b45309;
  border: 1px solid #fde68a;
}

.status-danger {
  background: #fee2e2;
  color: #b91c1c;
  border: 1px solid #fca5a5;
}

.status-neutral {
  background: #f1f5f9;
  color: #64748b;
  border: 1px solid #e2e8f0;
}

.status-tramite {
  background: #eef2ff;
  color: #4338ca;
  border: 1px solid #c7d2fe;
}

.tag-blocked {
  display: inline-block;
  margin-left: 6px;
  padding: 2px 6px;
  background: #ef4444;
  color: #fff;
  border-radius: 4px;
  font-size: 8px;
  font-weight: 800;
}

/* =========================================================
   BOTONES EN TABLAS
   ========================================================= */

.action-buttons-cell {
  display: flex;
  gap: 6px;
  justify-content: flex-end;
}

.btn-action-sm {
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 11px;
  font-weight: 700;
  border: 1px solid transparent;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  transition: 0.2s ease;
}

.btn-pay {
  background: #eef2ff;
  color: #3730a3;
  border-color: #c7d2fe;
}

.btn-pay:hover {
  background: #4338ca;
  color: #ffffff;
}

.btn-info {
  background: #f1f5f9;
  color: #334155;
  border-color: #cbd5e1;
}

.btn-info:hover {
  background: #334155;
  color: #ffffff;
}

.btn-danger {
  background: #fef2f2;
  color: #dc2626;
  border-color: #fca5a5;
}

.btn-danger:hover:not(:disabled) {
  background: #dc2626;
  color: #ffffff;
}

.btn-success {
  background: #f0fdf4;
  color: #16a34a;
  border-color: #bbf7d0;
}

.btn-success:hover {
  background: #16a34a;
  color: #ffffff;
}

.btn-action-sm:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* =========================================================
   FORMULARIOS
   ========================================================= */

.sportra-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.field-block {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.field-block label {
  font-size: 11px;
  font-weight: 700;
  color: #475569;
}

.sportra-input {
  background: #f8fafc;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  padding: 10px 14px;
  color: var(--text-dark);
  font-size: 13px;
  outline: none;
}

.sportra-input::placeholder {
  color: #94a3b8;
}

.sportra-input:focus {
  border-color: var(--blue-accent);
  background: #ffffff;
}

.sportra-select {
  --background: #f8fafc;
  --color: #091133;
  --placeholder-color: #94a3b8;
  --placeholder-opacity: 1;
  --padding-start: 14px;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  min-height: 42px;
  font-size: 13px;
  background: #f8fafc;
}

.btn-primary-sportra {
  width: 100%;
  padding: 12px;
  background: var(--blue-accent);
  border: 0;
  border-radius: 12px;
  color: #060e2d;
  font-size: 13px;
  font-weight: 800;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: 0.2s ease;
  margin-top: 8px;
}

.btn-primary-sportra:hover:not(:disabled) {
  background: var(--blue-hover);
}

.btn-primary-sportra:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* =========================================================
   MODALES Y TOAST
   ========================================================= */

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(6, 14, 45, 0.8);
  backdrop-filter: blur(10px);
  z-index: 2000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.modal-card-wrapper {
  max-width: 500px;
  width: 100%;
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
  margin: 4px 0 0;
  color: #fff;
}

.eyebrow {
  font-size: 9px;
  font-weight: 800;
  letter-spacing: 0.1em;
  color: var(--blue-accent);
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

.form-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
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

.form-field input {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--border-dark);
  border-radius: 10px;
  padding: 10px 14px;
  color: #fff;
  font-size: 13px;
  outline: none;
}

.sportra-modal-select {
  --background: rgba(255, 255, 255, 0.05);
  --color: #ffffff;
  --placeholder-color: #8e9cc0;
  --padding-start: 14px;
  border: 1px solid var(--border-dark);
  border-radius: 10px;
  min-height: 42px;
  font-size: 13px;
}

.modal-actions-footer {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: 10px;
  margin-top: 8px;
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
  background: var(--blue-accent);
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

/* NOTIFICACIÓN TOAST */

.notification-toast {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #eef2ff;
  border: 1px solid #c7d2fe;
  padding: 12px 18px;
  border-radius: 12px;
  color: #3730a3;
  font-size: 13px;
  font-weight: 700;
  margin-bottom: 24px;
}

.toast-icon {
  color: #4338ca;
  font-size: 18px;
}

.btn-close-toast {
  margin-left: auto;
  background: transparent;
  border: 0;
  color: #6366f1;
  cursor: pointer;
  font-size: 16px;
}

/* FOTOS & SEGURIDAD */

.admin-photo-preview {
  width: 90px;
  height: 60px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.security-card {
  max-width: 600px;
}

.security-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-top: 16px;
}

.totp-enrollment {
  display: flex;
  flex-direction: column;
  gap: 16px;
  align-items: center;
}

.totp-qr {
  border-radius: 12px;
  padding: 8px;
  background: #fff;
  border: 1px solid #e2e8f0;
}

.totp-secret {
  background: #f1f5f9;
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 12px;
  color: #0f172a;
}

.recovery-codes-panel {
  background: #fffbeb;
  border: 1px solid #fde68a;
  border-radius: 12px;
  padding: 16px;
  color: #92400e;
}

.codes-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
  margin: 12px 0;
}

.recovery-code {
  background: #ffffff;
  padding: 6px;
  text-align: center;
  border-radius: 6px;
  font-size: 11px;
  border: 1px solid #fef3c7;
}

.user-logs-container {
  max-height: 300px;
  overflow-y: auto;
}

.log-row {
  padding: 10px;
  border-bottom: 1px solid var(--border-dark);
  font-size: 12px;
}

.log-time {
  font-size: 10px;
  color: var(--blue-accent);
}

.filter-box {
  display: flex;
  gap: 8px;
}

.filter-row {
  display: flex;
  gap: 8px;
  align-items: center;
}

.input-month {
  padding: 6px 12px;
  font-size: 12px;
}

.btn-block {
  width: 100%;
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

@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(123, 150, 255, 0.7); }
  70% { box-shadow: 0 0 0 8px rgba(123, 150, 255, 0); }
  100% { box-shadow: 0 0 0 0 rgba(123, 150, 255, 0); }
}

@media (max-width: 960px) {
  .site-header { padding: 12px 20px; }
  .admin-grid-layout { grid-template-columns: 1fr; }
  .admin-hero-card { flex-direction: column; align-items: flex-start; }
  .metrics-row { width: 100%; justify-content: space-between; }
  .footer-bottom { flex-direction: column; gap: 10px; text-align: center; }
}
</style>

```