document.addEventListener("DOMContentLoaded", function () {
  // Elementos para el scroll suave
  const funciones = document.getElementById('funciones');
  const pasosSection = document.getElementById("pasos-heading");
  const testimonios = document.getElementById("testimonios");
  const btnMasInfo = document.getElementById("btn-mas-info");
  
  // Elementos para el modal
  const demoButtons = document.querySelectorAll('.navegacion__link--registrar, .repartidores__enlace');
  const demoModal = new bootstrap.Modal(document.getElementById('demoModal'));
  const demoForm = document.getElementById('demoForm');

  // Funciones de scroll suave
  if (btnMasInfo) {
    btnMasInfo.addEventListener("click", function () {
      pasosSection.scrollIntoView({
        behavior: "smooth",
      });
    });
  }

  // Funcionalidad del modal
  demoButtons.forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      demoModal.show();
    });
  });

  // Validación en tiempo real del correo
  const emailInput = document.getElementById('email');
  let emailCheckTimeout;
  let isTrialEligible = true;
  
  if (emailInput) {
    emailInput.addEventListener('blur', function() {
      const email = this.value.trim();
      if (email && email.includes('@')) {
        clearTimeout(emailCheckTimeout);
        emailCheckTimeout = setTimeout(() => {
          checkTrialEligibility(email);
        }, 500);
      }
    });
  }
  
  function checkTrialEligibility(email) {
    fetch('check-trial-eligibility.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success && !data.eligible) {
        isTrialEligible = false;
        // Mostrar advertencia
        const emailField = document.getElementById('email');
        const warningDiv = document.getElementById('trial-warning') || createWarningDiv();
        warningDiv.innerHTML = `
          <i class="bi bi-exclamation-triangle-fill"></i> 
          <strong>Este correo ya utilizó la prueba gratuita.</strong><br>
          <small>${data.recommendation}</small>
        `;
        warningDiv.style.display = 'block';
        emailField.parentNode.appendChild(warningDiv);
      } else {
        isTrialEligible = true;
        const warningDiv = document.getElementById('trial-warning');
        if (warningDiv) {
          warningDiv.style.display = 'none';
        }
      }
    })
    .catch(error => {
      console.log('Error checking trial eligibility:', error);
    });
  }
  
  function createWarningDiv() {
    const div = document.createElement('div');
    div.id = 'trial-warning';
    div.className = 'alert alert-warning mt-2 mb-0';
    div.style.fontSize = '0.9rem';
    return div;
  }

  // Manejo del formulario de registro
  demoForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validación del formulario
    if (!this.checkValidity()) {
      e.stopPropagation();
      this.classList.add('was-validated');
      return;
    }

    // Verificar si el correo no es elegible para trial
    if (!isTrialEligible) {
      Swal.fire({
        icon: 'info',
        title: 'Prueba gratuita ya utilizada',
        html: 'Este correo ya ha usado la prueba gratuita anteriormente.<br><br>Te invitamos a conocer nuestros planes de pago:<br><strong>Plan Básico: $399/mes</strong><br><strong>Plan Professional: $899/mes</strong>',
        confirmButtonText: 'Ver Planes',
        showCancelButton: true,
        cancelButtonText: 'Cerrar'
      }).then((result) => {
        if (result.isConfirmed) {
          demoModal.hide();
          document.querySelector('.planes').scrollIntoView({ behavior: 'smooth' });
        }
      });
      return;
    }

    // Recoger datos del formulario
    const formData = {
      nombre: document.getElementById('nombre').value,
      apellido: document.getElementById('apellido').value,
      empresa: document.getElementById('empresa').value,
      correo: document.getElementById('email').value,
      telefono: document.getElementById('telefono').value,
      giro: document.getElementById('giro').value,
      empleados: document.getElementById('empleados').value
    };

    // Mostrar spinner de "Procesando"
    const modalBody = document.querySelector('#demoModal .modal-body');
    modalBody.innerHTML = `
      <div class="alert alert-info text-center">
        <h4>Procesando solicitud...</h4>
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="small mt-2">Estamos procesando tu solicitud, por favor espera...</p>
      </div>
    `;
    document.querySelector('#demoModal .modal-footer').style.display = 'none';

    // Enviar datos con Fetch API
    fetch('register-procesar.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
      // Verificar si el registro fue exitoso
      if (data.success) {
        // Mostrar mensaje de éxito
        modalBody.innerHTML = `
          <div class="alert alert-success text-center">
            <h4>🎉 ¡Registro exitoso!</h4>
            <p>Tu cuenta ha sido creada correctamente.</p>
            <p class="fw-bold">Te hemos enviado a <strong>${formData.correo}</strong>:</p>
            <ul class="text-start">
              <li>✅ Tus credenciales de acceso</li>
              <li>✅ El enlace para acceder a tu demo de 15 días</li>
              <li>✅ Instrucciones para configurar tu cuenta</li>
            </ul>
            <div class="alert alert-warning mt-3">
              <i class="bi bi-exclamation-triangle-fill"></i> <strong>Importante:</strong> Si no encuentras el correo, revisa tu carpeta de <strong>spam o correo no deseado</strong>.
            </div>
            <div class="mt-3">
              <a href="login.php" class="btn btn-primary">Ir al Login</a>
            </div>
          </div>
        `;
      } else {
        // Mostrar error si el registro falló
        modalBody.innerHTML = `
          <div class="alert alert-danger text-center">
            <h4>❌ Error en el registro</h4>
            <p>${data.error || data.message || 'No se pudo completar el registro'}</p>
            <p>Por favor, inténtalo de nuevo o contáctanos directamente.</p>
          </div>
        `;
      }
      document.querySelector('#demoModal .modal-footer').style.display = 'none';
    })
    .catch(error => {
      // Mostrar mensaje de error
      modalBody.innerHTML = `
        <div class="alert alert-danger text-center">
          <h4>Ocurrió un error</h4>
          <p>No pudimos procesar tu solicitud en este momento. Por favor, inténtalo de nuevo más tarde.</p>
        </div>
      `;
      document.querySelector('#demoModal .modal-footer').style.display = 'none';
    });
  });

  // Resetear modal cuando se cierre
  document.getElementById('demoModal').addEventListener('hidden.bs.modal', function () {
    if(demoForm) {
      demoForm.reset();
      demoForm.classList.remove('was-validated');
    }
    
    // Restaurar el contenido original del modal
    const modalBody = document.querySelector('#demoModal .modal-body');
    const footer = document.querySelector('#demoModal .modal-footer');
    if (footer) footer.style.display = 'block';
  });

  // Planes y precios
  const planModalEl = document.getElementById('modal-plan');
  const planModal = new bootstrap.Modal(planModalEl);
  const demoModalInst = new bootstrap.Modal(document.getElementById('demoModal'));

  const wrapper = planModalEl.querySelector('#modal-content-wrapper');
  const originalInner = wrapper.innerHTML;

  const toggleBackground = document.querySelector('.toggle-background');
  const planButtons = document.querySelectorAll('.planes-activos button');
  const precios = document.querySelectorAll('.precio');
  const botonesPlan = document.querySelectorAll('.boton-plan');
  
  const descripciones = {
    basico: {
      mensual: "Plan Básico ideal para pequeños restaurantes por $399 MXN al mes: hasta 10 mesas, hasta 3 usuarios, órdenes ilimitadas, gestión de inventario, reportes básicos y soporte por email.",
      anual: "Plan Básico anual por $3,990 MXN con 17% de descuento: todo lo del plan mensual más ahorras $798 MXN (2 meses gratis). Ideal para empezar."
    },
    professional: {
      mensual: "Plan Professional por $899 MXN al mes: mesas ilimitadas, usuarios ilimitados, multi-sucursales, reportes avanzados, acceso a API, integraciones y soporte prioritario 24/7.",
      anual: "Professional anual por $8,630 MXN con 20% de descuento: todo lo del plan mensual más ahorras $2,158 MXN (2.4 meses gratis). Perfecto para restaurantes establecidos."
    }
  };
  
  let planSeleccionado = 'mensual';

  planButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      planSeleccionado = btn.dataset.plan;
      planButtons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      toggleBackground.style.transform = planSeleccionado === 'mensual'
        ? 'translateX(0)'
        : 'translateX(100%)';
      precios.forEach(p => p.textContent = p.dataset[planSeleccionado]);
    });
  });

  let current = {};
  
  function openPlanModal(tipoPlan, precio, ahorro) {
    wrapper.innerHTML = originalInner;
    attachWrapperClicks();
    attachLoginHandler();

    document.getElementById('modal-titulo').textContent =
      `Inscripción Plan ${tipoPlan.charAt(0).toUpperCase() + tipoPlan.slice(1)} ${planSeleccionado === 'mensual' ? 'Mensual' : 'Anual'}`;
    document.getElementById('modal-precio').textContent =
      `Precio: $${precio} MXN ${planSeleccionado === 'mensual' ? 'al mes' : 'al año'}`;
    const ahorroEl = document.getElementById('modal-ahorro');
    if (planSeleccionado === 'anual') {
      ahorroEl.textContent = `¡Ahorras ${ahorro}% comparado con el pago mensual!`;
      ahorroEl.style.display = 'block';
    } else {
      ahorroEl.style.display = 'none';
    }
    document.getElementById('modal-descripcion').innerHTML =
      descripciones[tipoPlan][planSeleccionado];

    planModal.show();
    current = { tipoPlan, precio, ahorro };
  }

  botonesPlan.forEach(boton => {
    boton.addEventListener('click', function() {
      // Obtener el plan_id según el tipo de plan y frecuencia seleccionada
      const planId = planSeleccionado === 'mensual' 
        ? this.dataset.planIdMensual 
        : this.dataset.planIdAnual;
      
      // Redirigir al checkout con el plan_id
      window.location.href = `checkout.php?plan_id=${planId}`;
    });
  });

  function attachWrapperClicks() {
    const btnLogin = document.getElementById('btn-login');
    const btnRegistrar = document.getElementById('btn-registrar');

    btnLogin.addEventListener('click', () => {
      document.getElementById('form-login').style.display = 'block';
    });
    btnRegistrar.addEventListener('click', () => {
      planModal.hide();
      demoModalInst.show();
    });
  }

  function attachLoginHandler() {
    const formLogin = document.getElementById('form-login');
    const correoField = document.getElementById('loginEmail');
    const claveField = document.getElementById('loginPassword');

    formLogin.addEventListener('submit', function(e) {
      e.preventDefault();
      if (!correoField.value.trim() || !claveField.value.trim()) {
        Swal.fire({ icon: 'warning', title: 'Correo y contraseña son requeridos' });
        return;
      }

      wrapper.innerHTML = `
        <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
          <p class="mt-2">Validando credenciales…</p>
        </div>`;

      const payload = new URLSearchParams({
        correo: correoField.value.trim(),
        clave: claveField.value
      });

      fetch('landing-login.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: payload
      })
      .then(res => res.json())
      .then(res => {
        if (res.type === 'success') {
          return Swal.fire({
            icon: 'success',
            title: res.msg,
            timer: 800,
            showConfirmButton: false
          }).then(() => window.location = 'dashboard.php');
        }
        Swal.fire({ icon: res.type, title: res.msg });
        openPlanModal(current.tipoPlan, current.precio, current.ahorro);
      })
      .catch(() => {
        Swal.fire('Error','No se pudo conectar al servidor','error');
        openPlanModal(current.tipoPlan, current.precio, current.ahorro);
      });
    }, { once: true });
  }

  attachWrapperClicks();
  attachLoginHandler();
});


