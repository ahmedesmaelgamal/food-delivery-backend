<!DOCTYPE html>
<html>
<head>
    <title>التحقق من OTP - تطبيق اتعابي</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            direction: rtl;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            direction: rtl;
        }
        h1 {
            color: #333333;
        }
        p {
            color: #666666;
            line-height: 1.6;
        }
        .otp {
            font-size: 24px;
            font-weight: bold;
            color: #333333;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #999999;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>التحقق من OTP </h1>
        @php
              $name = explode('@', $data['email'])[0];
        @endphp
        <p>عزيزي {{ $name }},</p>
        <p>رمز OTP الخاص بك للتحقق هو: <span class="otp">{{ $data['otp'] }}</span></p>
        <p style="color: red">تنتهي صلاحية هذا الرمز في خلال 5 دقائق .</p>
        <p>يرجى استخدام هذا الرمز لإكمال عملية التحقق الخاصة بك.</p>
        <p>شكراً لك!</p>
        <div class="footer">
            <p>إذا لم تطلب هذا الرمز، يرجى تجاهل هذا البريد الإلكتروني.</p>
            <p>مع تحياتي،</p>
            <p>تطبيق اتعابي</p>
        </div>
    </div>
</body>
</html>
<style>
    .otp span {
        display: inline-block;
        width: 30px;
        height: 40px;
        line-height: 40px;
        margin: 0 5px;
        background-color: #e0e0e0;
        border-radius: 5px;
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        color: #333333;
    }
</style>
