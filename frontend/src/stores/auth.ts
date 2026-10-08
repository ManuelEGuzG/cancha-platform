import { defineStore } from 'pinia';
import api from '@/services/api';

interface ComplejoUsuario {
  id: number;
  nombre: string;
  slug: string;
  rol: string;
}

interface Usuario {
  id: number;
  name: string;
  email: string;
  is_platform_admin: boolean;
  two_factor_enabled: boolean;
  complejos: ComplejoUsuario[];
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token') as string | null,
    usuario: null as Usuario | null,
  }),

  getters: {
    autenticado: (state) => !!state.token,
  },

  actions: {
    async login(email: string, password: string, code?: string) {
      const { data } = await api.post('/auth/login', { email, password, ...(code ? { code } : {}) });
      this.token = data.token;
      this.usuario = data.user;
      localStorage.setItem('token', data.token);
    },

    async cargarUsuario() {
      if (!this.token) return;
      const { data } = await api.get('/auth/me');
      this.usuario = data.user;
    },

    async logout() {
      try {
        await api.post('/auth/logout');
      } finally {
        this.token = null;
        this.usuario = null;
        localStorage.removeItem('token');
      }
    },
  },
});