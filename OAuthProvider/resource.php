<?php
// include our OAuth2 Server object
require_once __DIR__.'/server.php';

// Handle a request to a resource and authenticate the access token
if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
    echo json_encode(array('success' => false));
}
echo json_encode(array('success' => true, 'message' => 'You accessed my APIs!'));
?>
