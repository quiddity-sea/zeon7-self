<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - Zeon7 Admin</title>
    <link rel="stylesheet" href="/css/variables.css">
    <link rel="stylesheet" href="/css/base.css">
    <link rel="stylesheet" href="/css/components.css">
    <link rel="stylesheet" href="/admin/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;900&family=Source+Sans+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        .editor-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            height: calc(100vh - 200px);
        }

        .editor-pane {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            height: 100%;
        }

        textarea.editor-input {
            flex: 1;
            width: 100%;
            padding: 1rem;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            color: var(--text);
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 0.9rem;
            line-height: 1.6;
            resize: none;
        }

        textarea.editor-input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .preview-pane {
            flex: 1;
            padding: 1rem;
            background: var(--background);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            overflow-y: auto;
            color: var(--text);
        }

        .preview-pane h1, .preview-pane h2, .preview-pane h3 {
            margin-top: 1.5em;
            margin-bottom: 0.5em;
            color: var(--text);
        }
        
        .preview-pane p {
            margin-bottom: 1em;
            line-height: 1.6;
        }

        .preview-pane code {
            background: rgba(255,255,255,0.1);
            padding: 0.2em 0.4em;
            border-radius: 3px;
            font-family: monospace;
        }

        .preview-pane pre {
            background: rgba(0,0,0,0.3);
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="brand">
            ⚡ Zeon7
            <button class="theme-toggle" data-theme-toggle aria-label="Toggle theme">
                <span data-theme-icon>🌙</span>
            </button>
        </div>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <a href="posts.php" class="btn btn-secondary">← Back</a>
                <h1 id="pageTitle">New Post</h1>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button id="deleteBtn" class="btn btn-danger" style="display: none;">Delete</button>
                <button id="saveBtn" class="btn btn-primary">Save Draft</button>
                <button id="publishBtn" class="btn btn-success">Publish</button>
            </div>
        </div>

        <form id="postForm">
            <div class="meta-grid">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" class="form-control" required placeholder="Enter post title...">
                </div>
                <div class="form-group">
                    <label for="slug">Slug</label>
                    <input type="text" id="slug" class="form-control" placeholder="auto-generated-slug">
                </div>
            </div>

            <div class="editor-layout">
                <div class="editor-pane">
                    <label style="font-weight: 500;">Markdown Content</label>
                    <textarea id="content" class="editor-input" placeholder="Write your post content here..."></textarea>
                </div>
                <div class="editor-pane">
                    <label style="font-weight: 500;">Preview</label>
                    <div id="preview" class="preview-pane"></div>
                </div>
            </div>
        </form>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="/js/theme-switcher.js"></script>
    <script src="js/app.js"></script>
    <script src="js/posts.js"></script>
    <script>
        App.requireAuth();
        document.addEventListener('DOMContentLoaded', () => Posts.initEditor());
    </script>
</body>
</html>
