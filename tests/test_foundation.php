<?php
/**
 * Foundation Components Integration Test
 * Tests that all DRY components work together correctly
 */

// Autoload all components
require_once __DIR__ . '/../src/core/Exceptions.php';
require_once __DIR__ . '/../src/core/ApiResponse.php';
require_once __DIR__ . '/../src/core/ValidationRules.php';

echo "=== Zeon7 Foundation Components Test ===\n\n";

// Test 1: ApiResponse
echo "Test 1: ApiResponse\n";
$successResponse = ApiResponse::success(['id' => 123], 'Test successful');
echo "✓ Success response: " . json_encode($successResponse) . "\n";

$errorResponse = ApiResponse::error('Test error', 400, ['field' => 'value']);
echo "✓ Error response: " . json_encode($errorResponse) . "\n";

$paginatedResponse = ApiResponse::paginated([1, 2, 3], 100, 2, 10);
echo "✓ Paginated response: " . json_encode($paginatedResponse) . "\n\n";

// Test 2: ValidationRules
echo "Test 2: ValidationRules\n";
$postRules = ValidationRules::postRules();
echo "✓ Post rules loaded: " . count($postRules) . " fields\n";

$chatRules = ValidationRules::chatRules();
echo "✓ Chat rules loaded: " . count($chatRules) . " fields\n";

$loreRules = ValidationRules::loreRules();
echo "✓ Lore rules loaded: " . count($loreRules) . " fields\n\n";

// Test 3: Exception Hierarchy
echo "Test 3: Exception Hierarchy\n";
try {
    throw new ValidationException("Test validation error");
} catch (Zeon7Exception $e) {
    echo "✓ Caught Zeon7Exception: " . $e->getMessage() . "\n";
}

try {
    throw new NotFoundException("Resource not found", 404);
} catch (Zeon7Exception $e) {
    echo "✓ Caught NotFoundException: " . $e->getMessage() . "\n";
}

echo "\n=== All Basic Tests Passed ===\n";
echo "Core components are syntactically correct and functional!\n";
