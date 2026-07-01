<?php
/**
 * Releev Recruitment — Email helper
 * Uses PHPMailer with Gmail SMTP
 */

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_email($to, $to_name, $subject, $body_html, $reply_to = null, $reply_to_name = null) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($to, $to_name);
        if ($reply_to) $mail->addReplyTo($reply_to, $reply_to_name ?? '');

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body_html;
        $mail->AltBody = strip_tags($body_html);

        $mail->send();
        return ['ok' => true];
    } catch (Exception $e) {
        return ['ok' => false, 'error' => $mail->ErrorInfo];
    }
}

/* ── EMAIL TEMPLATES ────────────────────────── */

function tpl_admin_notification($title, $rows, $message_body = '') {
    $rows_html = '';
    foreach ($rows as $label => $value) {
        if (!$value) continue;
        $rows_html .= '<tr><td style="padding:8px 14px;border-bottom:1px solid #eee;font-weight:600;color:#333;width:140px;">' .
                      htmlspecialchars($label) . '</td><td style="padding:8px 14px;border-bottom:1px solid #eee;color:#555;">' .
                      nl2br(htmlspecialchars($value)) . '</td></tr>';
    }
    $msg_html = $message_body ? '<div style="background:#f4f8ff;padding:18px 22px;border-left:4px solid #0177E3;border-radius:6px;margin-top:20px;color:#333;line-height:1.7;">' . nl2br(htmlspecialchars($message_body)) . '</div>' : '';

    return '<!DOCTYPE html><html><body style="margin:0;background:#f4f8ff;font-family:Arial,sans-serif;">
        <div style="max-width:620px;margin:0 auto;background:#fff;">
            <div style="background:linear-gradient(135deg,#0177E3,#013A73);padding:32px;color:#fff;">
                <h1 style="margin:0;font-size:22px;font-weight:800;">Releev Recruitment</h1>
                <p style="margin:6px 0 0;opacity:0.85;font-size:14px;">' . htmlspecialchars($title) . '</p>
            </div>
            <div style="padding:30px;">
                <table style="width:100%;border-collapse:collapse;font-size:14px;">' . $rows_html . '</table>
                ' . $msg_html . '
                <p style="margin-top:28px;color:#888;font-size:12px;">Login to admin dashboard to manage this submission.</p>
            </div>
            <div style="background:#333;color:#aaa;padding:16px 30px;font-size:12px;text-align:center;">
                © ' . date('Y') . ' Releev Recruitment · Göteborg, Sweden
            </div>
        </div></body></html>';
}

function tpl_user_confirmation($name, $heading, $message) {
    return '<!DOCTYPE html><html><body style="margin:0;background:#f4f8ff;font-family:Arial,sans-serif;">
        <div style="max-width:620px;margin:0 auto;background:#fff;">
            <div style="background:linear-gradient(135deg,#0177E3,#013A73);padding:36px;color:#fff;text-align:center;">
                <h1 style="margin:0;font-size:24px;font-weight:800;">Releev Recruitment</h1>
                <p style="margin:8px 0 0;opacity:0.85;font-size:15px;">' . htmlspecialchars($heading) . '</p>
            </div>
            <div style="padding:36px 30px;color:#333;line-height:1.7;font-size:15px;">
                <p>Hi ' . htmlspecialchars($name) . ',</p>
                ' . $message . '
                <p style="margin-top:32px;">Best regards,<br><strong>The Releev Recruitment Team</strong></p>
            </div>
            <div style="background:#f4f8ff;padding:24px 30px;border-top:1px solid #e0ecfa;color:#666;font-size:13px;">
                📍 address, 1111, city, country.<br>
                📞 0000000 &nbsp;·&nbsp; ✉️ info@company.se
            </div>
            <div style="background:#333;color:#aaa;padding:14px 30px;font-size:12px;text-align:center;">
                © ' . date('Y') . ' Releev Recruitment
            </div>
        </div></body></html>';
}

