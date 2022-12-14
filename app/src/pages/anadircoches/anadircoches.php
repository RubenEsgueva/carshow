<?php 
    session_start();
	if (empty($_SESSION['usuario']))
	{
		include '/var/www/html/router.php';
		$router->pagesCatalogo(0);
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<style>.error {color: #FF0000;}</style>
	<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarShow - Añadir Coche</title>
	<link rel="stylesheet" href="anadircoches.css">
</head>
<body>
	<?php
		//este archivo y registro.php cumplen una función muy similar por lo que en su mayoría será la misma explicación.
		//para guardar datos hace falta conectarse a la base de datos.
		include '../../../server/conexion_db.php';
		$origen= "anadircoches";
		//este es un metodo de seguridad, obtenido de: https://www.w3schools.com/php/php_form_validation.asp
		function test_input($data)
		{
			$data = trim($data);
			$data = stripslashes($data);
			$data = htmlspecialchars($data);
			//$data = mysqli_real_escape_string($conexion, $data);
			return $data;
		}

		$modelo = $matricula = $imagen = $estado = "";
		$modelERR = $matERR = $estadoERR = $imgERR = $kmERR = $precioERR = $bdERR = "";
		$valido = true;
		//Cada vez que se pulse el boton de confirmar habrá que comprobar el contenido de cada casilla.
		if ($_SERVER["REQUEST_METHOD"] == "POST" and $_POST['CSRF_token'] == $_SESSION['tokenAna']) 
			{
				if (empty($_POST["model"]))
				{
					$modelERR = "Es necesario indicar el modelo del vehículo.";
					$valido = false;
				}
				else
				{
					$modelo = test_input($_POST["model"]);
				}
	
				if (empty($_POST["platenum"]))
				{
					$matERR = "Es necesario indicar la matrícula del vehículo.";
					$valido = false;
				}
				else
				{
					$matricula = test_input($_POST["platenum"]);
					$query = "SELECT * FROM COCHES WHERE matricula = '{$matricula}'";
					$resultado = mysqli_query($conexion,$query);
					if ($resultado->num_rows > 0) 
					{
						$matERR = "La matrícula ya está registrada.";
						$valido = false;
					}
					elseif (!preg_match("/^[a-zA-Z0-9]*$/",$matricula))
					{
						$matERR = "La matrícula no puede incluir espacios o caracteres especiales.";
						$valido = false;
					}
				}
	
				if (empty($_POST["imagen"]))
				{
					$imgERR = "Para verlo en el catálogo es necesario adjuntar una imagen.";
					$valido = false;
				}
				else
				{
					$exten_permit = array("jpg","jpeg","png","gif");
					$imagen = test_input($_POST["imagen"]);
					$exten_img = pathinfo($imagen, PATHINFO_EXTENSION);
					if (!in_array($exten_img, $exten_permit))
					{
						$imgERR = "El archivo adjuntado no tiene una extensión válida.";
						$valido = false;
					}
				}
	
				if (empty($_POST["status"]))
				{
					$estadoERR = "Por favor especifique el estado del vehículo.";
					$valido = false;
				}
				else
				{
					$estado = test_input($_POST["status"]);
				}
	
				if (!empty($_POST["km"]))
				{
					$kmtraje = test_input($_POST["km"]);
					if (!preg_match("/^[0-9]*$/",$kmtraje))
					{
						$kmERR = "Por favor exprese el kilometraje en números enteros.";
						$valido = false;
					}
				}
	
				if (!empty($_POST["price"]))
				{
					$precio = test_input($_POST["price"]);
					if (!preg_match("/^[0-9]*\.[0-9]{2}$/",$precio))
					{
						$precioERR = "Utilize el formato 9999.99 por favor.";
						$valido = false;
					}
				}
				//si todos los contenidos cumplen las condiciones entonces podremos añadir el elemento a la base de datos.
				if (empty($modelo) and empty($kilometraje) and empty($precio) and empty($matricula) and empty($imagen) and empty($estado))
				{

				}
				else
				{
					$usuario = $_SESSION['usuario'];
					$enviado="INSERT INTO COCHES VALUES ('{$matricula}', '{$modelo}', '{$usuario}', '{$estado}', '{$kilometraje}', '{$precio}', '{$imagen}')";
					if ($valido)
					{
						$resultado="Se ha anadido correctamente";
					}
					else
					{
						$resultado="Algun dato tenia un valor inadecuado";
					}
					include '/var/www/html/server/addlogs.php';
					if ($valido)
					{
						//primero metemos los datos que son obligatorios.
						$dist = intval($kmtraje);
						$query = "INSERT INTO COCHES (matricula,modelo,usuario,estado,imagen) VALUES ('{$matricula}', '{$modelo}', '{$usuario}', '{$estado}', '{$imagen}')";
						if ($conexion->query($query) === TRUE) 
						{
							//tras haber creado ya la fila con los datos principales vamos comprobando que datos que se pueden quedar vacíos hay puestos.
							if (isset($precio))
							{
								$query = "UPDATE COCHES SET precio = '{$precio}' WHERE matricula = '{$matricula}'";
							}
							if (isset($kmtraje))
							{
								$query = "UPDATE COCHES SET kilometraje = '{$kmtraje}' WHERE matricula = '{$matricula}'";
							}
							//para que se pueda ver la imagen en la web tenemos que importarla a donde podamos manejarla.
							$location = "/var/www/html/public/matriculas/{$_POST['matricula']}.png";
							move_uploaded_file($_FILES['imagen']['tmp_name'], $location);
							//tras meter toda la información necesaria volvemos a catalogo donde ahora debería aparecer el nuevo vehículo.
							include '/var/www/html/router.php';
							$router->pagesCatalogo(0);
						} 
					}
				}
				
		}
		else
		{
			$enviado="POST sin token o primer acceso a la pagina";
			$resultado="el POST no se ha procesado";
			include '/var/www/html/server/addlogs.php';
		}
		$token = md5(uniqid(rand(),true));
		$_SESSION['tokenAna'] = $token;
	?>
	<div><span class="error">* campo obligatorio</span></div>
	<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
		<input type="hidden" name="CSRF_token" value="<?php echo $token; ?>">
		<div>Modelo:*</div>
		<input type="text" class="casilla" name="model" placeholder="Ej.: Batmóvil 2016" autofocus>
		<span class="error"><?php echo $modelERR;?></span><br>
		<div>Matrícula (Sin espacios):*</div>
		<input type="text" class="casilla" name="platenum" placeholder="Especifique su matrícula">
		<span class="error"><?php echo $matERR;?></span><br>
		<div>Seleccione la imagen que desee adjuntar:*</div>
 		<input type="file" id="imagen" name="imagen">
		<span class="error"><?php echo $imgERR;?></span><br>
		<div>Estado:*</div>
		<input type="radio" id="nuevo" class="radio" name="status" value="Nuevo">
		<label for="nuevo">Nuevo</label><br>
		<input type="radio" id="seminuevo" class="radio" name="status" value="Seminuevo">
		<label for="seminuevo">Seminuevo</label><br>
		<span class="error"><?php echo $estadoERR;?></span>
		<div>Kilometraje:</div>
		<input type="text" class="casilla" name="km" placeholder="Introduzca los kilometros recorridos.">
		<span class="error"><?php echo $kmERR;?></span><br>
		<div>Precio:</div>
		<input type="text" class="casilla" name="price" placeholder="Formato: 9999.99">
		<span class="error"><?php echo $precioERR;?></span><br><br>
		<div class="boton">
		<input type="submit" class="boton" value="Confirmar"></div><br>
		<span class="error"><?php echo $bdERR;?></span>
		
	</form>
</body>
</html>
