<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activar cuenta</title>
</head>
<body style=" background-color: #add8e6; ">
    <p>Hola {{ $user->name }},</p>
    <p>Gracias por registrarte. Por favor, haz clic en el siguiente enlace para activar tu cuenta:</p>
    <a href="{{ $activationLink }}" target="_blank" style="padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">Activar cuenta</a>
    <p>Si no puedes hacer clic en el enlace, cópialo y pégalo en la barra de direcciones de tu navegador:</p>
    <p>{{ $activationLink }}</p>
    <p>Gracias,</p>
    <p>Tu equipo</p>
</body>
</html>
