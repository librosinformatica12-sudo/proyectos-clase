<?php

require_once __DIR__ . "/AccesoDatos.php";


// PASO A: Iniciar o reanudar la sesión (OBLIGATORIO al principio)
session_start();

// PASO B: Inicializar el array de doctores 'lista_doctores' en la sesión si no existe aún
if (!isset($_SESSION["lista_doctores"])) {
    $_SESSION["lista_doctores"] = array();
}

// PASO D: Si se recibe una nueva alta de doctor se procesa
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Capturamos los datos que viajan en el POST
    if (isset($_POST["licencia_doctor"], $_POST["nombre_doctor"], $_POST["apellido_doctor"])) {
        $nuevo_doctor = [];
            $nuevo_doctor["licencia_doctor"] = $_POST["licencia_doctor"];
            $nuevo_doctor["nombre_doctor"] = $_POST["nombre_doctor"];
            $nuevo_doctor["apellido_doctor"] = $_POST["apellido_doctor"];

        // 2. Acumulamos la nueva cita dentro del array guardado en la SESIÓN
        $_SESSION["lista_doctores"][] = $nuevo_doctor;


        // 3. Mandar a base de datos el nuevo doctor
        $resultado = PA_AltaDoctor(
            $_POST["licencia_doctor"],
            $_POST["nombre_doctor"],
            $_POST["apellido_doctor"]
        );

        if($resultado === -1){

        $mensaje = "Nombre vacio.";

        }
        else if ($resultado === -2)
        {
        $mensaje = "Apellido vacio.";

        }
        else if($resultado === -3){

        $mensaje = "Licencia vacia.";

        }
        else{
            $mensaje = "Doctor dado de alta correctamente.";
        }

    }
}


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Centro Médico - Cuadro de Doctores</title>
</head>
<body>

    <h2>Gestión de Doctores</h2>

    <?php if (isset($mensaje)): ?>
        <p style="color: green; font-weight: bold;"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <h3>Doctores dados de alta (Total: <?php echo count($_SESSION['lista_doctores']); ?>)</h3>

    <!-- PASO E: Renderizar la tabla si existen citas guardadas -->
    <?php if (!empty($_SESSION['lista_doctores'])): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Nº Licencia</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['lista_doctores'] as $indice => $doctor): ?>
                    <tr>
                        <td><?php echo $doctor["licencia_doctor"]; ?></td>
                        <td><?php echo $doctor["nombre_doctor"]; ?></td>
                        <td><?php echo $doctor["apellido_doctor"]; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>


    <?php else: ?>
        <p>No hay doctores actualmente.</p>
    <?php endif; ?>

    <br><hr>
    <a href="portal_citas.html"> +Volver al portal</a>

</body>
</html>