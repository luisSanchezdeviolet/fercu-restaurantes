<?php
require_once 'layouts/session.php';
requireLogin();

// Verificar que sea super admin
if (!isSuperAdmin()) {
    header('Location: dashboard.php');
    exit;
}

require_once 'controllers/SaasAdminController.php';

$companyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$status = isset($_GET['status']) ? (int)$_GET['status'] : 0;

if ($companyId > 0) {
    $controller = new SaasAdminController();
    $result = $controller->toggleCompanyStatus($companyId, $status);
    
    if ($result['success']) {
        $_SESSION['success_message'] = 'Estado de la empresa actualizado correctamente';
    } else {
        $_SESSION['error_message'] = 'Error al actualizar el estado: ' . $result['error'];
    }
}

header('Location: saas-admin.php');
exit;
?>

