document.addEventListener('DOMContentLoaded', function() {
    if (window.ordenUI) {
        return;
    }

    window.ordenUI = new OrdenUI();
    window.ordenUI.init();

    console.log('ordenUI inicializado correctamente');
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        if (window.ordenUI) {
            window.ordenUI.obtenerMesaSeleccionada();
        }
    });
} else {
    setTimeout(() => {
        if (window.ordenUI) {
            window.ordenUI.obtenerMesaSeleccionada();
        }
    }, 100);
}

const toastStyles = `
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
    }
    
    .toast {
        min-width: 300px;
        margin-bottom: 10px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideIn 0.3s ease-out;
    }
    
    .toast.success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }
    
    .toast.error {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }
    
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;

if (!document.head.querySelector('#toast-styles')) {
    const styleElement = document.createElement('style');
    styleElement.id = 'toast-styles';
    styleElement.innerHTML = toastStyles;
    document.head.appendChild(styleElement);
}