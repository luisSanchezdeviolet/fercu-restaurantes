class MenusUI {
    constructor() {
        this.mesaSeleccionada = null;
    }

    async init() {
        try {
            this.setupEventListeners();
            await this.cargarMesas();
        } catch (error) {
            console.error('Error al inicializar MenusUI:', error);
        }
    }

    setupEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            this.cargarMesas();
            setInterval(() => this.cargarMesas(), 30000);
        });
        
        window.addEventListener('focus', () => this.cargarMesas());
    }

    async cargarMesas() {
        const loading = document.getElementById('loading');
        const container = document.getElementById('mesas-container');
        const errorDiv = document.getElementById('error-message');
        
        try {
            loading.style.display = 'block';
            container.innerHTML = '';
            errorDiv.style.display = 'none';
            
            const response = await fetch('api/mesas.php?action=findAll');
            const result = await response.json();
            
            if (result.success) {
                this.mostrarMesas(result.data);
            } else {
                throw new Error(result.message || 'Error al cargar las mesas');
            }
            
        } catch (error) {
            console.error('Error:', error);
            errorDiv.textContent = 'Error al cargar las mesas: ' + error.message;
            errorDiv.style.display = 'block';
        } finally {
            loading.style.display = 'none';
        }
    }

    mostrarMesas(mesas) {
        const container = document.getElementById('mesas-container');
        
        if (!mesas || mesas.length === 0) {
            container.innerHTML = '<div class="col-12"><p class="text-center">No hay mesas disponibles</p></div>';
            return;
        }
        
        container.innerHTML = mesas.map(mesa => this.crearTarjetaMesa(mesa)).join('');
    }

    crearTarjetaMesa(mesa) {
        const estado = mesa.estado || 'disponible';
        const numeroMesa = mesa.numero_mesa || mesa.id;
        const asientos = mesa.asientos || '';

        const colorIcon = this.getColorByStatus(estado);
        const classCss = this.getClassByStatus(estado);
        const badgeClass = this.getBadgeClass(estado);
        
        return `
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card mesa-card ${classCss}" onclick="menusUI.seleccionarMesa(${mesa.id}, '${numeroMesa}', '${estado}')">
                    <div class="card-body text-center">
                        <svg class="mesa-icon" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                            <path fill="${colorIcon}" d="M512 160L256 32L0 160V208L16 216V376C16 389.255 26.7452 400 40 400H56C69.2548 400 80 389.255 80 376V248L224 320V472C224 485.255 234.745 496 248 496H264C277.255 496 288 485.255 288 472V320L432 248V376C432 389.255 442.745 400 456 400H472C485.255 400 496 389.255 496 376V216L512 208V160Z"></path>
                        </svg>
                        <h5 class="card-title">Mesa ${numeroMesa}</h5>
                        <span class="badge ${badgeClass} status-badge">${this.capitalizeFirst(estado)}</span>
                        ${asientos ? `<p class="card-text mt-2 mb-1"><small>Capacidad: ${asientos} personas</small></p>` : ''}
                    </div>
                </div>
            </div>
        `;
    }

    getColorByStatus(estado) {
        switch(estado.toLowerCase()) {
            case 'disponible':
            case 'libre':
                return '#28a745';
            case 'reservada':
                return '#ffc107';
            case 'ocupada':
                return '#dc3545';
            default:
                return '#6c757d';
        }
    }

    getClassByStatus(estado) {
        switch(estado.toLowerCase()) {
            case 'disponible':
            case 'libre':
                return 'mesa-disponible';
            case 'reservada':
                return 'mesa-reservada';
            case 'ocupada':
                return 'mesa-ocupada';
            default:
                return 'mesa-default';
        }
    }

    getBadgeClass(estado) {
        switch(estado.toLowerCase()) {
            case 'disponible':
            case 'libre':
                return 'bg-success';
            case 'reservada':
                return 'bg-warning text-dark';
            case 'ocupada':
                return 'bg-danger';
            default:
                return 'bg-secondary';
        }
    }

    capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
    }

     seleccionarMesa(id, numeroMesa, estado) {
        if(estado.toLowerCase() !== 'disponible' && estado.toLowerCase() != 'libre') {
            this.showNotification(`La mesa ${numeroMesa} está ${estado}. No se puede seleccionar.`, `${estado.toLowerCase() === 'ocupada' ? 'error' : 'info'}`);
            return;
        } else {
            this.abrirMenuConMesa(id, numeroMesa, estado);
        }
    }

    abrirMenuConMesa(id, numeroMesa, estado) {
        localStorage.setItem('mesaSeleccionada', JSON.stringify({
            id: id,
            numero_mesa: numeroMesa,
            estado: estado,
            timestamp: Date.now()
        }));
        window.location.href = `crear-orden.php?mesa_id=${id}&mesa_numero=${numeroMesa}&estado=${estado}`;
    }

    abrirMenuConStorage(id, numeroMesa, estado) {
        localStorage.setItem('mesaSeleccionada', JSON.stringify({
            id: id,
            numero_mesa: numeroMesa,
            estado: estado,
            timestamp: Date.now()
        }));
        
        window.location.href = 'menu.html';
    }

    abrirMenuConSession(id, numeroMesa, estado) {
        sessionStorage.setItem('mesaActual', JSON.stringify({
            id: id,
            numero_mesa: numeroMesa,
            estado: estado,
            timestamp: Date.now()
        }));
        
        window.location.href = 'menu.html';
    }

    async cambiarEstadoMesa(id, nuevoEstado) {
        try {
            const response = await fetch('api/mesas.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'updateStatus',
                    id: id,
                    estado: nuevoEstado
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.cargarMesas();
            } else {
                alert('Error al cambiar estado: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al cambiar estado de la mesa');
        }
    }

    showNotification(message, type = 'info') {
        const notification = this.createNotification(message, type);
        document.body.appendChild(notification);
        
       requestAnimationFrame(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
    });

        const delay = type === 'error' ? 2000 : 1000;
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
                    ${type === 'error' ? '<small>Selecciona otra mesa!</small>' : ''}
                </div>
                <button type="button" class="btn-close btn-close-white ms-2" aria-label="Close"></button>
            </div>
        `;

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
}