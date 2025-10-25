<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ✅ Rutas exactas según tu estructura real
require __DIR__ . '/controllers/PHPMailer/src/Exception.php';
require __DIR__ . '/controllers/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/controllers/PHPMailer/src/SMTP.php';
class Email {
    private string $smtpHost = 'smtp.gmail.com';
    private int $smtpPort = 587;
    private string $smtpUser = 'geralldbrt@gmail.com';
    private string $smtpPass = 'jnruyehnsyfajueg'; // App Password de Gmail
    private string $fromEmail = 'geralldbrt@gmail.com';
    private string $fromName  = 'Gestor Escolar';
    private int $debugLevel = 0;
    private string $debugOutput = 'html';

    public function enviarCorreo(string $destinatario, string $asunto, string $mensaje): bool {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $this->smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->smtpUser;
            $mail->Password   = $this->smtpPass;
            $mail->SMTPSecure = 'tls';
            $mail->Port       = $this->smtpPort;
            $mail->CharSet    = 'UTF-8';

            $mail->SMTPDebug  = $this->debugLevel;
            $mail->Debugoutput = $this->debugOutput;

            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($destinatario);

            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $mensaje;

            $mail->send();
            echo "✅ Correo enviado correctamente.";
            return true;
        } catch (Exception $e) {
            echo "❌ Error al enviar correo: {$mail->ErrorInfo}";
            return false;
        }
    }
}
