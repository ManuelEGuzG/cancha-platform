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
            <span class="eyebrow">
              <span class="pulse-indicator"></span>
              Administración de la plataforma
            </span>
            <h1 class="page-title">Panel Administrativo Global</h1>
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
          <button
            :class="['tab-btn', { active: vista === 'verificacion' }]"
            @click="vista = 'verificacion'"
          >
            <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
            <span>Verificación de Canchas</span>
          </button>
          <button
            :class="['tab-btn', { active: vista === 'reporte' }]"
            @click="vista = 'reporte'"
          >
            <ion-icon :icon="receiptOutline"></ion-icon>
            <span>Reporte Mensual</span>
          </button>
          <button :class="['tab-btn', { active: vista === 'fotos' }]" @click="vista = 'fotos'">
            <ion-icon :icon="imagesOutline"></ion-icon>
            <span>Fotos</span>
          </button>
          <button :class="['tab-btn', { active: vista === 'seguridad' }]" @click="vista = 'seguridad'">
            <ion-icon :icon="keyOutline"></ion-icon>
            <span>Seguridad</span>
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

        <section v-if="vista === 'verificacion'" class="tab-content animate-fade-in">
          <div class="section-card">
            <div class="card-header">
              <h3>Canchas pendientes de verificación</h3>
              <span class="counter-pill">{{ canchasRevision.length }}</span>
            </div>
            <div class="table-responsive">
              <table class="neon-table">
                <thead>
                  <tr><th>Cancha</th><th>Complejo</th><th>Deporte</th><th>Observaciones</th><th>Revisión</th></tr>
                </thead>
                <tbody>
                  <tr v-for="cancha in canchasRevision" :key="cancha.id">
                    <td class="font-bold">{{ cancha.nombre }}</td>
                    <td>{{ cancha.complejo?.nombre }}</td>
                    <td>{{ cancha.deporte?.nombre }}</td>
                    <td>
                      <input
                        v-model="observacionesRechazo[cancha.id]"
                        class="neon-input"
                        type="text"
                        maxlength="1000"
                        placeholder="Motivo si se rechaza"
                      />
                    </td>
                    <td>
                      <div class="action-buttons-cell">
                        <button class="btn-action-sm btn-success" @click="resolverVerificacion(cancha, 'aprobada')">Aprobar</button>
                        <button
                          class="btn-action-sm btn-danger"
                          :disabled="!observacionesRechazo[cancha.id]?.trim()"
                          @click="resolverVerificacion(cancha, 'rechazada')"
                        >Rechazar</button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="canchasRevision.length === 0">
                    <td colspan="5" class="text-center py-4">No hay canchas pendientes de revisión.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <section v-if="vista === 'reporte'" class="tab-content animate-fade-in">
          <div class="section-card">
            <div class="card-header header-with-filter">
              <div>
                <h3>Actividad mensual por cancha</h3>
                <p>El ingreso bruto es referencial; la regla de cobro todavía no está definida.</p>
              </div>
              <div class="action-buttons-cell">
                <input v-model="mesReporte" type="month" class="neon-input" />
                <button class="btn-action-sm btn-info" @click="cargarReporteMensual">Consultar</button>
                <button class="btn-action-sm btn-success" @click="descargarReporteMensual">Exportar CSV</button>
              </div>
            </div>
            <div class="table-responsive">
              <table class="neon-table">
                <thead>
                  <tr>
                    <th>Complejo</th><th>Cancha</th><th>Solicitudes</th><th>Aceptadas</th><th>Rechazadas</th>
                    <th>Vencidas</th><th>Horas confirmadas</th><th>Ingreso bruto ref.</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="fila in reporteMensual" :key="fila.cancha_id">
                    <td class="font-bold">{{ fila.complejo }}</td>
                    <td>{{ fila.cancha }}</td>
                    <td>{{ fila.solicitudes_recibidas }}</td>
                    <td>{{ fila.aceptadas }}</td>
                    <td>{{ fila.rechazadas }}</td>
                    <td>{{ fila.vencidas }}</td>
                    <td>{{ fila.horas_confirmadas }}</td>
                    <td class="text-lime font-bold">₡{{ Number(fila.ingreso_bruto_reservas).toLocaleString('es-CR') }}</td>
                  </tr>
                  <tr v-if="reporteMensual.length === 0">
                    <td colspan="8" class="text-center py-4">No hay datos para este mes.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <section v-if="vista === 'fotos'" class="tab-content animate-fade-in">
          <div class="section-card">
            <div class="card-header">
              <h3>Fotos pendientes de revisión</h3>
              <span class="counter-pill">{{ fotosRevision.length }}</span>
            </div>
            <div class="table-responsive">
              <table class="neon-table">
                <thead><tr><th>Foto</th><th>Cancha</th><th>Complejo</th><th>Descripción</th><th>Observaciones</th><th>Moderación</th></tr></thead>
                <tbody>
                  <tr v-for="foto in fotosRevision" :key="foto.id">
                    <td><img :src="foto.url" :alt="foto.caption || 'Foto de cancha pendiente'" class="admin-photo-preview" loading="lazy" /></td>
                    <td>{{ foto.cancha?.nombre }}</td>
                    <td>{{ foto.cancha?.complejo?.nombre }}</td>
                    <td>{{ foto.caption || 'Sin descripción' }}</td>
                    <td><input v-model="observacionesFoto[foto.id]" class="neon-input" maxlength="1000" placeholder="Motivo si se rechaza" /></td>
                    <td>
                      <div class="action-buttons-cell">
                        <button class="btn-action-sm btn-success" @click="resolverFoto(foto, 'aprobada')">Aprobar</button>
                        <button class="btn-action-sm btn-danger" :disabled="!observacionesFoto[foto.id]?.trim()" @click="resolverFoto(foto, 'rechazada')">Rechazar</button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="fotosRevision.length === 0"><td colspan="6" class="text-center py-4">No hay fotos pendientes.</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <section v-if="vista === 'seguridad'" class="tab-content animate-fade-in">
          <div class="section-card security-section">
            <div class="card-header">
              <div><h3>Verificación en dos pasos</h3><p>Usa una aplicación TOTP para proteger la cuenta administradora.</p></div>
              <span :class="['status-pill', twoFactorActivo ? 'status-active' : 'status-warning']">
                {{ twoFactorActivo ? 'Activa' : 'Inactiva' }}
              </span>
            </div>

            <div v-if="!twoFactorActivo" class="security-form">
              <form class="neon-form" @submit.prevent="iniciarDosFactores">
                <div class="field-block">
                  <label>Contraseña actual</label>
                  <input v-model="passwordDosFactores" type="password" autocomplete="current-password" class="neon-input" required />
                </div>
                <button type="submit" class="btn-submit-neon" :disabled="cargandoDosFactores">
                  {{ cargandoDosFactores ? 'Preparando...' : 'Configurar aplicación' }}
                </button>
              </form>

              <div v-if="configuracionDosFactores" class="totp-enrollment">
                <img v-if="codigoQr" :src="codigoQr" alt="Código QR de configuración TOTP" class="totp-qr" />
                <div class="field-block">
                  <label>Clave manual</label>
                  <code class="totp-secret">{{ configuracionDosFactores.secret }}</code>
                </div>
                <form class="neon-form" @submit.prevent="confirmarDosFactores">
                  <div class="field-block">
                    <label>Código de 6 dígitos</label>
                    <input v-model="codigoDosFactores" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" class="neon-input" required />
                  </div>
                  <button type="submit" class="btn-submit-neon" :disabled="cargandoDosFactores">Confirmar y activar</button>
                </form>
              </div>
            </div>

            <div v-else class="security-form">
              <div v-if="codigosRecuperacion.length" class="recovery-codes-panel" role="status">
                <h4>Códigos de recuperación</h4>
                <p>Guárdalos ahora. Cada código se puede usar una sola vez y no volverán a mostrarse.</p>
                <code v-for="codigo in codigosRecuperacion" :key="codigo" class="recovery-code">{{ codigo }}</code>
                <label class="recovery-confirmation">
                  <input v-model="recoveryCodesSaved" type="checkbox" />
                  Guardé los códigos en un lugar seguro
                </label>
              </div>

              <form class="neon-form" @submit.prevent="desactivarDosFactores">
                <div class="field-block">
                  <label>Contraseña actual</label>
                  <input v-model="passwordDesactivar2FA" type="password" autocomplete="current-password" class="neon-input" required />
                </div>
                <div class="field-block">
                  <label>Código TOTP o de recuperación</label>
                  <input v-model="codigoDesactivar2FA" type="text" autocomplete="one-time-code" class="neon-input" required />
                </div>
                <button type="submit" class="btn-action-sm btn-danger" :disabled="cargandoDosFactores">Desactivar 2FA</button>
              </form>
            </div>

            <p v-if="mensajeDosFactores" class="message-banner" role="status">{{ mensajeDosFactores }}</p>
            <p v-if="errorDosFactores" class="message-banner error" role="alert">{{ errorDosFactores }}</p>
            <button v-if="codigosRecuperacion.length" class="btn-submit-neon" :disabled="!recoveryCodesSaved" @click="cerrarSesionTras2FA">Volver a iniciar sesión</button>
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
                <ion-select
                  v-model="filtroCancha"
                  interface="popover"
                  placeholder="Filtrar por cancha"
                  class="neon-select filter-select"
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
import QRCode from 'qrcode';
import {
  shieldCheckmarkOutline, logOutOutline, businessOutline, peopleOutline,
  receiptOutline, checkmarkCircleOutline, closeCircleOutline, cardOutline,
  lockClosedOutline, lockOpenOutline, addCircleOutline, eyeOutline, personAddOutline,
  imagesOutline, keyOutline
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
  ,'images-outline': imagesOutline
  ,'key-outline': keyOutline
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
    mensajeDosFactores.value = '2FA activado. Los códigos se muestran una sola vez; tus sesiones anteriores se cerraron.';
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
/* ==========================================================
   PALETA SPORTRA
   #050A30  Azul noche      #0B2D9A  Azul profundo
   #2B59C3  Azul medio      #7BAAF7  Azul cielo
   #F5F7FF  Blanco azulado
   ========================================================== */
.sportra-admin-page {
  --c-night: #050A30;
  --c-deep: #0B2D9A;
  --c-mid: #2B59C3;
  --c-sky: #7BAAF7;
  --c-ice: #F5F7FF;

  /* Tonos derivados de la paleta */
  --c-surface: #FFFFFF;
  --c-hover: #EDF2FF;
  --c-line: #DCE5FA;
  --c-line-strong: #C2D2F5;
  --c-text: #050A30;
  --c-text-soft: #4A5688;
  --c-muted: #8089B0;

  /* Colores semánticos (estados) */
  --ok-bg: #E4F6EE;     --ok-fg: #0E6B4B;     --ok-bd: #B7E3CF;
  --warn-bg: #FFF4DA;   --warn-fg: #8A5A00;   --warn-bd: #F3DDA4;
  --bad-bg: #FFECEE;    --bad-fg: #B42335;    --bad-bd: #F5C2C8;

  --shadow-sm: 0 4px 14px rgba(11, 45, 154, 0.07);
  --shadow-md: 0 12px 32px rgba(11, 45, 154, 0.10);
  --shadow-lg: 0 28px 70px rgba(5, 10, 48, 0.35);
  --radius: 14px;
  --radius-sm: 10px;
}

ion-content.sportra-main-viewport {
  --background: radial-gradient(1100px 520px at 85% -8%, rgba(123, 170, 247, 0.22), transparent 60%),
                radial-gradient(900px 480px at -5% 110%, rgba(43, 89, 195, 0.10), transparent 60%),
                #F5F7FF;
  font-family: 'DM Sans', -apple-system, sans-serif;
  color: var(--c-text);
}

/* ===================== NAVBAR ===================== */
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
  background: rgba(5, 10, 48, 0.92);
  -webkit-backdrop-filter: blur(14px);
  backdrop-filter: blur(14px);
  border: 1px solid rgba(123, 170, 247, 0.25);
  border-radius: var(--radius);
  padding: 0.6rem 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 14px 40px rgba(5, 10, 48, 0.28);
}

