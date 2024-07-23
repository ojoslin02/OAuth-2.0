<?php
$username = $_POST['username'];
$password = $_POST['password'];
$public_key = openssl_pkey_get_public('file:///var/www/html/public_key.pem');
openssl_public_encrypt(json_encode(array("username"=>$username,"password"=>$password)), $encrypted, $public_key);

$url = 'http://10.0.0.10/auth.php';

// use key 'http' even if you send the request to https://...
$options = [
    'http' => [
        'header' => "Content-type: application/json",
        'method' => 'POST',
        'content' => json_encode(['data' => base64_encode($encrypted)]),
    ],
];

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

$decrypted_response = openssl_decrypt($response, 'aes256', hash("sha256", $password));

$auth_response = json_decode($decrypted_response, true);
$encode = base64_encode($auth_response['token']);
if ($auth_response['auth'] === 'success') {
    // Redirect to application.local with the token
    header("Location: http://10.0.0.20/index.php?token={$encode}");
} else {
    // Redirect to the login page
    header("Location: index.html");
}

?>
