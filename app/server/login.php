<?php
    session_start();
    include 'conexion_db.php';
    $usuario = mysqli_real_escape_string($conexion, $_POST['usuario']); //Escapar el nombre de usuario
    $contrasena = mysqli_real_escape_string($conexion, $_POST['contrasena']); //Escapar la contraseña
    $query = "SELECT * FROM USUARIOS WHERE usuario = '$usuario' AND pswd = '$contrasena'";
    $resultado = mysqli_query($conexion,$query);
    $origen= "login";
    $enviado= $query;
    include 'close_conexion_db.php';
    if ($_POST['CSRF_token'] == $_SESSION['tokenLogin'])
    {
        if ($resultado->num_rows > 0) {
            $_SESSION['usuario']= $usuario;
            $_SESSION['user_data'] = mysqli_fetch_array($resultado);
            $resultado= "Se ha iniciado sesion";
            include '/var/www/html/server/addlogs.php';
            echo '<script type="text/javascript">window.location.replace("http://localhost:81/src/pages/catalogo/catalogo.php");</script>';
        } else {
            $resultado= "No se ha iniciado sesion";
            include '/var/www/html/server/addlogs.php';
            echo '<script type="text/javascript">window.location.replace("http://localhost:81/src/pages/login/login.php");</script>';
        }
    }
    else
    {
        $resultado= "Llamada sin token ignorada";
        include '/var/www/html/server/addlogs.php';
    }
?>
