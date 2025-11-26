<?php
/**
 * Verificar estado actual del switch y probar activación
 */

$authToken = 'MzgwNjRhdWlk0574CFA7E6D9F34D8F306EB51648C8DA5D79A03333414C2FBF51CFA88A780F9867246CE317003A74';
$deviceId = '34987A67DA6C';
$cloudServer = 'shelly-208-eu.shelly.cloud';
$channel = 0;

// Limpiar y sanitizar el servidor
$cloudServer = trim($cloudServer);
$cloudServer = preg_replace('/:\d+.*$/', '', $cloudServer);
$cloudServer = filter_var($cloudServer, FILTER_SANITIZE_URL);

echo "<div class='info'><strong>Servidor limpio:</strong> {$cloudServer}</div>";

echo "<h1>🔍 Diagnóstico de Estado Shelly</h1>";
echo "<style>body{font-family:monospace;background:#f5f5f5;padding:20px} .success{background:#d4edda;padding:10px;margin:10px 0;border-left:4px solid #28a745} .error{background:#f8d7da;padding:10px;margin:10px 0;border-left:4px solid #dc3545} .info{background:#d1ecf1;padding:10px;margin:10px 0;border-left:4px solid #0dcaf0} pre{background:#fff;padding:15px;border:1px solid #ddd;overflow-x:auto} .warning{background:#fff3cd;padding:10px;margin:10px 0;border-left:4px solid #ffc107}</style>";

// 1. Obtener estado actual del dispositivo
echo "<h2>1️⃣ Estado Actual del Dispositivo</h2>";
$statusUrl = "https://{$cloudServer}/device/status?auth_key=" . urlencode($authToken) . "&id=" . urlencode($deviceId);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $statusUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$statusResponse = curl_exec($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$statusData = json_decode($statusResponse, true);

if ($statusData && isset($statusData['isok']) && $statusData['isok']) {
    $deviceStatus = $statusData['data']['device_status'];
    
    echo "<div class='success'><strong>✅ Dispositivo Online</strong></div>";
    echo "<div class='info'>";
    echo "<strong>Información del Dispositivo:</strong><br>";
    echo "Modelo: " . ($deviceStatus['code'] ?? 'N/A') . "<br>";
    echo "MAC: " . ($deviceStatus['sys']['mac'] ?? 'N/A') . "<br>";
    echo "IP: " . ($deviceStatus['eth']['ip'] ?? 'N/A') . "<br>";
    echo "</div>";
    
    // Mostrar estado de cada switch
    echo "<h3>Estado de Switches:</h3>";
    for ($i = 0; $i <= 3; $i++) {
        if (isset($deviceStatus["switch:{$i}"])) {
            $switch = $deviceStatus["switch:{$i}"];
            $output = $switch['output'] ? '🟢 ON' : '🔴 OFF';
            $source = $switch['source'] ?? 'N/A';
            
            echo "<div class='" . ($switch['output'] ? 'success' : 'warning') . "'>";
            echo "<strong>Switch {$i}:</strong> {$output} | Source: {$source}<br>";
            echo "Voltage: {$switch['voltage']}V | Current: {$switch['current']}A | Power: {$switch['apower']}W<br>";
            echo "Temperature: {$switch['temperature']['tC']}°C";
            echo "</div>";
        }
    }
} else {
    echo "<div class='error'>❌ Error al obtener estado: " . htmlspecialchars($statusResponse) . "</div>";
}

// 2. Intentar activar el switch
echo "<h2>2️⃣ Intentar Activar Switch {$channel}</h2>";
$activateUrl = "https://{$cloudServer}/device/relay/control";
$postData = [
    'channel' => $channel,
    'turn' => 'on',
    'id' => $deviceId,
    'auth_key' => $authToken
];

echo "<div class='info'>";
echo "<strong>URL:</strong> {$activateUrl}<br>";
echo "<strong>POST Data:</strong><br><pre>" . json_encode($postData, JSON_PRETTY_PRINT) . "</pre>";
echo "</div>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $activateUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$activateResponse = curl_exec($ch);
$activateCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$activateData = json_decode($activateResponse, true);

if ($activateData && isset($activateData['isok']) && $activateData['isok']) {
    echo "<div class='success'>";
    echo "<strong>✅ Comando Enviado Exitosamente</strong><br>";
    echo "HTTP Code: {$activateCode}<br>";
    echo "Respuesta: <pre>" . htmlspecialchars($activateResponse) . "</pre>";
    echo "</div>";
    
    // Esperar 2 segundos y verificar estado
    echo "<div class='info'>⏳ Esperando 2 segundos...</div>";
    sleep(2);
    
    // 3. Verificar que el switch cambió de estado
    echo "<h2>3️⃣ Verificar Cambio de Estado</h2>";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $statusUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $statusResponse2 = curl_exec($ch);
    curl_close($ch);
    
    $statusData2 = json_decode($statusResponse2, true);
    
    if ($statusData2 && isset($statusData2['isok']) && $statusData2['isok']) {
        $switch = $statusData2['data']['device_status']["switch:{$channel}"];
        $output = $switch['output'];
        
        if ($output) {
            echo "<div class='success'><strong>🎉 ¡ÉXITO! El switch {$channel} está ENCENDIDO</strong></div>";
        } else {
            echo "<div class='warning'><strong>⚠️ ADVERTENCIA: El comando se envió pero el switch {$channel} sigue APAGADO</strong></div>";
            echo "<div class='info'>";
            echo "<strong>Posibles causas:</strong><br>";
            echo "1. El switch está configurado para controlarse localmente (source != 'SHC')<br>";
            echo "2. Hay una configuración de bloqueo en el dispositivo<br>";
            echo "3. El canal físico no corresponde con el canal lógico<br>";
            echo "4. El switch tiene un delay configurado<br>";
            echo "</div>";
        }
        
        echo "<div class='info'>";
        echo "<strong>Estado actual Switch {$channel}:</strong><br>";
        echo "Output: " . ($output ? '🟢 ON' : '🔴 OFF') . "<br>";
        echo "Source: " . ($switch['source'] ?? 'N/A') . "<br>";
        echo "</div>";
    }
} else {
    echo "<div class='error'>";
    echo "<strong>❌ Error al Activar</strong><br>";
    echo "HTTP Code: {$activateCode}<br>";
    echo "Respuesta: <pre>" . htmlspecialchars($activateResponse) . "</pre>";
    echo "</div>";
}

// 4. Recomendaciones
echo "<h2>4️⃣ Recomendaciones</h2>";
echo "<div class='info'>";
echo "<strong>Si el comando se envía correctamente pero el relay no activa físicamente:</strong><br><br>";
echo "1. <strong>Verifica el canal correcto:</strong> Tu dispositivo tiene 4 switches (0, 1, 2, 3). Asegúrate de usar el canal correcto.<br><br>";
echo "2. <strong>Verifica el cableado:</strong> El relay debe estar conectado correctamente a la carga (motor, cerradura, etc.)<br><br>";
echo "3. <strong>Verifica el tipo de salida:</strong> Algunos Shelly Pro tienen salidas de tipo 'potential free' que requieren configuración especial.<br><br>";
echo "4. <strong>Verifica en la app Shelly:</strong> Intenta activar manualmente desde la app oficial. Si funciona ahí pero no desde el API, puede haber configuración de seguridad.<br><br>";
echo "5. <strong>Verifica el 'source':</strong> Si dice 'init' en lugar de 'SHC' (Shelly Cloud), significa que no está configurado para control remoto.<br><br>";
echo "</div>";
?>
