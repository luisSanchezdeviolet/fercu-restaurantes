class ProductoCRUD extends ProductoService {
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
            this.showNotification('Error al obtener los productos', 'error');
            return [];
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
            this.showNotification('Error al obtener el producto', 'error');
            return null;
        }
    }

    async findByCategoria(categoriaId) {
        try {
            const response = await this.makeRequest(`${this.baseUrl}?action=findByCategoria&categoria_id=${categoriaId}`, {
                method: 'GET'
            });

            if (response.success) {
                return response.data;
            } else {
                this.showNotification(response.message, 'error');
                return [];
            }
        } catch (error) {
            this.showNotification('Error al obtener productos por categoría', 'error');
            return [];
        }
    }

    async create(productoData, imageFile = null) {
        try {
            const validation = this.validateProductData(productoData);
            if (!validation.valid) {
                this.showNotification(validation.errors.join(', '), 'error');
                return { success: false, errors: validation.errors };
            }

            if (productoData.ingredientes && productoData.ingredientes.length > 0) {
                const ingredientesValidation = this.validateIngredientes(productoData.ingredientes);
                if (!ingredientesValidation.valid) {
                    this.showNotification(ingredientesValidation.errors.join(', '), 'error');
                    return { success: false, errors: ingredientesValidation.errors };
                }
            }

            let response;

            if (imageFile) {
                const fileValidation = this.validateFile(imageFile);
                if (!fileValidation.valid) {
                    this.showNotification(fileValidation.errors.join(', '), 'error');
                    return { success: false, errors: fileValidation.errors };
                }

                const formData = new FormData();
                formData.append('action', 'create');
                formData.append('nombre', productoData.nombre);
                formData.append('precio', productoData.precio);
                formData.append('categoria_id', productoData.categoria_id || '');
                formData.append('descripcion', productoData.descripcion || '');
                formData.append('ingredientes', JSON.stringify(productoData.ingredientes || []));
                formData.append('imagen', imageFile);
                formData.append('estado', productoData.estado);

                response = await this.makeFormRequest(this.baseUrl, formData);
            } else {
                response = await this.makeRequest(this.baseUrl, {
                    method: 'POST',
                    body: JSON.stringify({
                        action: 'create',
                        nombre: productoData.nombre,
                        precio: productoData.precio,
                        categoria_id: productoData.categoria_id || null,
                        descripcion: productoData.descripcion || null,
                        ingredientes: productoData.ingredientes || [],
                        estado: productoData.estado || null
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
            this.showNotification('Error al crear el producto', 'error');
            return { success: false, message: 'Error de conexión' };
        }
    }

    async update(id, productoData, imageFile = null) {
        try {
            const validation = this.validateProductData(productoData);
            if (!validation.valid) {
                this.showNotification(validation.errors.join(', '), 'error');
                return { success: false, errors: validation.errors };
            }

            if (productoData.ingredientes && productoData.ingredientes.length > 0) {
                const ingredientesValidation = this.validateIngredientes(productoData.ingredientes);
                if (!ingredientesValidation.valid) {
                    this.showNotification(ingredientesValidation.errors.join(', '), 'error');
                    return { success: false, errors: ingredientesValidation.errors };
                }
            }

            let response;

            if (imageFile) {
                const fileValidation = this.validateFile(imageFile);
                if (!fileValidation.valid) {
                    this.showNotification(fileValidation.errors.join(', '), 'error');
                    return { success: false, errors: fileValidation.errors };
                }
                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('id', id);
                formData.append('nombre', productoData.nombre);
                formData.append('precio', productoData.precio);
                formData.append('categoria_id', productoData.categoria_id || '');
                formData.append('descripcion', productoData.descripcion || '');
                formData.append('ingredientes', JSON.stringify(productoData.ingredientes || []));
                formData.append('imagen', imageFile);
                formData.append('estado', productoData.estado);

                response = await this.makeFormRequest(this.baseUrl, formData, 'PUT');
            } else {
                response = await this.makeRequest(this.baseUrl, {
                    method: 'PUT',
                    body: JSON.stringify({
                        action: 'update',
                        id: id,
                        nombre: productoData.nombre,
                        precio: productoData.precio,
                        categoria_id: productoData.categoria_id || null,
                        descripcion: productoData.descripcion || null,
                        ingredientes: productoData.ingredientes || [],
                        estado: productoData.estado || null
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
            this.showNotification('Error al actualizar el producto', 'error');
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
            this.showNotification('Error al eliminar el producto', 'error');
            return { success: false, message: 'Error de conexión' };
        }
    }

    async addIngrediente(productoId, ingredienteId, cantidad = 1) {
        try {
            if (!productoId || !ingredienteId) {
                this.showNotification('ID del producto e ingrediente son requeridos', 'error');
                return { success: false, message: 'Parámetros faltantes' };
            }

            if (!cantidad || isNaN(cantidad) || parseFloat(cantidad) <= 0) {
                this.showNotification('La cantidad debe ser un número mayor a 0', 'error');
                return { success: false, message: 'Cantidad inválida' };
            }

            const response = await this.makeRequest(this.baseUrl, {
                method: 'POST',
                body: JSON.stringify({
                    action: 'addIngrediente',
                    producto_id: productoId,
                    ingrediente_id: ingredienteId,
                    cantidad: cantidad
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
            this.showNotification('Error al agregar ingrediente', 'error');
            return { success: false, message: 'Error de conexión' };
        }
    }

    async removeIngrediente(productoId, ingredienteId) {
        try {
            if (!productoId || !ingredienteId) {
                this.showNotification('ID del producto e ingrediente son requeridos', 'error');
                return { success: false, message: 'Parámetros faltantes' };
            }

            const response = await this.makeRequest(this.baseUrl, {
                method: 'POST',
                body: JSON.stringify({
                    action: 'removeIngrediente',
                    producto_id: productoId,
                    ingrediente_id: ingredienteId
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
            this.showNotification('Error al eliminar ingrediente', 'error');
            return { success: false, message: 'Error de conexión' };
        }
    }

    formatPrice(price) {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN'
        }).format(price);
    }

    sanitizeProductData(rawData) {
        return {
            nombre: (rawData.nombre || '').trim(),
            precio: parseFloat(rawData.precio) || 0,
            categoria_id: rawData.categoria_id || null,
            descripcion: (rawData.descripcion || '').trim() || null,
            ingredientes: Array.isArray(rawData.ingredientes) ? rawData.ingredientes : [],
            estado: rawData.estado || null
        };
    }

    createEmptyProduct() {
        return {
            id: null,
            nombre: '',
            precio: 0,
            categoria_id: null,
            descripcion: '',
            imagen: null,
            ingredientes: [],
            estado: null,
            created_at: null,
            updated_at: null
        };
    }
}