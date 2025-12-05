<?php
require_once __DIR__ . '/../src/services/ConfigService.php';
require_once __DIR__ . '/../src/services/AIServiceFactory.php';

try {
    echo "Testing AI Connection...\n";
    
    $config = new ConfigService();
    $provider = $config->getCurrentProvider();
    $key = $config->getApiKey($provider);
    
    echo "Provider: $provider\n";
    echo "Key found: " . ($key ? "YES (starts with " . substr($key, 0, 4) . "...)" : "NO") . "\n";
    
    if (!$key) {
        die("Cannot test without API key.\n");
    }
    
    $model = $config->getModel($provider);
    echo "Model: $model\n";
    
    $ai = AIServiceFactory::create($provider, $key, $model);
    
    echo "Sending test message: 'Hello, are you online?'\n";
    $response = $ai->chat("Hello, are you online?");
    
    echo "Response:\n";
    echo $response['reply'] . "\n";
    echo "Usage: " . json_encode($response['usage']) . "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
