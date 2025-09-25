<?php
ob_clean();
error_reporting(0);
header('Content-Type: application/json');
session_start();

// ✅ Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Invalid request method. Use POST."]);
    exit;
}

// ✅ Get user input safely
$input = json_decode(file_get_contents("php://input"), true);
$userMessage = isset($input['message']) ? trim($input['message']) : '';

if (empty($userMessage)) {
    echo json_encode(["reply" => "Please enter a message."]);
    exit;
}

// ✅ Connect to database
require_once "db.php"; // <-- use your existing db.php with $conn

// ✅ Prepare default DB results
$userId = 0;
if (isset($input['user_id']) && (int)$input['user_id'] > 0) {
    $userId = (int)$input['user_id'];
} elseif (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0) {
    $userId = (int)$_SESSION['user_id'];
}
$dbData = [
    "points_balance" => null,
    "total_vouchers" => null,
    "last_voucher"   => null,
];

// ✅ Query points balance
$pointsQuery = $conn->prepare("SELECT points FROM user WHERE id = ?");
if ($pointsQuery) {
    $pointsQuery->bind_param("i", $userId);
    $pointsQuery->execute();
    $pointsResult = $pointsQuery->get_result();
    if ($row = $pointsResult->fetch_assoc()) {
        $dbData['points_balance'] = (int)$row['points'];
    }
    $pointsQuery->close();
}

// ✅ Query total vouchers redeemed
$vouchersQuery = $conn->prepare("SELECT COUNT(*) as total FROM cartitemhistory WHERE user_id = ?");
if ($vouchersQuery) {
    $vouchersQuery->bind_param("i", $userId);
    $vouchersQuery->execute();
    $vouchersResult = $vouchersQuery->get_result();
    if ($row = $vouchersResult->fetch_assoc()) {
        $dbData['total_vouchers'] = (int)$row['total'];
    }
    $vouchersQuery->close();
}

// ✅ Query last redeemed voucher
$lastVoucherQuery = $conn->prepare("\n    SELECT v.title, h.completed_date AS redeemed_at\n    FROM cartitemhistory h\n    JOIN voucher v ON h.voucher_id = v.id\n    WHERE h.user_id = ?\n    ORDER BY h.completed_date DESC\n    LIMIT 1\n");
if ($lastVoucherQuery) {
    $lastVoucherQuery->bind_param("i", $userId);
    $lastVoucherQuery->execute();
    $lastVoucherResult = $lastVoucherQuery->get_result();
    if ($row = $lastVoucherResult->fetch_assoc()) {
        $dbData['last_voucher'] = $row;
    }
    $lastVoucherQuery->close();
}

// ✅ Also query total available vouchers in catalog
$availableQuery = $conn->prepare("SELECT COUNT(*) as total FROM voucher");
if ($availableQuery) {
    $availableQuery->execute();
    $availableResult = $availableQuery->get_result();
    if ($row = $availableResult->fetch_assoc()) {
        $dbData['available_vouchers'] = (int)$row['total'];
    }
    $availableQuery->close();
}

$conn->close();

// ✅ Gemini API setup
require_once "config.php";
$apiKey = GEMINI_API_KEY;

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;

$contextText = "Database Context:\n"
    . "Points Balance: " . ($dbData['points_balance'] ?? "Unknown") . "\n"
    . "Total Vouchers Redeemed: " . ($dbData['total_vouchers'] ?? "Unknown") . "\n"
    . "Available Vouchers: " . ($dbData['available_vouchers'] ?? "Unknown") . "\n"
    . "Last Voucher: " . ($dbData['last_voucher']['title'] ?? "No vouchers redeemed yet") . " ("
    . ($dbData['last_voucher']['redeemed_at'] ?? "-") . ")\n";

$payload = [
    "system_instruction" => [
        "parts" => [[
            "text" => "You are Optima Bank’s official AI assistant for the Optima Voucher System.
                Your role includes:
                - Vouchers (earning, redeeming, validity, expiry rules, status, history, totals).
                - Points (balance, usage, deduction, accumulation).
                - Voucher redemption flow and system rules.
                - Optima Bank services directly related to loyalty, rewards, account access, and authentication (login issues, reset password, verification, sign-in problems).
                
                CRITICAL RULES:
                1. If the user asks about the voucher system, always explain the actual system rules:
                • Users earn points from transactions.
                • Points can be redeemed for vouchers.
                • Redeemed vouchers are stored in redeem history.
                • Users can check their balance, total vouchers, and redemption history anytime.
                
                2. If the question matches a predefined function (like 'my points', 'total voucher', 'last voucher', 'available vouchers'), use ONLY the database values provided by the backend. NEVER invent numbers.
                2b. For 'available vouchers', use 'available_vouchers'. For user totals, use 'total_vouchers'.
                
                3. For login/authentication problems or voucher redemption problems, DO NOT refuse. Ask 1–3 clarifying questions if needed and provide concrete troubleshooting steps. Keep replies concise and actionable.
                
                4. If the question is unrelated to vouchers, points, or Optima Bank services (e.g. weather, jokes, general knowledge), reply with:
                'Sorry, I can only help with voucher redemption, login issues, and Optima Bank services.'
                
                5. Be polite, concise, and use short paragraphs or bullet points."
        ]]
    ],
    "contents" => [[
        "role" => "user",
        "parts" => [
            ["text" => $contextText . "\nUser Question: " . $userMessage]
        ]
    ]]
];

// Add lightweight intent hint for the model
$intent = null;
$lowerMsg = strtolower($userMessage);
if (preg_match('/login|log in|sign in|signin|authentication|password|reset password|otp/', $lowerMsg)) {
    $intent = 'login_issue';
} elseif (preg_match('/redeem|redeeming|redemption|voucher code|cannot redeem|can\'t redeem/', $lowerMsg)) {
    $intent = 'redeem_issue';
}
if ($intent) {
    $payload["contents"][0]["parts"][0]["text"] = "Intent: " . $intent . "\n" . $payload["contents"][0]["parts"][0]["text"];
}

// ✅ cURL Request with error handling
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($ch);

if ($response === false) {
    $errorMsg = curl_error($ch);
    curl_close($ch);
    echo json_encode(["reply" => "Error: " . $errorMsg]);
    exit;
}

curl_close($ch);
$data = json_decode($response, true);

if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
    $reply = "AI service returned an unexpected response.";
} else {
    $reply = $data['candidates'][0]['content']['parts'][0]['text'];
}

echo json_encode([
    "reply" => $reply,
    "db_data" => $dbData // Optional: return for debugging in frontend
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>
