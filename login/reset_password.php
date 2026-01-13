<?php
require_once '../config/config.php';

$message = '';
$error = '';
$token = $_GET['token'] ?? '';
$valid_token = false;
$email = '';

if (empty($token)) {
    $error = "Token inválido ou ausente.";
} else {
    // Verifica se o token é válido
    $sql = "SELECT email, expires_at, used FROM password_reset_tokens WHERE token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $token_data = $result->fetch_assoc();
        
        if ($token_data['used'] == 1) {
            $error = "Este token já foi utilizado. Solicite uma nova recuperação de senha.";
        } elseif (strtotime($token_data['expires_at']) < time()) {
            $error = "Este token expirou. Solicite uma nova recuperação de senha.";
        } else {
            $valid_token = true;
            $email = $token_data['email'];
        }
    } else {
        $error = "Token inválido.";
    }
    
    $stmt->close();
}

// Processa a redefinição de senha
if ($_SERVER["REQUEST_METHOD"] == "POST" && $valid_token) {
    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($new_password) || empty($confirm_password)) {
        $error = "Por favor, preencha todos os campos.";
    } elseif (strlen($new_password) < 6) {
        $error = "A senha deve ter no mínimo 6 caracteres.";
    } elseif ($new_password !== $confirm_password) {
        $error = "As senhas não coincidem.";
    } else {
        // Atualiza a senha
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE usuario SET password = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $hashed_password, $email);
        
        if ($stmt->execute()) {
            // Marca o token como usado
            $sql_token = "UPDATE password_reset_tokens SET used = 1 WHERE token = ?";
            $stmt_token = $conn->prepare($sql_token);
            $stmt_token->bind_param("s", $token);
            $stmt_token->execute();
            $stmt_token->close();
            
            $message = "Senha redefinida com sucesso! Você já pode fazer login com sua nova senha.";
            $valid_token = false; // Impede nova tentativa
        } else {
            $error = "Erro ao atualizar senha. Por favor, tente novamente.";
        }
        
        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Redefinir Senha - Solução Certa</title>
  <link rel="shortcut icon" type="image/png" href="<?= $url; ?>/assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="<?= $url; ?>/assets/css/styles.min.css" />
</head>
<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <div class="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">
      <div class="d-flex align-items-center justify-content-center w-100">
        <div class="row justify-content-center w-100">
          <div class="col-md-8 col-lg-6 col-xxl-3">
            <div class="card mb-0">
              <div class="card-body">
                <a href="<?= $url; ?>/index.php" class="text-nowrap logo-img text-center d-block py-3 w-100">
                  <img src="<?= $url; ?>/assets/images/logos/logo.png" width="250" alt="">
                </a>
                <p class="text-center">Redefinir Senha</p>
                
                <?php if ($message): ?>
                  <div class="alert alert-success" role="alert">
                    <?= $message; ?>
                    <div class="mt-3">
                      <a href="index.php" class="btn btn-primary">Ir para o Login</a>
                    </div>
                  </div>
                <?php elseif ($error): ?>
                  <div class="alert alert-danger" role="alert">
                    <?= $error; ?>
                    <div class="mt-3">
                      <a href="forgot_password.php" class="btn btn-secondary">Solicitar Nova Recuperação</a>
                    </div>
                  </div>
                <?php elseif ($valid_token): ?>
                  <form action="reset_password.php?token=<?= htmlspecialchars($token); ?>" method="post">
                    <div class="mb-3">
                      <label for="password" class="form-label">Nova Senha</label>
                      <div class="position-relative">
                        <input type="password" class="form-control" name="password" id="password" required 
                               minlength="6" placeholder="Mínimo 6 caracteres">
                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3" 
                                id="togglePassword" style="border: none; background: none; padding: 0; margin: 0; z-index: 10;">
                          <i class="ti ti-eye" id="eyeIcon"></i>
                        </button>
                      </div>
                    </div>
                    <div class="mb-3">
                      <label for="confirm_password" class="form-label">Confirmar Nova Senha</label>
                      <div class="position-relative">
                        <input type="password" class="form-control" name="confirm_password" id="confirm_password" required 
                               minlength="6" placeholder="Digite a senha novamente">
                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3" 
                                id="toggleConfirmPassword" style="border: none; background: none; padding: 0; margin: 0; z-index: 10;">
                          <i class="ti ti-eye" id="eyeIconConfirm"></i>
                        </button>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-3">Redefinir Senha</button>
                    <div class="text-center">
                      <a href="index.php" class="text-decoration-none">Voltar para o login</a>
                    </div>
                  </form>
                <?php else: ?>
                  <div class="alert alert-warning" role="alert">
                    Token inválido ou expirado.
                    <div class="mt-3">
                      <a href="forgot_password.php" class="btn btn-primary">Solicitar Nova Recuperação</a>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="<?= $url; ?>/assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="<?= $url; ?>/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.0.8/dist/iconify-icon.min.js"></script>
  <script>
    // Validação de confirmação de senha
    document.getElementById('confirm_password')?.addEventListener('input', function() {
      const password = document.getElementById('password').value;
      const confirmPassword = this.value;
      
      if (password !== confirmPassword) {
        this.setCustomValidity('As senhas não coincidem');
      } else {
        this.setCustomValidity('');
      }
    });

    // Toggle mostrar/ocultar senha
    document.getElementById('togglePassword')?.addEventListener('click', function() {
      const passwordInput = document.getElementById('password');
      const eyeIcon = document.getElementById('eyeIcon');
      
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('ti-eye');
        eyeIcon.classList.add('ti-eye-off');
      } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('ti-eye-off');
        eyeIcon.classList.add('ti-eye');
      }
    });

    // Toggle mostrar/ocultar confirmação de senha
    document.getElementById('toggleConfirmPassword')?.addEventListener('click', function() {
      const confirmPasswordInput = document.getElementById('confirm_password');
      const eyeIconConfirm = document.getElementById('eyeIconConfirm');
      
      if (confirmPasswordInput.type === 'password') {
        confirmPasswordInput.type = 'text';
        eyeIconConfirm.classList.remove('ti-eye');
        eyeIconConfirm.classList.add('ti-eye-off');
      } else {
        confirmPasswordInput.type = 'password';
        eyeIconConfirm.classList.remove('ti-eye-off');
        eyeIconConfirm.classList.add('ti-eye');
      }
    });
  </script>
</body>
</html>

