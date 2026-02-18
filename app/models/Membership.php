<?php
/**
 * Modelo de Membresías
 */

class Membership {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtener todos los planes de membresía
     */
    public function getPlans($activeOnly = true) {
        $where = $activeOnly ? "WHERE is_active = 1" : "";
        $stmt = $this->db->query("SELECT * FROM membership_plans $where ORDER BY monthly_cost");
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener un plan por ID
     */
    public function getPlanById($id) {
        $stmt = $this->db->prepare("SELECT * FROM membership_plans WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtener todas las membresías
     */
    public function getAll($filters = []) {
        $where = [];
        $params = [];
        
        if (!empty($filters['status'])) {
            $where[] = "m.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['resident_id'])) {
            $where[] = "m.resident_id = ?";
            $params[] = $filters['resident_id'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $stmt = $this->db->prepare("
            SELECT m.*, 
                   mp.name as plan_name,
                   mp.monthly_cost,
                   CONCAT(u.first_name, ' ', u.last_name) as resident_name,
                   p.property_number
            FROM memberships m
            INNER JOIN membership_plans mp ON m.membership_plan_id = mp.id
            INNER JOIN residents r ON m.resident_id = r.id
            INNER JOIN users u ON r.user_id = u.id
            INNER JOIN properties p ON r.property_id = p.id
            $whereClause
            ORDER BY m.created_at DESC
        ");
        
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener membresía por ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT m.*, 
                   mp.name as plan_name,
                   mp.monthly_cost,
                   mp.benefits,
                   CONCAT(u.first_name, ' ', u.last_name) as resident_name,
                   p.property_number
            FROM memberships m
            INNER JOIN membership_plans mp ON m.membership_plan_id = mp.id
            INNER JOIN residents r ON m.resident_id = r.id
            INNER JOIN users u ON r.user_id = u.id
            INNER JOIN properties p ON r.property_id = p.id
            WHERE m.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Crear nueva membresía
     */
    public function create($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO memberships 
                (resident_id, membership_plan_id, start_date, end_date, status, payment_day, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $data['resident_id'],
                $data['membership_plan_id'],
                $data['start_date'],
                $data['end_date'] ?? null,
                $data['status'] ?? 'active',
                $data['payment_day'] ?? 1,
                $data['notes'] ?? null
            ]);
            
            if ($result && class_exists('AuditController')) {
                AuditController::log('create', 'Membresía creada', 'memberships', $this->db->lastInsertId());
            }
            
            return $result ? $this->db->lastInsertId() : false;
        } catch (Exception $e) {
            error_log("Error creating membership: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar membresía
     */
    public function update($id, $data) {
        try {
            $stmt = $this->db->prepare("
                UPDATE memberships 
                SET membership_plan_id = ?,
                    end_date = ?,
                    status = ?,
                    payment_day = ?,
                    notes = ?
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                $data['membership_plan_id'],
                $data['end_date'] ?? null,
                $data['status'],
                $data['payment_day'],
                $data['notes'] ?? null,
                $id
            ]);
            
            if ($result && class_exists('AuditController')) {
                AuditController::log('update', 'Membresía actualizada', 'memberships', $id);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error updating membership: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener estadísticas de membresías
     */
    public function getStats() {
        $stats = [];
        
        // Total de membresías activas
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM memberships WHERE status = 'active'");
        $stats['active'] = $stmt->fetch()['total'];
        
        // Total de membresías por plan
        $stmt = $this->db->query("
            SELECT mp.name, COUNT(*) as total
            FROM memberships m
            INNER JOIN membership_plans mp ON m.membership_plan_id = mp.id
            WHERE m.status = 'active'
            GROUP BY mp.id
        ");
        $stats['by_plan'] = $stmt->fetchAll();
        
        // Ingresos mensuales estimados
        $stmt = $this->db->query("
            SELECT SUM(mp.monthly_cost) as total
            FROM memberships m
            INNER JOIN membership_plans mp ON m.membership_plan_id = mp.id
            WHERE m.status = 'active'
        ");
        $stats['monthly_income'] = $stmt->fetch()['total'] ?? 0;
        
        return $stats;
    }
    
    /**
     * Obtener pagos de una membresía
     */
    public function getPayments($membershipId) {
        $stmt = $this->db->prepare("
            SELECT * FROM membership_payments
            WHERE membership_id = ?
            ORDER BY period DESC
        ");
        $stmt->execute([$membershipId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Crear un nuevo plan de membresía
     */
    public function createPlan($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO membership_plans 
                (name, description, monthly_cost, benefits, is_active)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $benefits = is_array($data['benefits']) ? json_encode($data['benefits']) : $data['benefits'];
            
            $result = $stmt->execute([
                $data['name'],
                $data['description'] ?? null,
                $data['monthly_cost'],
                $benefits,
                $data['is_active'] ?? 1
            ]);
            
            if ($result && class_exists('AuditController')) {
                AuditController::log('create', 'Plan de membresía creado: ' . $data['name'], 'membership_plans', $this->db->lastInsertId());
            }
            
            return $result ? $this->db->lastInsertId() : false;
        } catch (Exception $e) {
            error_log("Error creating membership plan: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar un plan de membresía
     */
    public function updatePlan($id, $data) {
        try {
            $stmt = $this->db->prepare("
                UPDATE membership_plans 
                SET name = ?,
                    description = ?,
                    monthly_cost = ?,
                    benefits = ?,
                    is_active = ?
                WHERE id = ?
            ");
            
            $benefits = is_array($data['benefits']) ? json_encode($data['benefits']) : $data['benefits'];
            
            $result = $stmt->execute([
                $data['name'],
                $data['description'] ?? null,
                $data['monthly_cost'],
                $benefits,
                $data['is_active'] ?? 1,
                $id
            ]);
            
            if ($result && class_exists('AuditController')) {
                AuditController::log('update', 'Plan de membresía actualizado: ' . $data['name'], 'membership_plans', $id);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error updating membership plan: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar un plan de membresía
     */
    public function deletePlan($id) {
        try {
            // Check if plan has active memberships
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total FROM memberships 
                WHERE membership_plan_id = ? AND status = 'active'
            ");
            $stmt->execute([$id]);
            $count = $stmt->fetch()['total'];
            
            if ($count > 0) {
                return false; // Cannot delete plan with active memberships
            }
            
            $stmt = $this->db->prepare("DELETE FROM membership_plans WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result && class_exists('AuditController')) {
                AuditController::log('delete', 'Plan de membresía eliminado', 'membership_plans', $id);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error deleting membership plan: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cambiar estado de un plan de membresía
     */
    public function togglePlanStatus($id) {
        try {
            $plan = $this->getPlanById($id);
            if (!$plan) {
                return false;
            }
            
            $newStatus = $plan['is_active'] ? 0 : 1;
            
            $stmt = $this->db->prepare("UPDATE membership_plans SET is_active = ? WHERE id = ?");
            $result = $stmt->execute([$newStatus, $id]);
            
            if ($result && class_exists('AuditController')) {
                $status = $newStatus ? 'activo' : 'inactivo';
                AuditController::log('update', 'Estado del plan de membresía cambiado a: ' . $status, 'membership_plans', $id);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error toggling membership plan status: " . $e->getMessage());
            return false;
        }
    }
}
