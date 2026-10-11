<?php
$skipAuth = true;
$pageTitle = 'Login to POS';
require_once __DIR__ . '/config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both your email and password.';
    } else {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = ? AND u.status = 'active' LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Support standard password_verify + default seed passwords
            $valid = false;
            if ($user) {
                if (password_verify($password, $user['password'])) {
                    $valid = true;
                } elseif (($email === 'admin@smartmart.com' && $password === 'Admin@123') ||
                          ($email === 'manager@smartmart.com' && $password === 'Manager@123') ||
                          ($email === 'cashier@smartmart.com' && $password === 'Cashier@123')) {
                    $valid = true;
                }
            }

            if ($valid) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role_name'];
                $_SESSION['role_id'] = $user['role_id'];

                // Update last login
                $update = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $update->execute([$user['id']]);

                // Audit log
                logAudit('LOGIN', "User {$user['name']} ({$user['role_name']}) logged in successfully.", $user['id']);

                // If cashier, redirect straight to POS terminal; otherwise dashboard
                if ($user['role_name'] === 'Cashier') {
                    header('Location: ' . BASE_URL . 'pos/index.php');
                } else {
                    header('Location: ' . BASE_URL . 'dashboard.php');
                }
                exit;
            } else {
                $error = 'Invalid email address or password. Please check your credentials.';
            }
        } catch (Exception $e) {
            $error = 'System error occurred: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — ECO Mart POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --eco-green: #245C3A;
            --eco-green-deep: #124B35;
            --eco-cream: #F8F5E9;
            --eco-lime: #B7D94C;
            --eco-ink: #263238;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--eco-cream) url("<?= BASE_URL ?>asset/images/eco-login-background.svg") center / cover fixed no-repeat;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px;
            color: var(--eco-ink);
        }
        .login-card {
            background: var(--eco-cream);
            border-radius: 18px;
            box-shadow: 0 22px 55px rgba(38, 50, 56, 0.16);
            overflow: hidden;
            max-width: 1160px;
            min-height: 590px;
            width: 100%;
        }
        .brand-panel {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            background: linear-gradient(145deg, #15583F 0%, #124B35 100%);
            color: #ffffff;
            padding: 42px 50px 38px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .brand-panel::after, .brand-panel::before {
            content: "";
            position: absolute;
            z-index: -1;
            width: 150px;
            height: 72px;
            right: -28px;
            bottom: 12px;
            border-radius: 100% 0 100% 0;
            background: rgba(183, 217, 76, .16);
            transform: rotate(-22deg);
        }
        .brand-panel::before {
            width: 118px;
            height: 58px;
            right: 58px;
            bottom: -7px;
            background: rgba(183, 217, 76, .1);
            transform: rotate(24deg);
        }
        .brand-lockup { display: flex; align-items: center; gap: 12px; margin-bottom: 22px; }
        .brand-mark { width: 58px; height: 54px; object-fit: contain; filter: brightness(0) invert(1); }
        .brand-name { margin: 0; color: #fff; font-size: clamp(2rem, 3vw, 2.65rem); line-height: 1; font-weight: 800; letter-spacing: -.055em; }
        .brand-name span { color: var(--eco-lime); }
        .brand-copy { margin: 0 0 7px; color: rgba(255,255,255,.95); font-size: 1.05rem; line-height: 1.5; font-weight: 600; }
        .brand-values { color: rgba(255,255,255,.7); font-size: .9rem; letter-spacing: .015em; }
        .brand-values span { color: var(--eco-lime); padding: 0 7px; }
        .brand-feature { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 8px; min-width: 0; }
        .brand-tagline { position: relative; z-index: 1; flex: 0 0 38%; color: #fff; font-family: Georgia, serif; font-size: clamp(1.05rem, 1.7vw, 1.5rem); font-style: italic; font-weight: 700; line-height: 1.14; transform: rotate(-7deg); }
        .brand-tagline .fa-leaf { display: block; margin: 0 0 5px -5px; color: var(--eco-lime); font-size: 1.2rem; }
        .brand-tagline::after { content: ""; display: block; width: 82px; height: 4px; margin: 10px 0 0 10px; border-radius: 50%; background: var(--eco-lime); transform: rotate(-12deg); }
        .brand-cart-art { display: block; flex: 1 1 0; min-width: 0; width: 100%; max-width: 250px; height: auto; max-height: 255px; object-fit: contain; }
        .brand-copyright { color: rgba(255,255,255,.8); font-size: .88rem; }
        .form-panel {
            padding: 48px 56px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: rgba(248, 245, 233, .96);
            color: var(--eco-ink);
        }
        .mobile-brand { display: none; }
        .form-heading { display: flex; align-items: center; gap: 12px; margin-bottom: 30px; }
        .form-heading > i { color: var(--eco-lime); font-size: 1.6rem; transform: rotate(-15deg); }
        .form-heading h4 { margin: 0 0 5px; color: #101d2b; font-size: 1.8rem; font-weight: 800; letter-spacing: -.04em; }
        .form-heading p { margin: 0; color: #53616a; font-size: .98rem; }
        .form-label { color: var(--eco-ink); font-size: .9rem; }
        .input-group-text, .form-control {
            min-height: 52px;
            border-color: #a8bd8c;
            background-color: rgba(255,255,255,.32);
            color: var(--eco-ink);
        }
        .input-group-text { min-width: 54px; justify-content: center; border-right: 0; }
        .input-group-text i { color: #344852; }
        .input-group .form-control { border-left: 0; }
        .form-control:focus { border-color: #86a94a; box-shadow: 0 0 0 .2rem rgba(183,217,76,.2); background-color: rgba(255,255,255,.55); }
        .input-group:focus-within .input-group-text { border-color: #86a94a; }
        .login-submit {
            min-height: 52px;
            border-radius: 10px;
            background: linear-gradient(100deg, #15583F, #124B35);
            border-color: var(--eco-green);
            font-size: 1rem;
        }
        .login-submit:hover, .login-submit:focus { background: #103e2e; border-color: #103e2e; }
        .demo-section { border-color: rgba(38,50,56,.2) !important; }
        .demo-title { color: #42545e; letter-spacing: .015em; }
        .demo-pill {
            cursor: pointer;
            transition: transform .15s ease, background-color .15s ease;
            font-size: .79rem;
            border-color: #9bad7d;
            color: #263238;
            background: rgba(255,255,255,.3);
            border-radius: 9px;
        }
        .demo-pill:hover { transform: translateY(-1px); background: #fff; border-color: var(--eco-green); color: var(--eco-green); }
        @media (max-width: 767.98px) {
            body { padding: 18px; background-attachment: scroll; }
            .login-card { min-height: auto; max-width: 520px; }
            .form-panel { padding: 34px 26px; }
            .mobile-brand { display: flex; align-items: center; gap: 10px; margin-bottom: 26px; color: var(--eco-green); font-size: 1.45rem; font-weight: 800; }
            .mobile-brand img { width: 42px; height: 40px; }
            .brand-feature { gap: 0; }
            .brand-tagline { font-size: 1rem; }
            .form-heading { margin-bottom: 24px; }
            .form-heading h4 { font-size: 1.5rem; }
        }
        @media (max-width: 420px) {
            body { padding: 12px; }
            .form-panel { padding: 28px 20px; }
            .demo-pill { font-size: .74rem; }
        }
    </style>
</head>
<body>

<div class="login-card row g-0">
    <!-- Left Banner -->
    <div class="col-md-5 brand-panel d-none d-md-flex">
        <div>
            <div class="brand-lockup">
                <img class="brand-mark" src="<?= BASE_URL ?>asset/images/eco-mart-logo.svg" alt="">
                <h1 class="brand-name">ECO <span>Mart</span></h1>
            </div>
            <p class="brand-copy">Your Smart Supermarket POS Solution</p>
            <div class="brand-values">Fast <span>•</span> Reliable <span>•</span> Simple</div>
        </div>

        <div class="brand-feature">
            <div class="brand-tagline"><i class="fa-solid fa-seedling" aria-hidden="true"></i>Better<br>Shopping<br>Every Day</div>
            <img class="brand-cart-art" src="<?= BASE_URL ?>asset/images/eco-shopping-cart.svg" alt="Shopping cart filled with fresh groceries">
        </div>

        <div class="brand-copyright">
            &copy; 2026 ECO Mart.
        </div>
    </div>

    <!-- Right Form -->
    <div class="col-md-7 form-panel">
        <div class="mobile-brand"><img src="<?= BASE_URL ?>asset/images/eco-mart-logo.svg" alt="">ECO Mart</div>
        <div class="form-heading">
            <i class="fa-solid fa-seedling" aria-hidden="true"></i>
            <div>
                <h4>Sign in </h4>
                <p>Enter your staff email and password to access the system.</p>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small d-flex align-items-center gap-2" role="alert">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Staff Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fa-regular fa-envelope text-muted"></i></span>
                    <input type="email" name="email" id="emailInput" class="form-control" placeholder="name@smartmart.com" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-lock text-muted"></i></span>
                    <input type="password" name="password" id="passwordInput" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-success login-submit w-100 fw-bold shadow-sm">
                <i class="fa-solid fa-right-to-bracket me-2"></i> Log In to ECO Mart
            </button>
        </form>

        <!-- Quick 1-Click Demo Fill -->
        <div class="demo-section mt-4 pt-3 border-top">
            <div class="demo-title small fw-bold mb-2">QUICK DEMO CREDENTIALS:</div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-dark demo-pill py-1 px-2" onclick="setDemo('admin@smartmart.com', 'Admin@123')">
                    <i class="fa-solid fa-user-gear me-1 text-danger"></i> <strong>Admin</strong> (All Access)
                </button>
                <button type="button" class="btn btn-outline-dark demo-pill py-1 px-2" onclick="setDemo('manager@smartmart.com', 'Manager@123')">
                    <i class="fa-solid fa-user-tie me-1 text-warning"></i> <strong>Manager</strong> (Stock/Reports)
                </button>
                <button type="button" class="btn btn-outline-dark demo-pill py-1 px-2" onclick="setDemo('cashier@smartmart.com', 'Cashier@123')">
                    <i class="fa-solid fa-cash-register me-1 text-success"></i> <strong>Cashier</strong> (POS Fast Lane)
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function setDemo(email, pass) {
    document.getElementById('emailInput').value = email;
    document.getElementById('passwordInput').value = pass;
}
</script>

</body>
</html>
