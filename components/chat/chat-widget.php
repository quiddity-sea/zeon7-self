<?php
/**
 * Chat Widget Component
 *
 * Renders the interactive agent chat interface.
 */
function renderChatWidget(AgentContextService $agentCtx): void
{
    $name = htmlspecialchars($agentCtx->getDisplayName());
    $accent = htmlspecialchars($agentCtx->getThemeAccent());
    ?>
    <div id="agent-chat" class="chat-widget" style="--agent-accent: <?= $accent ?>">
        <div class="agent-header">
            <span class="agent-name"><?= $name ?></span>
            <span class="agent-status" id="agent-connection-status">●</span>
        </div>
        <div class="chat-messages" id="chat-messages"></div>
        <div class="chat-input-area">
            <textarea id="chat-input" placeholder="Message <?= $name ?>..." rows="1"></textarea>
            <button id="chat-send" class="btn-send">↵</button>
        </div>
    </div>
    <?php
}
