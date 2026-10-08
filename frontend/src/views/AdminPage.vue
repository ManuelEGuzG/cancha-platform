<template>
  <ion-page class="sportra-hybrid-app">
    <ion-content class="sportra-main-viewport" :scroll-y="true">
      <!-- HEADER Y HERO BANNER OSCURO SUPERIOR -->
      <header class="dark-top-section">
        <div class="header-container">
          <div class="brand-brand-text" @click="router.push('/home')">
            <span class="brand-title">SPORTRA<span class="dot-blue">.</span></span>
            <small class="brand-sub">ADMINISTRACIÓN GLOBAL</small>
          </div>

          <div class="navbar-actions">
            <button class="nav-btn-danger-pill" type="button" @click="cerrarSesion">
              <ion-icon name="log-out-outline"></ion-icon>
              <span>Salir</span>
            </button>
          </div>
        </div>

        <div class="hero-card-banner">
          <div class="hero-banner-content">
            <div class="hero-left-info">
              <span class="platform-badge">
                PLATAFORMA CENTRAL <span class="badge-separator">/</span>
                <span class="badge-subtext">Control global</span>
              </span>
              <h1 class="hero-main-title">Panel Administrativo</h1>
              <p class="hero-description">
                <ion-icon name="shield-checkmark-outline" class="hero-location-icon"></ion-icon>
                Control de suscripciones, gestión de sedes, accesos e historial de reservas.
              </p>
            </div>
          </div>
        </div>
      </header>

      <!-- CUERPO CLARO -->
      <main class="light-body-section">
        <div class="body-container">

          <!-- Notificación Toast -->
          <div v-if="mensaje" class="message-banner success" role="status">
            <ion-icon name="checkmark-circle-outline"></ion-icon>
            <span>{{ mensaje }}</span>
            <button type="button" class="btn-close-toast" @click="mensaje = ''" style="background:none; border:0; cursor:pointer; margin-left:auto;">
              <ion-icon name="close-circle-outline" style="font-size: 16px;"></ion-icon>
            </button>
          </div>

          <!-- Pestañas de Navegación -->
          <div class="panel-tabs-bar">
            <button :class="['tab-item', { active: vista === 'complejos' }]" type="button" @click="vista = 'complejos'">
              <ion-icon name="business-outline"></ion-icon>
              <span>Complejos & Pagos</span>
            </button>
            <button :class="['tab-item', { active: vista === 'usuarios' }]" type="button" @click="vista = 'usuarios'">
              <ion-icon name="people-outline"></ion-icon>
              <span>Usuarios & Permisos</span>
            </button>
            <button :class="['tab-item', { active: vista === 'movimientos' }]" type="button" @click="vista = 'movimientos'">
              <ion-icon name="receipt-outline"></ion-icon>
              <span>Movimientos Globales</span>
            </button>
            <button :class="['tab-item', { active: vista === 'verificacion' }]" type="button" @click="vista = 'verificacion'">
              <ion-icon name="checkmark-circle-outline"></ion-icon>
              <span>Verificación</span>
            </button>
            <button :class="['tab-item', { active: vista === 'reporte' }]" type="button" @click="vista = 'reporte'">
              <ion-icon name="stats-chart-outline"></ion-icon>
              <span>Reporte Mensual</span>
            </button>
            <button :class="['tab-item', { active: vista === 'fotos' }]" type="button" @click="vista = 'fotos'">
              <ion-icon name="images-outline"></ion-icon>
              <span>Fotos</span>
            </button>
            <button :class="['tab-item', { active: vista === 'seguridad' }]" type="button" @click="vista = 'seguridad'">
              <ion-icon name="key-outline"></ion-icon>
              <span>Seguridad</span>
            </button>
          </div>

          <!-- 3 Tarjetas de Métricas Superiores -->
          <div class="metrics-grid-three">
            <div class="metric-white-card">
              <div class="metric-card-header">
                <span class="metric-label">Complejos</span>
                <ion-icon name="business-outline" class="metric-icon-blue"></ion-icon>
              </div>
              <div class="metric-value-row">
                <strong class="metric-main-number">{{ facturacion.length }}</strong>
              </div>
              <div class="metric-footer-note">Sedes registradas en el sistema</div>
            </div>

            <div class="metric-white-card">
              <div class="metric-card-header">
                <span class="metric-label">Al día</span>
                <ion-icon name="checkmark-circle-outline" class="metric-icon-blue"></ion-icon>
              </div>
              <div class="metric-value-row">
                <strong class="metric-main-number">{{ complejosAlDia }}</strong>
              </div>
              <div class="metric-footer-note">Suscripciones vigentes</div>
            </div>

            <div class="metric-white-card">
              <div class="metric-card-header">
                <span class="metric-label">Usuarios</span>
                <ion-icon name="people-outline" class="metric-icon-blue"></ion-icon>
              </div>
              <div class="metric-value-row">
                <strong class="metric-main-number">{{ usuarios.length }}</strong>
              </div>
              <div class="metric-footer-note">Cuentas de encargados/propietarios</div>
            </div>
          </div>

          <!-- PESTAÑA 1: COMPLEJOS Y SUSCRIPCIONES -->
          <div v-if="vista === 'complejos'" class="two-columns-grid animate-fade-in" style="grid-template-columns: 2fr 1fr;">
            <div class="white-panel-card">
              <div class="card-header-row">
                <div>
                  <span class="section-kicker">GESTIÓN DE SEDES</span>
                  <h2 class="section-title">Estado de Complejos y Suscripciones</h2>
                </div>
              </div>

              <div class="table-responsive">
                <table class="light-table">
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
                        <span v-if="!c.activo" class="status-chip rechazada" style="margin-left: 6px;">BLOQUEADO</span>
                      </td>
                      <td>
                        <span :class="['table-status-pill', getBadgeClass(c)]">
                          {{ getBadgeText(c) }}
                        </span>
                      </td>
                      <td>{{ c.total_canchas }}</td>
                      <td>{{ c.reservas_mes }}</td>
                      <td class="font-bold text-blue">{{ moneda(c.ingreso_estimado_mes) }}</td>
                      <td class="text-right">
                        <div style="display: inline-flex; gap: 6px;">
                          <button class="mini-btn success" type="button" @click="abrirPago(c)" title="Registrar Pago">
                            Pago
                          </button>
                          <button 
                            type="button"
                            :class="['mini-btn', c.activo ? 'danger' : 'success']" 
                            @click="toggleComplejo(c)"
                          >
                            {{ c.activo ? 'Bloquear' : 'Activar' }}
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="facturacion.length === 0">
                      <td colspan="6" class="text-center py-6 text-muted">No hay complejos registrados.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="white-panel-card">
              <div class="card-header-row">
                <div>
                  <span class="section-kicker">NUEVA SEDE</span>
                  <h2 class="section-title">Crear Complejo</h2>
                </div>
              </div>

              <form @submit.prevent="crearComplejo" style="display: flex; flex-direction: column; gap: 14px;">
                <div class="field-block">
                  <label class="form-label-light">Provincia</label>
                  <ion-select v-model="provinciaId" interface="popover" placeholder="Seleccione..." class="light-select" @ionChange="onProvinciaChange">
                    <ion-select-option v-for="p in provincias" :key="p.id" :value="p.id">{{ p.nombre }}</ion-select-option>
                  </ion-select>
                </div>

                <div class="field-block">
                  <label class="form-label-light">Cantón</label>
                  <ion-select v-model="cantonId" interface="popover" placeholder="Seleccione..." class="light-select" :disabled="!provinciaId" @ionChange="onCantonChange">
                    <ion-select-option v-for="c in cantones" :key="c.id" :value="c.id">{{ c.nombre }}</ion-select-option>
                  </ion-select>
                </div>

                <div class="field-block">
                  <label class="form-label-light">Distrito</label>
                  <ion-select v-model="nuevoComplejo.distrito_id" interface="popover" placeholder="Seleccione..." class="light-select" :disabled="!cantonId">
                    <ion-select-option v-for="d in distritos" :key="d.id" :value="d.id">{{ d.nombre }}</ion-select-option>
                  </ion-select>
                </div>

                <div class="field-block">
                  <label class="form-label-light">Nombre del Complejo</label>
                  <input v-model="nuevoComplejo.nombre" type="text" placeholder="Ej: Complejo Camp Nou" required class="light-text-input" />
                </div>

                <div class="field-block">
                  <label class="form-label-light">WhatsApp</label>
                  <input v-model="nuevoComplejo.whatsapp_numero" type="text" placeholder="Ej: 88888888" required class="light-text-input" />
                </div>

                <button type="submit" class="btn-action-primary" style="margin-top: 8px; width: 100%; justify-content: center;" :disabled="!nuevoComplejo.distrito_id || !nuevoComplejo.nombre">
                  <ion-icon name="add-circle-outline"></ion-icon>
                  <span>Crear Complejo</span>
                </button>
              </form>
            </div>
          </div>

          <!-- PESTAÑA 2: USUARIOS Y ACTIVIDAD -->
          <div v-if="vista === 'usuarios'" class="two-columns-grid animate-fade-in" style="grid-template-columns: 2fr 1fr;">
            <div class="white-panel-card">
              <div class="card-header-row">
                <div>
                  <span class="section-kicker">CONTROL DE ACCESOS</span>
                  <h2 class="section-title">Usuarios Existentes</h2>
                </div>
              </div>

              <div class="table-responsive">
                <table class="light-table">
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
                        <span v-if="!u.activo" class="status-chip rechazada" style="margin-left: 6px;">BLOQUEADO</span>
                      </td>
                      <td>{{ u.email }}</td>
                      <td>
                        <span class="cancha-badge">
                          {{ u.complejos?.map((c: any) => c.nombre).join(', ') || 'Sin asignación' }}
                        </span>
                      </td>
                      <td class="text-right">
                        <div style="display: inline-flex; gap: 6px;">
                          <button class="mini-btn success" type="button" @click="verActividad(u)" title="Ver Historial">
                            Actividad
                          </button>
                          <button 
                            type="button"
                            :class="['mini-btn', u.activo ? 'danger' : 'success']" 
                            @click="toggleUsuario(u)"
                          >
                            {{ u.activo ? 'Bloquear' : 'Activar' }}
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="white-panel-card">
              <div class="card-header-row">
                <div>
                  <span class="section-kicker">NUEVO ENCARGADO</span>
                  <h2 class="section-title">Crear Usuario</h2>
                </div>
              </div>

              <form @submit.prevent="crearUsuario" style="display: flex; flex-direction: column; gap: 14px;">
                <div class="field-block">
                  <label class="form-label-light">Nombre Completo</label>
                  <input v-model="nuevoUsuario.name" type="text" placeholder="Ej: Carlos Ruiz" required class="light-text-input" />
                </div>

                <div class="field-block">
                  <label class="form-label-light">Correo Electrónico</label>
                  <input v-model="nuevoUsuario.email" type="email" placeholder="carlos@ejemplo.com" required class="light-text-input" />
                </div>

                <div class="field-block">
                  <label class="form-label-light">Contraseña</label>
                  <input v-model="nuevoUsuario.password" type="password" placeholder="••••••••" required class="light-text-input" />
                </div>

                <div class="field-block">
                  <label class="form-label-light">Asignar Complejo</label>
                  <ion-select v-model="nuevoUsuario.complejo_id" interface="popover" placeholder="Seleccione..." class="light-select">
                    <ion-select-option v-for="c in facturacion" :key="c.complejo_id" :value="c.complejo_id">{{ c.nombre }}</ion-select-option>
                  </ion-select>
                </div>

                <div class="field-block">
                  <label class="form-label-light">Rol asignado</label>
                  <ion-select v-model="nuevoUsuario.rol" interface="popover" class="light-select">
                    <ion-select-option value="propietario">Propietario</ion-select-option>
                    <ion-select-option value="encargado">Encargado</ion-select-option>
                  </ion-select>
                </div>

                <button type="submit" class="btn-action-primary" style="margin-top: 8px; width: 100%; justify-content: center;" :disabled="!nuevoUsuario.email || !nuevoUsuario.password">
                  <ion-icon name="person-add-outline"></ion-icon>
                  <span>Crear Usuario</span>
                </button>
              </form>
            </div>
          </div>

          <!-- VERIFICACIÓN DE CANCHAS -->
          <div v-if="vista === 'verificacion'" class="white-panel-card animate-fade-in">
            <div class="card-header-row">
              <div>
                <span class="section-kicker">MODERACIÓN</span>
                <h2 class="section-title">Canchas pendientes de verificación</h2>
              </div>
              <span class="badge-count-blue">{{ canchasRevision.length }} pendientes</span>
            </div>
            <div class="table-responsive">
              <table class="light-table">
                <thead>
                  <tr><th>Cancha</th><th>Complejo</th><th>Deporte</th><th>Observaciones</th><th class="text-right">Revisión</th></tr>
                </thead>
                <tbody>
                  <tr v-for="cancha in canchasRevision" :key="cancha.id">
                    <td class="font-bold">{{ cancha.nombre }}</td>
                    <td>{{ cancha.complejo?.nombre }}</td>
                    <td>{{ cancha.deporte?.nombre }}</td>
                    <td>
                      <input
                        v-model="observacionesRechazo[cancha.id]"
                        class="light-text-input"
                        type="text"
                        maxlength="1000"
                        placeholder="Motivo si se rechaza"
                      />
                    </td>
                    <td class="text-right">
                      <div style="display: inline-flex; gap: 6px;">
                        <button class="mini-btn success" type="button" @click="resolverVerificacion(cancha, 'aprobada')">Aprobar</button>
                        <button
                          type="button"
                          class="mini-btn danger"
                          :disabled="!observacionesRechazo[cancha.id]?.trim()"
                          @click="resolverVerificacion(cancha, 'rechazada')"
                        >Rechazar</button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="canchasRevision.length === 0">
                    <td colspan="5" class="text-center py-6 text-muted">No hay canchas pendientes de revisión.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- REPORTE MENSUAL -->
          <div v-if="vista === 'reporte'" class="white-panel-card animate-fade-in">
            <div class="card-header-row" style="align-items: center; flex-wrap: wrap;">
              <div>
                <span class="section-kicker">ANÁLISIS FINANCIERO</span>
                <h2 class="section-title">Actividad mensual por cancha</h2>
                <p style="font-size: 12px; color: #64748b; margin: 2px 0 0;">El ingreso bruto es referencial según reservas registradas.</p>
              </div>
              <div style="display: flex; gap: 8px; align-items: center;">
                <input v-model="mesReporte" type="month" class="light-text-input" style="width: 150px;" />
                <button class="mini-btn success" type="button" @click="cargarReporteMensual" style="padding: 8px 14px;">Consultar</button>
                <button class="btn-action-primary" type="button" @click="descargarReporteMensual" style="padding: 8px 14px; font-size: 12px;">Exportar CSV</button>
              </div>
            </div>
            <div class="table-responsive">
              <table class="light-table">
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
                    <td class="font-bold text-blue">{{ moneda(fila.ingreso_bruto_reservas) }}</td>
                  </tr>
                  <tr v-if="reporteMensual.length === 0">
                    <td colspan="8" class="text-center py-6 text-muted">No hay datos para este mes.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- FOTOS -->
          <div v-if="vista === 'fotos'" class="white-panel-card animate-fade-in">
            <div class="card-header-row">
              <div>
                <span class="section-kicker">MULTIMEDIA</span>
                <h2 class="section-title">Fotos pendientes de revisión</h2>
              </div>
              <span class="badge-count-blue">{{ fotosRevision.length }} pendientes</span>
            </div>
            <div class="table-responsive">
              <table class="light-table">
                <thead><tr><th>Foto</th><th>Cancha</th><th>Complejo</th><th>Descripción</th><th>Observaciones</th><th class="text-right">Moderación</th></tr></thead>
                <tbody>
                  <tr v-for="foto in fotosRevision" :key="foto.id">
                    <td><img :src="foto.url" :alt="foto.caption || 'Foto de cancha'" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;" loading="lazy" /></td>
                    <td>{{ foto.cancha?.nombre }}</td>
                    <td>{{ foto.cancha?.complejo?.nombre }}</td>
                    <td>{{ foto.caption || 'Sin descripción' }}</td>
                    <td><input v-model="observacionesFoto[foto.id]" class="light-text-input" maxlength="1000" placeholder="Motivo rechazo..." /></td>
                    <td class="text-right">
                      <div style="display: inline-flex; gap: 6px;">
                        <button class="mini-btn success" type="button" @click="resolverFoto(foto, 'aprobada')">Aprobar</button>
                        <button class="mini-btn danger" type="button" :disabled="!observacionesFoto[foto.id]?.trim()" @click="resolverFoto(foto, 'rechazada')">Rechazar</button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="fotosRevision.length === 0"><td colspan="6" class="text-center py-6 text-muted">No hay fotos pendientes.</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- SEGURIDAD (2FA) -->
          <div v-if="vista === 'seguridad'" class="white-panel-card animate-fade-in">
            <div class="card-header-row">
              <div>
                <span class="section-kicker">AUTENTICACIÓN</span>
                <h2 class="section-title">Verificación en dos pasos</h2>
                <p style="font-size: 12px; color: #64748b; margin: 2px 0 0;">Protege la cuenta administradora mediante aplicaciones TOTP.</p>
              </div>
              <span :class="['table-status-pill', twoFactorActivo ? 'confirmada' : 'rechazada']">
                {{ twoFactorActivo ? 'Activa' : 'Inactiva' }}
              </span>
            </div>

            <div v-if="!twoFactorActivo" style="max-width: 450px; margin-top: 16px;">
              <form @submit.prevent="iniciarDosFactores" style="display: flex; flex-direction: column; gap: 14px;">
                <div class="field-block">
                  <label class="form-label-light">Contraseña actual</label>
                  <input v-model="passwordDosFactores" type="password" autocomplete="current-password" class="light-text-input" required />
                </div>
                <button type="submit" class="btn-action-primary" :disabled="cargandoDosFactores">
                  <span>{{ cargandoDosFactores ? 'Preparando...' : 'Configurar aplicación' }}</span>
                </button>
              </form>

              <div v-if="configuracionDosFactores" style="margin-top: 20px; background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0;">
                <img v-if="codigoQr" :src="codigoQr" alt="Código QR de configuración TOTP" style="display: block; margin: 0 auto 12px; border-radius: 8px;" />
                <div class="field-block" style="margin-bottom: 12px;">
                  <label class="form-label-light">Clave manual</label>
                  <code style="font-size: 12px; background: #e2e8f0; padding: 4px 8px; border-radius: 4px; display: block;">{{ configuracionDosFactores.secret }}</code>
                </div>
                <form @submit.prevent="confirmarDosFactores" style="display: flex; flex-direction: column; gap: 12px;">
                  <div class="field-block">
                    <label class="form-label-light">Código de 6 dígitos</label>
                    <input v-model="codigoDosFactores" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" class="light-text-input" required />
                  </div>
                  <button type="submit" class="btn-action-primary" :disabled="cargandoDosFactores">
                    <span>Confirmar y activar</span>
                  </button>
                </form>
              </div>
            </div>

            <div v-else style="max-width: 450px; margin-top: 16px;">
              <div v-if="codigosRecuperacion.length" style="background: #fef3c7; border: 1px solid #fcd34d; padding: 16px; border-radius: 12px; margin-bottom: 16px;">
                <h4 style="margin: 0 0 6px; font-size: 14px; font-weight: 800; color: #b45309;">Códigos de recuperación</h4>
                <p style="font-size: 12px; color: #78350f; margin: 0 0 10px;">Guárdalos ahora. Cada código se puede usar una sola vez.</p>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px; margin-bottom: 12px;">
                  <code v-for="codigo in codigosRecuperacion" :key="codigo" style="background: #ffffff; padding: 4px 8px; border-radius: 4px; font-size: 11px; text-align: center; border: 1px solid #fde68a;">{{ codigo }}</code>
                </div>
                <label style="display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 700; color: #92400e; cursor: pointer;">
                  <input v-model="recoveryCodesSaved" type="checkbox" />
                  <span>Guardé los códigos en un lugar seguro</span>
                </label>
              </div>

              <form @submit.prevent="desactivarDosFactores" style="display: flex; flex-direction: column; gap: 14px;">
                <div class="field-block">
                  <label class="form-label-light">Contraseña actual</label>
                  <input v-model="passwordDesactivar2FA" type="password" autocomplete="current-password" class="light-text-input" required />
                </div>
                <div class="field-block">
                  <label class="form-label-light">Código TOTP o de recuperación</label>
                  <input v-model="codigoDesactivar2FA" type="text" autocomplete="one-time-code" class="light-text-input" required />
                </div>
                <button type="submit" class="mini-btn danger" style="padding: 10px; justify-content: center; font-weight: 800;" :disabled="cargandoDosFactores">Desactivar 2FA</button>
              </form>
            </div>

            <p v-if="mensajeDosFactores" class="message-banner success" style="margin-top: 16px;" role="status">{{ mensajeDosFactores }}</p>
            <p v-if="errorDosFactores" class="message-banner error" style="margin-top: 16px;" role="alert">{{ errorDosFactores }}</p>
            <button v-if="codigosRecuperacion.length" class="btn-action-primary" style="margin-top: 16px;" :disabled="!recoveryCodesSaved" @click="cerrarSesionTras2FA">
              <span>Volver a iniciar sesión</span>
            </button>
          </div>

          <!-- MOVIMIENTOS / RESERVAS GLOBALES -->
          <div v-if="vista === 'movimientos'" class="white-panel-card animate-fade-in">
            <div class="card-header-row" style="align-items: center; flex-wrap: wrap;">
              <div>
                <span class="section-kicker">AUDITORÍA</span>
                <h2 class="section-title">Historial Global de Reservas</h2>
                <p style="font-size: 12px; color: #64748b; margin: 2px 0 0;">Auditoría de reservas en todos los complejos registrados.</p>
              </div>

              <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <ion-select 
                  v-model="filtroComplejo" 
                  interface="popover" 
                  placeholder="Filtrar por complejo" 
                  class="light-select"
                  style="width: 180px;"
                  @ionChange="cargarMovimientos"
                >
                  <ion-select-option :value="null">Todos los Complejos</ion-select-option>
                  <ion-select-option v-for="c in facturacion" :key="c.complejo_id" :value="c.complejo_id">{{ c.nombre }}</ion-select-option>
                </ion-select>

                <ion-select
                  v-model="filtroCancha"
                  interface="popover"
                  placeholder="Filtrar por cancha"
                  class="light-select"
                  style="width: 180px;"
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
              <table class="light-table">
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
                    <td class="font-bold text-blue">{{ m.fecha }} <small class="text-muted">({{ m.hora_inicio }} - {{ m.hora_fin }})</small></td>
                    <td>{{ m.cancha?.complejo?.nombre }} — <span class="font-bold">{{ m.cancha?.nombre }}</span></td>
                    <td>{{ m.nombre_cliente }}</td>
                    <td>
                      <span :class="['table-status-pill', m.estado]">
                        {{ m.estado }}
                      </span>
                    </td>
                  </tr>
                  <tr v-if="movimientos.length === 0">
                    <td colspan="4" class="text-center py-6 text-muted">No se encontraron movimientos registrados.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </main>

      <!-- MODAL REGISTRAR PAGO -->
      <div v-if="complejoPago" class="modal-backdrop">
        <div class="white-panel-card" style="width: 100%; max-width: 480px; margin: auto;">
          <div class="card-header-row">
            <div>
              <span class="section-kicker">PAGO DE SUSCRIPCIÓN</span>
              <h2 class="section-title">{{ complejoPago.nombre }}</h2>
            </div>
            <button class="mini-btn danger" type="button" @click="complejoPago = null" aria-label="Cerrar">
              <ion-icon name="close-outline"></ion-icon>
            </button>
          </div>

          <form @submit.prevent="guardarPago" style="display: flex; flex-direction: column; gap: 14px; margin-top: 16px;">
            <div class="field-block">
              <label class="form-label-light">Monto Pago (₡)</label>
              <input v-model.number="nuevoPago.monto" type="number" required class="light-text-input" />
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
              <div class="field-block">
                <label class="form-label-light">Vigencia Desde</label>
                <input v-model="nuevoPago.periodo_desde" type="date" required class="light-text-input" />
              </div>
              <div class="field-block">
                <label class="form-label-light">Vigencia Hasta</label>
                <input v-model="nuevoPago.periodo_hasta" type="date" required class="light-text-input" />
              </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
              <div class="field-block">
                <label class="form-label-light">Fecha del Pago</label>
                <input v-model="nuevoPago.fecha_pago" type="date" required class="light-text-input" />
              </div>
              <div class="field-block">
                <label class="form-label-light">Método de Pago</label>
                <ion-select v-model="nuevoPago.metodo" interface="popover" class="light-select">
                  <ion-select-option value="sinpe">SINPE Móvil</ion-select-option>
                  <ion-select-option value="transferencia">Transferencia Bancaria</ion-select-option>
                  <ion-select-option value="efectivo">Efectivo</ion-select-option>
                </ion-select>
              </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 10px;">
              <button type="button" class="mini-btn danger" @click="complejoPago = null">Cancelar</button>
              <button type="submit" class="btn-action-primary">Guardar Pago</button>
            </div>
          </form>
        </div>
      </div>

      <!-- MODAL ACTIVIDAD DE USUARIO -->
      <div v-if="usuarioActividad" class="modal-backdrop">
        <div class="white-panel-card" style="width: 100%; max-width: 500px; margin: auto;">
          <div class="card-header-row">
            <div>
              <span class="section-kicker">BITÁCORA DE ACTIVIDAD</span>
              <h2 class="section-title">{{ usuarioActividad.name }}</h2>
            </div>
            <button class="mini-btn danger" type="button" @click="usuarioActividad = null" aria-label="Cerrar">
              <ion-icon name="close-outline"></ion-icon>
            </button>
          </div>

          <div style="max-height: 300px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; margin-top: 16px;">
            <div v-for="log in actividad" :key="log.id" style="padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 12px;">
              <div style="color: #64748b; font-weight: 700; margin-bottom: 2px;">{{ log.created_at }}</div>
              <div style="color: #091133;"><strong>{{ log.log_name }}</strong>: {{ log.description }}</div>
            </div>
            <div v-if="actividad.length === 0" class="text-center py-6 text-muted">
              Este usuario no registra actividad reciente.
            </div>
          </div>
        </div>
      </div>
    </ion-content>
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
  imagesOutline, keyOutline, closeOutline, statsChartOutline, timeOutline,
  funnelOutline, locationOutline, appsOutline, walletOutline, alertCircleOutline
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
  'close-outline': closeOutline,
  'stats-chart-outline': statsChartOutline,
  'time-outline': timeOutline,
  'funnel-outline': funnelOutline,
  'location-outline': locationOutline,
  'apps-outline': appsOutline,
  'wallet-outline': walletOutline,
  'alert-circle-outline': alertCircleOutline
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

