<!DOCTYPE html>
<html>
<head>
    <title>reCAPTCHA demo: Simple page</title>
    <script src="https://recaptcha.net/recaptcha/api.js" async defer></script>
</head>
<body>
<form action="/test2" method="POST">
    <div class="g-recaptcha" data-sitekey="6LchhsIeAAAAAC6Q8YMgsbsBVNRU-gbA3w6Rt6g4"></div>
    <?php echo csrf_field(); ?>
    <br/>
    <input type="submit" value="Submit">
</form>
</body>
</html>
