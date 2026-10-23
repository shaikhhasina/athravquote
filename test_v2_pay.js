const testV2Pay = async () => {
    const clientId = 'SU2603021630567009029077';
    const clientSecret = '041e3f9f-298a-4882-982e-45f88683d15e';
    const clientVersion = '1';

    const params = new URLSearchParams();
    params.append('grant_type', 'client_credentials');
    params.append('client_id', clientId);
    params.append('client_secret', clientSecret);
    params.append('client_version', clientVersion);

    const authRes = await fetch('https://api.phonepe.com/apis/identity-manager/v1/oauth/token', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params
    });

    const authData = await authRes.json();
    const token = authData.access_token;

    const payload = {
        merchantOrderId: 'MT' + Date.now(),
        amount: 100 * 100,
        paymentFlow: {
            type: "PG_CHECKOUT",
            merchantUrls: {
                redirectUrl: 'https://quote.atharventerprises.co/api/callback.php',
                cancelUrl: 'https://quote.atharventerprises.co/api/callback.php'
            }
        },
        merchantId: clientId
    };

    const payRes = await fetch('https://api.phonepe.com/apis/pg/checkout/v2/pay', {
        method: 'POST',
        headers: {
            'Authorization': 'O-Bearer ' + token,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    });
    
    // Wait, let's just log what we can
    const payData = await payRes.text();
    console.log(payData);
};
testV2Pay();
