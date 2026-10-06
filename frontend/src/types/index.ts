export interface Complejo {
  id: number;
  nombre: string;
  slug: string;
  logo_url: string | null;
  direccion_texto: string | null;
  latitud: string | null;
  longitud: string | null;
  distrito: string;
  canton: string;
  provincia: string;
  total_canchas: number;
  precio_desde: number;
}

export interface Cancha {
  id: number;
  nombre: string;
  precio_hora: number;
  deporte: string;
}

export interface ComplejoDetalle {
  id: number;
  nombre: string;
  slug: string;
  descripcion: string | null;
  logo_url: string | null;
  direccion_texto: string | null;
  latitud: string | null;
  longitud: string | null;
  telefono: string | null;
  whatsapp_numero: string | null;
  distrito: string;
  canton: string;
  provincia: string;
  canchas: Cancha[];
}

export interface BloqueDisponibilidad {
  hora_inicio: string;
  hora_fin: string;
  estado: 'disponible' | 'reservado' | 'en_tramite' | 'cerrada' | 'en_mantenimiento';
}

export interface DisponibilidadCancha {
  cancha_id: number;
  nombre: string;
  precio_hora: number;
  bloques: BloqueDisponibilidad[];
}

export interface Disponibilidad {
  fecha: string;
  canchas: DisponibilidadCancha[];
}