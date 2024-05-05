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

        .alert {
            background-color: #add8e6; /* Azul pastel */
            border: 1px solid #ccc;
            padding: 20px;
            width: 35%;
        }

        .alert h4 {
            margin-top: 0; /* Eliminar margen superior del título */
        }
    </style>
</head>
<body>
    <div class="alert" role="alert">
        <h4>¡Usuario Activado!</h4>
        <p>¡El usuario {{ $usuario->name }} ha sido activado correctamente!</p>
        <hr>
        <p class="mb-0">¡Enhorabuena!</p>
    </div>
</body>
</html>
