<?php

// PASO A: Iniciar o reanudar la sesión (OBLIGATORIO al principio)
session_start();

// PASO B: Inicializar el array de citas 'lista_citas'en la sesión si no existe aún
if (!isset($_SESSION["lista_citas"])) {
    $_SESSION["lista_citas"] = array();
}

// PASO C: Comprobar si se ha solicitado borrar todas las citas
if ($_POST ["accion"] ===  "borrar_citas") {
    // borrar todas las citas de la sesion
    unset($_SESSION["lista_citas"]);
    $_SESSION["lista_citas"] = [];
    // mensaje para mostrar
    $mensaje ="Todas las cietas han sido canceladas.";

}

// PASO D: Si se recibe una nueva cita individual por $_POST, se procesa
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Capturamos los datos que viajan en el POST
    if (isset($_POST["paciente"], $_POST["especialidad"], $_POST["fecha_hora"])) {
        $nueva_cita = [
            "paciente" => $_POST["paciente"],
            "especialidad" => $_POST["especialidad"],
            "fecha_hora" => $_POST["fecha_hora"]
        ];

        // 2. Acumulamos la nueva cita dentro del array guardado en la SESIÓN
        $_SESSION["lista_citas"][] = $nueva_cita;
    }
}

    $mensaje = "Cita añadida";

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Centro Médico - Mis Citas</title>
</head>
<body>

    <h2>Gestión de Citas Médicas</h2>

    <?php if (isset($mensaje)): ?>
        <p style="color: green; font-weight: bold;"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <h3>Citas Registradas en esta Sesión (Total: <?php echo count($_SESSION['lista_citas']); ?>)</h3>

    <!-- PASO E: Renderizar la tabla si existen citas guardadas -->
    <?php if (!empty($_SESSION['lista_citas'])): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Paciente</th>
                    <th>Especialidad</th>
                    <th>Fecha y Hora</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['lista_citas'] as $indice => $cita): ?>
                    <tr>
                        <td><?php echo $cita["paciente"]; ?></td>
                        <td><?php echo $cita["especialidad"]; ?></td>
                        <td><?php echo $cita["fecha_hora"]; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>

        <!-- Formulario para vaciar la sesión -->
        <form action="gestionar_citas.php" method="post">
            <input type="hidden" name="accion" value="borrar_citas">
            <button type="submit" name="boton-cancelar" value="borrar"> 
                Cancelar todas las citas
            </button>
        </form>

    <?php else: ?>
        <p>No hay citas agendadas actualmente.</p>
    <?php endif; ?>

    <br><hr>
    <a href="portal_citas.html"> +Pedir otra cita</a>

</body>
</html>