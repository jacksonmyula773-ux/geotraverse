<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $newPassword = $_POST['password'];
    
    $mail = new PHPMailer(true);
    
    try {
        // SMTP Configuration (GMAIN KWA MFANO)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your_email@gmail.com';     // ← EMAIL YAKO
        $mail->Password   = 'your_app_password';        // ← APP PASSWORD (sio password ya kawaida)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('admin@geotraverse.com', 'GeoTraverse Admin');
        $mail->addAddress($email);
        
        // Email Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset - GeoTraverse Admin Panel';
        $mail->Body = '
            <html>
            <head>
                <style>
                    .container { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #0f74ba; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { background: #f4f4f4; padding: 30px; border-radius: 0 0 10px 10px; }
                    .password { font-size: 24px; font-weight: bold; color: #0f74ba; background: white; padding: 10px; text-align: center; border-radius: 5px; margin: 20px 0; }
                    .button { background: #0f74ba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h2>GeoTraverse Admin Panel</h2>
                    </div>
                    <div class="content">
                        <h3>Password Reset Request</h3>
                        <p>We received a request to reset your password. Here is your new password:</p>
                        <div class="password">' . $newPassword . '</div>
                        <p>Use this password to login to your account.</p>
                        <p style="margin-top: 20px;">
                            <a href="https://yourdomain.com/admin" class="button">Login to Dashboard</a>
                        </p>
                        <p style="margin-top: 20px; font-size: 12px; color: #666;">
                            For security reasons, please change this password after logging in.
                        </p>
                    </div>
                </div>
            </body>
            </html>
        ';
        
        $mail->send();
        echo json_encode(['success' => true, 'message' => 'Email sent successfully']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $mail->ErrorInfo]);
    }
}
?>