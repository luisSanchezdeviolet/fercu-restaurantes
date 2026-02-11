
class IngredienteCRUD extends IngredienteService {
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
            this.showNotification('Error al obtener las categorías', 'error');
            return [];
        }
    }

async create(ingredienteData) {
    try {
        let response;
        if (ingredienteData) {
            response = await fetch(this.baseUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'create',
                    nombre: ingredienteData.nombre,
                    estado: ingredienteData.estado,
                    cantidad: ingredienteData.cantidad,
                    unidadMedida: ingredienteData.unidadMedida
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


    async update(id, ingredienteData) {
        try {
            let response;
            if (ingredienteData) {
                response = await this.makeRequest(this.baseUrl, {
                    method: 'PUT',
                    body: JSON.stringify({
                        action: 'update',
                        id: id,
                        nombre: ingredienteData.nombre,
                        estado: ingredienteData.estado,
                        cantidad: ingredienteData.cantidad,
                        unidadMedida: ingredienteData.unidadMedida
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
            this.showNotification('Error al actualizar el ingrediente', 'error');
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
            this.showNotification('Error al eliminar el ingrediente', 'error');
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
            this.showNotification('Error al obtener el ingrediente', 'error');
            return null;
        }
    }
}