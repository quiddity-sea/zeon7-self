<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Zeon7 Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Maven+Pro:wght@400;500;600&family=Montserrat:wght@400;600;800;900&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/zeon7-theme.css?v=3">
    <link rel="stylesheet" href="css/components/sidebar.css">
    <link rel="stylesheet" href="css/components/header-row.css">
    <link rel="stylesheet" href="css/components/terminal.css?v=1">
    <style>
        .settings-container { 
            padding: 3rem; 
            max-width: 100%; /* Updated to 100% */
            margin: 0 auto; /* Center container */
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        /* Form Card */
        .settings-card {
            background: rgba(11, 18, 25, 0.6);
            border: 1px solid var(--border-hairline);
            padding: 3rem;
            border-radius: 4px;
            position: relative;
            height: 100%; /* Match height */
        }
        
        /* Terminal Card */
        .terminal-card {
            background: rgba(11, 18, 25, 0.9);
            /* border: 1px solid var(--cyan-dim); Removed as requested */
            padding: 3rem; /* Updated to 3rem */
            border-radius: 4px;
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
            padding-bottom: 3rem; /* Updated to 3rem */
        }

        .terminal-window {
            flex-grow: 1;
            background: #000;
            border: 1px solid #333;
            padding: 1rem;
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.85rem;
            color: #00ff00;
            overflow-y: auto;
            max-height: none; /* Allow it to grow */
            height: 100%;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.8);
            margin-bottom: 0;
        }

        .log-line { margin-bottom: 0.25rem; word-break: break-all; }
        .log-line.error { color: #ff3333; }
        .log-line.success { color: #00ff00; }
        .log-line.info { color: #00ccff; }
        .log-line.system { color: #888; font-style: italic; }

        .cursor {
            display: inline-block;
            width: 8px;
            height: 14px;
            background: #00ff00;
            animation: blink 1s step-end infinite;
            vertical-align: middle;
        }

        @keyframes blink { 50% { opacity: 0; } }
        
        /* Section Title */
        .section-title {
            font-family: var(--font-ui);
            color: var(--cyan);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--cyan-dim);
            padding-bottom: 1rem;
            font-size: 1.1rem;
        }

        /* Form Groups */
        .form-group { margin-bottom: 2rem; }
        
        .form-label {
            display: block;
            font-family: var(--font-ui);
            color: var(--text-muted);
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-control {
            width: 100%;
            background: var(--bg-void);
            border: 1px solid var(--border-hairline);
            color: var(--text-main);
            padding: 1rem;
            font-family: var(--font-body);
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--cyan);
            box-shadow: 0 0 10px rgba(77, 238, 234, 0.1);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%234deeea%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 12px;
        }

        .helper-text {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: var(--text-muted);
            font-style: italic;
        }

        /* Toggle Switch (Checkbox) */
        .toggle-wrapper {
            display: flex;
            align-items: center;
            gap: 1rem;
            cursor: pointer;
        }
        
        /* Save Button Container */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 3rem;
            border-top: 1px solid rgba(255,255,255,0.05);
            padding-top: 2rem;
        }

        /* Status Badges */
        .status-badge {
            font-family: var(--font-ui);
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            background: rgba(255,255,255,0.05);
            color: var(--text-muted);
            border: 1px solid transparent;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <?php
        $pageTitle = 'SYSTEM SETTINGS';
        $pageSubtitle = 'CONFIGURE AI PROVIDERS & KEYS';
        include 'components/header.php';
        ?>

        <div class="settings-container">
            <!-- Left Column: Config Form -->
            <div class="settings-card">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--cyan-dim); margin-bottom: 2rem; padding-bottom: 1rem;">
                    <h3 class="section-title" style="border: none; margin: 0; padding: 0;">AI Configuration</h3>
                    <div style="display: flex; gap: 1rem;">
                        <span id="keyStatus" class="status-badge">KEY: CHECKING...</span>
                        <span id="aiStatus" class="status-badge">AI: WAITING...</span>
                    </div>
                </div>
                
                <form id="settingsForm">
                    <div class="form-group">
                        <label for="provider" class="form-label">AI Provider</label>
                        <select id="provider" class="form-control">
                            <option value="gemini">Google Gemini</option>
                            <option value="openrouter">OpenRouter</option>
                        </select>
                        <span class="helper-text">Select the backend engine for Zeon7.</span>
                    </div>

                    <div class="form-group">
                        <label for="model" class="form-label">Model Name</label>
                        
                        <!-- Gemini Dropdown -->
                        <select id="geminiModel" class="form-control" style="display: none;">
                            <option value="gemini-pro-latest">Gemini Pro (Latest)</option>
                            <option value="gemini-flash-latest">Gemini Flash (Latest)</option>
                        </select>

                        <!-- OpenRouter/Custom Input -->
                        <input type="text" id="customModel" class="form-control" placeholder="e.g. openai/gpt-4" style="display: none;">
                        
                        <span class="helper-text" id="modelHelp">Select the AI model version.</span>
                    </div>

                    <div class="form-group">
                        <label for="apiKey" class="form-label">API Key</label>
                        <div style="position: relative;">
                            <input type="password" id="apiKey" class="form-control" placeholder="••••••••••••••••" style="padding-right: 40px;">
                            <button type="button" id="toggleKeyBtn" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; z-index: 10;">
                                <!-- Eye Icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                        <span class="helper-text">Enter new key to update. Leave empty to keep current key.</span>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary" id="saveBtn">UPDATE PROTOCOLS</button>
                    </div>
                </form>
            </div>

            <!-- Right Column: Terminal -->
            <?php include 'components/terminal-panel.php'; ?>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script src="js/settings.js"></script>
    <script>
        // Ensure Auth logic runs
        if(typeof App !== 'undefined') App.requireAuth();
        document.addEventListener('DOMContentLoaded', () => {
            Settings.init();
        });
    </script>
</body>
</html>