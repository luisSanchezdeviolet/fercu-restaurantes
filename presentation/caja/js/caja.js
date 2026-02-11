document.addEventListener('DOMContentLoaded', function() {
    if (window.cajasApp) {
        return;
    }

    window.cajasApp = new CajasUI();
    window.cajasApp.init();
    window.cajasUI = window.cajasApp;
});

const cajasToastStyles = `
    <style>
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .toast-notification {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 5px;
    }
    
    .ingredientes-panel {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 10px;
    }
    
    .ingrediente-item {
        background-color: white;
        transition: all 0.2s ease;
        border: 1px solid #dee2e6;
    }
    
    .ingrediente-item:hover {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-color: #007bff;
    }
    
    .cantidad-input {
        text-align: center;
        font-weight: 500;
    }
    
    .remove-ingrediente {
        transition: all 0.2s ease;
    }
    
    .remove-ingrediente:hover {
        transform: scale(1.1);
    }
    
    #image-preview .card {
        transition: all 0.2s ease;
        border: 2px dashed #dee2e6;
    }
    
    #image-preview .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        border-color: #007bff;
    }
    
    .form-label.fw-bold {
        color: #495057;
        font-size: 0.9rem;
    }
    
    .input-group-text {
        background-color: #e9ecef;
        border-color: #ced4da;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .ingredientes-panel h6 {
        color: #495057;
        border-bottom: 2px solid #007bff;
        padding-bottom: 5px;
        margin-bottom: 15px;
    }
    
    .btn-outline-danger:hover {
        transform: scale(1.05);
    }
    </style>
`;

if (!document.head.querySelector('#cajas-toast-styles')) {
    const styleElement = document.createElement('div');
    styleElement.id = 'cajas-toast-styles';
    styleElement.innerHTML = cajasToastStyles;
    document.head.appendChild(styleElement);
}