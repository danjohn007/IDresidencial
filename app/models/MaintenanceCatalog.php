<?php
/**
 * Modelo del Catálogo de Incidencias Fijas (mantenimiento recurrente)
 */

class MaintenanceCatalog {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ------------------------------------------------------------------
    // CRUD
    // ------------------------------------------------------------------

    public function getAll($activeOnly = false) {
        $where = $activeOnly ? 'WHERE active = 1' : '';
        $stmt = $this->db->query(
            "SELECT * FROM maintenance_catalog $where ORDER BY next_due ASC, id DESC"
        );
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM maintenance_catalog WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $nextDue = $this->calculateNextDue(null, $data['interval_value'], $data['interval_unit']);

        $stmt = $this->db->prepare("
            INSERT INTO maintenance_catalog
                (title, description, category, location, priority, interval_value, interval_unit, next_due, active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");

        return $stmt->execute([
            $data['title'],
            $data['description'] ?? null,
            $data['category'],
            $data['location'] ?? null,
            $data['priority'] ?? 'media',
            (int) $data['interval_value'],
            $data['interval_unit'],
            $nextDue,
        ]);
    }

    public function update($id, $data) {
        // Recalculate next_due only when interval fields change
        $current = $this->findById($id);
        if (!$current) return false;

        $intervalChanged = (
            (int)$data['interval_value'] !== (int)$current['interval_value'] ||
            $data['interval_unit'] !== $current['interval_unit']
        );

        $nextDue = $intervalChanged
            ? $this->calculateNextDue($current['last_generated'], $data['interval_value'], $data['interval_unit'])
            : $current['next_due'];

        $stmt = $this->db->prepare("
            UPDATE maintenance_catalog
            SET title          = ?,
                description    = ?,
                category       = ?,
                location       = ?,
                priority       = ?,
                interval_value = ?,
                interval_unit  = ?,
                next_due       = ?,
                active         = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['title'],
            $data['description'] ?? null,
            $data['category'],
            $data['location'] ?? null,
            $data['priority'] ?? 'media',
            (int) $data['interval_value'],
            $data['interval_unit'],
            $nextDue,
            isset($data['active']) ? (int)$data['active'] : (int)$current['active'],
            $id,
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM maintenance_catalog WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ------------------------------------------------------------------
    // Report generation
    // ------------------------------------------------------------------

    /**
     * Returns catalog items whose next_due date is today or in the past and that are active.
     */
    public function getDueItems() {
        $stmt = $this->db->prepare("
            SELECT * FROM maintenance_catalog
            WHERE active = 1
              AND next_due IS NOT NULL
              AND next_due <= CURDATE()
            ORDER BY next_due ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Generate a maintenance_report for a catalog item and update its scheduling.
     * Returns the ID of the newly created report or false on error.
     */
    public function generateReport($catalogItem) {
        $db = $this->db;

        $db->beginTransaction();
        try {
            // Insert the maintenance report
            $stmt = $db->prepare("
                INSERT INTO maintenance_reports
                    (resident_id, property_id, category, title, description, priority, location, status)
                VALUES (NULL, NULL, ?, ?, ?, ?, ?, 'pendiente')
            ");
            $stmt->execute([
                $catalogItem['category'],
                $catalogItem['title'],
                $catalogItem['description'] ?? '',
                $catalogItem['priority'],
                $catalogItem['location'] ?? null,
            ]);
            $reportId = $db->lastInsertId();

            // Update last_generated and next_due on the catalog entry
            $today   = date('Y-m-d');
            $nextDue = $this->calculateNextDue($today, $catalogItem['interval_value'], $catalogItem['interval_unit']);

            $upd = $db->prepare("
                UPDATE maintenance_catalog
                SET last_generated = ?, next_due = ?
                WHERE id = ?
            ");
            $upd->execute([$today, $nextDue, $catalogItem['id']]);

            $db->commit();
            return (int) $reportId;
        } catch (Exception $e) {
            $db->rollBack();
            return false;
        }
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Calculate the next due date starting from $baseDate (or today).
     *
     * @param string|null $baseDate  Y-m-d or null (uses today)
     * @param int         $value     Numeric interval (e.g. 6)
     * @param string      $unit      'dias' | 'meses' | 'anios'
     * @return string  Y-m-d
     */
    public function calculateNextDue($baseDate, $value, $unit) {
        $base = $baseDate ? new DateTime($baseDate) : new DateTime();
        $value = max(1, (int) $value);

        switch ($unit) {
            case 'dias':
                $base->modify("+{$value} days");
                break;
            case 'anios':
                $base->modify("+{$value} years");
                break;
            default: // 'meses'
                $base->modify("+{$value} months");
                break;
        }

        return $base->format('Y-m-d');
    }

    /**
     * Human-readable interval label.
     */
    public static function intervalLabel($value, $unit) {
        $labels = [
            'dias'  => ['día', 'días'],
            'meses' => ['mes', 'meses'],
            'anios' => ['año', 'años'],
        ];
        $pair = $labels[$unit] ?? [$unit, $unit];
        $word = (int)$value === 1 ? $pair[0] : $pair[1];
        return "Cada {$value} {$word}";
    }
}
