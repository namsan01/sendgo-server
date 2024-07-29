<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>비밀번호 재설정 요청</title>
</head>
<body>
    <h1>비밀번호 재설정 요청</h1>
    <p>안녕하세요,</p>
    <p>비밀번호 재설정 요청을 받았습니다. 아래 버튼을 클릭하여 비밀번호를 재설정하십시오:</p>
    <a href="{{ $resetUrl }}">비밀번호 재설정</a>
    <p>이 요청을 하신 적이 없다면 이 이메일을 무시하셔도 됩니다.</p>
    <p>감사합니다,<br>{{ config('app.name') }}</p>
</body>
</html>
