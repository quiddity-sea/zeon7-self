<?php
require_once __DIR__ . '/../src/middleware/AuthMiddleware.php';
AuthMiddleware::enforcePageAuth();
?>
<!DOCTYPE html>
<html lang="en" class="hud-scanline">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zeon7 Operator Matrix — User Management</title>
    <link rel="stylesheet" href="../css/zeon7-theme.css?v=14.0">
    <style>
        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .form-group {
            margin-bottom: 1.15rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 0.75rem;
            font-family: var(--font-mono);
            letter-spacing: 0.08em;
            color: var(--text-secondary);
            text-transform: uppercase;
        }
        .ip-telemetry-box {
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(34, 211, 238, 0.2);
            border-radius: 4px;
            padding: 0.85rem;
            max-height: 180px;
            overflow-y: auto;
            margin-top: 0.5rem;
        }
        .ip-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.35rem 0.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-family: var(--font-mono);
            font-size: 0.8rem;
        }
        .ip-item:last-child {
            border-bottom: none;
        }
        .ip-tag {
            color: var(--color-cyan);
            font-weight: bold;
        }
        .ip-time {
            color: var(--text-muted);
            font-size: 0.7rem;
            margin-left: 0.5rem;
        }
        .btn-remove-ip {
            background: none;
            border: 1px solid rgba(244, 63, 94, 0.4);
            color: var(--color-coral);
            border-radius: 3px;
            padding: 1px 6px;
            font-size: 0.7rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-remove-ip:hover {
            background: rgba(244, 63, 94, 0.2);
            border-color: var(--color-coral);
        }
        .toggle-switch {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            cursor: pointer;
            user-select: none;
        }
    </style>
</head>
<body>
<div class="app-wrapper">
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <?php
        $pageTitle = 'OPERATOR MATRIX';
        $pageSubtitle = 'IDENTITY REGISTRY & ACCESS CONTROL';
        include 'components/header.php';
        ?>
        
        <div class="dashboard-container">
            
            <div class="hud-border" data-tilt>
                <div class="hud-corner-tr"></div>
                <div class="hud-corner-bl"></div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <div>
                        <h2>OPERATOR ROSTER</h2>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">
                            Authorised identity profiles, cryptographic access tiers, and telemetry logs.
                        </p>
                    </div>
                    <button id="addUserBtn" class="btn btn-green">+ REGISTER NEW OPERATOR</button>
                </div>

                <div class="table-container">
                    <table class="hud-table">
                        <thead>
                            <tr>
                                <th style="width: 18%;">OPERATOR</th>
                                <th style="width: 20%;">IDENTITY & LOCATION</th>
                                <th style="width: 22%;">COMMS / EMAIL</th>
                                <th style="width: 12%;">ACCESS TIER</th>
                                <th style="width: 14%;">LAST RECORDED IP</th>
                                <th style="width: 14%; text-align: right;">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody id="userList">
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 2.5rem; color: var(--text-muted); font-family: var(--font-mono);">
                                    QUERYING OPERATOR DATABASE...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Pop-In HUD Modal for User Create/Edit -->
        <div id="userModal" class="modal-overlay">
            <div class="modal hud-border" style="max-width: 600px;">
                <div class="hud-corner-tr"></div>
                <div class="hud-corner-bl"></div>
                
                <div class="modal-header">
                    <h3 id="modalTitle" class="text-cyan">REGISTER OPERATOR</h3>
                    <button id="modalCloseX" class="close-btn">&times;</button>
                </div>
                
                <form id="userForm">
                    <input type="hidden" id="userId">
                    
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Username / Handle <span style="color:var(--color-coral);">*</span></label>
                            <input type="text" id="userUsername" class="input-box" placeholder="e.g. Neo_01" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" id="userEmail" class="input-box" placeholder="operator@domain.com">
                        </div>
                    </div>
                    
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" id="userFirstName" class="input-box" placeholder="First Name">
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" id="userLastName" class="input-box" placeholder="Last Name">
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Location / Station</label>
                            <input type="text" id="userLocation" class="input-box" placeholder="e.g. London, UK">
                        </div>
                        <div class="form-group">
                            <label id="passwordLabel">Password <span style="color:var(--color-coral);">*</span></label>
                            <input type="password" id="userPassword" class="input-box" placeholder="••••••••" autocomplete="new-password">
                            <small id="passwordHelp" style="display:none; color:var(--text-muted); font-size:0.7rem;">Leave empty to retain existing password</small>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="toggle-switch">
                            <input type="checkbox" id="userIsPrime" style="accent-color: var(--color-cyan); width: 16px; height: 16px;">
                            <span style="font-family:var(--font-mono); font-size:0.8rem; color:var(--text-primary);">Grant Prime Operator Privileges</span>
                        </label>
                    </div>

                    <!-- IP Telemetry Management Section (Visible during edit mode) -->
                    <div id="ipTelemetrySection" style="display: none; margin-bottom: 1.5rem; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <label style="font-size:0.75rem; font-family:var(--font-mono); text-transform:uppercase; color:var(--color-cyan); margin:0;">
                                🛰 Recorded IP Telemetry History
                            </label>
                            <button type="button" id="clearAllIpsBtn" class="btn-remove-ip" style="padding:2px 8px;">Purge All IPs</button>
                        </div>
                        <div id="ipListContainer" class="ip-telemetry-box">
                            <!-- IP entries dynamically injected -->
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
                        <button type="button" id="modalCancelBtn" class="btn btn-secondary">CANCEL</button>
                        <button type="submit" id="modalSubmitBtn" class="btn btn-primary">SAVE OPERATOR</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script src="js/app.js"></script>
<script src="js/users.js"></script>
<script>
    App.requireAuth();
</script>
</body>
</html>
