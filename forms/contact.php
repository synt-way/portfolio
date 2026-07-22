<?php
header('Content-Type: text/plain');

// Allow POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// Get form data
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validate
if ($name === '' || $email === '' || $subject === '' || $message === '') {
    http_response_code(400);
    echo 'All fields are required.';
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo 'Invalid email address.';
    exit;
}

/* =========================
   1) SEND MESSAGE TO YOU
   ========================= */

$to = 'deepak@syntway.org';

$headers  = "From: {$name} <deepak@syntway.org>\r\n";
$headers .= "Reply-To: {$email}\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$body =
    "New Contact Message\n\n" .
    $message;

mail($to, $subject, $body, $headers);


/* =========================
   2) ONE-TIME AUTO-REPLY
   ========================= */

$logFile = __DIR__ . '/contacts.log';
$sentBefore = false;

// Check if user already contacted
if (file_exists($logFile)) {
    $emails = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (in_array($email, $emails)) {
        $sentBefore = true;
    }
}

// Send welcome email ONLY first time
if (!$sentBefore) {

    $autoSubject = "Welcome to Syntway Private Limited";

   $autoMessage =
    "Hi {$name},\n\n" .
    "Thank you for contacting Syntway Private Limited.\n\n" .
    "We have successfully received your message and appreciate you reaching out to us. " .
    "Your inquiry has been noted and forwarded for review, and our team will respond as soon as possible.\n\n" .
    "At Syntway Private Limited, we focus on delivering reliable and practical technology solutions aligned with real business needs. " .
    "Your message is important to us, and we will ensure it is handled appropriately.\n\n" .
    "If you have any additional details to share, you may reply directly to this email.\n\n" .
    "Regards,\n" .
    "Deepak Kumar\n" .
    "Syntway Private Limited\n" .
    "deepak@syntway.org\n\n" .
    "If you did not submit this message, please ignore this email.";

    $autoHeaders  = "From: Syntway <deepak@syntway.org>\r\n";
    $autoHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";

    mail($email, $autoSubject, $autoMessage, $autoHeaders);

    // Save email so auto-reply is not sent again
    file_put_contents($logFile, $email . PHP_EOL, FILE_APPEND | LOCK_EX);
}

/* =========================
   3) RESPONSE FOR AJAX
   ========================= */

echo 'OK';
