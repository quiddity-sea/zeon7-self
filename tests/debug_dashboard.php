<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../src/Services/ConfigService.php';
require_once __DIR__ . '/../src/Services/DashboardService.php';

echo "Instantiating DashboardService...\n";
try {
    $service = new DashboardService();
    echo "Service instantiated.\n";

    echo "Calling getDailyTheme()...\n";
    $theme = $service->getDailyTheme();
    echo "Result:\n";
    print_r($theme);

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
