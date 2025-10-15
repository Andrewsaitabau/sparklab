<?php
header('Content-Type: application/json');

// Get the API key from the request
$apiKey = $_POST['api_key'] ?? '';

// Get the user message
$userMessage = trim($_POST['message'] ?? '');

if (!$userMessage) {
    echo json_encode(['reply' => 'Please enter a message.']);
    exit;
}

// Validate API key format
if (!$apiKey || !preg_match('/^sk-[a-zA-Z0-9]+$/', $apiKey)) {
    echo json_encode(['reply' => 'Invalid API key format. API key must start with "sk-".']);
    exit;
}

// SparkLab knowledge base
$sparklabKnowledge = "
SparkLab is a software and IT solutions company. 
We provide website development, system applications, project management, and client support. 
Our services include dashboard systems, booking systems, and client management solutions.
If asked about something outside our scope, politely redirect to our services.
";

// Prepare the API payload
$data = [
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        ['role' => 'system', 'content' => $sparklabKnowledge],
        ['role' => 'user', 'content' => $userMessage]
    ],
    'max_tokens' => 150,
    'temperature' => 0.7
];

// Initialize cURL
$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Set timeout to 30 seconds
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Verify SSL certificate

// Execute the request
$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(['reply' => 'Error connecting to OpenAI: ' . curl_error($ch)]);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($httpCode !== 200) {
    echo json_encode(['reply' => 'OpenAI API returned HTTP code: ' . $httpCode]);
    exit;
}

curl_close($ch);

// Parse the API response
$result = json_decode($response, true);

if (isset($result['choices'][0]['message']['content'])) {
    $reply = trim($result['choices'][0]['message']['content']);
} else {
    $reply = 'Sorry, I could not understand that. Please try again.';
}

echo json_encode(['reply' => $reply]);