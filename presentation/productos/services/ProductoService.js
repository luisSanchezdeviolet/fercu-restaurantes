class ProductoService {
    constructor() {
        this.baseUrl = '../../../api/productos.php';
        this.maxRetries = 1;
        this.retryDelay = 1000;
    }

    async makeRequest(url, options = {}) {
        let lastError;

        for (let attempt = 1; attempt <= this.maxRetries; attempt++) {
            try {
                const defaultHeaders = {
                    'Content-Type': 'application/json'
                };

                const requestOptions = {
                    ...options,
                    headers: {
                        ...defaultHeaders,
                        ...(options.headers || {})
                    }
                };

                if (options.method && options.method.toUpperCase() === 'GET') {
                    requestOptions.method = 'GET';
                } else {
                    requestOptions.method = requestOptions.method || 'POST';
                }
                requestOptions.credentials = 'include';
                requestOptions.cache = 'no-cache';

                const response = await fetch(url, requestOptions);

                if (!response.ok) {
                    let errorText = '';
                    try {
                        errorText = await response.text();
                    } catch (textError) {
                        console.log(textError);
                    }

                    if (!errorText && response.status === 500) {
                        errorText = 'El servidor respondió sin detalles. Verifique logs del servidor.';
                    }

                    const errorMessage = this.getErrorMessage(response.status, errorText);
                    throw new Error(errorMessage);
                }

                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const result = await response.json();
                    return result;
                } else {
                    const text = await response.text();
                    throw new Error(`Respuesta no es JSON válido: ${text.substring(0, 200)}`);
                }
            } catch (error) {
                lastError = error;
                if (error.message.includes('HTTP error') && error.message.includes('4')) {
                    break;
                }

                if (attempt < this.maxRetries) {
                    await this.delay(this.retryDelay);
                    this.retryDelay *= 1.5;
                }
            }
        }

        throw lastError;
    }

    async makeFormRequest(url, formData, method = 'POST') {
        let lastError;
        for (let attempt = 1; attempt <= this.maxRetries; attempt++) {
            try {
                const response = await fetch(url, {
                    method: method,
                    body: formData
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    const errorMessage = this.getErrorMessage(response.status, errorText);
                    throw new Error(errorMessage);
                }

                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const result = await response.json();
                    return result;
                } else {
                    const text = await response.text();
                    throw new Error(`Respuesta no es JSON válido: ${text.substring(0, 200)}`);
                }
            } catch (error) {
                lastError = error;
                if (error.message.includes('HTTP error') && error.message.includes('4')) {
                    break;
                }

                if (attempt < this.maxRetries) {
                    await this.delay(this.retryDelay);
                }
            }
        }

        throw lastError;
    }

    getErrorMessage(status, errorText) {
        switch (status) {
            case 400:
                return `Solicitud inválida: ${errorText || 'Datos enviados incorrectos'}`;
            case 401:
                return 'No autorizado: Credenciales inválidas';
            case 403:
                return 'Prohibido: No tienes permisos para esta acción';
            case 404:
                return 'No encontrado: El recurso solicitado no existe';
            case 413:
                return 'Archivo demasiado grande: Reduce el tamaño de la imagen';
            case 415:
                return 'Tipo de archivo no soportado: Usa JPG, PNG, SVG o WEBP';
            case 422:
                return `Datos no procesables: ${errorText || 'Verifica los campos enviados'}`;
            case 500:
                return `Error interno del servidor: ${errorText || 'Contacta al administrador'}`;
            case 502:
                return 'Error de gateway: El servidor está temporalmente no disponible';
            case 503:
                return 'Servicio no disponible: Intenta más tarde';
            case 504:
                return 'Timeout del servidor: La operación tardó demasiado';
            default:
                return `Error HTTP ${status}: ${errorText || 'Error desconocido'}`;
        }
    }

    async delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    showNotification(message, type = 'info') {
        const notification = this.createNotification(message, type);
        document.body.appendChild(notification);

        requestAnimationFrame(() => {
            notification.classList.add('show');
        });

        const delay = type === 'error' ? 8000 : 5000;
        setTimeout(() => {
            this.dismissNotification(notification);
        }, delay);
    }

    createNotification(message, type) {
        const notification = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';
        const icon = type === 'success' ? '✓' : type === 'error' ? '⚠' : 'ⓘ';

        notification.className = `toast-notification alert alert-dismissible ${bgColor} text-white`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 350px;
            max-width: 90vw;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease-out;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            border-radius: 8px;
            border: none;
        `;

        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <span class="me-2" style="font-size: 1.2em;">${icon}</span>
                <div class="flex-grow-1">
                    <div style="font-weight: 500;">${message}</div>
                    ${type === 'error' ? '<small>Revisa nuevamente!</small>' : ''}
                </div>
                <button type="button" class="btn-close btn-close-white ms-2" aria-label="Close"></button>
            </div>
        `;

        notification.classList.add('toast-notification-hidden');

        notification.querySelector('.btn-close').addEventListener('click', () => {
            this.dismissNotification(notification);
        });

        return notification;
    }

    dismissNotification(notification) {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }

    validateFile(file) {
        const errors = [];
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/svg+xml', 'image/webp'];
        const maxSize = 5 * 1024 * 1024;

        if (!file) {
            errors.push('No se ha seleccionado ningún archivo');
            return { valid: false, errors };
        }

        if (file.name.toLowerCase().match(/\.(php|exe|dll)$/)) {
            errors.push('Tipo de archivo peligroso');
        }

        if (!validTypes.includes(file.type)) {
            errors.push(`Tipo de archivo no válido: ${file.type}. Usa: JPG, PNG, SVG o WEBP`);
        }

        if (file.size > maxSize) {
            errors.push(`Archivo demasiado grande: ${(file.size / 1024 / 1024).toFixed(2)}MB. Máximo: 5MB`);
        }

        if (file.size === 0) {
            errors.push('El archivo está vacío');
        }

        return {
            valid: errors.length === 0,
            errors: errors
        };
    }

    async checkServerHealth() {
        try {
            const response = await fetch(this.baseUrl + '?action=health', {
                method: 'GET',
                timeout: 5000
            });

            return response.ok;
        } catch (error) {
            return false;
        }
    }

    validateProductData(productData) {
        const errors = [];

        if (!productData.nombre || productData.nombre.trim() === '') {
            errors.push('El nombre del producto es obligatorio');
        }

        if (!productData.precio || isNaN(productData.precio) || parseFloat(productData.precio) < 0) {
            errors.push('El precio debe ser un número válido mayor o igual a 0');
        }

        if (productData.nombre && productData.nombre.length > 100) {
            errors.push('El nombre del producto no puede exceder 100 caracteres');
        }

        if (productData.descripcion && productData.descripcion.length > 500) {
            errors.push('La descripción no puede exceder 500 caracteres');
        }

        return {
            valid: errors.length === 0,
            errors: errors
        };
    }

    validateIngredientes(ingredientes) {
        const errors = [];

        if (!Array.isArray(ingredientes)) {
            errors.push('Los ingredientes deben ser un array');
            return { valid: false, errors };
        }

        ingredientes.forEach((ingrediente, index) => {
            if (!ingrediente.ingrediente_id) {
                errors.push(`Ingrediente ${index + 1}: ID de ingrediente es requerido`);
            }

            if (!ingrediente.cantidad || isNaN(ingrediente.cantidad) || parseFloat(ingrediente.cantidad) <= 0) {
                errors.push(`Ingrediente ${index + 1}: La cantidad debe ser un número mayor a 0`);
            }
        });

        return {
            valid: errors.length === 0,
            errors: errors
        };
    }
}

if (!document.getElementById('toast-notification-styles')) {
    const style = document.createElement('style');
    style.id = 'toast-notification-styles';
    style.textContent = `
        .toast-notification.show {
            opacity: 1 !important;
            transform: translateX(0) !important;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }
    `;
    document.head.appendChild(style);
}