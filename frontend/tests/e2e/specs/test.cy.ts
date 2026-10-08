describe('My First Test', () => {
  it('Visits the app root url', () => {
    cy.visit('/home')
    cy.contains('h1', 'DOMINA LA CANCHA HOY')
  })
})

describe('Public reservation request', () => {
  it('selects a slot, submits customer data and opens the prepared WhatsApp chat', () => {
    cy.intercept('GET', '**/api/complejos/soccer-center', {
      body: {
        data: {
          id: 1,
          nombre: 'Soccer Center',
          slug: 'soccer-center',
          descripcion: 'Complejo de prueba',
          logo_url: null,
          direccion_texto: 'San José',
          telefono: '2222-1111',
          whatsapp_numero: '88888888',
          distrito: 'Carmen',
          canton: 'San José',
          provincia: 'San José',
          canchas: [{
            id: 1,
            nombre: 'Cancha 1',
            precio_hora: 18000,
            deporte: 'Fútbol',
            fotos: [{ url: 'https://images.example.test/cancha-aprobada.jpg', caption: 'Cancha principal' }],
            calificacion_promedio: 4.8,
            cantidad_resenas: 3,
          }],
        },
      },
    }).as('complejo')

    cy.intercept('GET', '**/api/complejos/soccer-center/disponibilidad*', {
      body: {
        data: {
          fecha: new Date().toISOString().slice(0, 10),
          canchas: [{
            cancha_id: 1,
            nombre: 'Cancha 1',
            precio_hora: 18000,
            calificacion_promedio: 4.8,
            cantidad_resenas: 3,
            bloques: [{ hora_inicio: '18:00', hora_fin: '19:00', estado: 'disponible' }],
          }],
        },
      },
    }).as('disponibilidad')

    cy.intercept('POST', '**/api/complejos/soccer-center/solicitudes', (request) => {
      expect(request.body).to.include({
        cancha_id: 1,
        nombre_cliente: 'Ana Prueba',
        cedula_cliente: '123456789',
        telefono_cliente: '88888888',
      })
      expect(request.body.horas).to.deep.equal(['18:00'])
      request.reply({
        statusCode: 201,
        body: {
          data: {
            solicitud_id: 'solicitud-e2e',
            expira_en: new Date(Date.now() + 15 * 60_000).toISOString(),
            whatsapp_url: 'https://wa.me/50688888888?text=Solicitud',
          },
        },
      })
    }).as('crearSolicitud')

    cy.visit('/complejo/soccer-center')
    cy.wait('@complejo')
    cy.wait('@disponibilidad')
    cy.get('.gallery-grid img').should('have.attr', 'src', 'https://images.example.test/cancha-aprobada.jpg')
    cy.get('.slot-btn.slot-disponible').contains('18:00').click()
    cy.contains('button', 'Solicitar 1 hora').click()
    cy.get('input[placeholder="Ej: Juan Pérez"]').type('Ana Prueba')
    cy.get('input[placeholder="Número de identificación"]').type('123456789')
    cy.get('input[placeholder="Ej: 8888-8888"]').type('88888888')

    cy.window().then((window) => {
      cy.stub(window, 'open').as('whatsappOpen').returns({ location: {}, opener: null })
    })
    cy.contains('button', 'Solicitar horas').click()
    cy.wait('@crearSolicitud')
    cy.get('@whatsappOpen').should('have.been.calledWith', 'about:blank', '_blank')
  })
})

