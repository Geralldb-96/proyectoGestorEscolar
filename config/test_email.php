<?php
require __DIR__ . '/Email.php';

echo "Intentando enviar correo...<br>";

$email = new Email();
$destinatario = 'geralldbrt@gmail.com';
$asunto = 'Prueba de notificación desde Gestor Escolar';
$mensaje = '<h3>Hola 👋</h3><p>Este es un correo de prueba enviado desde tu sistema PHP con PHPMailer.</p>';

if ($email->enviarCorreo($destinatario, $asunto, $mensaje)) {
    echo "✅ Correo enviado correctamente.";
} else {
    echo "❌ Error al enviar el correo.";
}
?>
