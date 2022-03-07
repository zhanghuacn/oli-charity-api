<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Email Confirmation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
        body {
            background-color: #e9ecef;
            font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif;
        }
        .container {
            width: 600px;
            margin: 90px auto 36px auto;
            padding: 1px 24px;
            background: #fff;
            box-sizing: border-box;
            box-shadow: 4px 4px 4px rgba(175, 175, 175, 0.2);
        }
        .container h1 {
            font-size: 32px;
            line-height: 48px;
            font-weight: 700;
            letter-spacing: -1px;
            margin: 36px 0 24px 0;
        }
        .container p {
            font-size: 16px;
            line-height: 24px;
        }
        .icon-wrap {
            display: flex;
            justify-content: center;
            padding-top: 36px ;
        }
        .icon {
            width: 200px;
            height: 147px;
        }
        .code {
            text-align: center;
            font-size: 30px;
            color: #1a82e2;
            padding: 30px 0;
        }
        .footer {
            font-size: 14px;
            line-height: 20px;
            color: #666;
            text-align: center;
            width: 600px;
            margin: 0 auto;
        }
    </style>
</head>

<body>
<div class="container">
    <div class="icon-wrap">
        <img class="icon" src="https://vapor-ap-southeast-2-storage-1641439528.s3.ap-southeast-2.amazonaws.com/public/email/imagine2080Logo.svg" />
    </div>
    <h2>Your verification code is</h2>
    <div class="code">{{$code}}</div>
    <p>Your verification code is valid for 15 minutes. In order to ensure the security of your account, please do not provide this verification code to anyone. Thank you for your support to Imagine 2080!</p>
    <p>Cheers, <br /> Imagine2080 Teams</p>
</div>
<div class="footer">
    You received this email because we received a request for the {{ $operation }} of Imagine2080 for your account. If you didn't request the {{ $operation }}, you can safely delete this email.
</div>
</body>
</html>
