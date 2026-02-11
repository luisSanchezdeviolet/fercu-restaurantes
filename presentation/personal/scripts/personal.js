const form = document.querySelector('#form-empleado');
const emailEmpleado = document.querySelector('#email');
const passwordEmpleado = document.querySelector('#password');
const rolEmpleado = document.querySelector('#role');
const alerta = document.querySelector('#alerta-empleado');

let currentPage = 1;
let recordsPerPage = 10;
let allEmployees = [];
let filteredEmployees = [];

document.addEventListener('DOMContentLoaded', function () {
    loadEmployeeData();
    createEmployee();
    toggleModalUpdate();
    toggleModalDelete();
    setupSearchAndFilters();
});

function setupSearchAndFilters() {
    const searchInput = document.getElementById('search-input');
    const roleFilter = document.getElementById('role-filter');
    const recordsSelect = document.getElementById('records-per-page');

    searchInput.addEventListener('input', function() {
        currentPage = 1;
        filterAndDisplayEmployees();
    });

    roleFilter.addEventListener('change', function() {
        currentPage = 1;
        filterAndDisplayEmployees();
    });

    recordsSelect.addEventListener('change', function() {
        recordsPerPage = parseInt(this.value);
        currentPage = 1;
        filterAndDisplayEmployees();
    });
}

function loadEmployeeData() {
    fetch('presentation/personal/findAll.php')
    .then(response => response.json())
    .then(data => {
        if (data.length === 0 || data.error) {
            allEmployees = [];
            filteredEmployees = [];
            displayEmployees([]);
            return;
        }

        allEmployees = data;
        filterAndDisplayEmployees();
    })
    .catch(error => {
        const tbody = document.getElementById('empleados-body');
        tbody.innerHTML = `<tr><td colspan="4">Error al cargar los datos.</td></tr>`;
        console.error('Error:', error);
    });
}

function filterAndDisplayEmployees() {
    const searchTerm = document.getElementById('search-input').value.toLowerCase();
    const roleFilter = document.getElementById('role-filter').value;

    filteredEmployees = allEmployees.filter(employee => {
        const matchesSearch = employee.nombre.toLowerCase().includes(searchTerm) || 
                            employee.email.toLowerCase().includes(searchTerm);
        const matchesRole = roleFilter === '' || employee.rol === roleFilter;
        
        return matchesSearch && matchesRole;
    });

    const totalPages = Math.ceil(filteredEmployees.length / recordsPerPage);
    const startIndex = (currentPage - 1) * recordsPerPage;
    const endIndex = startIndex + recordsPerPage;
    const employeesToShow = filteredEmployees.slice(startIndex, endIndex);

    displayEmployees(employeesToShow);
    
    updatePaginationControls(totalPages);
    
    updatePaginationInfo();
}

