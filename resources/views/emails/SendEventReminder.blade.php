<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Prize Notification</title>
    <style type="text/css">
        body {
            background-color: #e9ecef;
            font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif;
        }

        .container {
            width: 600px;
            margin: 90px auto 36px;
            padding: 1px 24px;
            background: #fff;
            box-sizing: border-box;
            box-shadow: 4px 4px 4px rgba(175, 175, 175, 0.2);
        }

        .container h1 {
            color: #264653;
            text-align: center;
            word-wrap: break-word;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 38px;
            font-weight: 500;
            line-height: 38px;
            margin: 2px auto;
        }

        .container h2 {
            padding: 0;
            color: #2a9d8f;
            text-align: center;
            word-wrap: break-word;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 22px;
            font-weight: 500;
            text-transform: uppercase;
            line-height: 22px;
            margin: 10px auto;
        }


        .icon {
            width: 200px;
            height: 147px;
        }

        .icon-wrap {
            display: flex;
            justify-content: center;
        }

        .prize-image {
            width: 300px;
            height: 300px;
        }

        .text {
            padding: 0;
            margin-top: 0;
        }

        .container p:first-child {
            margin-top: 10px 0;
            display: inline-block;
        }

        .footer {
            font-size: 14px;
            line-height: 20px;
            color: #666;
            text-align: center;
            width: 600px;
            margin: 0px auto;
            text-transform: uppercase;
        }


    </style>
</head>
<body>
<div class="container">
    <div class="icon-wrap">
        <img class="icon"
             src="https://vapor-ap-southeast-2-storage-1641439528.s3.ap-southeast-2.amazonaws.com/public/email/imagine2080Logo.svg"
             alt="imagine logo">
    </div>
    <div class="content">
        <h3>Dear user</h3>
    </div>
    <p>
        The lucky draw of {{ $event }} will be held as scheduled after {{ $days }} days. All registers will have a
        chance to win the prize. Please confirm your registration and Raffle ticket number.
    </p>
    <p>Cheers, <br/>Imagine2080 Teams</p>
</div>
<div class="footer">
    <h3>About us: www.Imagine2080.com.au</h3>
</div>

</body>
</html>
