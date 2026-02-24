<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Pago - <?php echo htmlspecialchars($fee['period']); ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 13px; color: #333; background: #fff; }
        .receipt { max-width: 680px; margin: 20px auto; padding: 30px; border: 1px solid #ddd; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 16px; margin-bottom: 20px; }
        .header h1 { font-size: 22px; color: #2563eb; }
        .header p { color: #555; font-size: 13px; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: bold; background: #d1fae5; color: #065f46; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 24px; margin-bottom: 20px; }
        .info-item dt { font-size: 11px; color: #6b7280; text-transform: uppercase; margin-bottom: 2px; }
        .info-item dd { font-size: 14px; color: #111; font-weight: 500; }
        .amount-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px; text-align: center; margin-bottom: 20px; }
        .amount-box .label { font-size: 12px; color: #6b7280; text-transform: uppercase; }
        .amount-box .value { font-size: 32px; font-weight: bold; color: #16a34a; }
        .footer { margin-top: 30px; padding-top: 16px; border-top: 1px solid #e5e7eb; text-align: center; color: #9ca3af; font-size: 11px; }
        .evidence-section { margin-top: 16px; }
        .evidence-section img { max-width: 300px; border: 1px solid #e5e7eb; border-radius: 4px; margin-top: 8px; }
        @media print {
            body { background: white; }
            .no-print { display: none; }
            .receipt { border: none; max-width: 100%; margin: 0; padding: 20px; }
        }
    </style>
</head>
<body>
<div class="receipt">
    <div class="header">
        <h1><?php echo htmlspecialchars($residencialName); ?></h1>
        <p>Recibo de Pago de Cuota de Mantenimiento</p>
        <p style="margin-top:6px;color:#9ca3af;">Folio: #<?php echo str_pad($fee['id'], 6, '0', STR_PAD_LEFT); ?></p>
    </div>

    <div style="text-align:center;margin-bottom:16px;">
        <span class="badge">✓ PAGADO</span>
    </div>

    <div class="amount-box">
        <div class="label">Monto Pagado</div>
        <div class="value">$<?php echo number_format($fee['amount'], 2); ?></div>
        <div style="color:#555;font-size:12px;margin-top:4px;">Periodo: <?php echo htmlspecialchars($fee['period']); ?></div>
    </div>

    <div class="info-grid">
        <div class="info-item">
            <dt>Propiedad</dt>
            <dd><?php echo htmlspecialchars($fee['property_number']); ?><?php if ($fee['section']): ?> - <?php echo htmlspecialchars($fee['section']); ?><?php endif; ?></dd>
        </div>
        <div class="info-item">
            <dt>Residente</dt>
            <dd><?php echo htmlspecialchars(trim($fee['first_name'] . ' ' . $fee['last_name'])) ?: '-'; ?></dd>
        </div>
        <?php if ($fee['phone']): ?>
        <div class="info-item">
            <dt>Teléfono</dt>
            <dd><?php echo htmlspecialchars($fee['phone']); ?></dd>
        </div>
        <?php endif; ?>
        <?php if (!empty($fee['email'])): ?>
        <div class="info-item">
            <dt>Correo</dt>
            <dd><?php echo htmlspecialchars($fee['email']); ?></dd>
        </div>
        <?php endif; ?>
        <div class="info-item">
            <dt>Fecha de Vencimiento</dt>
            <dd><?php echo date('d/m/Y', strtotime($fee['due_date'])); ?></dd>
        </div>
        <div class="info-item">
            <dt>Fecha de Pago</dt>
            <dd><?php echo $fee['paid_date'] ? date('d/m/Y', strtotime($fee['paid_date'])) : '-'; ?></dd>
        </div>
        <div class="info-item">
            <dt>Método de Pago</dt>
            <dd style="text-transform:capitalize;"><?php echo $fee['payment_method'] ? htmlspecialchars($fee['payment_method']) : '-'; ?></dd>
        </div>
        <div class="info-item">
            <dt>Referencia</dt>
            <dd><?php echo $fee['payment_reference'] ? htmlspecialchars($fee['payment_reference']) : '-'; ?></dd>
        </div>
        <?php if (!empty($fee['movement_description'])): ?>
        <div class="info-item" style="grid-column:span 2;">
            <dt>Descripción</dt>
            <dd><?php echo htmlspecialchars($fee['movement_description']); ?></dd>
        </div>
        <?php endif; ?>
        <?php if ($fee['notes']): ?>
        <div class="info-item" style="grid-column:span 2;">
            <dt>Notas</dt>
            <dd><?php echo nl2br(htmlspecialchars($fee['notes'])); ?></dd>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($fee['payment_confirmation']): ?>
    <?php $ext = strtolower(pathinfo($fee['payment_confirmation'], PATHINFO_EXTENSION)); ?>
    <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
    <div class="evidence-section">
        <dt style="font-size:11px;color:#6b7280;text-transform:uppercase;">Evidencia de Pago</dt>
        <img src="<?php echo BASE_URL . '/public/' . htmlspecialchars($fee['payment_confirmation']); ?>" alt="Evidencia">
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <div class="footer">
        <p>Este documento es un comprobante de pago generado el <?php echo date('d/m/Y H:i'); ?></p>
        <p><?php echo htmlspecialchars($residencialName); ?></p>
    </div>
</div>

<div class="no-print" style="text-align:center;margin:16px;">
    <button onclick="window.print()" style="padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:14px;">
        🖨️ Imprimir
    </button>
    &nbsp;
    <button onclick="window.close()" style="padding:8px 20px;background:#6b7280;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:14px;">
        Cerrar
    </button>
</div>

<script>
// Auto print on load
// window.onload = function() { window.print(); };
</script>
</body>
</html>
