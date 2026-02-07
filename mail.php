<?php
error_log("RUNNING: NEW MAIL FILE v3 " . time());

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

require 'vendor/autoload.php';


// Set JSON header for AJAX response
header('Content-Type: application/json');

// Function to send JSON response
function sendResponse($status, $message, $data = []) {
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}


// Function to create virtual card HTML
function createVirtualCardHTML($name, $cardNumber, $enrollmentDate) {

    return "
    <div style='
        width: 430px;
        height: 260px;
        margin: 0 auto;
        padding: 25px;
        border-radius: 18px;
        background: linear-gradient(135deg,#4b6cb7,#182848);
        color: #fff;
        font-family: Arial, sans-serif;
        position: relative;
        box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    '>

        <!-- Logo -->
        <div style='font-size: 18px; font-weight: 700; letter-spacing: 0.5px;'>
            ForgeIndia Connect
        </div>
        <div style='font-size: 10px; opacity: 0.9; margin-top: 3px;'>Career Guidance Member Card</div>

        <!-- Card Number -->
        <div style='
            margin-top: 35px;
            font-size: 32px;
            letter-spacing: 6px;
            font-weight: bold;
            color: #fed201;
        '>
            {$cardNumber}
        </div>

        <!-- Cardholder Info -->
        <div style='margin-top: 28px; display: flex; justify-content: space-between;'>

            <div>
                <div style='font-size: 10px; opacity: 0.6;'>CARD HOLDER</div>
                <div style='font-size: 16px; font-weight: 600; margin-top: 3px;'>
                    " . strtoupper($name) . "
                </div>
            </div>

            <div>
                <div style='font-size: 10px; opacity: 0.6;'>ENROLLED</div>
                <div style='font-size: 14px; font-weight: 600; margin-top: 3px;'>
                    {$enrollmentDate}
                </div>
            </div>

        </div>

        <!-- Active Status -->
        <div style='
            position: absolute;
            bottom: 18px;
            right: 25px;
            font-size: 12px;
            color: #4ade80;
            font-weight: bold;
        '>
            ● ACTIVE
        </div>

    </div>
    ";
}


// Get and sanitize form data
$name    = htmlspecialchars(trim($_POST['fullname'] ?? ''));
$email   = htmlspecialchars(trim($_POST['email'] ?? ''));
$subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
$message = htmlspecialchars(trim($_POST['message'] ?? ''));
$payment = htmlspecialchars(trim($_POST['payment_id'] ?? ''));

// Validate required fields
if (empty($name) || empty($email) || empty($message)) {
    sendResponse('error', 'Please fill all required fields.');
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse('error', 'Please provide a valid email address.');
}

// ================= RECAPTCHA VERIFICATION =================
$recaptchaSecret = '6Lfidi4sAAAAAMXlw1nEtFlasfkbt09U6VbYK2XR';
$recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

// Allow localhost bypass
if ($recaptchaResponse === "localhost_bypass_token") {
    // Skip verification for localhost testing
    error_log("Recaptcha: Localhost bypass accepted.");
} else {
    // Real verification
    if (empty($recaptchaResponse)) {
        sendResponse('error', 'Please complete the reCAPTCHA verification.');
    }

    $verifyURL = 'https://www.google.com/recaptcha/api/siteverify';
    $postData = http_build_query([
        'secret' => $recaptchaSecret,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ]);

    // Use curl to verify
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $verifyURL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $responseData = json_decode($response, true);

    if (!$responseData['success'] || $responseData['score'] < 0.5 || $responseData['action'] !== 'submit') {
        error_log("Recaptcha Failed: " . print_r($responseData, true));
        sendResponse('error', 'Security verification failed. Please try again.');
    }
}
// ==========================================================

// Handle file upload if present
$resumeAttached = false;
$resumeError = '';
if (!empty($_FILES['resume']['name'])) {
    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $fileType = $_FILES['resume']['type'];
    $fileSize = $_FILES['resume']['size'];
    
    if (!in_array($fileType, $allowedTypes)) {
        sendResponse('error', 'Please upload a PDF or DOC/DOCX file.');
    }
    
    if ($fileSize > 26214400) { // 25MB limit (25 * 1024 * 1024)
        $fileSizeMB = round($fileSize / (1024 * 1024), 2);
        sendResponse('error', "File size ({$fileSizeMB}MB) exceeds the maximum limit of 25MB. Please upload a smaller file.");
    }
    
    $resumeAttached = true;
}

// Check if this is a paid enrollment
$isPaidEnrollment = !empty($payment);

// Generate virtual card details for paid enrollments
function generateVirtualCardNumber() {
    $file = 'virtual_card_counter.txt';

    // If file doesn't exist, create it starting at 9000
    if (!file_exists($file)) {
        file_put_contents($file, "9000");
    }

    // Open the file with read+write mode
    $handle = fopen($file, "c+");

    if (flock($handle, LOCK_EX)) {  // Lock file to prevent race conditions
        // Read the current last number
        $lastNumber = intval(trim(fread($handle, 100)));

        // Generate next number
        $nextNumber = $lastNumber + 1;

        // Move pointer to start of file & write updated number
        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, $nextNumber);

        fflush($handle);
        flock($handle, LOCK_UN);
    }

    fclose($handle);

    return $nextNumber;
}

