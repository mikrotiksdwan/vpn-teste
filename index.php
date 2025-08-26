<?php
session_start();

// Configurações do banco
$db_host = 'localhost';
$db_name = 'radius';
$db_user = 'radius';
$db_pass = 'rt25rt--2025';

// Conexão com o banco
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// Funções SSHA
function generate_ssha_password($password) {
    $salt = random_bytes(4);
    $hash = sha1($password . $salt, true);
    return '{SSHA}' . base64_encode($hash . $salt);
}

function verify_ssha_password($password, $ssha_hash) {
    if (substr($ssha_hash, 0, 6) !== '{SSHA}') return false;
    
    $decoded = base64_decode(substr($ssha_hash, 6));
    $hash = substr($decoded, 0, 20);
    $salt = substr($decoded, 20);
    
    return sha1($password . $salt, true) === $hash;
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: change-password.php');
    exit;
}

// Processar formulários
$message = '';
$current_view = isset($_GET['token']) ? 'reset' : (isset($_POST['recovery']) ? 'recovery' : 'login');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // Login normal
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        
        $stmt = $pdo->prepare("SELECT username, value as password_hash FROM radcheck WHERE email = ? AND attribute = 'SSHA-Password'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && verify_ssha_password($password, $user['password_hash'])) {
            $_SESSION['user_email'] = $email;
            $_SESSION['user_logged_in'] = true;
            $current_view = 'change';
        } else {
            $message = '<div class="alert alert-danger">Email ou senha incorretos!</div>';
        }
    }
    elseif (isset($_POST['change_password'])) {
        // Trocar senha
        if (!isset($_SESSION['user_logged_in'])) {
            $message = '<div class="alert alert-danger">Sessão expirada. Faça login novamente.</div>';
            unset($_SESSION['user_logged_in']);
        } else {
            $new_password = trim($_POST['new_password']);
            $confirm_password = trim($_POST['confirm_password']);
            
            if ($new_password !== $confirm_password) {
                $message = '<div class="alert alert-danger">As senhas não coincidem!</div>';
            } elseif (strlen($new_password) < 8) {
                $message = '<div class="alert alert-danger">A senha deve ter pelo menos 8 caracteres!</div>';
            } else {
                $new_hash = generate_ssha_password($new_password);
                $stmt = $pdo->prepare("UPDATE radcheck SET value = ? WHERE email = ? AND attribute = 'SSHA-Password'");
                
                if ($stmt->execute([$new_hash, $_SESSION['user_email']])) {
                    $message = '<div class="alert alert-success">Senha alterada com sucesso!</div>';
                    unset($_SESSION['user_logged_in']);
                    shell_exec('sudo systemctl restart freeradius');
                    $current_view = 'login';
                }
            }
        }
    }
    // ... resto dos outros formulários ...
}

// Se usuário está logado mas não veio do POST, mostrar tela de troca
if (isset($_SESSION['user_logged_in']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $current_view = 'change';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Senha VPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .auth-container { max-width: 400px; margin: 50px auto; }
        .card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .card-header { border-radius: 15px 15px 0 0 !important; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <div class="card">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4><i class="bi bi-shield-lock"></i> VPN Portal</h4>
                    <p class="mb-0">Gerencie sua senha de acesso</p>
                </div>
                <div class="card-body p-4">
                    <?php echo $message; ?>
                    
                    <!-- Login -->
                    <?php if ($current_view == 'login'): ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Senha</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100 mb-3">Entrar</button>
                        <div class="text-center">
                            <a href="?view=recovery" class="text-decoration-none">Esqueci minha senha</a>
                        </div>
                    </form>
                    <?php endif; ?>

                    <!-- Trocar Senha (Após Login) -->
                    <?php if ($current_view == 'change' && isset($_SESSION['user_logged_in'])): ?>
                    <div class="text-center mb-4">
                        <h5>Olá, <?php echo $_SESSION['user_email']; ?></h5>
                        <p class="text-muted">Digite sua nova senha abaixo</p>
                    </div>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nova Senha</label>
                            <input type="password" class="form-control" name="new_password" required 
                                   placeholder="Mínimo 8 caracteres" minlength="8">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar Nova Senha</label>
                            <input type="password" class="form-control" name="confirm_password" required 
                                   placeholder="Digite a senha novamente" minlength="8">
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-key"></i> Alterar Senha
                        </button>
                        <div class="text-center">
                            <a href="?logout=true" class="text-decoration-none text-danger">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </a>
                        </div>
                    </form>
                    <?php endif; ?>

                    <!-- ... outros formulários ... -->
                </div>
            </div>
        </div>
    </div>
</body>
</html>
