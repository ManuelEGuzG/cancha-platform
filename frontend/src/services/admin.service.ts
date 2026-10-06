import api from './api';

export default {
  usuarios() {
    return api.get('/panel/admin/usuarios');
  },
  crearUsuario(payload: { name: string; email: string; password: string; telefono?: string; complejo_id: number; rol: string }) {
    return api.post('/panel/admin/usuarios', payload);
  },
  toggleEstadoUsuario(userId: number) {
    return api.patch(`/panel/admin/usuarios/${userId}/estado`);
  },
  actividadUsuario(userId: number) {
    return api.get(`/panel/admin/usuarios/${userId}/actividad`);
  },
  resumenFacturacion() {
    return api.get('/panel/admin/facturacion/resumen');
  },
  movimientos(params?: { complejo_id?: number; cancha_id?: number }) {
    return api.get('/panel/admin/facturacion/movimientos', { params });
  },
  canchas() {
    return api.get('/panel/admin/canchas/verificacion');
  },
  registrarPago(complejoId: number, payload: { monto: number; periodo_desde: string; periodo_hasta: string; fecha_pago: string; metodo?: string; notas?: string }) {
    return api.post(`/panel/admin/complejos/${complejoId}/pagos`, payload);
  },
  historialPagos(complejoId: number) {
    return api.get(`/panel/admin/complejos/${complejoId}/pagos`);
  },
  toggleEstadoComplejo(complejoId: number) {
    return api.patch(`/panel/admin/complejos/${complejoId}/estado`);
  },
  crearComplejo(payload: { distrito_id: number; nombre: string; whatsapp_numero?: string; telefono?: string }) {
    return api.post('/panel/complejos', payload);
  },
  canchasPorVerificar() {
    return api.get('/panel/admin/canchas/verificacion', { params: { estado: 'pendiente' } });
  },
  verificarCancha(canchaId: number, payload: { estado_verificacion: 'aprobada' | 'rechazada'; observaciones_admin?: string }) {
    return api.patch(`/panel/admin/canchas/${canchaId}/verificacion`, payload);
  },
  reporteMensual(mes: string) {
    return api.get('/panel/admin/reportes/mensual', { params: { mes } });
  },
  exportarReporteMensual(mes: string) {
    return api.get('/panel/admin/reportes/mensual/exportar', {
      params: { mes },
      responseType: 'blob',
    });
  },
};