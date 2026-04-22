<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

function sendResetEmail($to, $resetLink, $userName) {
    $mail = new PHPMailer(true);
    
    try {
        // Mailtrap SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.mailtrap.io';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'YOUR_MAILTRAP_USERNAME';  // Badilisha
        $mail->Password   = 'YOUR_MAILTRAP_PASSWORD';  // Badilisha
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 2525;
        
        // Recipients
        $mail->setFrom('noreply@geotraverse.com', 'GeoTraverse');
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request - GeoTraverse';
        $mail->Body    = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 10px;'>
            <div style='text-align: center; margin-bottom: 20px;'>
                <img src='https://i.postimg.cc/MT6jTVHh/weblogo.png' alt='Logo' style='width: 60px;'>
                <h2 style='color: #0f74ba;'>GeoTraverse</h2>
            </div>
            <h3>Password Reset Request</h3>
            <p>Hello <strong>" . htmlspecialchars($userName) . "</strong>,</p>
            <p>We received a request to reset your password.</p>
            <p>Click the button below to reset your password:</p>
            <div style='text-align: center; margin: 25px 0;'>
                <a href='" . $resetLink . "' style='background: #0f74ba; color: white; padding: 10px 25px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a>
            </div>
            <p>This link will expire in <strong>1 hour</strong>.</p>
            <p>If you didn't request this, please ignore this email.</p>
            <hr>
            <p style='font-size: 12px; color: #666; text-align: center;'>GeoTraverse - Building Tomorrow's Legacy Today</p>
        </div>
        ";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>