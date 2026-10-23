const crypto = require('crypto');

async function test() {
    const amount = 100;
    const phone = '9999999999';
    const name = 'Atharv Client';

    const merchantId = 'SU2603021630567009029077';
    const saltKey = '041e3f9f-298a-4882-982e-45f88683d15e';
    const saltIndex = 1;

    const transactionId = 'T' + Date.now() + Math.floor(Math.random()*1000);
    const callbackUrl = 'https://quote.atharventerprises.co/api/callback.php?id=' + transactionId;

    const payload = {
        merchantId: merchantId,
        merchantTransactionId: transactionId,
        merchantUserId: 'MUID' + Date.now(),
        amount: Math.round(amount * 100), // in paise
        redirectUrl: callbackUrl,
        redirectMode: 'POST',
        callbackUrl: callbackUrl,
        mobileNumber: phone,
        paymentInstrument: {
            type: 'PAY_PAGE'
        }
    };

    const base64Payload = Buffer.from(JSON.stringify(payload)).toString('base64');
    const stringToHash = base64Payload + '/pg/v1/pay' + saltKey;
    const sha256 = crypto.createHash('sha256').update(stringToHash).digest('hex');
    const checksum = sha256 + '###' + saltIndex;

    const response = await fetch('https://api.phonepe.com/apis/pg/v1/pay', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-VERIFY': checksum
        },
        body: JSON.stringify({ request: base64Payload })
    });

    const data = await response.json();
    console.log(JSON.stringify(data, null, 2));
}

test();
