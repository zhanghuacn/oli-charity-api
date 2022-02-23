<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        #app {
            line-height: 150%;
        }
    </style>
</head>
<body>
<div id="app">
    <div>Dear User：</div>
    <br/>
    <div>Hello！</div>
    <br/>
    <div>Thank you for using oli charity. You are in the process of {{ $operation  }}. Please enter the verification
        code <span style="color: red">{{ $code }}</span>（valid within 15 minutes）in the verification code input box to
        complete the verification。
    </div>
    <div>If it is not your own operation, please ignore this email. Please understand the inconvenience caused to you!
    </div>
    <br/>
    <div>oli charity</div>
</div>
</body>
</html>
