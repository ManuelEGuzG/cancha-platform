import api from './api';
import type { Complejo, ComplejoDetalle, Disponibilidad } from '@/types';

export default {
  listar(params?: { provincia_id?: number; distrito_id?: number; canton_id?: number }) {
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

  crearSolicitud(slug: string, payload: {
    cancha_id: number;
    nombre_cliente: string;
    cedula_cliente: string;
    telefono_cliente: string;
    fecha: string;
    horas: string[];
    observaciones?: string;
    website?: string;
    captcha_token?: string;
  }) {
    return api.post<{ data: { solicitud_id: string; expira_en: string; whatsapp_url: string } }>(
      `/complejos/${slug}/solicitudes`,
      payload,
    );
  },
};