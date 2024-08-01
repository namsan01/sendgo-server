<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>비밀번호 재설정</title>
    <style>
        /* 이메일의 스타일을 여기에 추가 */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .header {
            background-color: #007bff;
            color: #fff;
            padding: 10px;
            text-align: center;
        }
        .content {
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9em;
            color: #777;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
        </div>
        <div class="content">
            <h1>안녕하세요 샌드고입니다!</h1>
            <h2>고객님의 비밀번호 재설정 요청을 받았습니다. <br/> 아래 버튼을 클릭하여 비밀번호를 재설정하십시오.</h2>
            <h2>
            <a href="{{ $url }}" class="button">비밀번호 재설정</a>
            </h2>
            <h2>요청을 하지 않으셨다면, 고객센터에 문의하여 주십시오.</h2>
        </div>
        <div class="footer">
            <h2>감사합니다 샌드고</h2>
            <h3>&copy; {{ date('Y') }} My Application. All rights reserved.</h3>
        </div>
    </div>
</body>
</html>

