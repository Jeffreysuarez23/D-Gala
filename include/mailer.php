<?php
use PHPMailer\PHPMailer\{PHPMailer, SMTP, Exception};

// Incluimos solo una vez las clases de PHPMailer
require_once __DIR__ . '/../phpmailer/src/Exception.php';
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';

class Mailer {
    public function enviarEmail($email, $asunto, $cuerpo) {
        // Configuración general
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8'; 

        try {
            // Configuración del servidor SMTP
            $mail->SMTPDebug = SMTP::DEBUG_OFF;
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'sandovaldgala@gmail.com';   // tu correo Gmail
            $mail->Password   = 'urgh coeb nchu zdoj';       // tu App Password de Gmail (no tu contraseña real)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // Remitente y destinatario
            $mail->setFrom('sandovaldgala@gmail.com', 'Dgala Importaciones Sandoval');
            $mail->addAddress($email);

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $cuerpo; // ya no usamos utf8_decode
            $mail->AltBody = strip_tags($cuerpo); // versión de texto plano

            // Enviar correo
            if ($mail->send()) {
                return true;
            } else {
                error_log("Error al enviar correo: {$mail->ErrorInfo}");
                return false;
            }

        } catch (Exception $e) {
            error_log("Error al enviar el correo electrónico: {$mail->ErrorInfo}");
            return false;
        }
    }
}
