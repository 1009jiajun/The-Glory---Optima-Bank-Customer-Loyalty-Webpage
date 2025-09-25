<?php
// ai_chatbot.php (Improved Version)
session_start();

header('Content-Type: application/json');

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

require_once "config.php";
$apiKey = CHATGPT_API_KEY;
// ✅ Gemini API request setup
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;

$payload = [
    "system_instruction" => [
        "parts" => [[
            "text" => "You are Optima Bank’s official AI assistant for the Optima Voucher System.
                Your role is STRICTLY limited to answering questions about:
                - Vouchers (earning, redeeming, validity, expiry rules, status, history, totals).
                - Points (balance, usage, deduction, accumulation).
                - Voucher redemption flow and system rules.
                - Optima Bank services directly related to loyalty, rewards, and voucher management.
                
                CRITICAL RULES:
                1. If the user asks about the voucher system, always explain the actual system rules:
                • Users earn points from transactions.
                • Points can be redeemed for vouchers.
                • Redeemed vouchers are stored in redeem history.
                • Users can check their balance, total vouchers, and redemption history anytime.
                
                2. If the question matches a predefined function (like 'my points', 'total voucher', 'last voucher'), use the database query results provided by the backend to answer. NEVER invent numbers.
                3. If the question is unrelated to vouchers, points, or Optima Bank services (e.g. weather, jokes, general knowledge), strictly reply with:
                'Sorry, I can only help with voucher redemption and Optima Bank services.'
                
                4. Be polite, concise, and format answers with short paragraphs or bullet points for clarity.
                
                Remember: Stay inside the Optima Bank domain. NEVER answer personal, random, or unrelated questions."
        ]]
    ],
    "contents" => [[
        "role" => "user",
        "parts" => [["text" => $userMessage]]
    ]]
];

// ✅ cURL Request with error handling
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 10 // Prevents hanging requests
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(["reply" => "Error connecting to AI service. Please try again later."]);
    curl_close($ch);
    exit;
}

curl_close($ch);

$data = json_decode($response, true);

// ✅ Better error fallback
$reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? "Sorry, I can only help with voucher redemption and Optima Bank services.";

// ✅ Send JSON response
echo json_encode(["reply" => $reply], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>