.brand-box { display: flex; align-items: center; gap: 0.7rem; }

.brand-badge {
  width: 38px;
  height: 38px;
  background: linear-gradient(135deg, var(--c-deep), var(--c-mid));
  border: 1px solid rgba(123, 170, 247, 0.45);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.brand-icon { font-size: 1.2rem; color: var(--c-ice); }

.brand-name {
  font-family: 'Barlow Condensed', sans-serif;
  font-weight: 700;
  font-size: 1.4rem;
  letter-spacing: 0.04em;
  color: var(--c-ice);
}

.neon-dot { color: var(--c-sky); }

.role-badge {
  font-size: 0.62rem;
  background: rgba(123, 170, 247, 0.15);
  border: 1px solid rgba(123, 170, 247, 0.4);
  color: var(--c-sky);
  padding: 0.15rem 0.55rem;
  border-radius: 999px;
  margin-left: 0.5rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  vertical-align: middle;
}

.btn-logout {
  background: rgba(245, 247, 255, 0.06);
  border: 1px solid rgba(245, 247, 255, 0.22);
  color: var(--c-ice);
  padding: 0.5rem 1rem;
  border-radius: var(--radius-sm);
  font-weight: 600;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  gap: 0.45rem;
  cursor: pointer;
  transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
}

.btn-logout:hover {
  background: rgba(123, 170, 247, 0.18);
  border-color: var(--c-sky);
  color: #ffffff;
}

/* ===================== LAYOUT ===================== */
.admin-wrapper {
  max-width: 1280px;
  margin: 0 auto;
  padding: 6.5rem 1.5rem 4rem;
}

/* ===================== HERO ===================== */
.admin-hero {
  position: relative;
  overflow: hidden;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1.5rem;
  margin-bottom: 1.75rem;
  padding: 1.9rem 2.1rem;
  background: linear-gradient(125deg, var(--c-night) 0%, #071463 45%, var(--c-deep) 100%);
  border: 1px solid rgba(123, 170, 247, 0.25);
  border-radius: 20px;
  box-shadow: var(--shadow-md);
  color: var(--c-ice);
}

.admin-hero::after {
  content: '';
  position: absolute;
  width: 380px;
  height: 380px;
  right: -90px;
  top: -170px;
  background: radial-gradient(circle, rgba(123, 170, 247, 0.35), transparent 68%);
  pointer-events: none;
}

.hero-text, .metrics-row { position: relative; z-index: 1; }

.eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--c-sky);
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 0.72rem;
  font-weight: 700;
}

