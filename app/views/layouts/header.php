<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'ERP Residencial'; ?> - <?php echo SITE_NAME; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        <?php
        // Get theme color from database
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'theme_color'");
        $themeRow = $stmt->fetch();
        $themeColor = $themeRow ? $themeRow['setting_value'] : 'blue';
        
        // Define color values for each theme
        $themeColors = [
            'blue' => ['rgb' => '59, 130, 246', 'hex' => '#3b82f6', 'hover' => '#2563eb'],
            'green' => ['rgb' => '34, 197, 94', 'hex' => '#22c55e', 'hover' => '#16a34a'],
            'purple' => ['rgb' => '168, 85, 247', 'hex' => '#a855f7', 'hover' => '#9333ea'],
            'red' => ['rgb' => '239, 68, 68', 'hex' => '#ef4444', 'hover' => '#dc2626'],
            'orange' => ['rgb' => '249, 115, 22', 'hex' => '#f97316', 'hover' => '#ea580c'],
            'indigo' => ['rgb' => '99, 102, 241', 'hex' => '#6366f1', 'hover' => '#4f46e5']
        ];
        
        $currentColor = $themeColors[$themeColor];
        ?>
        
        :root {
            --theme-color: <?php echo $currentColor['hex']; ?>;
            --theme-color-hover: <?php echo $currentColor['hover']; ?>;
            --theme-color-rgb: <?php echo $currentColor['rgb']; ?>;
        }
        
        .sidebar-item:hover {
            background-color: rgba(<?php echo $currentColor['rgb']; ?>, 0.1);
        }
        .sidebar-item.active {
            background-color: rgba(<?php echo $currentColor['rgb']; ?>, 0.2);
            border-left: 4px solid <?php echo $currentColor['hex']; ?>;
        }
        
        /* Apply theme color to primary buttons and elements */
        .btn-primary {
            background-color: <?php echo $currentColor['hex']; ?> !important;
        }
        .btn-primary:hover {
            background-color: <?php echo $currentColor['hover']; ?> !important;
        }
        .text-primary {
            color: <?php echo $currentColor['hex']; ?> !important;
        }
        .bg-primary {
            background-color: <?php echo $currentColor['hex']; ?> !important;
        }
        .border-primary {
            border-color: <?php echo $currentColor['hex']; ?> !important;
        }
    </style>
</head>
<body class="bg-gray-50">