describe('Nearby court search', () => {
  it('sorts by browser location and links the detail to Google Maps', () => {
    cy.intercept('GET', '**/api/geografia/provincias', { body: { data: [] } }).as('provincias')
    cy.intercept({ method: 'GET', pathname: '/api/complejos' }, {
      body: {
        data: [
          {
            id: 1,
            nombre: 'Cancha Lejana',
            slug: 'cancha-lejana',
            logo_url: null,
            direccion_texto: 'Alajuela',
            latitud: '10.0000000',
            longitud: '-84.2000000',
            distrito: 'Alajuela',
            canton: 'Alajuela',
            provincia: 'Alajuela',
            total_canchas: 1,
            precio_desde: 18000,
          },
          {
            id: 2,
            nombre: 'Cancha Cercana',
            slug: 'cancha-cercana',
            logo_url: null,
            direccion_texto: 'San José',
            latitud: '9.9340000',
            longitud: '-84.0840000',
            distrito: 'Carmen',
            canton: 'San José',
            provincia: 'San José',
            total_canchas: 1,
            precio_desde: 18000,
          },
        ],
      },
    }).as('complejos')

    cy.intercept('GET', '**/api/complejos/cancha-cercana', {
      body: {
        data: {
          id: 2,
          nombre: 'Cancha Cercana',
          slug: 'cancha-cercana',
          descripcion: null,
          logo_url: null,
          direccion_texto: 'San José',
          latitud: '9.9340000',
          longitud: '-84.0840000',
          telefono: null,
          whatsapp_numero: null,
          distrito: 'Carmen',
          canton: 'San José',
          provincia: 'San José',
          canchas: [],
        },
      },
    }).as('detalleCercano')
    cy.intercept('GET', '**/api/complejos/cancha-cercana/disponibilidad*', {
      body: { data: { fecha: new Date().toISOString().slice(0, 10), canchas: [] } },
    })

    cy.visit('/home', {
      onBeforeLoad(window) {
        Object.defineProperty(window.navigator, 'geolocation', {
          configurable: true,
          value: {
            getCurrentPosition: (success: (position: { coords: { latitude: number; longitude: number } }) => void) => {
              success({ coords: { latitude: 9.933, longitude: -84.083 } })
            },
          },
        })
      },
    })
    cy.wait('@provincias')
    cy.wait('@complejos')
    cy.get('.hero-search-bar').click()
    cy.contains('button', 'Buscar cerca de mí').click()
    cy.get('.card-item').first().should('contain', 'Cancha Cercana').click()
    cy.wait('@detalleCercano')
    cy.get('.google-maps-link')
      .should('have.attr', 'href', 'https://www.google.com/maps/search/?api=1&query=9.9340000,-84.0840000')
  })
})

describe('Administrator two-factor login', () => {
  it('requests an OTP after password and completes admin sign-in', () => {
    cy.intercept('POST', '**/api/auth/login', (request) => {
      if (!request.body.code) {
        request.reply({
          statusCode: 422,
          body: { message: 'El código de autenticación es inválido.', errors: { code: ['Código requerido'] } },
        })
        return
      }

      expect(request.body.code).to.equal('123456')
      request.reply({
        statusCode: 200,
        body: {
          token: 'admin-e2e-token',
          user: { id: 1, name: 'Admin', email: 'admin@example.test', is_platform_admin: true, two_factor_enabled: true, complejos: [] },
        },
      })
    }).as('login')
    cy.intercept('GET', '**/api/auth/me', {
      body: { user: { id: 1, name: 'Admin', email: 'admin@example.test', is_platform_admin: true, two_factor_enabled: true, complejos: [] } },
    })
    cy.intercept('GET', '**/api/panel/admin/facturacion/resumen', { body: { data: [] } })
    cy.intercept('GET', '**/api/panel/admin/usuarios', { body: { data: [] } })
    cy.intercept('GET', '**/api/geografia/provincias', { body: { data: [] } })

    cy.visit('/login')
    cy.get('input[type="email"]').type('admin@example.test')
    cy.get('input[type="password"]').type('un-password-de-prueba')
    cy.contains('button', 'Ingresar al Panel').click()
    cy.get('input[placeholder="Código de 6 dígitos o recuperación"]').should('be.visible').type('123456')
    cy.contains('button', 'Ingresar al Panel').click()
    cy.location('pathname').should('eq', '/admin')
  })
})

describe('Court photo management', () => {
  it('lets an owner upload a photo and shows that it awaits review', () => {
    let fotoSubida = false
    const canchaBase = {
      id: 1,
      nombre: 'Cancha 1',
      activa: true,
      estado_verificacion: 'aprobada',
      observaciones_admin: null,
      precio_hora: 18000,
      deporte: { nombre: 'Fútbol' },
      fotos: [],
    }

    cy.intercept('GET', '**/api/panel/complejos/1/canchas', (request) => {
      request.reply({ body: { data: [{
        ...canchaBase,
        fotos: fotoSubida ? [{ id: 9, url: 'https://images.example.test/court.jpg', caption: null, estado_verificacion: 'pendiente' }] : [],
      }] } })
    }).as('listarCanchas')
    cy.intercept('POST', '**/api/panel/canchas/1/fotos', (request) => {
      fotoSubida = true
      expect(request.headers['content-type']).to.contain('multipart/form-data')
      request.reply({ statusCode: 201, body: { data: { id: 9, url: 'https://images.example.test/court.jpg', estado_verificacion: 'pendiente' } } })
    }).as('subirFoto')

    cy.visit('/panel/canchas?complejoId=1', {
      onBeforeLoad(window) { window.localStorage.setItem('token', 'owner-e2e-token') },
    })
    cy.wait('@listarCanchas')
    cy.get('input[type="file"]').selectFile({
      contents: Cypress.Buffer.from('fake-image-content'),
      fileName: 'cancha.jpg',
      mimeType: 'image/jpeg',
    }, { force: true })
    cy.wait('@subirFoto')
    cy.wait('@listarCanchas')
    cy.contains('Foto subida y enviada a revisión.').should('be.visible')
    cy.contains('Publicada').should('not.exist')
  })
})

