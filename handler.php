<?php
header('Content-Type: application/json');

// Check if token is received
if (!isset($_POST['token'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No token provided']);
    exit;
}

$ssoToken = $_POST['token'];

// Configuration
$tenantId = 'c7928542-1e64-4821-a662-1e0653fc9009';
$clientId = '3a6cc383-da59-4665-94b5-a5a64430a77b';
$resource = 'api://microsoftsilentlogin.onrender.com/3a6cc383-da59-4665-94b5-a5a64430a77b';
$graphScope = 'https://graph.microsoft.com/User.Read';
$scope = "$resource/access_as_user $graphScope";

// Log request details
error_log("SSO Token: $ssoToken");
error_log("Requested scope: $scope");

// Exchange SSO token for access token
$tokenUrl = "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/token";
$data = [
    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
    'client_id' => $clientId,
    'assertion' => $ssoToken,
    'scope' => $scope,
    'requested_token_use' => 'on_behalf_of'
];

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Log token exchange response
error_log("Token exchange HTTP code: $httpCode");
error_log("Token exchange response: $response");

if ($httpCode != 200) {
    http_response_code($httpCode);
    echo json_encode(['error' => 'Token exchange failed', 'details' => $response]);
    exit;
}

$tokenData = json_decode($response, true);
$accessToken = $tokenData['access_token'];

// Log access token details
error_log("Access token: $accessToken");
error_log("Token response: " . print_r($tokenData, true));

// Call Microsoft Graph to get user details
$graphUrl = 'https://graph.microsoft.com/v1.0/me';
$ch = curl_init($graphUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);
$graphResponse = curl_exec($ch);
$graphHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Log Graph API response
error_log("Graph API HTTP code: $graphHttpCode");
error_log("Graph API response: $graphResponse");

if ($graphHttpCode != 200) {
    http_response_code($graphHttpCode);
    echo json_encode(['error' => 'Graph API call failed', 'details' => $graphResponse]);
    exit;
}

$userData = json_decode($graphResponse, true);

// Extract name and email with fallbacks
$name = $userData['displayName'] ?? $userData['givenName'] ?? 'Unknown Name';
$email = $userData['mail'] ?? $userData['userPrincipalName'] ?? 'Unknown Email';

// Log extracted data
error_log("Extracted name: $name");
error_log("Extracted email: $email");

// Return JSON response to front-end
$responseData = [
    'name' => $name,
    'email' => $email
];

echo json_encode($responseData);
?>