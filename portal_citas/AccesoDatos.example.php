<?php

// Copia este archivo a AccesoDatos.php y pon tus credenciales reales.

$host = '127.0.0.1';
$port = 3306;
$database = 'sakila';
$username = 'TU_USUARIO';
$password = 'TU_CONTRASENA';

mysqli_report(MYSQLI_REPORT_OFF);
$connection = @new mysqli($host, $username, $password, $database, $port);

function PA_AltaDoctor(string $n_licencia, string $n_nombre, string $n_apellido){
	global $connection;

	sql = "CALL AltaCita(?, ?, ?)";
	stmt = $connection->prepare($sql);

	stmt->bind_param("sss", $n_licencia, $n_nombre, $n_apellido);

	if ($stmt->execute()) {
		$error = stmt->error;
		$stmt->close();
		two = "Error al ejecutar el procedimiento almacenado: " . $error;
	}
	stmt->close();

	$resultado = $connection->query(select @resultado as resultado);

}

function PA_AltaPacientes()
