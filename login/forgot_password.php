<?php
require_once '../config/config.php';

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (empty($email)) {
        $error = "Por favor, informe seu e-mail.";
    } else {
        // Verifica se o usuário existe
        $sql = "SELECT id, nome FROM usuario WHERE email = ? AND status = 'ativo'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Gera token único
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Salva o token no banco
            $sql_token = "INSERT INTO password_reset_tokens (email, token, expires_at) VALUES (?, ?, ?)";
            $stmt_token = $conn->prepare($sql_token);
            $stmt_token->bind_param("sss", $email, $token, $expires_at);
            
            if ($stmt_token->execute()) {
                // Envia email com o link de recuperação
                require_once '../vendor/autoload.php';
                require_once '../config/mail_config.php';
                
                $reset_link = $url . "/login/reset_password.php?token=" . $token;
                
                try {
                    $mail = getMailer();
                    $mail->addAddress($email, $user['nome']);
                    $mail->Subject = 'Recuperação de Senha - Solução Certa';
                    $mail->Body = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
                            .content { padding: 20px; background-color: #f9f9f9; }
                            .button { display: inline-block; padding: 12px 24px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h2>Recuperação de Senha</h2>
                            </div>
                            <div class='content'>
                                <p>Olá, <strong>{$user['nome']}</strong>!</p>
                                <p>Você solicitou a recuperação de senha para sua conta no sistema Solução Certa.</p>
                                <p>Clique no botão abaixo para redefinir sua senha:</p>
                                <p style='text-align: center;'>
                                    <a href='{$reset_link}' class='button'>Redefinir Senha</a>
                                </p>
                                <p>Ou copie e cole o link abaixo no seu navegador:</p>
                                <p style='word-break: break-all; color: #007bff;'>{$reset_link}</p>
                                <p><strong>Este link expira em 1 hora.</strong></p>
                                <p>Se você não solicitou esta recuperação, ignore este e-mail.</p>
                            </div>
                            <div class='footer'>
                                <p>Solução Certa - Sistema de Gestão</p>
                                <p>Este é um e-mail automático, por favor não responda.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                    ";
                    $mail->AltBody = "Olá, {$user['nome']}!\n\nVocê solicitou a recuperação de senha. Acesse o link abaixo para redefinir sua senha:\n\n{$reset_link}\n\nEste link expira em 1 hora.\n\nSe você não solicitou esta recuperação, ignore este e-mail.";
                    
                    if ($mail->send()) {
                        $message = "Um e-mail com as instruções para recuperação de senha foi enviado para {$email}.";
                    } else {
                        $error = "Erro ao enviar e-mail: " . $mail->ErrorInfo;
                        error_log("Erro PHPMailer: " . $mail->ErrorInfo);
                    }
                } catch (Exception $e) {
                    $error = "Erro ao enviar e-mail. Por favor, verifique as configurações de e-mail ou tente novamente mais tarde.";
                    error_log("Exceção PHPMailer: " . $e->getMessage());
                }
            } else {
                $error = "Erro ao processar solicitação. Por favor, tente novamente.";
            }
            
            $stmt_token->close();
        } else {
            // Por segurança, não revela se o email existe ou não
            $message = "Se o e-mail informado estiver cadastrado, você receberá um link para recuperação de senha.";
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
  <title>Esqueci minha senha - Solução Certa</title>
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
                <p class="text-center">Recuperação de Senha</p>
                
                <?php if ($message): ?>
                  <div class="alert alert-success" role="alert">
                    <?= $message; ?>
                  </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                  <div class="alert alert-danger" role="alert">
                    <?= $error; ?>
                  </div>
                <?php endif; ?>
                
                <form action="forgot_password.php" method="post">
                  <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" name="email" id="email" required 
                           placeholder="Digite seu e-mail cadastrado">
                    <small class="form-text text-muted">Enviaremos um link para redefinir sua senha.</small>
                  </div>
                  <button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-3">Enviar Link de Recuperação</button>
                  <div class="text-center">
                    <a href="index.php" class="text-decoration-none">Voltar para o login</a>
                  </div>
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
</body>
</html>

