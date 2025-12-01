<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Zeon7 Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Maven+Pro:wght@400;500;600&family=Montserrat:wght@400;600;800;900&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/zeon7-theme.css?v=3">
    <style>
        .settings-container { padding: 3rem; max-width: 800px; }
        
        /* Form Card */
        .settings-card {
            background: rgba(11, 18, 25, 0.6);
            border: 1px solid var(--border-hairline);
            padding: 3rem;
            border-radius: 4px;
            position: relative;
        }
        
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
    </style>
</head>
<body>
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <div class="header-bar">
            <div>
                <span class="page-title">SYSTEM SETTINGS</span>
                <span class="page-subtitle">CONFIGURE AI PROVIDERS & KEYS</span>
            </div>
        </div>

        <div class="settings-container">
            <div class="settings-card">
                <h3 class="section-title">AI Configuration</h3>
                
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
                        <input type="text" id="model" class="form-control" placeholder="e.g. gemini-1.5-pro">
                        <span class="helper-text">Leave empty to use default provider model.</span>
                    </div>

                    <div class="form-group">
                        <label for="apiKey" class="form-label">API Key</label>
                        <input type="password" id="apiKey" class="form-control" placeholder="••••••••••••••••">
                        <span class="helper-text">Enter new key to update. Leave empty to keep current key.</span>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary" id="saveBtn">UPDATE PROTOCOLS</button>
                    </div>
                </form>
            </div>
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