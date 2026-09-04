<?php
require_once __DIR__ . '/../src/services/AuthService.php';
$authService = new AuthService();
if ($authService->isAuthenticated()) {
    header('Location: index.php');
    exit;
}
$errorMessages = [
    'link_required'         => 'GOOGLE ACCOUNT NOT LINKED — Contact your administrator to link your Google account.',
    'token_failed'          => 'GOOGLE AUTH FAILED — Could not retrieve access token. Please try again.',
    'invalid_state'         => 'SECURITY CHECK FAILED — Session state mismatch. Please try again.',
    'no_email'              => 'GOOGLE AUTH FAILED — No email address returned by Google.',
    'google_cancelled'      => 'GOOGLE AUTH CANCELLED.',
    'google_not_configured' => 'GOOGLE AUTH NOT CONFIGURED on this server.',
];
$errorParam   = $_GET['error'] ?? '';
$errorMessage = $errorMessages[$errorParam] ?? '';
?>
<!DOCTYPE html>
<html lang="en" class="hud-scanline">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operator Access — Zeon7 Cybernetic Portal</title>
    <link rel="stylesheet" href="../css/zeon7-theme.css?v=15.0">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: radial-gradient(circle at center, #0e1726 0%, #030609 100%);
        }
        .auth-card-hud {
            width: 100%;
            max-width: 460px;
            text-align: center;
            padding: 3rem 2.5rem;
        }
        .brand-logo-large {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            border: 2px solid var(--color-cyan);
            box-shadow: 0 0 25px rgba(var(--color-cyan-rgb), 0.4);
            margin-bottom: 1.5rem;
            object-fit: cover;
        }
        .auth-divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.75rem 0;
            color: var(--color-cyan);
            font-family: var(--font-mono);
            font-size: 0.7rem;
            letter-spacing: 0.15em;
            opacity: 0.6;
        }
        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            border-top: 1px solid rgba(var(--color-cyan-rgb), 0.3);
        }
        .btn-google {
            width: 100%;
            padding: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            background: transparent;
            border: 1px solid rgba(var(--color-cyan-rgb), 0.4);
            color: var(--color-text);
            font-family: var(--font-mono);
            font-size: 0.8rem;
            letter-spacing: 0.1em;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-google:hover {
            border-color: var(--color-cyan);
            background: rgba(var(--color-cyan-rgb), 0.08);
            color: var(--color-cyan);
        }
        .btn-google svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }
        .alert-box {
            padding: 0.75rem 1rem;
            font-family: var(--font-mono);
            font-size: 0.78rem;
            margin-bottom: 1.5rem;
            text-align: left;
            line-height: 1.5;
        }
        .alert-error {
            background: rgba(244, 63, 94, 0.12);
            border: 1px solid var(--color-coral);
            color: var(--color-coral);
        }
    </style>
</head>
<body>
<div class="hud-border auth-card-hud" id="authCard" data-tilt>
    <div class="hud-corner-tr"></div>
    <div class="hud-corner-bl"></div>

    <img src="../assets/images/logo_1759683970.png" class="brand-logo-large" alt="ZEON7"
         onerror="this.src='https://api.dicebear.com/7.x/bottts/svg?seed=zeon7'">

    <h2 style="letter-spacing: 0.15em; font-size: 1.35rem; margin-bottom: 0.25rem;">OPERATOR ACCESS</h2>
    <div style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--color-cyan); letter-spacing: 0.1em; margin-bottom: 2rem;">
        // IDENTITY VERIFICATION REQUIRED
    </div>

    <?php if ($errorMessage): ?>
    <div class="alert-box alert-error"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <div id="errorAlert" class="alert-box alert-error" style="display: none;"></div>

    <form id="loginForm">
        <div style="margin-bottom: 1.25rem; text-align: left;">
            <label for="username">OPERATOR ID</label>
            <input type="text" id="username" class="input-box" placeholder="USERNAME"
                   required autofocus autocomplete="username">
        </div>
        <div style="margin-bottom: 1.5rem; text-align: left;">
            <label for="password">PASSKEY</label>
            <input type="password" id="password" class="input-box" placeholder="ENTER ACCESS CODE"
                   required autocomplete="current-password"
                   style="text-align: center; letter-spacing: 0.2em; font-size: 1.1rem;">
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.9rem;">
            INITIALISE OPERATOR SESSION
        </button>
    </form>

    <div class="auth-divider">// OR</div>

    <a href="/api/auth/google_redirect.php" class="btn-google">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
        </svg>
        AUTHENTICATE VIA GOOGLE
    </a>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="js/animations.js?v=11.0"></script>
<script src="js/app.js"></script>
<script>
    App.redirectIfAuth();

    const form       = document.getElementById('loginForm');
    const errorAlert = document.getElementById('errorAlert');
    const authCard   = document.getElementById('authCard');

    if (typeof gsap !== 'undefined') {
        gsap.from(authCard, { duration: 0.8, scale: 0.9, opacity: 0, y: 30, ease: 'back.out(1.4)' });
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        errorAlert.style.display = 'none';

        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;

        try {
            const res = await App.login(username, password);
            if (res.success) {
                if (typeof gsap !== 'undefined') {
                    gsap.to(authCard, {
                        scale: 1.05, opacity: 0, duration: 0.4, ease: 'power2.in',
                        onComplete: () => window.location.href = 'index.php'
                    });
                } else {
                    window.location.href = 'index.php';
                }
            } else {
                errorAlert.textContent = res.error || 'ACCESS DENIED: INVALID CREDENTIALS';
                errorAlert.style.display = 'block';
                if (typeof gsap !== 'undefined') {
                    gsap.fromTo(authCard, { x: -10 }, { x: 10, duration: 0.08, repeat: 5, yoyo: true, ease: 'power1.inOut' });
                }
            }
        } catch (err) {
            errorAlert.textContent = 'SYSTEM ERROR: ' + err.message;
            errorAlert.style.display = 'block';
        }
    });
</script>
</body>
</html>
