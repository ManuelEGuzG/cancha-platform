import api from './api';
import type { Complejo, ComplejoDetalle, Disponibilidad } from '@/types';

export default {
  listar(params?: { distrito_id?: number; canton_id?: number }) {
    return api.get<{ data: Complejo[] }>('/complejos', { params });
  },

  detalle(slug: string) {
    return api.get<{ data: ComplejoDetalle }>(`/complejos/${slug}`);
  },

  disponibilidad(slug: string, fecha?: string) {
    return api.get<{ data: Disponibilidad }>(`/complejos/${slug}/disponibilidad`, {
      params: fecha ? { fecha } : {},
    });
  },

  enlaceWhatsApp(slug: string, params?: { cancha_id?: number; fecha?: string; hora_inicio?: string }) {
    return api.get<{ data: { enlace: string } }>(`/complejos/${slug}/whatsapp`, { params });
  },
};