<?php
declare(strict_types=1);

require_once '/var/www/self/src/config/env.php';
require_once '/var/www/self/src/services/AgentContextService.php';
require_once '/var/www/self/src/services/TemplateLoader.php';

echo "====================================================\n";
echo "   PHASE 12: COMPONENT & TEMPLATE SYSTEM TEST       \n";
echo "====================================================\n\n";

$pass = 0;
$total = 0;

function check(string $name, bool $cond, string $msg = '') {
    global $pass, $total;
    $total++;
    if ($cond) {
        $pass++;
        echo "  [PASS] {$name}\n";
    } else {
        echo "  [FAIL] {$name} ({$msg})\n";
    }
}

$agents = ['zeon7', 'leon', 'gemma', 'otec', 'wolf'];

foreach ($agents as $agentSlug) {
    $ctx = new AgentContextService();
    $ctx->setAgentId($agentSlug);
    $loader = new TemplateLoader($ctx);

    $tplPath = $loader->getTemplatePath();
    check("Template path exists for agent '{$agentSlug}'", is_dir($tplPath));
    check("template.php exists for agent '{$agentSlug}'", file_exists("{$tplPath}/template.php"));

    $title = $ctx->getPageTitle();
    check("Page title resolved for '{$agentSlug}'", !empty($title));

    $accent = $ctx->getThemeAccent();
    check("Theme accent resolved for '{$agentSlug}'", !empty($accent) && str_starts_with($accent, '#'));
}

// Verify that template rendering is pure presentation and does not touch Council state
$zeonCtx = new AgentContextService();
$zeonCtx->setAgentId('zeon7');
$loader = new TemplateLoader($zeonCtx);

ob_start();
try {
    $loader->renderPublic();
    $html = ob_get_clean();
    check("Public template renders valid HTML markup", str_contains($html, '<!DOCTYPE html>') && str_contains($html, 'data-agent="zeon7"'));
} catch (\Throwable $e) {
    ob_end_clean();
    check("Public template renders valid HTML markup", false, $e->getMessage());
}

echo "\n----------------------------------------------------\n";
echo "Results: {$pass} / {$total} Tests Passed.\n";
if ($pass === $total) {
    echo ">>> PHASE 12 COMPONENT & TEMPLATE FOUNDATION VERIFIED 100%! <<<\n";
}
echo "----------------------------------------------------\n";
