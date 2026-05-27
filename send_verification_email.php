<?php
require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';
require_once 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendVerificationEmail($to_email, $name, $verification_link) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'isip_t.v.belikova@mpt.ru';
        $mail->Password   = 'xwvc bnyb wtad btus';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port       = 587;
        
        $mail->setFrom('isip_t.v.belikova@mpt.ru', 'Electric Dough');
        $mail->addAddress($to_email, $name);
        
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Подтверждение регистрации - Electric Dough';
        
        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #53161D; padding: 20px; text-align: center; color: white; }
                .content { padding: 30px; background: #f5f5f5; }
                .button { 
                    display: inline-block; 
                    padding: 12px 30px; 
                    background: #53161D; 
                    color: white; 
                    text-decoration: none; 
                    border-radius: 5px;
                    margin: 20px 0;
                }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Electric Dough</h2>
                    <p>Рок-пекарня</p>
                </div>
                <div class="content">
                    <h3>Добро пожаловать, ' . htmlspecialchars($name) . '!</h3>
                    <p>Спасибо за регистрацию в нашей рок-пекарне!</p>
                    <p>Для завершения регистрации и активации аккаунта, пожалуйста, подтвердите свой email, нажав на кнопку ниже:</p>
                    <div style="text-align: center;">
                        <a href="' . $verification_link . '" class="button">Подтвердить email</a>
                    </div>
                    <p>Или скопируйте ссылку в браузер:</p>
                    <p style="word-break: break-all; font-size: 12px;">' . $verification_link . '</p>
                    <p>Ссылка действительна в течение 24 часов.</p>
                    <p>Если вы не регистрировались на нашем сайте, просто проигнорируйте это письмо.</p>
                </div>
                <div class="footer">
                    <p>&copy; 2026 Electric Dough. Все права защищены.</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        $mail->AltBody = "Добро пожаловать, $name!\n\nДля подтверждения регистрации перейдите по ссылке: " . $verification_link;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Письмо не отправлено. Ошибка: {$mail->ErrorInfo}");
        return false;
    }
}

function getVerificationLink($token) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . '://' . $host . '/verify_email.php?token=' . $token;
}
?>