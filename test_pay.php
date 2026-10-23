<?php

$amount = 100;
$phone = '9999999999';
$name = 'Atharv Client';

$merchantId = 'SU2603021630567009029077';
$saltKey = '041e3f9f-298a-4882-982e-45f88683d15e';
$saltIndex = 1;

$transactionId = 'T' . time() . rand(1000, 9999);
$callbackUrl = 'https://quote.atharventerprises.co/api/callback.php?id=' . $transactionId;

$payload = [
    'merchantId' => $merchantId,
    'merchantTransactionId' => $transactionId,
    'merchantUserId' => 'MUID' . time(),
    'amount' => round((float)$amount * 100), // in paise
    'redirectUrl' => $callbackUrl,
    'redirectMode' => 'POST',
    'callbackUrl' => $callbackUrl,
    'mobileNumber' => $phone,
    'paymentInstrument' => [
        'type' => 'PAY_PAGE'
    ]
];

$base64Payload = base64_encode(json_encode($payload));
$stringToHash = $base64Payload . '/pg/v1/pay' . $saltKey;
$sha256 = hash('sha256', $stringToHash);
$checksum = $sha256 . '###' . $saltIndex;

// Make POST request to PhonePe
$ch = curl_init('https://api.phonepe.com/apis/hermes/pg/v1/pay');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['request' => $base64Payload]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-VERIFY: ' . $checksum
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpcode\n";
echo "Response: $response\n";
?>
