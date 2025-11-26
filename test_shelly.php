<?php
/**
 * Test de diagnóstico para Shelly Cloud API
 * Usar: http://tu-dominio.com/test_shelly.php
 */

// Configuración de tu dispositivo
$authToken = 'MzgwNjRhdWlk0574CFA7E6D9F34D8F306EB51648C8DA5D79A03333414C2FBF51CFA88A780F9867246CE317003A74';
$deviceId = '34987A67DA6C';
$cloudServer = 'shelly-208-eu.shelly.cloud';
$outputChannel = 0;

echo "<h1>🔧 Test de Diagnóstico Shelly Cloud API</h1>";
echo "<style>body{font-family:monospace;background:#f5f5f5;padding:20px} .success{background:#d4edda;padding:10px;margin:10px 0;border-left:4px solid #28a745} .error{background:#f8d7da;padding:10px;margin:10px 0;border-left:4px solid #dc3545} .info{background:#d1ecf1;padding:10px;margin:10px 0;border-left:4px solid #0dcaf0} pre{background:#fff;padding:15px;border:1px solid #ddd;overflow-x:auto}</style>";

echo "<div class='info'><strong>Configuración:</strong><br>";
echo "Device ID: {$deviceId}<br>";
echo "Cloud Server: {$cloudServer}<br>";
echo "Canal: {$outputChannel}<br>";
echo "Token: " . substr($authToken, 0, 20) . "...<br>";
echo "</div>";

// Test 1: URL con canal en el path
echo "<h2>Test 1: Canal en la URL (/device/relay/{channel}/control)</h2>";
$url1 = "https://{$cloudServer}/device/relay/{$outputChannel}/control";
$params1 = [
    'auth_key' => $authToken,
    'id' => $deviceId,
    'turn' => 'on'
];
$fullUrl1 = $url1 . '?' . http_build_query($params1);
echo "<div class='info'><strong>URL:</strong><br><pre>{$fullUrl1}</pre></div>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $fullUrl1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$response1 = curl_exec($ch);
$httpCode1 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError1 = curl_error($ch);
curl_close($ch);

if ($curlError1) {
    echo "<div class='error'><strong>Error cURL:</strong> {$curlError1}</div>";
} else {
    echo "<div class='" . ($httpCode1 == 200 ? 'success' : 'error') . "'>";
    echo "<strong>HTTP Code:</strong> {$httpCode1}<br>";
    echo "<strong>Respuesta:</strong><pre>" . htmlspecialchars($response1) . "</pre>";
    echo "</div>";
}

// Test 2: URL sin canal, solo parámetros
echo "<h2>Test 2: Canal como parámetro (/device/relay/control?channel=X)</h2>";
$url2 = "https://{$cloudServer}/device/relay/control";
$params2 = [
    'auth_key' => $authToken,
    'id' => $deviceId,
    'channel' => $outputChannel,
    'turn' => 'on'
];
$fullUrl2 = $url2 . '?' . http_build_query($params2);
echo "<div class='info'><strong>URL:</strong><br><pre>{$fullUrl2}</pre></div>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $fullUrl2);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$response2 = curl_exec($ch);
$httpCode2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError2 = curl_error($ch);
curl_close($ch);

if ($curlError2) {
    echo "<div class='error'><strong>Error cURL:</strong> {$curlError2}</div>";
} else {
    echo "<div class='" . ($httpCode2 == 200 ? 'success' : 'error') . "'>";
    echo "<strong>HTTP Code:</strong> {$httpCode2}<br>";
    echo "<strong>Respuesta:</strong><pre>" . htmlspecialchars($response2) . "</pre>";
    echo "</div>";
}

// Test 3: Sin especificar canal
echo "<h2>Test 3: Sin canal (/device/relay/control)</h2>";
$url3 = "https://{$cloudServer}/device/relay/control";
$params3 = [
    'auth_key' => $authToken,
    'id' => $deviceId,
    'turn' => 'on'
];
$fullUrl3 = $url3 . '?' . http_build_query($params3);
echo "<div class='info'><strong>URL:</strong><br><pre>{$fullUrl3}</pre></div>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $fullUrl3);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$response3 = curl_exec($ch);
$httpCode3 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError3 = curl_error($ch);
curl_close($ch);

if ($curlError3) {
    echo "<div class='error'><strong>Error cURL:</strong> {$curlError3}</div>";
} else {
    echo "<div class='" . ($httpCode3 == 200 ? 'success' : 'error') . "'>";
    echo "<strong>HTTP Code:</strong> {$httpCode3}<br>";
    echo "<strong>Respuesta:</strong><pre>" . htmlspecialchars($response3) . "</pre>";
    echo "</div>";
}

// Test 4: Obtener info del dispositivo
echo "<h2>Test 4: Obtener información del dispositivo (/device/status)</h2>";
$url4 = "https://{$cloudServer}/device/status";
$params4 = [
    'auth_key' => $authToken,
    'id' => $deviceId
];
$fullUrl4 = $url4 . '?' . http_build_query($params4);
echo "<div class='info'><strong>URL:</strong><br><pre>{$fullUrl4}</pre></div>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $fullUrl4);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$response4 = curl_exec($ch);
$httpCode4 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError4 = curl_error($ch);
curl_close($ch);

