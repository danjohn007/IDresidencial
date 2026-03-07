<?php
/**
 * Controlador de Reglas de Penalización
 */

require_once APP_PATH . '/controllers/AuditController.php';

class PenaltyRulesController extends Controller {

    private $db;

    public function __construct() {
        $this->requireAuth();
        $this->requireRole(['superadmin', 'administrador']);
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Vista principal: listar y configurar regla activa
     */
    public function index() {
        $data = [
            'title' => 'Reglas de Penalización',
            'rule' => null,
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cutDayType = $this->post('cut_day_type', 'first');
            $cutDay = intval($this->post('cut_day', 1));
            $graceDays = min(15, max(0, intval($this->post('grace_days', 0))));

            $afterType = $this->post('after_cutday_type', 'percentage');
            $afterValue = floatval($this->post('after_cutday_value', 0));
            $nextMonthType = $this->post('next_month_type', 'percentage');
            $nextMonthValue = floatval($this->post('next_month_value', 0));
            $secondMonthType = $this->post('second_month_type', 'percentage');
            $secondMonthValue = floatval($this->post('second_month_value', 0));

            if ($cutDayType === 'custom' && ($cutDay < 1 || $cutDay > 28)) {
                $data['error'] = 'El día de corte personalizado debe estar entre 1 y 28';
            } else {
                // Desactivar regla anterior
                $this->db->exec("UPDATE penalty_rules SET is_active = 0");

                $stmt = $this->db->prepare("
                    INSERT INTO penalty_rules
                        (cut_day_type, cut_day, grace_days,
                         after_cutday_type, after_cutday_value,
                         next_month_type, next_month_value,
                         second_month_type, second_month_value,
                         is_active, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)
                ");
                $stmt->execute([
                    $cutDayType, $cutDay, $graceDays,
                    $afterType, $afterValue,
                    $nextMonthType, $nextMonthValue,
                    $secondMonthType, $secondMonthValue,
                    $_SESSION['user_id']
                ]);

                AuditController::log('create', 'Regla de penalización configurada', 'penalty_rules', $this->db->lastInsertId());
                $_SESSION['success_message'] = 'Regla de penalización guardada exitosamente';
                $this->redirect('penaltyRules');
                return;
            }
        }

        // Obtener regla activa
        $stmt = $this->db->query("SELECT * FROM penalty_rules WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
        $data['rule'] = $stmt->fetch();

        $this->view('penalty_rules/index', $data);
    }
}