describe('Administrator media moderation', () => {
  it('approves a photo from the moderation queue', () => {
    cy.intercept('GET', '**/api/auth/me', {
      body: { user: { id: 1, name: 'Admin', email: 'admin@example.test', is_platform_admin: true, two_factor_enabled: true, complejos: [] } },
    }).as('adminMe')
    cy.intercept('GET', '**/api/panel/admin/facturacion/resumen', { body: { data: [] } })
    cy.intercept('GET', '**/api/panel/admin/usuarios', { body: { data: [] } })
    cy.intercept('GET', '**/api/geografia/provincias', { body: { data: [] } })
    cy.intercept('GET', '**/api/panel/admin/fotos/verificacion', {
      body: { data: { data: [{ id: 9, url: 'https://images.example.test/court.jpg', caption: 'Cancha', cancha: { nombre: 'Cancha 1', complejo: { nombre: 'Soccer Center' } } }] } },
    }).as('fotosPendientes')
    cy.intercept('PATCH', '**/api/panel/admin/fotos/9/verificacion', { statusCode: 200, body: { data: { id: 9, estado_verificacion: 'aprobada' } } }).as('aprobarFoto')

    cy.visit('/admin', {
      onBeforeLoad(window) { window.localStorage.setItem('token', 'admin-e2e-token') },
    })
    cy.wait('@adminMe')
    cy.contains('button', 'Fotos').click({ force: true })
    cy.wait('@fotosPendientes')
    cy.contains('button', 'Aprobar').click({ force: true })
    cy.wait('@aprobarFoto')
  })
})

describe('Administrator 2FA enrollment', () => {
  it('scans a local QR, confirms TOTP and requires saving recovery codes', () => {
    cy.intercept('GET', '**/api/auth/me', {
      body: { user: { id: 1, name: 'Admin', email: 'admin@example.test', is_platform_admin: true, two_factor_enabled: false, complejos: [] } },
    }).as('adminMe')
    cy.intercept('GET', '**/api/panel/admin/facturacion/resumen', { body: { data: [] } })
    cy.intercept('GET', '**/api/panel/admin/usuarios', { body: { data: [] } })
    cy.intercept('GET', '**/api/geografia/provincias', { body: { data: [] } })
    cy.intercept('POST', '**/api/auth/2fa/setup', {
      statusCode: 200,
      body: { data: { secret: 'JBSWY3DPEHPK3PXP', otpauth_url: 'otpauth://totp/Sportra:admin%40example.test?secret=JBSWY3DPEHPK3PXP&issuer=Sportra' } },
    }).as('setup2fa')
    cy.intercept('POST', '**/api/auth/2fa/confirm', {
      statusCode: 200,
      body: { message: 'Verificación de dos pasos activada.', data: { recovery_codes: ['ABCDE-12345', 'FGHIJ-67890'] } },
    }).as('confirm2fa')

    cy.visit('/admin', {
      onBeforeLoad(window) { window.localStorage.setItem('token', 'admin-enrollment-token') },
    })
    cy.wait('@adminMe')
    cy.contains('button', 'Seguridad').click({ force: true })
    cy.get('input[autocomplete="current-password"]').first().type('admin-password')
    cy.contains('button', 'Configurar aplicación').click()
    cy.wait('@setup2fa')
    cy.get('img[alt="Código QR de configuración TOTP"]').should('be.visible')
    cy.get('input[autocomplete="one-time-code"]').first().type('123456')
    cy.contains('button', 'Confirmar y activar').click()
    cy.wait('@confirm2fa')
    cy.contains('ABCDE-12345').should('be.visible')
    cy.contains('button', 'Volver a iniciar sesión').should('be.disabled')
    cy.get('input[type="checkbox"]').check()
    cy.contains('button', 'Volver a iniciar sesión').should('be.enabled')
  })
})

