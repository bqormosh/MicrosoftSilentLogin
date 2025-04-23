<?php
header('Content-Type: application/json');

// Include Composer autoloader
require_once 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Firebase\JWT\Key;

// Check if token is received
if (!isset($_POST['token'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No token provided']);
    exit;
}

$ssoToken = $_POST['token'];

// Configuration
$tenantId = 'c7928542-1e64-4821-a662-1e0653fc9009';
$expectedAudience = 'api://microsoftsilentlogin.onrender.com/3a6cc383-da59-4665-94b5-a5a64430a77b';
$jwksUri = "https://login.microsoftonline.com/$tenantId/discovery/v2.0/keys";

// Log request details
error_log("SSO Token: $ssoToken");

// Fetch JWKS (JSON Web Key Set) to validate token
$jwksResponse = file_get_contents($jwksUri);
if ($jwksResponse === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch JWKS']);
    exit;
}
$jwks = json_decode($jwksResponse, true);

// Validate and decode JWT
try {
    // Decode token with JWKS
    $decodedToken = JWT::decode($ssoToken, JWK::parseKeySet($jwks));
    $decodedArray = (array) $decodedToken;

    // Log decoded token
    error_log("Decoded token: " . print_r($decodedArray, true));

    // Verify audience and issuer
    if ($decodedArray['aud'] !== $expectedAudience) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid token audience']);
        exit;
    }
    if ($decodedArray['iss'] !== "https://sts.windows.net/$tenantId/") {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid token issuer']);
        exit;
    }

    // Extract name and email
    $name = $decodedArray['name'] ?? $decodedArray['given_name'] ?? 'Unknown Name';
    $email = $decodedArray['upn'] ?? $decodedArray['unique_name'] ?? 'Unknown Email';

    // Log extracted data
    error_log("Extracted name: $name");
    error_log("Extracted email: $email");

    // Return JSON response to front-end
    $responseData = [
        'name' => $name,
        'email' => $email
    ];

    echo json_encode($responseData);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Token validation failed', 'details' => $e->getMessage()]);
    exit;
}
?>