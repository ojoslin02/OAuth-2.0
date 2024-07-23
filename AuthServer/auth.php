<?php

$encrypted = json_decode(file_get_contents('php://input'), true)['data'];
$private_key = openssl_pkey_get_private('file:///var/www/html/private_key.pem');
openssl_private_decrypt(base64_decode($encrypted), $decrypted_credentials, $private_key);

$credentials = json_decode($decrypted_credentials, true);
$username = $credentials['username'];
$password = $credentials['password'];


// Send credentials to provider
$postData = http_build_query(['grant_type' => 'client_credentials']);

$options = [
    'http' => [
        'method' => 'POST',
        'header' => [
            'Authorization: Basic ' . base64_encode("$username:$password"),
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen($postData),
            'Accept: ' . ("*/*"),
            'User-Agent:' . ("curl/7.81.0"),
        ],
        'content' => $postData
    ],
    'ssl' => [
        'verify_peer' => true,
        'cafile' => './certificate.pem', // Update with your certificate file path
    ],
];

$context = stream_context_create($options);
$provider_response = file_get_contents('https://10.0.0.15/token.php', false, $context);

// Extract access token from provider response
$provider_token = json_decode($provider_response, true)['access_token'];

// Encrypt token with sha256 hash of password
if ($provider_token) {
    // $plaintext, $cipher, $key
    $key = "VAFqlvBwxzMJQHpOAImLHRnqaNrzErya";
    $token = openssl_encrypt(base64_encode($provider_token), "aes256", hash("sha256", $key));
    $auth = "success";
} else {
    $token = "";
    $auth = "fail";
}

// Send auth response to client
$json_response = json_encode(array("auth" => $auth, "token" => $token));
echo openssl_encrypt($json_response, 'aes256', hash("sha256", $password));
?>
