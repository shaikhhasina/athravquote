<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$amount = isset($input['amount']) ? $input['amount'] : 0;
$phone = isset($input['phone']) ? $input['phone'] : '9999999999';

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
    http_response_code(500);
    echo json_encode(['error' => 'Failed to authenticate with PhonePe (OAuth)', 'details' => $authData]);
    exit;
}

$accessToken = $authData['access_token'];

// 2. Initiate Payment
$transactionId = 'MT' . time() . rand(100, 999);
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$domain = $protocol . '://' . $host;
$callbackUrl = $domain . '/api/callback.php?id=' . $transactionId;

$payload = [
    'merchantId' => $clientId,
    'merchantOrderId' => $transactionId,
    'amount' => round((float)$amount * 100), // in paise
    'paymentFlow' => [
        'type' => 'PG_CHECKOUT',
        'merchantUrls' => [
            'redirectUrl' => $callbackUrl,
            'cancelUrl' => $callbackUrl
        ]
    ],
    'mobileNumber' => $phone
];

$chPay = curl_init('https://api.phonepe.com/apis/pg/checkout/v2/pay');
curl_setopt($chPay, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chPay, CURLOPT_POST, true);
curl_setopt($chPay, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($chPay, CURLOPT_HTTPHEADER, [
    'Authorization: O-Bearer ' . $accessToken,
    'Content-Type: application/json'
]);

$payResponse = curl_exec($chPay);
curl_close($chPay);

$payData = json_decode($payResponse, true);

if (isset($payData['redirectUrl'])) {
    echo json_encode(['url' => $payData['redirectUrl']]);
} else {
    http_response_code(400);
    echo json_encode([
        'error' => isset($payData['message']) ? $payData['message'] : 'Payment initiation failed',
        'details' => $payData
    ]);
}
?>
