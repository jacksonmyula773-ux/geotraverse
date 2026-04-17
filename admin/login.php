<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $newPassword = $_POST['password'];
    
    $mail = new PHPMailer(true);
    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your_email@gmail.com';
        $mail->Password   = 'your_app_password';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('admin@geotraverse.com', 'GeoTraverse Admin');
        $mail->addAddress($email);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset - GeoTraverse Admin';
        $mail->Body    = "
            <h2>Password Reset Request</h2>
            <p>Your new password is: <strong style='font-size:18px;'>{$newPassword}</strong></p>
            <p>Login here: <a href='https://geotraverse.com/admin'>GeoTraverse Admin</a></p>
            <p>Please change your password after logging in.</p>
        ";
        
        $mail->send();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $mail->ErrorInfo]);
    }
}
?>