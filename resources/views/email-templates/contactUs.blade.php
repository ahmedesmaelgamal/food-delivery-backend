<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Contact Message</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f6f6f6;
            margin: 0;
            padding: 20px;
        }
        .email-container {
            max-width: 600px;
            margin: auto;
            background: #ffffff;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .header {
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        h2 {
            margin: 0;
            color: #333;
        }
        .info-row {
            margin: 10px 0;
        }
        .label {
            font-weight: bold;
            color: #222;
        }
        .message-box {
            background: #f8f8f8;
            padding: 15px;
            border-left: 4px solid #4a90e2;
            border-radius: 4px;
            margin-top: 10px;
            white-space: pre-line;
        }
        .footer {
            margin-top: 30px;
            font-size: 13px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="email-container">

    <div class="header">
        <h2>📩 New Contact Us Message</h2>
    </div>

    <div class="info-row">
        <span class="label">Name:</span> {{ $data['name'] }}
    </div>

    <div class="info-row">
        <span class="label">Email:</span> {{ $data['email'] }}
    </div>

    @if(isset($data['mobile_number']))
        <div class="info-row">
            <span class="label">Mobile:</span> {{ $data['mobile_number'] }}
        </div>
    @endif

    @if(isset($data['subject']))
        <div class="info-row">
            <span class="label">Subject:</span> {{ $data['subject'] }}
        </div>
    @endif

    <div class="info-row">
        <span class="label">Message:</span>
        <div class="message-box">
            {{ $data['message'] }}
        </div>
    </div>

    <div class="footer">
        This message was sent from the Maxem mobile app.
    </div>

</div>
</body>
</html>
