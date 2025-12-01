<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zeon7 Industrial Cockpit</title>
    <!-- Fonts: Montserrat (Headers), Ubuntu (UI), Maven Pro (Body) -->
    <link href="https://fonts.googleapis.com/css2?family=Maven+Pro:wght@400;500;600&family=Montserrat:wght@400;600;800;900&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* --- 1. INDUSTRIAL DESIGN TOKENS --- */
        :root {
            /* Palette: Deep Slate Blue & Safety Orange */
            --bg-void: #020617;       /* Sidebar/Deepest depth */
            --bg-body: #0f172a;       /* The "Deep Slate Blue" base */
            --bg-panel: rgba(15, 23, 42, 0.6); /* Glassy panel on top of blue */
            
            --border-hairline: 1px solid rgba(255, 255, 255, 0.08);
            --border-active: 1px solid var(--orange);
            
            --orange: #FF4500;        /* Zeon7 Signal Orange */
            --text-main: #ffffff;
            --text-muted: #94a3b8;    /* Cool Grey */
            
            /* Typography Rules */
            --font-head: 'Montserrat', sans-serif; /* Heavy Headers */
            --font-ui:   'Ubuntu', sans-serif;     /* Buttons/Labels */
            --font-body: 'Maven Pro', sans-serif;  /* Reading text */
        }

        /* --- 2. RESET --- */
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background-color: var(--bg-body);
            /* Subtle gradient to give depth to the blue, matching high-end dark modes */
            background-image: radial-gradient(circle at 90% 10%, #1e293b 0%, var(--bg-body) 60%);
            color: var(--text-main);
            font-family: var(--font-body);
            height: 100vh;
            overflow: hidden;
            display: flex;
            font-size: 15px; /* Maven Pro reads better slightly larger */
        }

        /* --- 3. SIDEBAR (Collapsed) --- */
        .sidebar {
            width: 80px; /* Slim */
            background: var(--bg-void);
            border-right: var(--border-hairline);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.5rem 0;
            z-index: 10;
            transition: width 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
        }
        
        .sidebar:hover { width: 260px; align-items: flex-start; padding-left: 24px; }

        .brand-container {
            height: 50px;
            margin-bottom: 3rem;
            display: flex;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        .brand-logo {
            width: 40px;
            /* White glow for the dot matrix logo */
            filter: invert(1) drop-shadow(0 0 8px rgba(255, 69, 0, 0.4));
            transition: all 0.3s;
        }
        
        /* Optional: If the logo is already white transparent, remove 'invert(1)' */

        .sidebar:hover .brand-logo { margin-right: 15px; } 

        .brand-text {
            font-family: var(--font-head);
            font-weight: 900;
            letter-spacing: 2px;
            font-size: 1.2rem;
            color: white;
            opacity: 0;
            transform: translateX(20px);
            transition: all 0.3s 0.1s;
            white-space: nowrap;
        }

        .sidebar:hover .brand-text { opacity: 1; transform: translateX(0); }

        .nav-item {
            display: flex; align-items: center; gap: 1.5rem;
            color: var(--text-muted);
            text-decoration: none;
            font-family: var(--font-ui); /* Ubuntu for UI elements */
            font-size: 0.85rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1.5rem;
            white-space: nowrap;
            opacity: 0.6;
            transition: all 0.2s;
            padding: 0.8rem 0.5rem;
            border-left: 3px solid transparent;
            width: 100%;
        }
        
        .nav-item:hover, .nav-item.active { 
            color: var(--text-main); 
            opacity: 1; 
            background: linear-gradient(90deg, rgba(255,255,255,0.03) 0%, transparent 100%);
        }
        .nav-item.active { border-left-color: var(--orange); }
        .nav-item i { font-style: normal; font-size: 1.2rem; width: 24px; text-align: center; }

        /* --- 4. MAIN COCKPIT --- */
        .cockpit {
            flex: 1;
            display: flex;
        }

        /* LEFT: CHAT (The Feed) */
        .chat-panel {
            flex: 6;
            display: flex; flex-direction: column;
            border-right: var(--border-hairline);
            /* Deep Blue tint background */
            background: rgba(15, 23, 42, 0.4); 
            backdrop-filter: blur(10px);
        }

        .chat-header {
            height: 80px;
            border-bottom: var(--border-hairline);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 2.5rem;
        }
        .status-block { 
            font-family: var(--font-ui); 
            font-size: 0.75rem; 
            color: var(--orange); 
            border: 1px solid rgba(255, 69, 0, 0.3);
            padding: 6px 12px;
            background: rgba(255, 69, 0, 0.05);
            letter-spacing: 1px;
            display: flex; align-items: center; gap: 10px;
            font-weight: 700;
        }
        .status-dot { width: 6px; height: 6px; background: var(--orange); border-radius: 50%; box-shadow: 0 0 10px var(--orange); }

        .chat-stream {
            flex: 1;
            padding: 3rem;
            overflow-y: auto;
        }

        .msg { display: flex; gap: 1.5rem; margin-bottom: 2.5rem; max-width: 90%; }
        .msg.user { flex-direction: row-reverse; align-self: flex-end; }
        
        .label {
            font-family: var(--font-ui);
            font-size: 0.7rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
            display: block;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }

        .content {
            font-family: var(--font-body); /* Maven Pro */
            font-size: 1.1rem;
            line-height: 1.7;
            color: var(--text-main);
            font-weight: 400;
            background: var(--bg-panel);
            padding: 1.5rem 2rem;
            border-left: 2px solid var(--orange);
            border-radius: 0 4px 4px 0;
        }
        
        .msg.user .content { 
            background: transparent; 
            border: 1px solid var(--border-hairline); 
            border-left: none;
            color: var(--text-muted); 
            text-align: right; 
            border-radius: 4px;
        }

        .input-area {
            padding: 2rem;
            border-top: var(--border-hairline);
            display: flex; gap: 0;
            background: rgba(2, 6, 23, 0.8);
        }
        
        .input-box {
            flex: 1;
            background: transparent;
            border: none;
            border-bottom: 1px solid var(--text-muted);
            color: var(--text-main);
            font-family: var(--font-body);
            font-size: 1.1rem;
            padding: 1rem 0;
            transition: border 0.3s;
        }
        .input-box:focus { outline: none; border-bottom: 1px solid var(--orange); }
        .input-box::placeholder { color: #334155; font-family: var(--font-ui); text-transform: uppercase; letter-spacing: 1px; font-size: 0.9rem; }

        .btn-send {
            background: transparent;
            color: var(--orange);
            border: 1px solid var(--orange);
            font-family: var(--font-ui);
            font-weight: 700;
            text-transform: uppercase;
            padding: 0 2.5rem;
            cursor: pointer;
            margin-left: 1.5rem;
            transition: all 0.2s;
            letter-spacing: 1px;
        }
        .btn-send:hover { background: var(--orange); color: black; box-shadow: 0 0 20px var(--orange); }

        /* RIGHT: FLIGHT DECK (The Instrument Panel) */
        .deck-panel {
            flex: 4;
            background: rgba(2, 6, 23, 0.95); /* Nearly opaque black/blue */
            display: flex; flex-direction: column;
            border-left: var(--border-hairline);
        }

        .tabs { display: flex; border-bottom: var(--border-hairline); }
        .tab { 
            flex: 1; padding: 1.5rem; background: none; border: none; 
            color: var(--text-muted); font-family: var(--font-head); 
            font-weight: 800; font-size: 0.8rem; letter-spacing: 2px;
            cursor: pointer; position: relative; opacity: 0.5;
            transition: opacity 0.2s;
        }
        .tab:hover { opacity: 0.8; color: var(--text-main); }
        .tab.active { opacity: 1; color: var(--text-main); }
        .tab.active::after { 
            content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 3px; background: var(--orange); 
            box-shadow: 0 -2px 15px var(--orange);
        }

        .controls { padding: 3rem; overflow-y: auto; }
        
        /* Widget */
        .widget { margin-bottom: 3.5rem; }
        .widget-head {
            font-family: var(--font-ui);
            color: var(--orange);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 1.2rem;
            display: flex; justify-content: space-between;
            border-bottom: 1px solid rgba(255, 69, 0, 0.2);
            padding-bottom: 0.5rem;
            font-weight: 700;
        }

        /* Big Typography Context */
        .context-display {
            padding-left: 1.5rem;