<?php
session_start();

require_once 'vendor/autoload.php';

$fb = new Facebook\Facebook([
'app_id' => 'YOUR_APP_ID',
'app_secret' => 'YOUR_APP_SECRET',
'default_graph_version' => 'v18.0',
]);

$helper = $fb->getRedirectLoginHelper();

try {
$accessToken = $helper->getAccessToken();
} catch(Exception $e) {
echo 'Error: ' . $e->getMessage();
exit;
}

if (! isset($accessToken)) {
echo "Access denied";
exit;
}

$response = $fb->get('/me?fields=id,name,email', $accessToken);

$user = $response->getGraphUser();

$_SESSION['user_name'] = $user['name'];
$_SESSION['user_email'] = $user['email'];

header("Location: dashboard.php");

?>