<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Prize Notification</title>
    <style type="text/css">
        body{
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

        .container h1{
            color: #264653;
            text-align: center;
            word-wrap: break-word;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 38px;
            font-weight: 500;
            line-height: 38px;
            margin: 2px auto;
        }

        .container h2{
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


        .icon{
            width: 200px;
            height: 147px;
        }

        .icon-wrap{
            display: flex;
            justify-content: center;
        }

        .prize-image{
            width: 300px;
            height: 300px;
        }

        .text{
            padding: 0;
            margin-top: 0;
        }

        .container p:first-child{
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
        <img class="icon" src="https://vapor-ap-southeast-2-storage-1641439528.s3.ap-southeast-2.amazonaws.com/public/email/imagine2080Logo.svg" alt="imagine logo">
    </div>

    <div class="icon-wrap">
        <img class="prize-image" src="{{ $image }}" alt="{{ $prize }}">
    </div>

    <div class="text">
        <h1>Congrats!</h1>
        <h2>You have won</h2>
    </div>
    <div class="content">
        <h3>Dear user</h3>
    </div>
    <p>Congratulations, you've won the {{ $prize }}  in our {{ $event }}!</p>
    <p>You can claim your prize on the day of the banquet.<a href="https://mp.weixin.qq.com/s?__biz=MzA5NzIzNjMzNw==&mid=2651418415&idx=2&sn=f068b6676b4b0518403e5138aa489075&chksm=8b5e2b31bc29a2279cd10e0dc10b01ca989642bd5ec402be199b6893492ec890132f1b3c4f3b&token=860683828&lang=zh_CN#rd"><strong>More info</strong></a></p>
    <p>If you have any questions, please contact the administrator of the WeChat group.</p>

    <p>Cheers, <br/>Imagine2080 Teams</p>
</div>
<div class="footer">
    <h3>About us: www.Imagine2080.com.au</h3>
</div>

</body>
</html>