function moneda(valor: number | string | undefined | null): string {
  return `₡${Number(valor || 0).toLocaleString('es-CR', { maximumFractionDigits: 0 })}`;
}

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
  if (c.estado_suscripcion === 'vencida') return 'rechazada';
  if (c.estado_suscripcion === 'por_vencer') return 'aceptada';
  if (c.estado_suscripcion === 'al_dia') return 'confirmada';
  return '';
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

.sportra-hybrid-app,
.sportra-main-viewport,
.sportra-hybrid-app ion-content {
  --background: #f4f6fc !important;
  background-color: #f4f6fc !important;
  font-family: 'Plus Jakarta Sans', -apple-system, sans-serif !important;
  color: #091133;
}

/* Sección superior oscura (Exactamente igual a PanelPage) */
.dark-top-section {
  background: linear-gradient(180deg, #040924 0%, #081039 100%);
  color: #ffffff;
  padding: 24px 48px 36px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.header-container {
  max-width: 1400px;
  margin: 0 auto 28px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.brand-brand-text { cursor: pointer; display: flex; flex-direction: column; }

.brand-title {
  font-size: 22px;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: #ffffff;
  line-height: 1;
}

.brand-title .dot-blue { color: #5b7eff; }

.brand-sub {
  font-size: 9px;
  font-weight: 800;
  letter-spacing: 0.14em;
  color: #64748b;
  margin-top: 4px;
}

.navbar-actions { display: flex; align-items: center; gap: 12px; }

.nav-btn-danger-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 18px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 99px;
  color: #cbd5e1;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.nav-btn-danger-pill:hover {
  background: rgba(239, 68, 68, 0.15);
  color: #fca5a5;
  border-color: rgba(239, 68, 68, 0.3);
}

.hero-card-banner { max-width: 1400px; margin: 0 auto; }

.hero-banner-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 32px;
}

.platform-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.05em;
  color: #64748b;
  margin-bottom: 12px;
  text-transform: uppercase;
}

.badge-separator { color: #475569; }
.badge-subtext { color: #94a3b8; }

.hero-main-title {
  font-size: 34px;
  font-weight: 800;
  margin: 0 0 10px;
  color: #ffffff;
  letter-spacing: -0.03em;
}

.hero-description {
  font-size: 13px;
  color: #94a3b8;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 500;
}

.hero-location-icon { font-size: 16px; color: #60a5fa; }

/* Pestañas */
.panel-tabs-bar {
  display: flex;
  gap: 8px;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 12px;
  overflow-x: auto;
  margin-bottom: 28px;
}

.tab-item {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: transparent;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 700;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.tab-item.active { background: #e0e7ff; color: #4338ca; }
.tab-item:hover:not(.active) { background: #f1f5f9; color: #334155; }

/* Cuerpo */
.light-body-section { padding: 28px 48px 80px; }
.body-container { max-width: 1400px; margin: 0 auto; display: flex; flex-direction: column; gap: 28px; }

/* Métricas adaptadas a 3 columnas */
.metrics-grid-three {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

.metric-white-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 20px 24px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.02);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.metric-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.metric-label { font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; }
.metric-icon-blue { font-size: 18px; color: #6366f1; }
.metric-value-row { margin-bottom: 8px; }

.metric-main-number {
  font-size: 28px;
  font-weight: 800;
  color: #0f172a;
  letter-spacing: -0.02em;
  line-height: 1;
}

.metric-footer-note { font-size: 12px; color: #94a3b8; font-weight: 500; }

/* Tarjetas Principales */
.white-panel-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 28px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.card-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  margin-bottom: 20px;
}

.section-kicker {
  display: block;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.12em;
  color: #6366f1;
  margin-bottom: 4px;
  text-transform: uppercase;
}

.section-title {
  font-size: 20px;
  font-weight: 800;
  color: #091133;
  margin: 0;
  letter-spacing: -0.02em;
}

.badge-count-blue {
  background: #eef2ff;
  color: #4338ca;
  font-size: 13px;
  font-weight: 800;
  padding: 4px 12px;
  border-radius: 99px;
}

/* Grillas */
.two-columns-grid { display: grid; gap: 28px; align-items: start; }

/* Formularios claros */
.form-label-light {
  display: block;
  font-size: 12px;
  font-weight: 700;
  color: #475569;
  margin-bottom: 6px;
}

.light-text-input {
  background: #ffffff;
  color: #091133;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 13px;
  width: 100%;
  outline: none;
  transition: border-color 0.2s;
}

.light-text-input:focus { border-color: #6366f1; }

.light-select {
  --background: #ffffff;
  --color: #091133;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  min-height: 40px;
  font-size: 13px;
  width: 100%;
}

/* Tablas Claras */
.table-responsive {
  width: 100%;
  overflow-x: auto;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
}

.light-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; }

.light-table th {
  padding: 12px 16px;
  background: #f8fafc;
  color: #64748b;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  border-bottom: 1px solid #e2e8f0;
  white-space: nowrap;
}

.light-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; color: #091133; }

.cancha-badge {
  background: #f1f5f9;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.table-status-pill {
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
  padding: 3px 8px;
  border-radius: 4px;
  background: #f1f5f9;
  color: #64748b;
}

.table-status-pill.confirmada,
.table-status-pill.aceptada { background: #dcfce7; color: #15803d; }
.table-status-pill.rechazada,
.table-status-pill.cancelada { background: #fee2e2; color: #b91c1c; }

/* Botones */
.btn-action-primary {
  background: #6366f1;
  border: 0;
  color: #ffffff;
  font-weight: 800;
  padding: 10px 18px;
  border-radius: 8px;
  font-size: 13px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.mini-btn {
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 800;
  cursor: pointer;
  border: 0;
}

.mini-btn.success { background: #dcfce7; color: #15803d; }
.mini-btn.danger { background: #fee2e2; color: #b91c1c; }

/* Modales */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(4, 9, 36, 0.6);
  display: grid;
  place-items: center;
  z-index: 1000;
  padding: 20px;
}

.message-banner {
  padding: 12px 16px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 8px;
}

.message-banner.success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.message-banner.error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

.font-bold { font-weight: 700; }
.text-blue { color: #4338ca; }
.text-muted { color: #94a3b8; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.py-6 { padding-top: 24px; padding-bottom: 24px; }

@media (max-width: 1100px) {
  .metrics-grid-three { grid-template-columns: 1fr; }
  .two-columns-grid { grid-template-columns: 1fr !important; }
  .dark-top-section { padding: 16px 20px 30px; }
  .light-body-section { padding: 24px 20px 60px; }
  .hero-banner-content { flex-direction: column; align-items: flex-start; gap: 20px; }
}
</style>