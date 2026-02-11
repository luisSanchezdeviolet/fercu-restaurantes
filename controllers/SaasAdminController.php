<?php
require_once 'config/database.php';

class SaasAdminController
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todas las empresas/configuraciones registradas
     */
    public function getAllCompanies($page = 1, $limit = 20, $search = '', $status = 'all')
    {
        try {
            $offset = ($page - 1) * $limit;
            
            // Construir query
            $where = "WHERE 1=1";
            $params = [];
            
            if (!empty($search)) {
                $where .= " AND (c.nombre LIKE ? OR c.correo LIKE ? OR c.telefono LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if ($status !== 'all') {
                $where .= " AND c.activo = ?";
                $params[] = ($status === 'active') ? 1 : 0;
            }
            
            // Query principal
            $query = "
                SELECT 
                    c.*,
                    u.nombre as owner_name,
                    u.email as owner_email,
                    s.plan_id,
                    s.start_date,
                    s.limit_date,
                    s.status as subscription_status,
                    p.name as plan_name,
                    p.type as plan_type,
                    (SELECT COUNT(*) FROM usuarios WHERE configuracion_id = c.id) as total_users,
                    (SELECT COUNT(*) FROM mesas WHERE configuracion_id = c.id) as total_tables,
                    (SELECT COUNT(*) FROM productos WHERE configuracion_id = c.id) as total_products,
                    (SELECT COUNT(*) FROM ordenes WHERE configuracion_id = c.id) as total_orders
                FROM configuracion c
                LEFT JOIN usuarios u ON c.id_usuario = u.id
                LEFT JOIN subscriptions s ON c.id = s.configuracion_id AND s.status = 1
                LEFT JOIN plans p ON s.plan_id = p.id
                $where
                ORDER BY c.created_at DESC
                LIMIT $limit OFFSET $offset
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Contar total
            $countQuery = "SELECT COUNT(*) as total FROM configuracion c $where";
            $countStmt = $this->conn->prepare($countQuery);
            $countStmt->execute(array_slice($params, 0, -2)); // Sin limit y offset
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            return [
                'success' => true,
                'data' => $companies,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => ceil($total / $limit)
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener estadísticas generales del SAAS
     */
    public function getDashboardStats()
    {
        try {
            // Empresas totales
            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM configuracion");
            $totalCompanies = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Empresas activas
            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM configuracion WHERE activo = 1");
            $activeCompanies = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Suscripciones activas
            $stmt = $this->conn->query("
                SELECT COUNT(*) as total 
                FROM subscriptions 
                WHERE status = 1 
                AND limit_date >= CURDATE()
            ");
            $activeSubscriptions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Suscripciones por vencer (próximos 7 días)
            $stmt = $this->conn->query("
                SELECT COUNT(*) as total 
                FROM subscriptions 
                WHERE status = 1 
                AND limit_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            ");
            $expiringSoon = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Ingresos del mes actual
            $stmt = $this->conn->query("
                SELECT COALESCE(SUM(amount), 0) as total 
                FROM payments 
                WHERE status = 'completed' 
                AND MONTH(payment_date) = MONTH(CURRENT_DATE())
                AND YEAR(payment_date) = YEAR(CURRENT_DATE())
            ");
            $monthlyRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Nuevos registros este mes
            $stmt = $this->conn->query("
                SELECT COUNT(*) as total 
                FROM configuracion 
                WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
                AND YEAR(created_at) = YEAR(CURRENT_DATE())
            ");
            $newThisMonth = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Distribución por planes
            $stmt = $this->conn->query("
                SELECT 
                    p.name,
                    COUNT(s.id) as count
                FROM subscriptions s
                JOIN plans p ON s.plan_id = p.id
                WHERE s.status = 1
                GROUP BY p.id, p.name
            ");
            $planDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => [
                    'total_companies' => $totalCompanies,
                    'active_companies' => $activeCompanies,
                    'active_subscriptions' => $activeSubscriptions,
                    'expiring_soon' => $expiringSoon,
                    'monthly_revenue' => $monthlyRevenue,
                    'new_this_month' => $newThisMonth,
                    'plan_distribution' => $planDistribution
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Activar o desactivar una empresa
     */
    public function toggleCompanyStatus($configId, $status)
    {
        try {
            $stmt = $this->conn->prepare("UPDATE configuracion SET activo = ? WHERE id = ?");
            $stmt->execute([$status, $configId]);
            
            // Log de actividad
            $this->logActivity($configId, 'status_change', "Estado cambiado a: " . ($status ? 'Activo' : 'Inactivo'));
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Obtener detalle de una empresa
     */
    public function getCompanyDetails($configId)
    {
        try {
            $query = "
                SELECT 
                    c.*,
                    u.nombre as owner_name,
                    u.email as owner_email,
                    u.ultimo_login as owner_last_login
                FROM configuracion c
                LEFT JOIN usuarios u ON c.id_usuario = u.id
                WHERE c.id = ?
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$configId]);
            $company = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$company) {
                return ['success' => false, 'error' => 'Empresa no encontrada'];
            }
            
            // Obtener suscripciones
            $subsQuery = "
                SELECT s.*, p.name as plan_name, p.type as plan_type, p.amount
                FROM subscriptions s
                JOIN plans p ON s.plan_id = p.id
                WHERE s.configuracion_id = ?
                ORDER BY s.created_at DESC
            ";
            $stmt = $this->conn->prepare($subsQuery);
            $stmt->execute([$configId]);
            $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Obtener usuarios
            $usersQuery = "SELECT id, nombre, email, rol, activo, fecha_creacion FROM usuarios WHERE configuracion_id = ?";
            $stmt = $this->conn->prepare($usersQuery);
            $stmt->execute([$configId]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => [
                    'company' => $company,
                    'subscriptions' => $subscriptions,
                    'users' => $users
                ]
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Registrar actividad
     */
    private function logActivity($configId, $action, $description)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO saas_activity_log (configuracion_id, user_id, action, description, ip_address)
            VALUES (?, ?, ?, ?, ?)
        ");
        $userId = $_SESSION['user_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $stmt->execute([$configId, $userId, $action, $description, $ip]);
    }
}
?>

