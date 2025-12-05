<?php
require_once __DIR__ . '/../src/services/ConfigService.php';

try {
    echo "Testing ConfigService...\n";
    $service = new ConfigService();
    
    echo "Current Provider: " . $service->getCurrentProvider() . "\n";
    
    echo "Attempting to save provider...\n";
    $service->setProvider('gemini');
    echo "Provider saved successfully.\n";
    
    echo "Attempting to save API key...\n";
    $service->setApiKey('gemini', 'test_key_123');
    echo "API Key saved successfully.\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
