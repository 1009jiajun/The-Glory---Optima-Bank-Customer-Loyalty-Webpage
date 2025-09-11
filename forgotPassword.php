<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Headers: Content-Type");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

include "db.php";

date_default_timezone_set("Asia/Kuala_Lumpur");

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? null;

if (!$email) {
    echo json_encode(["status" => "error", "message" => "Email is required"]);
    exit;
}

$stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $token = bin2hex(random_bytes(32));
    $createdAt = date("Y-m-d H:i:s");
    $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

    $conn->query("INSERT INTO password_resets (email, token, created_at, expiry) VALUES ('$email','$token','$createdAt','$expiry')");

    // Example: send reset link (use PHPMailer in real projects)
    $resetLink = "http://localhost/TheGlory/resetPassword.html?token=$token";

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'boojiajun98@gmail.com'; 
        $mail->Password   = "gags fjmn ajcl nltr"; // directly use app password;
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('no-reply@theglory.com', 'TheGlory Support');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request - TheGlory';
        $mail->Body    = "Click <a href='$resetLink'>here</a> to reset your password.<br>This link expires in 1 hour.";
        $mail->AltBody = "Reset your password: $resetLink";

        $mail->send();
        echo json_encode(["status" => "success", "message" => "Reset link sent to your email"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Mailer Error: {$mail->ErrorInfo}"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No account found"]);
}

$stmt->close();
$conn->close();
?>
