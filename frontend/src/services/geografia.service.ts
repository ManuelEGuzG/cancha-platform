import api from './api';

export default {
  provincias() {
    return api.get('/geografia/provincias');
  },
  cantones(provinciaId: number) {
    return api.get('/geografia/cantones', { params: { provincia_id: provinciaId } });
  },
  distritos(cantonId: number) {
    return api.get('/geografia/distritos', { params: { canton_id: cantonId } });
  },
};