$virtualCardNumber = null;
$enrollmentDate = date('d M Y');

if ($isPaidEnrollment) {

    // Generate the next sequential Member ID (9001, 9002, 9003...)
    $virtualCardNumber = generateVirtualCardNumber();
    
    // Log entry (with LOCK_EX to prevent race conditions)
    $logEntry = date('Y-m-d H:i:s') . " | Card: " . $virtualCardNumber . " | Name: " . $name . " | Email: " . $email . " | Payment: " . $payment . "\n";
    
    file_put_contents('virtual_cards_log.txt', $logEntry, FILE_APPEND | LOCK_EX);
}


try {
    // ================= SEND TO COMPANY =====================
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.office365.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'info@forgeindiaconnect.com';
    $mail->Password   = 'wtnmtpwhwjwcqczl';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    // Recipients
    $mail->setFrom('info@forgeindiaconnect.com', 'ForgeIndia Connect Website');
    $mail->addAddress('info@forgeindiaconnect.com', 'ForgeIndia Connect');
    $mail->addReplyTo($email, $name);

    // Attach resume if uploaded
    if ($resumeAttached && isset($_FILES['resume']['tmp_name'])) {
        $mail->addAttachment($_FILES['resume']['tmp_name'], $_FILES['resume']['name']);
    }

    // Content
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    
    // Different subject based on enrollment type
    if ($isPaidEnrollment) {
        $mail->Subject = '🎯 PAID ENROLLMENT - Member Card #' . $virtualCardNumber . ' - ' . $name;
    } else {
        $mail->Subject = !empty($subject) ? $subject : 'New Contact Form Submission from ' . $name;
    }
    
    // Build HTML email body
    $emailBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: " . ($isPaidEnrollment ? '#28a745' : '#0053b0') . "; color: white; padding: 20px; text-align: center; }
            .content { background: #f4f4f4; padding: 20px; margin-top: 20px; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #0053b0; }
            .highlight { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; }
            .payment-box { background: #d4edda; padding: 20px; border-left: 4px solid #28a745; margin: 20px 0; }
            .card-box { background: #e7f3ff; padding: 20px; border-left: 4px solid #667eea; margin: 20px 0; border-radius: 5px; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
            .urgent { color: #dc3545; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>";
    
    if ($isPaidEnrollment) {
        $emailBody .= "<h2>🎯 PAID CAREER GUIDANCE ENROLLMENT</h2>";
    } else {
        $emailBody .= "<h2>New Contact Form Submission</h2>";
    }
    
    $emailBody .= "
            </div>
            <div class='content'>";
    
    if ($isPaidEnrollment) {
        $emailBody .= "
                <div class='payment-box'>
                    <h3 style='margin-top:0; color:#28a745;'>✅ Payment Received - Enrollment Confirmed</h3>
                    <p><strong>Enrollment Fee:</strong> ₹1,500.00</p>
                    <p><strong>Payment ID:</strong> {$payment}</p>
                    <p><strong>Payment Status:</strong> <span style='color:#28a745;'>SUCCESSFUL</span></p>
                    <p class='urgent'>⏰ ACTION REQUIRED: This applicant has paid for dedicated career guidance. Priority follow-up needed!</p>
                </div>
                
                <div class='card-box'>
                    <h3 style='margin-top:0; color:#667eea;'>💳 Virtual Member Card Generated</h3>
                    <p><strong>Member ID:</strong> <span style='font-size: 24px; color: #667eea; font-weight: bold;'>{$virtualCardNumber}</span></p>
                    <p><strong>Cardholder:</strong> {$name}</p>
                    <p><strong>Enrollment Date:</strong> {$enrollmentDate}</p>
                    <p style='margin-top: 15px; padding: 10px; background: #fff; border-left: 3px solid #ffc107;'>
                        <strong>Note:</strong> This virtual card has been sent to the applicant's email. Use this Member ID for tracking and priority support.
                    </p>
                </div>";
    }
    
    $emailBody .= "
                <h3>Applicant Information:</h3>
                <div class='field'>
                    <span class='label'>Name:</span> {$name}
                </div>
                <div class='field'>
                    <span class='label'>Email:</span> <a href='mailto:{$email}'>{$email}</a>
                </div>
                <div class='field'>
                    <span class='label'>Subject:</span> {$subject}
                </div>
                <div class='field'>
                    <span class='label'>Message:</span><br>
                    " . nl2br($message) . "
                </div>";
    
    if ($resumeAttached) {
        $emailBody .= "
                <div class='field'>
                    <span class='label'>Resume:</span> 📎 Attached to this email
                </div>";
    }
    
    if ($isPaidEnrollment) {
        $emailBody .= "
                <div class='highlight'>
                    <h3 style='margin-top:0;'>📋 Next Steps:</h3>
                    <ol>
                        <li>Review the attached resume</li>
                        <li>Verify payment in Razorpay dashboard (Payment ID: {$payment})</li>
                        <li><strong>Save Member ID {$virtualCardNumber} in your records</strong></li>
                        <li>Contact applicant within 24 hours</li>
                        <li>Schedule career guidance consultation</li>
                        <li>Provide dedicated support as per enrollment package</li>
                    </ol>
                </div>";
    }
    
    $emailBody .= "
            </div>
            <div class='footer'>
                <p>This email was sent from the ForgeIndia Connect website contact form</p>
                <p>Received on: " . date('F j, Y, g:i a') . "</p>
            </div>
        </div>
    </body>
    </html>";
    
    $mail->Body = $emailBody;
    $mail->AltBody = strip_tags(str_replace('<br>', "\n", $emailBody));

    // Send the email
    $mail->send();

    // ================= SEND AUTO-REPLY =====================
    $autoReply = new PHPMailer(true);
    
    $autoReply->isSMTP();
    $autoReply->Host       = 'smtp.office365.com';
    $autoReply->SMTPAuth   = true;
    $autoReply->Username   = 'info@forgeindiaconnect.com';
    $autoReply->Password   = 'wtnmtpwhwjwcqczl';
    $autoReply->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $autoReply->Port       = 587;
    
    $autoReply->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    // Recipients
    $autoReply->setFrom('info@forgeindiaconnect.com', 'ForgeIndia Connect');
    $autoReply->addAddress($email, $name);

    // Content
    $autoReply->isHTML(true);
    $autoReply->CharSet = 'UTF-8';
    
    if ($isPaidEnrollment) {
        $autoReply->Subject = '✅ Payment Confirmed - Your Virtual Member Card #' . $virtualCardNumber;
    } else {
        $autoReply->Subject = 'Thank you for contacting ForgeIndia Connect';
    }
    
    $autoReplyBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #ffffff; }
            .header { background: " . ($isPaidEnrollment ? '#28a745' : '#0053b0') . "; color: white; padding: 30px; text-align: center; border-radius: 5px 5px 0 0; }
            .content { padding: 30px; background: #f9f9f9; }
            .payment-success { background: #d4edda; padding: 20px; border-left: 4px solid #28a745; margin: 20px 0; border-radius: 5px; }
            .info-box { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; border-radius: 5px; }
            .footer { text-align: center; margin-top: 30px; padding: 20px; background: #333; color: white; border-radius: 0 0 5px 5px; }
            .button { display: inline-block; padding: 12px 30px; background: #fed201; color: #000; text-decoration: none; border-radius: 5px; margin-top: 20px; font-weight: bold; }
            ul { padding-left: 20px; }
            li { margin-bottom: 10px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>";
    
    if ($isPaidEnrollment) {
        $autoReplyBody .= "
                <h1>🎉 Enrollment Confirmed!</h1>
                <p style='margin:10px 0 0 0; font-size:16px;'>Welcome to ForgeIndia Connect Career Guidance Program</p>";
    } else {
        $autoReplyBody .= "<h1>Thank You for Contacting Us!</h1>";
    }
    
    $autoReplyBody .= "
            </div>
            <div class='content'>
                <p>Dear <strong>{$name}</strong>,</p>";
    
    if ($isPaidEnrollment) {
        $autoReplyBody .= "
                <div class='payment-success'>
                    <h3 style='margin-top:0; color:#28a745;'>✅ Payment Successful</h3>
                    <p><strong>Transaction ID:</strong> {$payment}</p>
                    <p><strong>Amount Paid:</strong> ₹1,500.00</p>
                    <p><strong>Service:</strong> Dedicated Career Guidance Support</p>
                    <p><strong>Date:</strong> " . date('F j, Y, g:i a') . "</p>
                </div>
                
                <h2 style='text-align: center; color: #667eea; margin: 30px 0 20px 0;'>💳 Your Virtual Member Card</h2>
                
                " . createVirtualCardHTML($name, $virtualCardNumber, $enrollmentDate) . "
                
                <h3>🎯 What's Next?</h3>
                <p>Thank you for enrolling in our <strong>Dedicated Career Guidance Program</strong>! We're excited to help you achieve your career goals.</p>
                
                <div class='info-box'>
                    <h4 style='margin-top:0;'>📞 We Will Contact You Soon</h4>
                    <p>Our career guidance team will reach out to you within <strong>24 hours</strong> to:</p>
                    <ul>
                        <li>Review your resume and career aspirations</li>
                        <li>Schedule your first consultation session</li>
                        <li>Discuss your personalized career roadmap</li>
                        <li>Answer any questions you may have</li>
                    </ul>
                    <p><strong>Important:</strong> Please keep your Member ID <span style='background: #f0f0f0; padding: 5px 10px; border-radius: 3px; font-weight: bold; color: #667eea;'>{$virtualCardNumber}</span> handy when you contact us for priority support!</p>
                </div>
                
                <h3>📋 What's Included in Your Enrollment:</h3>
                <ul>
                    <li>✅ One-on-one career consultation sessions</li>
                    <li>✅ Personalized job search strategy</li>
                    <li>✅ Resume review and optimization</li>
                    <li>✅ Interview preparation and guidance</li>
                    <li>✅ Industry insights and career advice</li>
                    <li>✅ Job opportunity recommendations</li>
                    <li>✅ Direct support from our career experts</li>
                    <li>✅ Priority support with your Member ID</li>
                </ul>
                
                <div style='background: #f0f9ff; padding: 20px; border-radius: 10px; border-left: 4px solid #0ea5e9; margin: 25px 0;'>
                    <h4 style='margin-top:0; color: #0369a1;'>💡 Pro Tip</h4>
                    <p style='margin: 0; color: #0c4a6e;'>Save a screenshot of your virtual member card for quick access. You can also download this email and show your card anytime!</p>
                </div>
                
                <p><strong>Your Application Summary:</strong></p>
                <p style='background: white; padding: 15px; border-left: 4px solid #0053b0; border-radius: 5px;'>
                    " . nl2br(substr($message, 0, 200)) . (strlen($message) > 200 ? '...' : '') . "
                </p>
                
                <div class='info-box'>
                    <h4 style='margin-top:0;'>💼 Need Immediate Assistance?</h4>
                    <p>If you have any urgent questions, feel free to contact us:</p>
                    <p>
                        📞 Phone: <strong>+91 63694-06416</strong><br>
                        📧 Email: <strong>info@forgeindiaconnect.com</strong><br>
                        💳 Your Member ID: <strong>{$virtualCardNumber}</strong>
                    </p>
                </div>";
    } else {
        $autoReplyBody .= "
                <p>Thank you for reaching out to ForgeIndia Connect. We have received your message and appreciate your interest in our services.</p>
                <p>Our team will review your inquiry and get back to you within 24-48 hours.</p>
                <p><strong>Your Message Summary:</strong></p>
                <p style='background: white; padding: 15px; border-left: 4px solid #0053b0; border-radius: 5px;'>
                    " . nl2br(substr($message, 0, 200)) . (strlen($message) > 200 ? '...' : '') . "
                </p>";
    }
    
    $autoReplyBody .= "
                <h3>🌟 Our Services</h3>
                <ul>
                    <li><strong>Job Consulting</strong> - Expert career guidance and job placement</li>
                    <li><strong>Insurance Services</strong> - Comprehensive insurance solutions</li>
                    <li><strong>Digital Marketing</strong> - Grow your online presence</li>
                    <li><strong>App Development</strong> - Custom mobile applications</li>
                    <li><strong>Website Development</strong> - Professional web solutions</li>
                </ul>
                
                <center>
                    <a href='https://forgeindiaconnect.com' class='button'>Visit Our Website</a>
                </center>
            </div>
            <div class='footer'>
                <p><strong>ForgeIndia Connect</strong></p>
                <p>No 10-I KNT Manickam Road, New bus stand<br>
                Krishnagiri-635001</p>
                <p>📞 +91 63694-06416<br>
                📧 info@forgeindiaconnect.com</p>
                <p style='margin-top:15px; font-size:12px;'>
                    © 2025 ForgeIndia Connect. All rights reserved.
                </p>
            </div>
        </div>
    </body>
    </html>";
    
    $autoReply->Body = $autoReplyBody;
    $autoReply->AltBody = strip_tags(str_replace(['<br>', '</p>'], "\n", $autoReplyBody));

    // Send auto-reply
    $autoReply->send();

    // Prepare response data
    $responseData = [];
    if ($isPaidEnrollment) {
        $responseData['virtual_card_number'] = $virtualCardNumber;
    }

    // Send success response
    sendResponse('success', 'Thank you for contacting us! We will get back to you soon.', $responseData);

} catch (Exception $e) {
    // Log error for debugging
    error_log('Mail Error: ' . $e->getMessage());
    
    // Send user-friendly error response
    sendResponse('error', 'Sorry, there was an error sending your message. Please try again later or contact us directly at info@forgeindiaconnect.com');
}
?>