import api from './api';

export default {
  listar(complejoId: number) {
    return api.get(`/panel/complejos/${complejoId}/canchas`);
  },
  crear(payload: { complejo_id: number; deporte_id: number; nombre: string; precio_hora: number }) {
    return api.post('/panel/canchas', payload);
  },
  actualizar(canchaId: number, payload: { nombre?: string; precio_hora?: number; activa?: boolean }) {
    return api.put(`/panel/canchas/${canchaId}`, payload);
  },
};