describe('Owner rental completion', () => {
  it('marks a finished rental complete and refreshes the history', () => {
    let completada = false
    const reservaBase = {
      id: 5,
      cancha_id: 1,
      fecha: '2026-01-01',
      hora_inicio: '09:00',
      hora_fin: '10:00',
      nombre_cliente: 'Cliente Demo',
      estado: 'confirmada',
      decision_propietario: null,
    }

    cy.intercept('GET', '**/api/panel/mis-complejos', {
      body: { data: [{ id: 1, nombre: 'Soccer Center', slug: 'soccer-center' }] },
    })
    cy.intercept('GET', '**/api/panel/complejos/1/agenda*', {
      body: { data: { fecha: '2026-10-06', canchas: [{ cancha_id: 1, nombre: 'Cancha 1', deporte: 'Fútbol', reservas: [], bloqueos: [] }] } },
    })
    cy.intercept('GET', '**/api/panel/complejos/1/estadisticas', {
      body: { data: {
        total_canchas: 1,
        reservas_hoy: 0,
        reservas_mes: { total: 1, confirmadas: 1, canceladas: 0, completadas: completada ? 1 : 0, no_presentadas: 0 },
        solicitudes_mes: { recibidas: 1, aceptadas: 1, rechazadas: 0, vencidas: 0 },
        demanda_por_hora: [],
        ingreso_estimado_mes: 18000,
      } },
    })
    cy.intercept('GET', '**/api/panel/reservas*', (request) => {
      request.reply({ body: { data: { data: [{ ...reservaBase, estado: completada ? 'completada' : 'confirmada' }], current_page: 1, last_page: 1 } } })
    }).as('historialOwner')
    cy.intercept('POST', '**/api/panel/reservas/5/completar', (request) => {
      completada = true
      request.reply({ statusCode: 200, body: { message: 'Alquiler marcado como completado.' } })
    }).as('completarAlquiler')

    cy.visit('/panel', {
      onBeforeLoad(window) { window.localStorage.setItem('token', 'owner-e2e-token') },
    })
    cy.wait('@historialOwner')
    cy.contains('button', 'Marcar completada').click()
    cy.wait('@completarAlquiler')
    cy.wait('@historialOwner')
    cy.get('.history-card').should('contain', 'Completada')
  })
})

describe('Owner weekly schedule', () => {
  it('shows per-day hours, exceptions and removes a future block', () => {
    let bloqueoEliminado = false
    cy.intercept('GET', '**/api/panel/canchas/1/horarios', (request) => {
      request.reply({ body: { data: {
        horarios_regulares: [{ dia_semana: 1, hora_apertura: '08:00:00', hora_cierre: '20:00:00' }],
        horarios_excepcion: [{ id: 3, fecha: '2026-12-25', hora_apertura: null, hora_cierre: null, motivo: 'Feriado' }],
        bloqueos: bloqueoEliminado ? [] : [{ id: 4, fecha: '2026-10-10', hora_inicio: '14:00', hora_fin: '15:00', motivo: 'mantenimiento' }],
      } } })
    }).as('cargarHorarios')
    cy.intercept('DELETE', '**/api/panel/bloqueos/4', (request) => {
      bloqueoEliminado = true
      request.reply({ statusCode: 200, body: { message: 'Bloqueo eliminado correctamente.' } })
    }).as('quitarBloqueo')

    cy.visit('/panel/canchas/1/horarios', {
      onBeforeLoad(window) { window.localStorage.setItem('token', 'owner-schedule-token') },
    })
    cy.wait('@cargarHorarios')
    cy.get('.weekly-schedule-row').should('have.length', 7)
    cy.contains('Cerrado').should('be.visible')
    cy.contains('Feriado').should('be.visible')
    cy.contains('mantenimiento').should('be.visible')
    cy.contains('button', 'Quitar').click()
    cy.wait('@quitarBloqueo')
    cy.wait('@cargarHorarios')
    cy.contains('mantenimiento').should('not.exist')
  })
})

