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
  subirFoto(canchaId: number, foto: File, caption?: string) {
    const datos = new FormData();
    datos.append('foto', foto);
    if (caption) datos.append('caption', caption);
    return api.post(`/panel/canchas/${canchaId}/fotos`, datos, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
  },
  eliminarFoto(fotoId: number) {
    return api.delete(`/panel/fotos/${fotoId}`);
  },
};