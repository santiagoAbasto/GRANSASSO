<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; color: #333; padding: 30px 20px; }
        .wrapper { max-width: 580px; margin: 0 auto; }
        .header { background: #25A7CA; color: white; padding: 24px 28px; border-radius: 10px 10px 0 0; }
        .header h2 { font-size: 20px; font-weight: 700; line-height: 1.3; }
        .body { background: #ffffff; padding: 28px; border-radius: 0 0 10px 10px; border: 1px solid #e5e7eb; border-top: none; font-size: 15px; line-height: 1.75; color: #374151; }
        .footer { margin-top: 24px; font-size: 12px; color: #9ca3af; text-align: center; }
        .footer a { color: #25A7CA; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h2>{{ $subject }}</h2>
        </div>
        <div class="body">
            {!! nl2br(e($body)) !!}
        </div>
        <div class="footer">
            <p>Recibiste este correo porque estás suscripto al newsletter.</p>
        </div>
    </div>
</body>
</html>
