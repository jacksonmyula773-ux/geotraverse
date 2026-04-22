<?php
$to = "jacksonmyula773@gmail.com";
$subject = "Test Email";
$message = "This is a test email from XAMPP!";
$headers = "From: test@geotraverse.com";

if(mail($to, $subject, $message, $headers)) {
    echo "✅ Email sent successfully!";
} else {
    echo "❌ Email failed to send.";
}
?>