if ($curlError4) {
    echo "<div class='error'><strong>Error cURL:</strong> {$curlError4}</div>";
} else {
    echo "<div class='" . ($httpCode4 == 200 ? 'success' : 'error') . "'>";
    echo "<strong>HTTP Code:</strong> {$httpCode4}<br>";
    echo "<strong>Respuesta:</strong><pre>" . htmlspecialchars($response4) . "</pre>";
    echo "</div>";
}

// Test 5: POST method con JSON-RPC (Gen2)
echo "<h2>Test 5: ✨ POST con JSON-RPC para Shelly Gen2/Pro</h2>";
$url5 = "https://{$cloudServer}/device/relay/control";
$postData5 = [
    'channel' => $outputChannel,
    'turn' => 'on',
    'id' => $deviceId,
    'auth_key' => $authToken
];
echo "<div class='info'><strong>URL:</strong><br><pre>{$url5}</pre>";
echo "<strong>POST Data:</strong><br><pre>" . json_encode($postData5, JSON_PRETTY_PRINT) . "</pre></div>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url5);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData5));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$response5 = curl_exec($ch);
$httpCode5 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError5 = curl_error($ch);
curl_close($ch);

if ($curlError5) {
    echo "<div class='error'><strong>Error cURL:</strong> {$curlError5}</div>";
} else {
    echo "<div class='" . ($httpCode5 == 200 ? 'success' : 'error') . "'>";
    echo "<strong>HTTP Code:</strong> {$httpCode5}<br>";
    echo "<strong>Respuesta:</strong><pre>" . htmlspecialchars($response5) . "</pre>";
    echo "</div>";
    
    $data5 = json_decode($response5, true);
    if ($data5 && isset($data5['isok']) && $data5['isok']) {
        echo "<div class='success'><strong>🎉 ¡ÉXITO! Este es el endpoint correcto.</strong></div>";
    }
}

// Test 6: Método Switch.Set para Gen2
echo "<h2>Test 6: ✨ Switch.Set method (Gen2 RPC)</h2>";
$url6 = "https://{$cloudServer}/device/relay/control";
$postData6 = [
    'id' => $deviceId,
    'auth_key' => $authToken,
    'channel' => $outputChannel,
    'turn' => 'on'
];
echo "<div class='info'><strong>URL:</strong><br><pre>{$url6}</pre>";
echo "<strong>POST Data:</strong><br><pre>" . json_encode($postData6, JSON_PRETTY_PRINT) . "</pre></div>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url6);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData6));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response6 = curl_exec($ch);
$httpCode6 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError6 = curl_error($ch);
curl_close($ch);

if ($curlError6) {
    echo "<div class='error'><strong>Error cURL:</strong> {$curlError6}</div>";
} else {
    echo "<div class='" . ($httpCode6 == 200 ? 'success' : 'error') . "'>";
    echo "<strong>HTTP Code:</strong> {$httpCode6}<br>";
    echo "<strong>Respuesta:</strong><pre>" . htmlspecialchars($response6) . "</pre>";
    echo "</div>";
    
    $data6 = json_decode($response6, true);
    if ($data6 && isset($data6['isok']) && $data6['isok']) {
        echo "<div class='success'><strong>🎉 ¡ÉXITO! Este es el endpoint correcto.</strong></div>";
    }
}

// Test 7: JSON-RPC en puerto 6022
echo "<h2>Test 7: 🎯 JSON-RPC en puerto 6022 (original)</h2>";
$url7 = "https://{$cloudServer}:6022/jrpc";
$rpcData = [
    'id' => 1,
    'method' => 'Switch.Set',
    'params' => [
        'id' => $outputChannel,
        'on' => true
    ]
];
$fullUrl7 = $url7 . "?auth_key=" . urlencode($authToken) . "&device_id=" . urlencode($deviceId);
echo "<div class='info'><strong>URL:</strong><br><pre>{$fullUrl7}</pre>";
echo "<strong>JSON-RPC Payload:</strong><br><pre>" . json_encode($rpcData, JSON_PRETTY_PRINT) . "</pre></div>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $fullUrl7);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($rpcData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response7 = curl_exec($ch);
$httpCode7 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError7 = curl_error($ch);
curl_close($ch);

if ($curlError7) {
    echo "<div class='error'><strong>Error cURL:</strong> {$curlError7}</div>";
} else {
    echo "<div class='" . ($httpCode7 == 200 ? 'success' : 'error') . "'>";
    echo "<strong>HTTP Code:</strong> {$httpCode7}<br>";
    echo "<strong>Respuesta:</strong><pre>" . htmlspecialchars($response7) . "</pre>";
    echo "</div>";
    
    $data7 = json_decode($response7, true);
    if ($data7 && !isset($data7['error'])) {
        echo "<div class='success'><strong>🎉 ¡ÉXITO! JSON-RPC funciona.</strong></div>";
    }
}

echo "<hr><p><strong>Instrucciones:</strong> Busca cuál test muestra 'isok: true' o un resultado exitoso sin errores. Ese es el formato correcto que debemos usar.</p>";
?>
