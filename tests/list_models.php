<?php
require_once __DIR__ . '/../src/services/ConfigService.php';

$config = new ConfigService();
$key = $config->getApiKey('gemini');

$url = "https://generativelanguage.googleapis.com/v1beta/models?key=$key";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (isset($data['models'])) {
    echo "Available Models:\n";
    foreach ($data['models'] as $model) {
        // Just print the name, which includes 'models/' prefix usually
        echo $model['name'] . "\n";
    }
} else {
    echo "Error listing models: " . $response . "\n";
}