function displayEmployees(employees) {
    const tbody = document.getElementById('empleados-body');
    tbody.innerHTML = '';

    if (employees.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4">No se encontraron empleados.</td></tr>';
        return;
    }

    employees.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${row.nombre}</td>
            <td>${row.email}</td>
            <td>${row.rol}</td>
            <td class="d-md-flex flex-row justify-content-md-between gap-2">
                <button class="btn btn-sm btn-primary btn-edit w-100 mb-2 mb-md-0" data-id="${row.id}">Editar</button>
                <button class="btn btn-sm btn-danger btn-delete w-100" data-id="${row.id}">Eliminar</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function updatePaginationControls(totalPages) {
    const paginationControls = document.getElementById('pagination-controls');
    paginationControls.innerHTML = '';

    if (totalPages <= 1) {
        return;
    }

    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = `
        <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">
            <i class="fas fa-chevron-left"></i> Anterior
        </a>
    `;
    paginationControls.appendChild(prevLi);

    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);

    if (startPage > 1) {
        const firstLi = document.createElement('li');
        firstLi.className = 'page-item';
        firstLi.innerHTML = `<a class="page-link" href="#" onclick="changePage(1)">1</a>`;
        paginationControls.appendChild(firstLi);

        if (startPage > 2) {
            const dotsLi = document.createElement('li');
            dotsLi.className = 'page-item disabled';
            dotsLi.innerHTML = `<span class="page-link">...</span>`;
            paginationControls.appendChild(dotsLi);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === currentPage ? 'active' : ''}`;
        li.innerHTML = `<a class="page-link" href="#" onclick="changePage(${i})">${i}</a>`;
        paginationControls.appendChild(li);
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            const dotsLi = document.createElement('li');
            dotsLi.className = 'page-item disabled';
            dotsLi.innerHTML = `<span class="page-link">...</span>`;
            paginationControls.appendChild(dotsLi);
        }

        const lastLi = document.createElement('li');
        lastLi.className = 'page-item';
        lastLi.innerHTML = `<a class="page-link" href="#" onclick="changePage(${totalPages})">${totalPages}</a>`;
        paginationControls.appendChild(lastLi);
    }

    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    nextLi.innerHTML = `
        <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">
            Siguiente <i class="fas fa-chevron-right"></i>
        </a>
    `;
    paginationControls.appendChild(nextLi);
}

function updatePaginationInfo() {
    const paginationInfo = document.getElementById('pagination-info');
    const totalRecords = filteredEmployees.length;
    const startRecord = totalRecords === 0 ? 0 : (currentPage - 1) * recordsPerPage + 1;
    const endRecord = Math.min(currentPage * recordsPerPage, totalRecords);

    paginationInfo.innerHTML = `
        Mostrando ${startRecord} a ${endRecord} de ${totalRecords} empleados
        ${allEmployees.length !== filteredEmployees.length ? `(filtrado de ${allEmployees.length} registros totales)` : ''}
    `;
}

function changePage(page) {
    const totalPages = Math.ceil(filteredEmployees.length / recordsPerPage);
    
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        filterAndDisplayEmployees();
    }
}

function createEmployee() {
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!emailEmpleado.value || !passwordEmpleado.value || !rolEmpleado.value) {
            alerta.innerHTML = `<div 
            class="alert alert-danger col-12 col-md-8 col-lg-6 mx-auto text-center"
            role="alert">
                <i class="fas fa-exclamation-triangle"></i> Por favor, completa todos los campos.
            </div>`;
            setTimeout(() => {
                alerta.innerHTML = '';
            }, 2000);
            return;
        }

        const formData = new FormData(form);

        fetch('presentation/personal/create.php', {
            method: 'POST',
            body: formData
        })
        .then(async response => {
            let data;
            try {
                data = await response.json();
            } catch (e) {
                throw new Error('Respuesta del servidor no es JSON válido');
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                loadEmployeeData();
                form.reset();
                alerta.innerHTML = `<div 
                class="alert alert-success col-12 col-md-8 col-lg-6 mx-auto text-center"
                role="alert">
                    <i class="fas fa-check-circle"></i> Empleado creado correctamente.
                </div>`;
                setTimeout(() => {
                    alerta.innerHTML = '';
                }, 2000);
            } else {
                alerta.innerHTML = `<div 
                class="alert alert-danger col-12 col-md-8 col-lg-6 mx-auto text-center"
                role="alert">
                    <i class="fas fa-exclamation-triangle"></i> Error al crear el empleado: ${data.message || 'Error desconocido'}.
                </div>`;
                setTimeout(() => {
                    alerta.innerHTML = '';
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alerta.innerHTML = `<div 
            class="alert alert-danger col-12 col-md-8 col-lg-6 mx-auto text-center"
            role="alert">
                <i class="fas fa-exclamation-triangle"></i> Error al crear el empleado: ${error.message || 'Error desconocido'}.
            </div>`;
            setTimeout(() => {
                alerta.innerHTML = '';
            }, 2000);
        });
    });
}

function toggleModalUpdate() {
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('btn-edit')) {
            const empleadoId = e.target.getAttribute('data-id');
            loadModalAndShowEdit(empleadoId);
        }
    });
}

function loadModalAndShowEdit(empleadoId) {
    const existingModal = document.getElementById('editPersonalModal');
    if (existingModal) {
        loadEmployeeDataForEdit(empleadoId);
        const modal = new bootstrap.Modal(existingModal);
        modal.show();
        setupSaveEmployeeEvent();
        return;
    }

    fetch('components/personal/ModalUpdate.php')
    .then(response => response.text())
    .then(html => {
        document.body.insertAdjacentHTML('beforeend', html);
        loadEmployeeDataForEdit(empleadoId);
        const modal = new bootstrap.Modal(document.getElementById('editPersonalModal'));
        modal.show();
        setupSaveEmployeeEvent();
    })
    .catch(error => {
        console.error('Error al cargar el modal:', error);
        alert('Error al cargar el formulario de edición');
    });
}

function loadEmployeeDataForEdit(empleadoId) {
    fetch(`presentation/personal/findById.php?id=${empleadoId}`)
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Error al cargar los datos del empleado');
            return;
        }
        
        document.getElementById('edit_id').value = data.id;
        document.getElementById('edit_name').value = data.nombre;
        document.getElementById('edit_email').value = data.email;
        document.getElementById('edit_role').value = data.rol;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cargar los datos del empleado');
    });
}

function setupSaveEmployeeEvent() {
    const existingBtn = document.getElementById('editForm');
    if (existingBtn) {
        existingBtn.removeEventListener('submit', handleFormSubmit);
        existingBtn.addEventListener('submit', handleFormSubmit);
    }
}

function handleFormSubmit(e) {
    e.preventDefault();
    const contenedorForm = document.getElementById('editMessage');
    const alerta = document.createElement('div');
    const form = document.getElementById('editForm');
    const formData = new FormData(form);
    fetch('presentation/personal/update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('editPersonalModal'));
            loadEmployeeData();
            const existingAlert = contenedorForm.querySelector('.alert');
            if (existingAlert) existingAlert.remove();
            contenedorForm.classList.remove('d-none');
            alerta.classList.add('alert', 'alert-success', 'd-flex', 'justify-content-center');
            alerta.innerHTML = 'Empleado actualizado correctamente';
            contenedorForm.appendChild(alerta);
            setTimeout(() => {
                alerta.remove();
                modal.hide();
            }, 2000);
        } else {
            const existingAlert = contenedorForm.querySelector('.alert');
            if (existingAlert) existingAlert.remove();
            contenedorForm.classList.remove('d-none');
            alerta.classList.add('alert', 'alert-danger', 'd-flex', 'justify-content-center');
            alerta.innerHTML = `Error al actualizar el empleado: ${data.message || 'Error desconocido'}`;
            contenedorForm.appendChild(alerta);
            setTimeout(() => {
                alerta.remove();
            }, 2000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar el empleado');
    });
}

function toggleModalDelete() {
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('btn-delete')) {
            const empleadoId = e.target.getAttribute('data-id');
            const existingModal = document.getElementById('deleteModalPersonal');
            if (existingModal) {
                loadDataForDelete(empleadoId);
                const modal = new bootstrap.Modal(existingModal);
                modal.show();
                setupDeleteEmployeeEvent();
                return;
            }

            fetch('components/personal/ModalDelete.php')
            .then(response => response.text())
            .then(html => {
                document.body.insertAdjacentHTML('beforeend', html);
                loadDataForDelete(empleadoId);
                const modal = new bootstrap.Modal(document.getElementById('deleteModalPersonal'));
                modal.show();
                setupDeleteEmployeeEvent();
            })
            .catch(error => {
                console.error('Error al cargar el modal:', error);
                alert('Error al cargar el formulario de edición');
            });
        }
    });
}

function loadDataForDelete(empleadoId) {
    fetch(`presentation/personal/findById.php?id=${empleadoId}`)
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Error al cargar los datos del empleado');
            return;
        }

        document.getElementById('delete_id').value = data.id;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cargar los datos del empleado');
    });
}

function setupDeleteEmployeeEvent() {
    const existingBtn = document.getElementById('deleteForm');
    if (existingBtn) {
        existingBtn.removeEventListener('submit', loadModalAndShowDelete);
        existingBtn.addEventListener('submit', loadModalAndShowDelete);
    }
}

function loadModalAndShowDelete(e) {
    e.preventDefault();
    const contenedorForm = document.getElementById('deleteMessage');
    const alerta = document.createElement('div');
    const form = document.getElementById('deleteForm');
    const formData = new FormData(form);
    fetch('presentation/personal/delete.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModalPersonal'));
            loadEmployeeData();
            const existingAlert = contenedorForm.querySelector('.alert');
            if (existingAlert) existingAlert.remove();
            contenedorForm.classList.remove('d-none');
            alerta.classList.add('alert', 'alert-success', 'd-flex', 'justify-content-center');
            alerta.innerHTML = 'Empleado eliminado correctamente';
            contenedorForm.appendChild(alerta);
            setTimeout(() => {
                alerta.remove();
                modal.hide();
            }, 2000);
        } else {
            const existingAlert = contenedorForm.querySelector('.alert');
            if (existingAlert) existingAlert.remove();
            contenedorForm.classList.remove('d-none');
            alerta.classList.add('alert', 'alert-danger', 'd-flex', 'justify-content-center');
            alerta.innerHTML = `Error al eliminar el empleado: ${data.message || 'Error desconocido'}`;
            contenedorForm.appendChild(alerta);
            setTimeout(() => {
                alerta.remove();
            }, 2000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar el empleado');
    });
}