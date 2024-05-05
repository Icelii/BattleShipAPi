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
            background-color: #f8d7da; /* Rojo pastel para danger */
            border: 1px solid #f5c6cb; /* Borde rojo */
            color: #721c24; /* Texto oscuro */
            padding: 20px;
            width: 35%;
        }

        .mensaje h1 {
            color: #721c24; /* Color de texto para h1 */
        }

        .mensaje p {
            margin-bottom: 0; /* Eliminar el margen inferior para el último párrafo */
        }
    </style>
</head>
<body>
    <div class="mensaje">
        <h1>Error al activar Usuario</h1>
        <p>Error al activar al usuario {{ $usuario->name }}</p>
    </div>
</body>
</html>
