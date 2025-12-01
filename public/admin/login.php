<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Zeon7 Mission Control</title>
    <link href="https://fonts.googleapis.com/css2?family=Maven+Pro:wght@400;500;600&family=Montserrat:wght@400;600;800;900&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/zeon7-theme.css?v=3">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: radial-gradient(circle at center, #1a2634 0%, #0b1219 100%);
        }
        .auth-card {
            background: rgba(11, 18, 25, 0.8);
            border: 1px solid var(--border-hairline);
            padding: 3rem;
            width: 100%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 0 30px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
        }
        .auth-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 2px;
            background: linear-gradient(90deg, transparent, var(--cyan), transparent);
        }
        .brand-logo {
            width: 80px;
            margin-bottom: 1.5rem;
            filter: drop-shadow(0 0 10px var(--cyan-dim));
        }
        .auth-title {
            font-family: var(--font-head);
            font-size: 1.5rem;
            color: var(--text-main);
            margin-bottom: 0.5rem;
            letter-spacing: 2px;
        }
        .auth-subtitle {
            font-family: var(--font-ui);
            font-size: 0.8rem;
            color: var(--cyan);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2rem;
        }
        .form-group { margin-bottom: 1.5rem; text-align: left; }
        .form-label {
            display: block;
            font-family: var(--font-ui);
            color: var(--text-muted);
            font-size: 0.7rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .form-input {
            width: 100%;
            background: rgba(0,0,0,0.3);
            border: 1px solid var(--border-hairline);
            color: var(--text-main);
            padding: 0.8rem;
            font-family: var(--font-body);
            font-size: 1rem;
            transition: all 0.3s;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--cyan);
            box-shadow: 0 0 15px rgba(77, 238, 234, 0.1);
        }
        .btn-login {
            width: 100%;
            background: rgba(77, 238, 234, 0.1);
            border: 1px solid var(--cyan);
            color: var(--cyan);
            padding: 1rem;
            font-family: var(--font-ui);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
        }
        .btn-login:hover {
            background: var(--cyan);
            color: var(--bg-void);
            box-shadow: 0 0 20px var(--cyan-dim);
        }
        .alert-error {
            background: rgba(255, 69, 0, 0.1);
            border: 1px solid var(--orange);
            color: var(--orange);
            padding: 0.8rem;
            font-family: var(--font-ui);
            font-size: 0.8rem;
            margin-bottom: 1.5rem;
            display: none;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <img src="assets/logo_1759683970.png" class="brand-logo" alt="ZEON7">
        <div class="auth-title">IDENTITY VERIFICATION</div>
        <div class="auth-subtitle">Secure Access Required</div>
        
        <div id="errorAlert" class="alert-error"></div>
        
        <form id="loginForm">
            <div class="form-group">
                <label class="form-label">Access Code</label>
                <input type="password" id="password" class="form-input" placeholder="ENTER PASSWORD" required autofocus>
            </div>
            
            <button type="submit" class="btn-login">Initialize Session</button>
        </form>
    </div>

    <script src="js/app.js"></script>
    <script>
        // Redirect if already logged in
        App.redirectIfAuth();

        const form = document.getElementById('loginForm');
        const errorAlert = document.getElementById('errorAlert');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const password = document.getElementById('password').value;
            
            try {
                const res = await App.login(password);
                
                if (res.success) {
                    window.location.href = 'index.php';
                } else {
                    errorAlert.textContent = res.error || 'ACCESS DENIED';
                    errorAlert.style.display = 'block';
                }
            } catch (e) {
                console.error('Login error', e);
                errorAlert.textContent = 'SYSTEM ERROR: ' + e.message;
                errorAlert.style.display = 'block';
            }
        });
    </script>
</body>
</html>
