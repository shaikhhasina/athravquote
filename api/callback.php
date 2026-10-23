<?php
$transactionId = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['transactionId']) ? $_POST['transactionId'] : null);

if (!$transactionId) {
    header('Location: /?payment=failed');
    exit;
}

$clientId = 'SU2603021630567009029077';
$clientSecret = '041e3f9f-298a-4882-982e-45f88683d15e';
$clientVersion = '1';

// 1. Generate OAuth Token
$ch = curl_init('https://api.phonepe.com/apis/identity-manager/v1/oauth/token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'grant_type' => 'client_credentials',
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'client_version' => $clientVersion
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);

$authResponse = curl_exec($ch);
curl_close($ch);

$authData = json_decode($authResponse, true);
if (!$authData || !isset($authData['access_token'])) {
    header('Location: /?payment=error');
    exit;
}

$accessToken = $authData['access_token'];

// 2. Check Status
$chStatus = curl_init("https://api.phonepe.com/apis/pg/checkout/v2/order/{$transactionId}/status");
curl_setopt($chStatus, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chStatus, CURLOPT_HTTPGET, true);
curl_setopt($chStatus, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: O-Bearer ' . $accessToken
]);

$statusResponse = curl_exec($chStatus);
curl_close($chStatus);

if ($statusResponse) {
    $data = json_decode($statusResponse, true);
    // V2 status response contains 'state' parameter (COMPLETED, PENDING, FAILED)
    if (isset($data['state']) && $data['state'] === 'COMPLETED') {
        header("Location: /?payment=success&tx=" . urlencode($transactionId));
        exit;
    } else {
        header("Location: /?payment=failed&tx=" . urlencode($transactionId));
        exit;
    }
} else {
    header('Location: /?payment=error');
    exit;
}
?>
