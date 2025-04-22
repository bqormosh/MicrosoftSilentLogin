<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
    exit;
}

$token = $_POST['token'];
if (!$token) {
    http_response_code(400);
    echo json_encode(["error" => "Missing token"]);
    exit;
}

// Decode token (without verifying signature here — for demo only)
$tokenParts = explode('.', $token);
if (count($tokenParts) != 3) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid token"]);
    exit;
}

$payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
$userInfo = json_decode($payload, true);

// Return user info
echo json_encode([
    "name" => $userInfo["name"],
    "email" => $userInfo["preferred_username"]
]);
?>
