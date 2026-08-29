<?php
// Simulate web environment
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['HTTP_HOST'] = 'localhost';

// Mock session
session_start();
$_SESSION['csrf_token'] = 'test_token_123';
$_SERVER['HTTP_X_CSRF_TOKEN'] = 'test_token_123';

// Mock input
$input = json_encode([
    'provider' => 'gemini',
    'api_key' => 'test_key_web_simulation'
]);

// Capture output
ob_start();

// Include the endpoint (this will execute it)
// We need to mock file_get_contents('php://input') which is hard.
// Instead, we'll instantiate the controller directly if possible, or use a different approach.
// Since the controller reads php://input, we can't easily mock it without a wrapper.
// Let's try to modify the test to use the controller class directly but mock the getJsonBody method? 
// No, BaseController reads php://input.

// Alternative: Use a stream wrapper to mock php://input
class VarStream {
    private $string;
    private $position;
    public function stream_open($path, $mode, $options, &$opened_path) {
        $this->string = $GLOBALS['mock_input'];
        $this->position = 0;
        return true;
    }
    public function stream_read($count) {
        $ret = substr($this->string, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }
    public function stream_eof() {
        return $this->position >= strlen($this->string);
    }
    public function stream_stat() {
        return [];
    }
}
stream_wrapper_unregister("php");
stream_wrapper_register("php", "VarStream");
$GLOBALS['mock_input'] = $input;

require_once __DIR__ . '/../public/api/config/update.php';

$output = ob_get_clean();
echo $output;
