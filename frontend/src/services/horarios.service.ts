import api from './api';

export default {
  index(canchaId: number) {
    return api.get(`/panel/canchas/${canchaId}/horarios`);
  },
  actualizarRegular(canchaId: number, horarios: { dia_semana: number; hora_apertura: string; hora_cierre: string }[]) {
    return api.put(`/panel/canchas/${canchaId}/horarios/regular`, { horarios });
  },
  crearExcepcion(payload: { cancha_id: number; fecha: string; hora_apertura?: string; hora_cierre?: string; motivo: string }) {
    return api.post('/panel/horarios/excepcion', payload);
  },
  eliminarExcepcion(id: number) {
    return api.delete(`/panel/horarios/excepcion/${id}`);
  },
};