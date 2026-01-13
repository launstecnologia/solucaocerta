<?php
/**
 * Configuração do PHPMailer
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function getMailer() {
    $mail = new PHPMailer(true);
    
    try {
        // Configurações do servidor
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port = MAIL_PORT;
        $mail->CharSet = 'UTF-8';
        
        // Configurações de SSL/TLS para resolver problemas de certificado
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Timeout de conexão
        $mail->Timeout = 30;
        
        // Remetente
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        
        // Configurações adicionais
        $mail->isHTML(true);
        
        return $mail;
    } catch (Exception $e) {
        error_log("Erro ao configurar PHPMailer: " . $mail->ErrorInfo);
        throw new Exception("Erro ao configurar sistema de e-mail");
    }
}

