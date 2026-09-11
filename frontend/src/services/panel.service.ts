import api from './api';

export default {
  misComplejos() {
    return api.get('/panel/mis-complejos');
  },

  agenda(complejoId: number, fecha?: string) {
    return api.get(`/panel/complejos/${complejoId}/agenda`, {
      params: fecha ? { fecha } : {},
    });
  },

  estadisticas(complejoId: number) {
    return api.get(`/panel/complejos/${complejoId}/estadisticas`);
  },

  crearReserva(payload: {
    cancha_id: number;
    nombre_cliente: string;
    telefono_cliente?: string;
    fecha: string;
    hora_inicio: string;
    hora_fin: string;
  }) {
    return api.post('/panel/reservas', payload);
  },

  cancelarReserva(reservaId: number) {
    return api.delete(`/panel/reservas/${reservaId}`);
  },

  crearBloqueo(payload: {
    cancha_id: number;
    fecha: string;
    hora_inicio: string;
    hora_fin: string;
    motivo: string;
    notas?: string;
  }) {
    return api.post('/panel/bloqueos', payload);
  },

  eliminarBloqueo(bloqueoId: number) {
    return api.delete(`/panel/bloqueos/${bloqueoId}`);
  },
};