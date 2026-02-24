<?php
/**
 * Modelo de Movimientos Financieros
 */

class Financial {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtener todos los movimientos con filtros
     */
    public function getMovements($filters = []) {
        $where = [];
        $params = [];
        
        if (!empty($filters['transaction_type'])) {
            $where[] = "fm.transaction_type = ?";
            $params[] = $filters['transaction_type'];
        }
        
        if (!empty($filters['movement_type_id'])) {
            $where[] = "fm.movement_type_id = ?";
            $params[] = $filters['movement_type_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "fm.transaction_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "fm.transaction_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['property_id'])) {
            $where[] = "fm.property_id = ?";
            $params[] = $filters['property_id'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $stmt = $this->db->prepare("
            SELECT fm.*, fm.is_unforeseen, fm.evidence_file,
                   fmt.name as movement_type_name,
                   fmt.category,
                   p.property_number,
                   r.id as resident_id,
                   CONCAT(u_resident.first_name, ' ', u_resident.last_name) as resident_name,
                   CONCAT(u_created.first_name, ' ', u_created.last_name) as created_by_name
            FROM financial_movements fm
            INNER JOIN financial_movement_types fmt ON fm.movement_type_id = fmt.id
            LEFT JOIN properties p ON fm.property_id = p.id
            LEFT JOIN residents r ON fm.resident_id = r.id
            LEFT JOIN users u_resident ON r.user_id = u_resident.id
            LEFT JOIN users u_created ON fm.created_by = u_created.id
            $whereClause
            ORDER BY fm.transaction_date DESC, fm.created_at DESC
        ");
        
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener un movimiento por ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT fm.*, fm.is_unforeseen, fm.evidence_file,
                   fmt.name as movement_type_name,
                   fmt.category,
                   p.property_number,
                   CONCAT(u_created.first_name, ' ', u_created.last_name) as created_by_name
            FROM financial_movements fm
            INNER JOIN financial_movement_types fmt ON fm.movement_type_id = fmt.id
            LEFT JOIN properties p ON fm.property_id = p.id
            LEFT JOIN users u_created ON fm.created_by = u_created.id
            WHERE fm.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Crear un nuevo movimiento
     */
    public function create($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO financial_movements 
                (movement_type_id, transaction_type, amount, description, reference_type, 
                 reference_id, property_id, resident_id, payment_method, payment_reference, 
                 transaction_date, created_by, notes, is_unforeseen, evidence_file)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $data['movement_type_id'],
                $data['transaction_type'],
                $data['amount'],
                $data['description'],
                $data['reference_type'] ?? null,
                $data['reference_id'] ?? null,
                $data['property_id'] ?? null,
                $data['resident_id'] ?? null,
                $data['payment_method'] ?? null,
                $data['payment_reference'] ?? null,
                $data['transaction_date'],
                $data['created_by'],
                $data['notes'] ?? null,
                $data['is_unforeseen'] ?? 0,
                $data['evidence_file'] ?? null
            ]);
            
            if ($result) {
                // Log audit
                if (class_exists('AuditController')) {
                    AuditController::log('create', 'Movimiento financiero creado', 'financial_movements', $this->db->lastInsertId());
                }
                return $this->db->lastInsertId();
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error creating financial movement: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar un movimiento
     */
    public function update($id, $data) {
        try {
            $stmt = $this->db->prepare("
                UPDATE financial_movements 
                SET movement_type_id = ?,
                    transaction_type = ?,
                    amount = ?,
                    description = ?,
                    property_id = ?,
                    resident_id = ?,
                    payment_method = ?,
                    payment_reference = ?,
                    transaction_date = ?,
                    notes = ?,
                    is_unforeseen = ?,
                    evidence_file = ?
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                $data['movement_type_id'],
                $data['transaction_type'],
                $data['amount'],
                $data['description'],
                $data['property_id'] ?? null,
                $data['resident_id'] ?? null,
                $data['payment_method'] ?? null,
                $data['payment_reference'] ?? null,
                $data['transaction_date'],
                $data['notes'] ?? null,
                $data['is_unforeseen'] ?? 0,
                $data['evidence_file'] ?? null,
                $id
            ]);
            
            if ($result && class_exists('AuditController')) {
                AuditController::log('update', 'Movimiento financiero actualizado', 'financial_movements', $id);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error updating financial movement: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar un movimiento
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM financial_movements WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result && class_exists('AuditController')) {
                AuditController::log('delete', 'Movimiento financiero eliminado', 'financial_movements', $id);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error deleting financial movement: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener tipos de movimiento
     */
    public function getMovementTypes($category = null) {
        $where = "WHERE is_active = 1";
        $params = [];
        
        if ($category) {
            $where .= " AND (category = ? OR category = 'ambos')";
            $params[] = $category;
        }
        
        $stmt = $this->db->prepare("
            SELECT * FROM financial_movement_types 
            $where
            ORDER BY name
        ");
        
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener estadísticas financieras
     */
    public function getStats($dateFrom = null, $dateTo = null) {
        // Default: últimos 12 meses
        if (!$dateFrom) {
            $dateFrom = date('Y-m-d', strtotime('-12 months'));
        }
        if (!$dateTo) {
            $dateTo = date('Y-m-d');
        }
        
        // Total ingresos
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(amount), 0) as total
            FROM financial_movements
            WHERE transaction_type = 'ingreso'
            AND transaction_date BETWEEN ? AND ?
        ");
        $stmt->execute([$dateFrom, $dateTo]);
        $totalIngresos = $stmt->fetch()['total'];
        
        // Total egresos
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(amount), 0) as total
            FROM financial_movements
            WHERE transaction_type = 'egreso'
            AND transaction_date BETWEEN ? AND ?
        ");
        $stmt->execute([$dateFrom, $dateTo]);
        $totalEgresos = $stmt->fetch()['total'];
        
        // Balance
        $balance = $totalIngresos - $totalEgresos;
        
        // Movimientos por mes
        $stmt = $this->db->prepare("
            SELECT 
                DATE_FORMAT(transaction_date, '%Y-%m') as month,
                transaction_type,
                SUM(amount) as total
            FROM financial_movements
            WHERE transaction_date BETWEEN ? AND ?
            GROUP BY month, transaction_type
            ORDER BY month
        ");
        $stmt->execute([$dateFrom, $dateTo]);
        $movementsByMonth = $stmt->fetchAll();
        
        // Movimientos por tipo
        $stmt = $this->db->prepare("
            SELECT 
                fmt.name,
                fm.transaction_type,
                COUNT(*) as count,
                SUM(fm.amount) as total
            FROM financial_movements fm
            INNER JOIN financial_movement_types fmt ON fm.movement_type_id = fmt.id
            WHERE fm.transaction_date BETWEEN ? AND ?
            GROUP BY fmt.id, fm.transaction_type
            ORDER BY total DESC
        ");
        $stmt->execute([$dateFrom, $dateTo]);
        $movementsByType = $stmt->fetchAll();
        
        return [
            'total_ingresos' => $totalIngresos,
            'total_egresos' => $totalEgresos,
            'balance' => $balance,
            'movements_by_month' => $movementsByMonth,
            'movements_by_type' => $movementsByType,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];
    }
}
