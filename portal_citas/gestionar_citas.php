<?php

require_once __DIR__ . "/AccesoDatos.php";

// PASO A: Iniciar o reanudar la sesión
session_start();

// PASO B: Inicializar los datos de sesión
if (!isset($_SESSION["lista_citas"])) {
    $_SESSION["lista_citas"] = [];
}
if (!isset($_SESSION["cita_editando"])) {
    $_SESSION["cita_editando"] = null;
}

$mensaje = "";
$mensajeOk = true;

function buscarCitaPorId(array $citas, $idCita)
{
    foreach ($citas as $cita) {
        if ((int)$cita["id_cita"] === (int)$idCita) {
            return $cita;
        }
    }
    return null;
}

// Sincronizamos la sesión con la base de datos (así también obtenemos el id_cita)
$_SESSION["lista_citas"] = PA_ListaCitas();

$accion = isset($_POST["accion"]) ? $_POST["accion"] : "";

$especialidadesValidas = ["Medicina General", "Pediatría", "Dermatología", "Traumatología"];

// PASO B2: Averiguar si existe el doctor indicado
$doctorValido = true;
if (isset($_POST["doctor_asignado"]) && $_POST["doctor_asignado"] !== "") {
    $licenciaBuscar = (int)$_POST["doctor_asignado"];
    if (count(PA_DameDoctor($licenciaBuscar)) === 0) {
        $doctorValido = false;
    }
}

// PASO C: Anular una cita concreta (solo en la sesión)
if ($accion === "borrar_cita") {
    $idCita = (int)($_POST["id_cita"] ?? 0);
    $encontrada = false;
    foreach ($_SESSION["lista_citas"] as $indice => $cita) {
        if ((int)$cita["id_cita"] === $idCita) {
            unset($_SESSION["lista_citas"][$indice]);
            $encontrada = true;
            break;
        }
    }
    $_SESSION["lista_citas"] = array_values($_SESSION["lista_citas"]);
    $_SESSION["cita_editando"] = null;
    if ($encontrada) {
        $mensaje = "Cita anulada de la sesión.";
    } else {
        $mensaje = "No se ha podido anular la cita.";
        $mensajeOk = false;
    }
}

// PASO C2: Anular todas las citas (solo en la sesión)
elseif ($accion === "borrar_citas") {
    $_SESSION["lista_citas"] = [];
    $_SESSION["cita_editando"] = null;
    $mensaje = "Todas las citas han sido canceladas de la sesión.";
}

// PASO D: Mostrar el formulario para modificar la hora de una cita
elseif ($accion === "mostrar_zona_edicion") {
    $_SESSION["cita_editando"] = (int)($_POST["id_cita"] ?? 0);
}

// PASO E: Guardar la nueva hora de una cita (en sesión y en base de datos)
elseif ($accion === "actualizar_cita") {
    $idCita = (int)($_POST["id_cita"] ?? 0);
    $citaActual = buscarCitaPorId($_SESSION["lista_citas"], $idCita);
    $fechaEntrada = trim($_POST["fecha_hora"] ?? "");

    $fecha = DateTime::createFromFormat("Y-m-d\TH:i", $fechaEntrada);
    if (!$fecha) {
        $fecha = DateTime::createFromFormat("Y-m-d\TH:i:s", $fechaEntrada);
    }

    if ($citaActual === null) {
        $mensaje = "La cita ya no existe.";
        $mensajeOk = false;
        $_SESSION["cita_editando"] = null;
    } else if ($fechaEntrada === "") {
        $mensaje = "Fecha vacía.";
        $mensajeOk = false;
    } else if (!$fecha) {
        $mensaje = "Fecha no válida.";
        $mensajeOk = false;
    } else {
        $resultado = PA_ActualizarCita(
            (string)$idCita,
            $citaActual["paciente"],
            $citaActual["especialidad"],
            $fecha->format("Y-m-d H:i:s"),
            (int)$citaActual["doctor_asignado"]
        );

        $_SESSION["lista_citas"] = PA_ListaCitas();

        if ($resultado === 0) {
            $mensaje = "Hora de la cita modificada (sesión y base de datos).";
            $_SESSION["cita_editando"] = null;
        } else if ($resultado === -4) {
            $mensaje = "La nueva fecha debe ser posterior al momento actual.";
            $mensajeOk = false;
        } else {
            $mensaje = "No se ha podido modificar la cita.";
            $mensajeOk = false;
        }
    }
}

