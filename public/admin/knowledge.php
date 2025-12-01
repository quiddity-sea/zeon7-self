<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knowledge Manager - Zeon7 Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Maven+Pro:wght@400;500;600&family=Montserrat:wght@400;600;800;900&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/zeon7-theme.css?v=3">
    <style>
        .knowledge-content { padding: 3rem; max-width: 1400px; }
        .upload-card { 
            border: 2px dashed var(--border-hairline); 
            border-radius: 4px; 
            padding: 3rem; 
            text-align: center; 
            cursor: pointer; 
            transition: all 0.2s; 
            background: rgba(0,0,0,0.2);
            margin-bottom: 2rem;
        }
        .upload-card:hover { 
            border-color: var(--cyan); 
            background: rgba(77, 238, 234, 0.05); 
        }
        .upload-icon { font-size: 3rem; margin-bottom: 1rem; opacity: 0.7; }
        .upload-text { font-family: var(--font-ui); color: var(--text-muted); }
        
        .files-table { width: 100%; border-collapse: collapse; }
        .files-table th { 
            text-align: left; 
            padding: 1rem; 
            border-bottom: 1px solid var(--border-hairline); 
            color: var(--orange); 
            font-family: var(--font-ui); 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            letter-spacing: 1px;
        }
        .files-table td { 
            padding: 1rem; 
            border-bottom: 1px solid rgba(255,255,255,0.05); 
            color: var(--text-main); 
            font-family: var(--font-body);
        }
        .action-btn { 
            background: none; 
            border: none; 
            color: var(--text-muted); 
            cursor: pointer; 
            transition: color 0.2s; 
        }
        .action-btn:hover { color: var(--orange); }
    </style>
</head>
<body>
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <div class="header-bar">
            <div>
                <span class="page-title">KNOWLEDGE BASE</span>
                <span class="page-subtitle">CONTEXT FILE MANAGEMENT</span>
            </div>
        </div>

        <div class="knowledge-content">
            <!-- Upload Zone -->
            <div id="uploadZone" class="upload-card">
                <div class="upload-icon">📄</div>
                <h3 style="margin-bottom: 0.5rem; font-family: var(--font-head); color: var(--text-main);">UPLOAD KNOWLEDGE FILE</h3>
                <p class="upload-text">Drag & drop .md or .txt files here, or click to browse</p>
                <input type="file" id="fileInput" accept=".md,.txt" style="display: none;">
            </div>
            <div id="uploadStatus" style="margin-bottom: 2rem; display: none;"></div>

            <!-- Files List -->
            <div>
                <div class="section-head" style="font-family: var(--font-ui); color: var(--cyan); font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 1.5rem; font-size: 0.8rem;">UPLOADED FILES</div>
                <div class="table-container">
                    <table class="files-table">
                        <thead>
                            <tr>
                                <th>Filename</th>
                                <th>Size</th>
                                <th>Uploaded</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="filesList">
                            <tr>
                                <td colspan="4" style="padding: 2rem; text-align: center; color: var(--text-muted);">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script src="js/knowledge.js"></script>
    <script>
        App.requireAuth();
    </script>
</body>
</html>