.pulse-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: var(--c-sky);
  box-shadow: 0 0 0 0 rgba(123, 170, 247, 0.7);
  animation: sportraPulse 2s infinite;
}

.page-title {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: clamp(2rem, 3.5vw, 2.8rem);
  font-weight: 700;
  margin: 0.35rem 0 0.4rem;
  color: var(--c-ice);
  line-height: 1.05;
  letter-spacing: 0.01em;
}

.page-subtitle { color: rgba(245, 247, 255, 0.72); font-size: 0.95rem; margin: 0; max-width: 520px; }

.metrics-row { display: flex; gap: 0.9rem; flex-wrap: wrap; }

.metric-card {
  background: rgba(245, 247, 255, 0.07);
  -webkit-backdrop-filter: blur(8px);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(123, 170, 247, 0.3);
  border-radius: var(--radius);
  padding: 0.85rem 1.25rem;
  display: flex;
  flex-direction: column;
  min-width: 124px;
  transition: transform 0.2s ease, border-color 0.2s ease, background-color 0.2s ease;
}

.metric-card:hover {
  transform: translateY(-2px);
  border-color: var(--c-sky);
  background: rgba(245, 247, 255, 0.12);
}

.metric-label {
  font-size: 0.68rem;
  color: var(--c-sky);
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.metric-value {
  font-size: 1.9rem;
  font-weight: 700;
  color: var(--c-ice);
  font-family: 'Barlow Condensed', sans-serif;
  line-height: 1.15;
}

.text-lime { color: var(--c-deep); }
.metric-card .text-lime { color: var(--c-sky); }
.modal-header .text-lime { color: var(--c-mid); }

/* ===================== TABS ===================== */
.tab-menu {
  display: flex;
  flex-wrap: wrap;
  gap: 0.55rem;
  margin-bottom: 1.5rem;
  padding: 0.5rem;
  background: var(--c-surface);
  border: 1px solid var(--c-line);
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
}

.tab-btn {
  background: transparent;
  border: 1px solid transparent;
  color: var(--c-text-soft);
  padding: 0.55rem 1rem;
  border-radius: var(--radius-sm);
  font-weight: 600;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
}

.tab-btn:hover { background: var(--c-hover); color: var(--c-deep); }

.tab-btn.active {
  background: linear-gradient(135deg, var(--c-deep), var(--c-mid));
  color: var(--c-ice);
  box-shadow: 0 6px 16px rgba(11, 45, 154, 0.32);
}

/* ===================== CARDS ===================== */
.admin-grid-layout {
  display: grid;
  grid-template-columns: 2.2fr 1fr;
  gap: 1.5rem;
  align-items: start;
}

.section-card {
  background: var(--c-surface);
  border: 1px solid var(--c-line);
  border-radius: 18px;
  padding: 1.6rem;
  box-shadow: var(--shadow-md);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.card-header h3 {
  margin: 0 0 0.2rem;
  font-size: 1.4rem;
  font-weight: 700;
  color: var(--c-night);
  font-family: 'Barlow Condensed', sans-serif;
  letter-spacing: 0.01em;
}

.card-header p { margin: 0; font-size: 0.85rem; color: var(--c-text-soft); }

.header-with-filter { flex-wrap: wrap; margin-bottom: 1rem; }

.counter-pill {
  min-width: 34px;
  text-align: center;
  background: var(--c-deep);
  color: var(--c-ice);
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 700;
}

.filter-select { min-width: 200px; }

/* ===================== TABLAS ===================== */
.table-responsive {
  overflow-x: auto;
  margin-top: 1rem;
  border: 1px solid var(--c-line);
  border-radius: 12px;
}

.neon-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.85rem;
  background: var(--c-surface);
}

.neon-table th {
  padding: 0.85rem 1rem;
  background: var(--c-hover);
  color: var(--c-deep);
  font-size: 0.68rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-weight: 700;
  border-bottom: 1px solid var(--c-line-strong);
  white-space: nowrap;
}

.neon-table td {
  padding: 0.9rem 1rem;
  border-bottom: 1px solid var(--c-line);
  color: var(--c-text);
  vertical-align: middle;
}

.neon-table tr:last-child td { border-bottom: none; }
.neon-table tbody tr { transition: background-color 0.15s ease; }
.neon-table tbody tr:hover td { background: #F5F8FF; }

.complexes-tag {
  display: inline-block;
  background: var(--c-hover);
  color: var(--c-mid);
  border: 1px solid var(--c-line-strong);
  padding: 0.2rem 0.6rem;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 600;
}

/* ===================== ESTADOS ===================== */
.status-pill {
  display: inline-block;
  padding: 0.25rem 0.7rem;
  border-radius: 999px;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  white-space: nowrap;
}

.status-active  { background: var(--ok-bg);   color: var(--ok-fg);   border: 1px solid var(--ok-bd); }
.status-warning { background: var(--warn-bg); color: var(--warn-fg); border: 1px solid var(--warn-bd); }
.status-danger  { background: var(--bad-bg);  color: var(--bad-fg);  border: 1px solid var(--bad-bd); }
.status-neutral { background: #EEF1FA;        color: var(--c-text-soft); border: 1px solid var(--c-line); }
.status-pending { background: rgba(123, 170, 247, 0.2); color: var(--c-deep); border: 1px solid var(--c-sky); }

.tag-blocked {
  background: var(--bad-bg);
  color: var(--bad-fg);
  border: 1px solid var(--bad-bd);
  font-size: 0.6rem;
  font-weight: 700;
  padding: 0.12rem 0.5rem;
  border-radius: 999px;
  margin-left: 0.4rem;
  letter-spacing: 0.06em;
}

/* ===================== BOTONES DE ACCIÓN ===================== */
.action-buttons-cell { display: flex; gap: 0.4rem; justify-content: flex-end; flex-wrap: wrap; align-items: center; }

.btn-action-sm {
  border: 1px solid transparent;
  padding: 0.4rem 0.75rem;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  cursor: pointer;
  transition: background-color 0.18s ease, color 0.18s ease, transform 0.18s ease, border-color 0.18s ease;
}

.btn-action-sm:hover:not(:disabled) { transform: translateY(-1px); }
.btn-action-sm:disabled { opacity: 0.45; cursor: not-allowed; }

.btn-pay { background: var(--c-hover); color: var(--c-deep); border-color: var(--c-line-strong); }
.btn-pay:hover { background: var(--c-deep); color: var(--c-ice); border-color: var(--c-deep); }

.btn-info { background: rgba(123, 170, 247, 0.18); color: var(--c-deep); border-color: rgba(123, 170, 247, 0.55); }
.btn-info:hover { background: var(--c-mid); color: var(--c-ice); border-color: var(--c-mid); }

.btn-danger { background: var(--bad-bg); color: var(--bad-fg); border-color: var(--bad-bd); }
.btn-danger:hover:not(:disabled) { background: var(--bad-fg); color: #ffffff; border-color: var(--bad-fg); }

.btn-success { background: var(--ok-bg); color: var(--ok-fg); border-color: var(--ok-bd); }
.btn-success:hover { background: var(--ok-fg); color: #ffffff; border-color: var(--ok-fg); }

/* ===================== FORMULARIOS ===================== */
.neon-form { display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem; }

.field-block label {
  display: block;
  font-size: 0.68rem;
  font-weight: 700;
  color: var(--c-deep);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 0.4rem;
}

.neon-input {
  width: 100%;
  height: 44px;
  background: var(--c-ice);
  border: 1px solid var(--c-line-strong);
  border-radius: var(--radius-sm);
  padding: 0 0.9rem;
  color: var(--c-text);
  outline: none;
  font-size: 0.875rem;
  transition: border-color 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
}

.neon-input::placeholder { color: var(--c-muted); }
.neon-input:hover { border-color: var(--c-sky); }

.neon-input:focus {
  background: #ffffff;
  border-color: var(--c-mid);
  box-shadow: 0 0 0 4px rgba(43, 89, 195, 0.16);
}

.neon-select {
  --background: #F5F7FF;
  --color: #050A30;
  --placeholder-color: #8089B0;
  --placeholder-opacity: 1;
  --padding-start: 0.9rem;
  --padding-end: 0.9rem;
  background: var(--c-ice);
  border: 1px solid var(--c-line-strong);
  border-radius: var(--radius-sm);
  min-height: 44px;
  font-size: 0.875rem;
  color: var(--c-text);
  transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.neon-select:hover { border-color: var(--c-sky); }
.neon-select:focus-within { border-color: var(--c-mid); box-shadow: 0 0 0 4px rgba(43, 89, 195, 0.16); }

input[type='checkbox'] { accent-color: var(--c-deep); width: 16px; height: 16px; }

.btn-submit-neon {
  background: linear-gradient(135deg, var(--c-deep), var(--c-mid));
  color: var(--c-ice);
  border: 1px solid transparent;
  padding: 0.8rem 1.3rem;
  border-radius: var(--radius-sm);
  font-weight: 700;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  cursor: pointer;
  margin-top: 0.5rem;
  box-shadow: 0 8px 20px rgba(11, 45, 154, 0.28);
  transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
}

.btn-submit-neon:hover:not(:disabled) {
  transform: translateY(-1px);
  filter: brightness(1.08);
  box-shadow: 0 12px 26px rgba(11, 45, 154, 0.38);
}

.btn-submit-neon:disabled { opacity: 0.5; cursor: not-allowed; box-shadow: none; }

/* ===================== MODALES ===================== */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(5, 10, 48, 0.62);
  -webkit-backdrop-filter: blur(5px);
  backdrop-filter: blur(5px);
  z-index: 2000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
}

.modal-box {
  background: var(--c-surface);
  border: 1px solid var(--c-line-strong);
  border-top: 4px solid var(--c-mid);
  border-radius: 18px;
  width: 100%;
  max-width: 540px;
  padding: 1.6rem;
  box-shadow: var(--shadow-lg);
  max-height: 92vh;
  overflow-y: auto;
}

.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; gap: 1rem; }

.modal-header h3 {
  margin: 0;
  font-size: 1.35rem;
  font-weight: 700;
  color: var(--c-night);
  font-family: 'Barlow Condensed', sans-serif;
}

.btn-close-modal {
  background: transparent;
  border: none;
  font-size: 1.5rem;
  color: var(--c-muted);
  cursor: pointer;
  display: flex;
  transition: color 0.18s ease, transform 0.18s ease;
}

.btn-close-modal:hover { color: var(--c-deep); transform: rotate(90deg); }

.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; }

.modal-actions { display: flex; justify-content: flex-end; gap: 0.6rem; margin-top: 1rem; flex-wrap: wrap; }
.modal-actions .btn-submit-neon { margin-top: 0; }

.btn-cancel {
  background: var(--c-surface);
  border: 1px solid var(--c-line-strong);
  color: var(--c-text-soft);
  padding: 0.65rem 1.1rem;
  border-radius: var(--radius-sm);
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.18s ease, color 0.18s ease, border-color 0.18s ease;
}

.btn-cancel:hover { background: var(--c-hover); border-color: var(--c-mid); color: var(--c-deep); }

.user-logs-container { display: flex; flex-direction: column; gap: 0.65rem; max-height: 350px; overflow-y: auto; padding-right: 0.25rem; }

.log-row {
  background: var(--c-ice);
  border: 1px solid var(--c-line);
  border-left: 3px solid var(--c-mid);
  padding: 0.75rem 0.9rem;
  border-radius: 8px;
  font-size: 0.8rem;
}

.log-time { color: var(--c-mid); font-size: 0.7rem; font-weight: 700; margin-bottom: 0.2rem; }
.log-details { color: var(--c-text); }

/* ===================== NOTIFICACIÓN ===================== */
.notification-toast {
  background: linear-gradient(90deg, rgba(123, 170, 247, 0.22), rgba(123, 170, 247, 0.08));
  border: 1px solid var(--c-sky);
  border-left: 4px solid var(--c-mid);
  color: var(--c-deep);
  padding: 0.85rem 1.15rem;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  gap: 0.7rem;
  margin-bottom: 1.5rem;
  font-weight: 600;
  font-size: 0.875rem;
  box-shadow: var(--shadow-sm);
}

.btn-close-toast { margin-left: auto; cursor: pointer; color: var(--c-mid); font-size: 1.2rem; transition: color 0.18s ease; }
.btn-close-toast:hover { color: var(--c-night); }

/* ===================== FOTOS ===================== */
.admin-photo-preview {
  width: 112px;
  height: 72px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid var(--c-line-strong);
  background: var(--c-hover);
}

/* ===================== SEGURIDAD ===================== */
.security-form { display: grid; gap: 1rem; max-width: 620px; margin-top: 1rem; }
.totp-enrollment { display: grid; gap: 1rem; padding-top: 1rem; }

.totp-qr {
  width: 240px;
  max-width: 100%;
  aspect-ratio: 1;
  padding: 10px;
  background: #fff;
  border: 1px solid var(--c-line-strong);
  border-radius: var(--radius-sm);
  box-shadow: var(--shadow-sm);
}

.totp-secret {
  display: block;
  overflow-wrap: anywhere;
  padding: 0.85rem;
  color: var(--c-ice);
  background: var(--c-night);
  border: 1px solid var(--c-deep);
  border-radius: var(--radius-sm);
  font-size: 0.85rem;
  letter-spacing: 0.04em;
}

.recovery-codes-panel {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(145px, 1fr));
  gap: 0.7rem;
  padding: 1.1rem;
  background: var(--warn-bg);
  border: 1px solid var(--warn-bd);
  border-radius: var(--radius-sm);
}

.recovery-codes-panel h4, .recovery-codes-panel p { grid-column: 1 / -1; margin: 0; color: var(--c-night); }
.recovery-codes-panel p { color: var(--warn-fg); font-size: 0.85rem; }

.recovery-code {
  overflow-wrap: anywhere;
  padding: 0.55rem;
  color: var(--c-night);
  background: #ffffff;
  border: 1px solid var(--warn-bd);
  border-radius: 8px;
  text-align: center;
  font-size: 0.8rem;
}

.recovery-confirmation { display: flex; align-items: center; gap: 0.5rem; grid-column: 1 / -1; color: var(--c-night); font-size: 0.85rem; }

.message-banner {
  margin-top: 1rem;
  padding: 0.85rem 1rem;
  border-radius: var(--radius-sm);
  background: rgba(123, 170, 247, 0.16);
  border: 1px solid var(--c-sky);
  color: var(--c-deep);
  font-size: 0.85rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.security-section .message-banner.error { background: var(--bad-bg); border-color: var(--bad-bd); color: var(--bad-fg); }

/* ===================== UTILIDADES ===================== */
.text-white { color: var(--c-night); font-weight: 600; }
.text-muted { color: var(--c-muted); }
.text-center { text-align: center; }
.text-right { text-align: right; }
.font-bold { font-weight: 700; }
.py-4 { padding-top: 1.25rem; padding-bottom: 1.25rem; }
.filter-box { display: flex; gap: 0.6rem; flex-wrap: wrap; }

/* ===================== ANIMACIONES ===================== */
@keyframes sportraPulse {
  0%   { box-shadow: 0 0 0 0 rgba(123, 170, 247, 0.65); }
  70%  { box-shadow: 0 0 0 9px rgba(123, 170, 247, 0); }
  100% { box-shadow: 0 0 0 0 rgba(123, 170, 247, 0); }
}

@keyframes sportraGlow {
  0%, 100% { box-shadow: 0 0 0 0 rgba(123, 170, 247, 0.45); }
  50%      { box-shadow: 0 0 16px 2px rgba(123, 170, 247, 0.55); }
}

@keyframes sportraFadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to   { opacity: 1; transform: translateY(0); }
}

@keyframes sportraPop {
  from { opacity: 0; transform: scale(0.95) translateY(10px); }
  to   { opacity: 1; transform: scale(1) translateY(0); }
}

.glow-pulse { animation: sportraGlow 3s ease-in-out infinite; }
.animate-fade-in { animation: sportraFadeIn 0.35s ease both; }
.animate-pop { animation: sportraPop 0.25s ease both; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease, transform 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-6px); }

@media (prefers-reduced-motion: reduce) {
  .glow-pulse, .pulse-indicator, .animate-fade-in, .animate-pop { animation: none; }
}

/* ===================== RESPONSIVE ===================== */
@media (max-width: 900px) {
  .admin-grid-layout { grid-template-columns: 1fr; }
  .admin-hero { flex-direction: column; align-items: flex-start; padding: 1.5rem; }
}

@media (max-width: 600px) {
  .navbar-container { padding: 0 0.75rem; }
  .admin-wrapper { padding: 6rem 0.9rem 3rem; }
  .btn-logout span { display: none; }
  .form-grid-2 { grid-template-columns: 1fr; }
  .tab-btn { padding: 0.5rem 0.75rem; font-size: 0.8rem; }
  .section-card { padding: 1.15rem; }
  .metric-card { flex: 1; min-width: 100px; }
}
</style>