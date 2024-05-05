<!DOCTYPE html>
<html>
<head>
    <title>Activación de Usuario</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .mensaje {
            background-color: #ffd700; /* Amarillo */
            border: 1px solid #ccc;
            padding: 20px;
            width: 35%;
        }

        .mensaje h1 {
            color: #000; /* Color de texto negro */
        }

        .mensaje p {
            color: #000; /* Color de texto negro */
            margin-bottom: 0; /* Eliminar el margen inferior para el último párrafo */
        }
    </style>
</head>
<body>
    <div class="mensaje">
        <h1>¡Usuario Activa!</h1>
        <p>¡El usuario {{ $usuario->name }} ya esta activado!</p>
        <hr>
        <p></p>
    </div>
</body>
</html>