// PASO F: Si se recibe una nueva cita individual por $_POST, se procesa
elseif (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["paciente"], $_POST["especialidad"], $_POST["fecha_hora"], $_POST["doctor_asignado"])
) {
    $paciente = trim($_POST["paciente"]);
    $especialidad = trim($_POST["especialidad"]);
    $fechaEntrada = trim($_POST["fecha_hora"]);
    $doctorPost = trim($_POST["doctor_asignado"]);

    $fecha = DateTime::createFromFormat("Y-m-d\TH:i", $fechaEntrada);
    if (!$fecha) {
        $fecha = DateTime::createFromFormat("Y-m-d\TH:i:s", $fechaEntrada);
    }

    if ($doctorPost === "") {
        $mensaje = "Debe indicar un doctor.";
        $mensajeOk = false;
    } else if (!ctype_digit($doctorPost)) {
        $mensaje = "Licencia del doctor inválida.";
        $mensajeOk = false;
    } else if (!$doctorValido) {
        $mensaje = "Doctor no encontrado. Prueba otro número de licencia.";
        $mensajeOk = false;
    } else if ($paciente === "") {
        $mensaje = "Nombre vacío.";
        $mensajeOk = false;
    } else if ($especialidad === "") {
        $mensaje = "Especialidad vacía.";
        $mensajeOk = false;
    } else if (!in_array($especialidad, $especialidadesValidas, true)) {
        $mensaje = "Especialidad no válida.";
        $mensajeOk = false;
    } else if ($fechaEntrada === "") {
        $mensaje = "Fecha vacía.";
        $mensajeOk = false;
    } else if (!$fecha) {
        $mensaje = "Fecha no válida.";
        $mensajeOk = false;
    } else {
        $doctor_id = (int)$doctorPost;

        $resultado = PA_AltaCita($paciente, $especialidad, $fecha->format("Y-m-d H:i:s"), $doctor_id);

        if ($resultado === -1) {
            $mensaje = "Nombre vacío.";
            $mensajeOk = false;
        } else if ($resultado === -2) {
            $mensaje = "Especialidad vacía.";
            $mensajeOk = false;
        } else if ($resultado === -3) {
            $mensaje = "Fecha vacía.";
            $mensajeOk = false;
        } else if ($resultado === -4) {
            $mensaje = "Fecha anterior al presente.";
            $mensajeOk = false;
        } else if ($resultado === -5) {
            $mensaje = "Doctor no válido.";
            $mensajeOk = false;
        } else {
            $_SESSION["lista_citas"] = PA_ListaCitas();
            $mensaje = "Cita añadida e insertada con éxito.";
        }
    }
}

// Cita que se está editando (si la hay)
$citaEditando = null;
if ($_SESSION["cita_editando"] !== null) {
    $citaEditando = buscarCitaPorId($_SESSION["lista_citas"], $_SESSION["cita_editando"]);
    if ($citaEditando === null) {
        $_SESSION["cita_editando"] = null;
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Centro Médico - Mis Citas</title>
</head>
<body>

    <h2>Gestión de Citas Médicas</h2>

    <?php if ($mensaje !== ""): ?>
        <p style="color: <?php echo $mensajeOk ? "green" : "red"; ?>; font-weight: bold;">
            <?php echo htmlspecialchars($mensaje); ?>
        </p>
    <?php endif; ?>

    <h3>Citas Registradas (Total: <?php echo count($_SESSION['lista_citas']); ?>)</h3>

    <?php if (!empty($_SESSION['lista_citas'])): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Paciente</th>
                    <th>Especialidad</th>
                    <th>Fecha y Hora</th>
                    <th>Dr. Asignado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['lista_citas'] as $cita): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cita["id_cita"]); ?></td>
                        <td><?php echo htmlspecialchars($cita["paciente"]); ?></td>
                        <td><?php echo htmlspecialchars($cita["especialidad"]); ?></td>
                        <td><?php echo htmlspecialchars($cita["fecha_hora"]); ?></td>
                        <td><?php echo htmlspecialchars($cita["doctor_asignado"] ?? ""); ?></td>
                        <td>
                            <form action="gestionar_citas.php" method="post" style="display:inline;">
                                <input type="hidden" name="accion" value="mostrar_zona_edicion">
                                <input type="hidden" name="id_cita" value="<?php echo $cita["id_cita"]; ?>">
                                <button type="submit">Modificar hora</button>
                            </form>
                            <form action="gestionar_citas.php" method="post" style="display:inline;">
                                <input type="hidden" name="accion" value="borrar_cita">
                                <input type="hidden" name="id_cita" value="<?php echo $cita["id_cita"]; ?>">
                                <button type="submit">Quitar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>

        <form action="gestionar_citas.php" method="post">
            <input type="hidden" name="accion" value="borrar_citas">
            <button type="submit">Cancelar todas las citas</button>
        </form>
    <?php endif; ?>

    <?php if ($citaEditando !== null): ?>
        <hr>
        <h3>Modificar la hora de la cita #<?php echo htmlspecialchars($citaEditando["id_cita"]); ?></h3>
        <p>
            Paciente: <strong><?php echo htmlspecialchars($citaEditando["paciente"]); ?></strong><br>
            Especialidad: <strong><?php echo htmlspecialchars($citaEditando["especialidad"]); ?></strong><br>
            Doctor: <strong><?php echo htmlspecialchars($citaEditando["doctor_asignado"]); ?></strong>
        </p>
        <form action="gestionar_citas.php" method="post">
            <input type="hidden" name="accion" value="actualizar_cita">
            <input type="hidden" name="id_cita" value="<?php echo $citaEditando["id_cita"]; ?>">

            <label for="fecha_hora">Nueva fecha y hora:</label>
            <input type="datetime-local" id="fecha_hora" name="fecha_hora"
                   value="<?php echo htmlspecialchars(str_replace(" ", "T", substr($citaEditando["fecha_hora"], 0, 16))); ?>"
                   required>
            <button type="submit">Guardar nueva hora</button>
        </form>
        <p><a href="gestionar_citas.php">Cancelar edición</a></p>
    <?php endif; ?>

    <br><hr>
    <a href="portal_citas.html"> + Pedir otra cita</a>

</body>
</html>