describe('Owner request inbox', () => {
  it('accepts a request, confirms payment and rejects another request in the panel', () => {
    let aceptada = false
    let pagada = false
    let rechazada = false
    const solicitud = {
      solicitud_id: 'solicitud-pendiente',
      reserva_id: 42,
      estado: 'pendiente',
      cancha_id: 1,
      cancha: 'Cancha 1',
      complejo: 'Soccer Center',
      fecha: '2026-10-10',
      nombre_cliente: 'Ana Cliente',
      cedula_cliente: '123456789',
      telefono_cliente: '88888888',
      observaciones: 'Llegamos diez minutos antes.',
      expira_en: new Date(Date.now() + 10 * 60_000).toISOString(),
      whatsapp_url: 'https://wa.me/50688888888?text=Solicitud',
      horas: [{ inicio: '18:00', fin: '19:00' }, { inicio: '19:00', fin: '20:00' }],
    }
    const otraSolicitud = {
      ...solicitud,
      solicitud_id: 'solicitud-para-rechazar',
      reserva_id: 43,
      nombre_cliente: 'Luis Cliente',
      whatsapp_url: 'https://wa.me/50688888888?text=OtraSolicitud',
    }

    cy.intercept('GET', '**/api/panel/mis-complejos', { body: { data: [{ id: 1, nombre: 'Soccer Center' }] } })
    cy.intercept('GET', '**/api/panel/complejos/1/agenda*', {
      body: { data: { fecha: '2026-10-06', canchas: [{ cancha_id: 1, nombre: 'Cancha 1', deporte: 'Fútbol', reservas: [], bloqueos: [] }] } },
    })
    cy.intercept('GET', '**/api/panel/complejos/1/estadisticas', {
      body: { data: { total_canchas: 1, reservas_hoy: 0, reservas_mes: { total: 0 }, solicitudes_mes: { aceptadas: 0, rechazadas: 0 }, demanda_por_hora: [], ingreso_estimado_mes: 0 } },
    })
    cy.intercept('GET', '**/api/panel/solicitudes', (request) => {
      const pendientes = [
        ...(pagada ? [] : [{ ...solicitud, estado: aceptada ? 'aceptada' : 'pendiente' }]),
        ...(rechazada ? [] : [otraSolicitud]),
      ]
      request.reply({ body: { data: pendientes, meta: { current_page: 1, last_page: 1, total: pendientes.length } } })
    }).as('solicitudesPendientes')
    cy.intercept('POST', '**/api/panel/reservas/42/aceptar', (request) => {
      aceptada = true
      request.reply({ statusCode: 200, body: { message: 'Solicitud aceptada.' } })
    }).as('aceptarSolicitud')
    cy.intercept('POST', '**/api/panel/reservas/42/confirmar-pago', (request) => {
      pagada = true
      request.reply({ statusCode: 200, body: { message: 'Pago confirmado.' } })
    }).as('confirmarPago')
    cy.intercept('POST', '**/api/panel/reservas/43/rechazar', (request) => {
      rechazada = true
      request.reply({ statusCode: 200, body: { message: 'Solicitud rechazada.' } })
    }).as('rechazarSolicitud')
    cy.intercept('GET', '**/api/panel/reservas*', { body: { data: { data: [], current_page: 1, last_page: 1 } } })

    cy.visit('/panel', {
      onBeforeLoad(window) { window.localStorage.setItem('token', 'owner-inbox-token') },
    })
    cy.wait('@solicitudesPendientes')
    cy.contains('h2', /solicitudes pendientes/i).should('be.visible')
    cy.contains('Ana Cliente').should('be.visible')
    cy.contains('.request-card', 'Ana Cliente').find('.time-badge').should('have.length', 2)
    cy.contains('.request-card', 'Ana Cliente').find('.time-badge').first().should('contain', '18:00').and('contain', '19:00')
    cy.get('a.request-btn.whatsapp').first().should('have.attr', 'href', solicitud.whatsapp_url)
    cy.contains('.request-card', 'Ana Cliente').contains('button', 'Aceptar').click({ force: true })
    cy.wait('@aceptarSolicitud')
    cy.wait('@solicitudesPendientes')
    cy.contains('.request-card', 'Ana Cliente').contains('button', 'Confirmar pago').should('be.visible').click({ force: true })
    cy.wait('@confirmarPago')
    cy.wait('@solicitudesPendientes')
    cy.contains('Ana Cliente').should('not.exist')
    cy.contains('.request-card', 'Luis Cliente').contains('button', 'Rechazar').click({ force: true })
    cy.wait('@rechazarSolicitud')
    cy.wait('@solicitudesPendientes')
    cy.contains('¡Todo al día!').should('be.visible')
  })
})
