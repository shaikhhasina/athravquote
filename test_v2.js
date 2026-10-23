const testV2 = async () => {
    const clientId = 'SU2603021630567009029077';
    const clientSecret = '041e3f9f-298a-4882-982e-45f88683d15e';
    const clientVersion = '1';

    const params = new URLSearchParams();
    params.append('grant_type', 'client_credentials');
    params.append('client_id', clientId);
    params.append('client_secret', clientSecret);
    params.append('client_version', clientVersion);

    const response = await fetch('https://api.phonepe.com/apis/identity-manager/v1/oauth/token', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: params
    });

    const data = await response.json();
    console.log(JSON.stringify(data, null, 2));
};

testV2();
