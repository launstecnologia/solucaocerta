<?php
require_once '../config/config.php';
session_start();

// Recupera mensagem de erro se existir
$error_message = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']); // Remove a mensagem após exibir

// Mantém o email preenchido se houver erro
$email_value = $_SESSION['login_email'] ?? '';
unset($_SESSION['login_email']);
?>

<!doctype html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sistema - Solução Certa</title>
  <link rel="shortcut icon" type="image/png" href="<?= $url; ?>/assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="<?= $url; ?>/assets/css/styles.min.css" />
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <div
      class="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">
      <div class="d-flex align-items-center justify-content-center w-100">
        <div class="row justify-content-center w-100">
          <div class="col-md-8 col-lg-6 col-xxl-3">
            <div class="card mb-0">
              <div class="card-body">
                <a href="<?= $url; ?>/index.php" class="text-nowrap logo-img text-center d-block py-3 w-100">
                  <img src="<?= $url; ?>/assets/images/logos/logo.png" width="250" alt="">
                </a>
                <p class="text-center">Sistema da Solução Certa</p>
                
                <?php if ($error_message): ?>
                  <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="ti ti-alert-circle"></i> Erro:</strong> <?= htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
                <?php endif; ?>
                
                <form action="authenticate.php" method="post">
                  <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" name="email" id="email" 
                           value="<?= htmlspecialchars($email_value); ?>" required autofocus>
                  </div>
                  <div class="mb-4">
                    <label for="password" class="form-label">Senha</label>
                    <div class="position-relative">
                      <input type="password" name="password" class="form-control" id="password" required>
                      <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3" 
                              id="togglePassword" style="border: none; background: none; padding: 0; margin: 0; z-index: 10;">
                        <i class="ti ti-eye" id="eyeIcon"></i>
                      </button>
                    </div>
                    <div class="text-end mt-2">
                      <a href="forgot_password.php" class="text-decoration-none small">Esqueci minha senha</a>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-4">ENTRAR</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="<?= $url; ?>/assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="<?= $url; ?>/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
  <script>
    // Auto-dismiss do alert após 5 segundos
    setTimeout(function() {
      var alert = document.querySelector('.alert');
      if (alert) {
        var bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
      }
    }, 5000);

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
  </script>
</body>

</html>