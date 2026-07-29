<?php
/**
 * Minimal mail helper for account verification emails.
 *
 * MAIL_MODE:
 *   'log'  (default) — doesn't require any mail server. Every "sent" email is
 *           written to storage/mail_log/ as a plain .txt file so you can open
 *           it and grab the verification link during local development. This
 *           is the safe default for XAMPP/MAMP, which have no MTA configured
 *           out of the box.
 *   'smtp' — attempts PHP's built-in mail() function. Only works if your
 *           server has a working mail transport (sendmail/Postfix/etc. on
 *           Linux, or an SMTP relay configured in php.ini on Windows/XAMPP).
 *
 * Switch modes by changing MAIL_MODE below. For a real production deployment,
 * swap this out for a proper SMTP library (PHPMailer, Symfony Mailer, etc.)
 * — the only thing that needs to change is inside send_mail().
 */
define('MAIL_MODE', 'log');
define('MAIL_FROM', 'no-reply@hpvhsvcompass.local');
define('MAIL_FROM_NAME', 'HPV·HSV Compass');

/**
 * @return array{ok: bool, mode: string, log_file?: string}
 */
function send_mail(string $to, string $subject, string $htmlBody): array {
    if (MAIL_MODE === 'smtp') {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM . ">\r\n";
        $sent = @mail($to, $subject, $htmlBody, $headers);
        if ($sent) return ['ok' => true, 'mode' => 'smtp'];
        // Fall through to log mode if mail() failed, so nothing is silently lost.
    }

    $logDir = __DIR__ . '/../storage/mail_log';
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);

    $filename = date('Y-m-d_His') . '_' . preg_replace('/[^a-z0-9]+/i', '_', $to) . '.html';
    $path = $logDir . '/' . $filename;

    $content = "<!-- To: {$to} -->\n<!-- Subject: " . htmlspecialchars($subject) . " -->\n<!-- Sent: " . date('c') . " -->\n\n{$htmlBody}";
    file_put_contents($path, $content);

    return ['ok' => true, 'mode' => 'log', 'log_file' => 'storage/mail_log/' . $filename];
}

function generate_token(): string {
    return bin2hex(random_bytes(32));
}

/**
 * Creates a fresh verification token for a user, stores it, and sends the email.
 * @return array{ok: bool, error?: string}
 */
function send_verification_email(array $user): array {
    $pdo = get_db();

    // Simple resend cooldown to prevent spam-clicking (60 seconds).
    if (!empty($user['verification_sent_at']) && (time() - strtotime($user['verification_sent_at'])) < 60) {
        $wait = 60 - (time() - strtotime($user['verification_sent_at']));
        return ['ok' => false, 'error' => "Please wait {$wait} more second(s) before requesting another verification email."];
    }

    $token = generate_token();
    $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

    $stmt = $pdo->prepare('UPDATE users SET verification_token = ?, verification_token_expires = ?, verification_sent_at = NOW() WHERE id = ?');
    $stmt->execute([$token, $expires, $user['id']]);

    $link = BASE_URL_ABSOLUTE() . '/verify.php?token=' . $token;

    $body = '
      <div style="font-family:sans-serif; max-width:480px; margin:0 auto;">
        <h2 style="color:#1f5068;">Verify your email</h2>
        <p>Hi ' . htmlspecialchars($user['name']) . ',</p>
        <p>Thanks for creating an account with HPV·HSV Compass. Click the button below to verify your email address:</p>
        <p style="margin:24px 0;">
          <a href="' . $link . '" style="background:linear-gradient(95deg,#2c7da0,#1f5e7e); color:white; padding:12px 24px; border-radius:40px; text-decoration:none; font-weight:bold;">Verify my email</a>
        </p>
        <p style="font-size:13px; color:#5f7a8c;">Or copy this link into your browser:<br>' . $link . '</p>
        <p style="font-size:13px; color:#5f7a8c;">This link expires in 24 hours. If you didn\'t create this account, you can ignore this email.</p>
      </div>';

    $result = send_mail($user['email'], 'Verify your email — HPV·HSV Compass', $body);
    return ['ok' => true, 'mail_mode' => $result['mode'], 'log_file' => $result['log_file'] ?? null];
}

/** Builds an absolute base URL (scheme + host + BASE_URL) for links inside emails, since email clients can't resolve relative paths. */
function BASE_URL_ABSOLUTE(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . BASE_URL;
}
