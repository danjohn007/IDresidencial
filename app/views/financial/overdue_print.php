<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cartera Vencida</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .subtitle { color: #555; margin-bottom: 12px; font-size: 12px; }
        .meta { margin-bottom: 16px; font-size: 11px; color: #444; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #f3f4f6; text-align: left; padding: 6px 8px; font-size: 11px; text-transform: uppercase; border: 1px solid #ddd; }
        td { padding: 5px 8px; border: 1px solid #ddd; vertical-align: top; }
        tr:nth-child(even) td { background: #f9fafb; }
        .badge-vencido  { color: #b91c1c; font-weight: bold; }
        .badge-pendiente { color: #92400e; }
        .days-overdue { color: #dc2626; font-weight: 600; }
        .total-row { background: #fef2f2; font-weight: bold; }
        .footer { margin-top: 24px; font-size: 10px; color: #888; }
        @media print {
            @page { margin: 1.5cm; }
            button { display: none !important; }
        }
    </style>
</head>
<body>
    <div style="margin-bottom:16px; display:flex; justify-content:space-between; align-items:flex-start;">
        <div>
            <h1>💳 Cartera Vencida</h1>
            <p class="subtitle">Cuotas pendientes y vencidas</p>
        </div>
        <button onclick="window.print()" style="padding:8px 18px;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px;">🖨 Imprimir / Guardar PDF</button>
    </div>

    <div class="meta">
        <strong>Período:</strong>
        <?php echo htmlspecialchars($date_from); ?> — <?php echo htmlspecialchars($date_to); ?>
        <?php if (!empty($search)): ?>
        &nbsp;|&nbsp; <strong>Búsqueda:</strong> <?php echo htmlspecialchars($search); ?>
        <?php endif; ?>
        &nbsp;|&nbsp; <strong>Generado:</strong> <?php echo date('d/m/Y H:i'); ?>
        &nbsp;|&nbsp; <strong>Total:</strong> $<?php echo number_format($totalAmount, 2); ?>
        (<?php echo count($records); ?> registros)
    </div>

    <table>
        <thead>
            <tr>
                <th>Propiedad</th>
                <th>Residente</th>
                <th>Teléfono</th>
                <th>Período</th>
                <th style="text-align:right">Monto</th>
                <th>Vencimiento</th>
                <th>Días Vencido</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($records)): ?>
            <tr><td colspan="8" style="text-align:center;color:#888;padding:16px;">No se encontraron registros</td></tr>
            <?php else: ?>
            <?php foreach ($records as $record): ?>
            <tr>
                <td><?php echo htmlspecialchars($record['property_number']); ?></td>
                <td><?php echo htmlspecialchars($record['resident_name'] ?? 'Sin asignar'); ?></td>
                <td><?php echo htmlspecialchars($record['resident_phone'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($record['period']); ?></td>
                <td style="text-align:right">$<?php echo number_format($record['amount'], 2); ?></td>
                <td><?php echo date('d/m/Y', strtotime($record['due_date'])); ?></td>
                <td class="days-overdue"><?php echo max(0, intval($record['days_overdue'])); ?> días</td>
                <td class="<?php echo $record['status'] === 'overdue' ? 'badge-vencido' : 'badge-pendiente'; ?>">
                    <?php echo $record['status'] === 'overdue' ? 'Vencido' : 'Pendiente'; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="4" style="text-align:right">Total:</td>
                <td style="text-align:right">$<?php echo number_format($totalAmount, 2); ?></td>
                <td colspan="3"></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <p class="footer">Generado el <?php echo date('d/m/Y H:i:s'); ?></p>
</body>
</html>
