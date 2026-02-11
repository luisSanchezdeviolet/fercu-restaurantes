class MesaCRUD extends MesaService {
    async findAll() {
        try {
            const response = await this.makeRequest(`${this.baseUrl}?action=findAll`, {
                method: 'GET'
            });

            if (response.success) {
                return response.data;
            } else {
                this.showNotification(response.message, 'error');
                return [];
            }
        } catch (error) {
            this.showNotification('Error al obtener las mesas', 'error');
            return [];
        }
    }

    async create(mesaData) {
        try {
            let response;
            if (mesaData) {
                response = await fetch(this.baseUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'create',
                        numero_mesa: mesaData.numero_mesa,
                        asientos: mesaData.asientos,
                        estado: mesaData.estado
                    })
                });
            }

            const data = await response.json();

            if (!response.ok) {
                this.showNotification(data.message || 'Error en la solicitud', 'error');
                return { success: false, message: data.message || 'Error en la solicitud' };
            }

            this.showNotification(data.message, 'success');
            return data;

        } catch (error) {
            this.showNotification('Error de conexión o interno', 'error');
            return { success: false, message: 'Error de conexión o interno' };
        }
    }

    async update(id, mesaData) {
        try {
            let response;
            if (mesaData) {
                response = await this.makeRequest(this.baseUrl, {
                    method: 'PUT',
                    body: JSON.stringify({
                        action: 'update',
                        id: id,
                        numero_mesa: mesaData.numero_mesa,
                        asientos: mesaData.asientos,
                        estado: mesaData.estado
                    })
                });
            }

            if (response.success) {
                this.showNotification(response.message, 'success');
                return response;
            } else {
                this.showNotification(response.message, 'error');
                return response;
            }
        } catch (error) {
            this.showNotification('Error al actualizar la mesa', 'error');
            return { success: false, message: 'Error de conexión' };
        }
    }

    async delete(id) {
        try {
            const response = await this.makeRequest(this.baseUrl, {
                method: 'DELETE',
                body: JSON.stringify({
                    action: 'delete',
                    id: id
                })
            });

            if (response.success) {
                this.showNotification(response.message, 'success');
                return response;
            } else {
                this.showNotification(response.message, 'error');
                return response;
            }
        } catch (error) {
            this.showNotification('Error al eliminar la mesa', 'error');
            return { success: false, message: 'Error de conexión' };
        }
    }

    async findById(id) {
        try {
            const response = await this.makeRequest(`${this.baseUrl}?action=findById&id=${id}`, {
                method: 'GET'
            });

            if (response.success) {
                return response.data;
            } else {
                this.showNotification(response.message, 'error');
                return null;
            }
        } catch (error) {
            this.showNotification('Error al obtener la mesa', 'error');
            return null;
        }
    }

    async findByNumeroMesa(numero_mesa) {
        try {
            const response = await this.makeRequest(`${this.baseUrl}?action=findByNumeroMesa&numero_mesa=${numero_mesa}`, {
                method: 'GET'
            });

            if (response.success) {
                return response.data;
            } else {
                this.showNotification(response.message, 'error');
                return null;
            }
        } catch (error) {
            this.showNotification('Error al obtener la mesa', 'error');
            return null;
        }
    }

    async filterByEstado(estado) {
        try {
            const response = await this.makeRequest(`${this.baseUrl}?action=filterByEstado&estado=${estado}`, {
                method: 'GET'
            });

            if (response.success) {
                return response.data;
            } else {
                this.showNotification(response.message, 'error');
                return [];
            }
        } catch (error) {
            this.showNotification('Error al filtrar mesas por estado', 'error');
            return [];
        }
    }

    async cambiarEstado(id, estado) {
        try {
            const response = await fetch(this.baseUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'cambiarEstado',
                    id: id,
                    estado: estado
                })
            });

            const data = await response.json();

            if (!response.ok) {
                this.showNotification(data.message || 'Error en la solicitud', 'error');
                return { success: false, message: data.message || 'Error en la solicitud' };
            }

            this.showNotification(data.message, 'success');
            return data;

        } catch (error) {
            this.showNotification('Error al cambiar el estado de la mesa', 'error');
            return { success: false, message: 'Error de conexión o interno' };
        }
    }

    async getMesasDisponibles() {
        return await this.filterByEstado('Disponible');
    }

    async getMesasOcupadas() {
        return await this.filterByEstado('Ocupada');
    }

    async getMesasReservadas() {
        return await this.filterByEstado('Reservada');
    }

    async ocuparMesa(id) {
        return await this.cambiarEstado(id, 'Ocupada');
    }

    async liberarMesa(id) {
        return await this.cambiarEstado(id, 'Disponible');
    }

    async reservarMesa(id) {
        return await this.cambiarEstado(id, 'Reservada');
    }

    async getEstadisticas() {
        try {
            const todasLasMesas = await this.findAll();
            
            if (!todasLasMesas || todasLasMesas.length === 0) {
                return {
                    total: 0,
                    disponibles: 0,
                    ocupadas: 0,
                    reservadas: 0,
                    porcentajeOcupacion: 0
                };
            }

            const estadisticas = {
                total: todasLasMesas.length,
                disponibles: todasLasMesas.filter(mesa => mesa.estado === 'Disponible').length,
                ocupadas: todasLasMesas.filter(mesa => mesa.estado === 'Ocupada').length,
                reservadas: todasLasMesas.filter(mesa => mesa.estado === 'Reservada').length
            };

            estadisticas.porcentajeOcupacion = Math.round(
                ((estadisticas.ocupadas + estadisticas.reservadas) / estadisticas.total) * 100
            );

            return estadisticas;

        } catch (error) {
            this.showNotification('Error al obtener estadísticas', 'error');
            return {
                total: 0,
                disponibles: 0,
                ocupadas: 0,
                reservadas: 0,
                porcentajeOcupacion: 0
            };
        }
    }
}