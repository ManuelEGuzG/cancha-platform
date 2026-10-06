import { request as sendHttp } from 'node:http';

const apiUrls = (process.env.API_URLS || process.env.API_URL || '').split(',').filter(Boolean);
const complejo = process.env.COMPLEJO_SLUG;
const canchaId = Number(process.env.CANCHA_ID);
const fecha = process.env.FECHA;
const hora = process.env.HORA || '12:00';
const solicitudes = Number(process.env.SOLICITUDES || 5);
const lecturas = Number(process.env.LECTURAS || 50);

if (apiUrls.length === 0 || !complejo || !canchaId || !fecha) {
  throw new Error('Define API_URLS, COMPLEJO_SLUG, CANCHA_ID y FECHA antes de ejecutar.');
}

function percentil(valores, porcentaje) {
  const ordenados = [...valores].sort((a, b) => a - b);
  return ordenados[Math.min(ordenados.length - 1, Math.ceil(ordenados.length * porcentaje) - 1)];
}

function solicitarHttp(url, method, body) {
  const inicio = performance.now();

  return new Promise((resolve) => {
    const request = sendHttp(url, {
      method,
      agent: false,
      headers: body ? { 'Content-Type': 'application/json', Accept: 'application/json' } : { Accept: 'application/json' },
    }, (response) => {
      const chunks = [];
      response.on('data', (chunk) => chunks.push(chunk));
      response.on('end', () => resolve({
        status: response.statusCode || 0,
        body: Buffer.concat(chunks).toString('utf8').slice(0, 300),
        ms: performance.now() - inicio,
      }));
    });

    request.on('error', (error) => resolve({ status: 0, error: error.message, ms: performance.now() - inicio }));
    if (body) request.write(JSON.stringify(body));
    request.end();
  });
}

async function solicitarReserva(indice) {
  return solicitarHttp(`${apiUrls[indice % apiUrls.length]}/complejos/${complejo}/solicitudes`, 'POST', {
    cancha_id: canchaId,
    nombre_cliente: `Carga ${indice}`,
    cedula_cliente: String(100000000 + indice),
    telefono_cliente: String(88900000 + indice),
    fecha,
    horas: [hora],
  });
}

async function leerDisponibilidad(indice) {
  return solicitarHttp(
    `${apiUrls[indice % apiUrls.length]}/complejos/${complejo}/disponibilidad?fecha=${fecha}`,
    'GET',
  );
}

const escrituras = await Promise.all(Array.from({ length: solicitudes }, (_, indice) => solicitarReserva(indice)));
const lecturasResultados = await Promise.all(Array.from({ length: lecturas }, (_, indice) => leerDisponibilidad(indice)));
const creadas = escrituras.filter((resultado) => resultado.status === 201).length;
const conflictos = escrituras.filter((resultado) => resultado.status === 409).length;
const fallidas = escrituras.filter((resultado) => ![201, 409].includes(resultado.status));

console.log(JSON.stringify({
  solicitudes,
  resultados_escritura: {
    creadas,
    conflictos,
    otros_estados: fallidas.map(({ status, error }) => error ? `${status}:${error}` : status),
    errores_respuesta: [...new Set(fallidas.map(({ body }) => body).filter(Boolean))],
    p50_ms: Math.round(percentil(escrituras.map(({ ms }) => ms), 0.5)),
    p95_ms: Math.round(percentil(escrituras.map(({ ms }) => ms), 0.95)),
  },
  lecturas,
  resultados_disponibilidad: {
    errores_http: lecturasResultados.filter(({ status }) => status !== 200).length,
    errores_transporte: lecturasResultados.filter(({ status }) => status === 0).length,
    p50_ms: Math.round(percentil(lecturasResultados.map(({ ms }) => ms), 0.5)),
    p95_ms: Math.round(percentil(lecturasResultados.map(({ ms }) => ms), 0.95)),
  },
}, null, 2));

if (creadas !== 1 || conflictos !== solicitudes - 1 || fallidas.length > 0
  || lecturasResultados.some(({ status }) => status !== 200)) {
  process.exitCode = 1;
}