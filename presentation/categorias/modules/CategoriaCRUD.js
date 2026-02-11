
class CategoriaCRUD extends CategoriaService {
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

    async create(categoriaData, imageFile = null) {
        try {
            let response;

            if (imageFile) {
                const formData = new FormData();
                console.log(categoriaData);
                formData.append('action', 'create');
                formData.append('nombre', categoriaData.nombre);
                formData.append('estado', categoriaData.estado);
                formData.append('imagen', imageFile);
                response = await this.makeFormRequest(this.baseUrl, formData);
            } else {
                response = await this.makeRequest(this.baseUrl, {
                    method: 'POST',
                    body: JSON.stringify({
                        action: 'create',
                        nombre: categoriaData.nombre,
                        estado: categoriaData.estado
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
            this.showNotification('Error al crear la categoría', 'error');
            return { success: false, message: 'Error de conexión' };
        }
    }

    async update(id, categoriaData, imageFile = null) {
        try {
            let response;
            if (imageFile) {
                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('id', id);
                formData.append('nombre', categoriaData.nombre);
                formData.append('estado', categoriaData.estado);
                formData.append('imagen', imageFile);
                response = await this.makeFormRequest(this.baseUrl, formData, 'PUT');
            } else {
                response = await this.makeRequest(this.baseUrl, {
                    method: 'PUT',
                    body: JSON.stringify({
                        action: 'update',
                        id: id,
                        nombre: categoriaData.nombre,
                        estado: categoriaData.estado
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
            this.showNotification('Error al actualizar la categoría', 'error');
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
            this.showNotification('Error al eliminar la categoría', 'error');
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
            this.showNotification('Error al obtener la categoría', 'error');
            return null;
        }
    }
}