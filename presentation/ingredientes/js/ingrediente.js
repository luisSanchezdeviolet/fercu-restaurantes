document.addEventListener('DOMContentLoaded', function() {
    if (window.ingredientesUI) {
        return;
    }

    window.ingredientesUI = new IngredientesUI();
    window.ingredientesUI.init();
    
    console.log('ingredientesUI inicializado correctamente');
});

const toastStyles = `
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
    </style>
`;

if (!document.head.querySelector('#toast-styles')) {
    const styleElement = document.createElement('div');
    styleElement.id = 'toast-styles';
    styleElement.innerHTML = toastStyles;
    document.head.appendChild(styleElement);
}