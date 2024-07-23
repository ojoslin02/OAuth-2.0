<?php

// Retrieve the token from the URL query parameters
$token = $_GET['token'];
$decoded_token = base64_decode($token);

// Decrypt the token with the shared secret
$key = "VAFqlvBwxzMJQHpOAImLHRnqaNrzErya";
$decrypted_token = openssl_decrypt($decoded_token, "aes256", hash("sha256", $key));
$final = base64_decode($decrypted_token);

// Verify the token
$options = [
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => http_build_query(['access_token' => $final])
    ],
    'ssl' => [
        'verify_peer' => true,
        'cafile' => './certificate.pem', // Update with your certificate file path
    ],
];
$context = stream_context_create($options);
$response = file_get_contents('https://10.0.0.15/resource.php', false, $context);

$decoded_response = json_decode($response, true);
if ($decoded_response['success']) {
    // Token verification, redirect to login.html
    header("Location: login.html");
}
?>
