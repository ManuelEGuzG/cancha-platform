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
          canchas: [{ id: 1, nombre: 'Cancha 1', precio_hora: 18000, deporte: 'Fútbol' }],
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
