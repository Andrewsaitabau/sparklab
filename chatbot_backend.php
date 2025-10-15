<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$userMessage = trim($input["message"] ?? "");

if (empty($userMessage)) {
    echo json_encode(["reply" => "⚠️ Please provide a message."]);
    exit;
}

// Fallback response if JavaScript SDK fails
echo json_encode(["reply" => "I'm currently using the JavaScript client directly. Please check the browser console for any errors."]);
?>