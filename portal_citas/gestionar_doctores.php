<?php

/*
 * gestionar_doctores.php (portal_citas - versión antigua)
 *
 * ÍNDICE RÁPIDO (Ctrl+F):
 *   SESION
 *   CARGAR DOCTORES DE LA BD
 *   CAPTURAR POST
 *   VALIDAR CAMPOS
 *   LLAMAR PA_AltaDoctor
 *   CODIGOS DE RESULTADO
 *   GUARDAR EN SESION
 *   RENDER TABLA
 */

require_once __DIR__ . "/AccesoDatos.php";

// ===== SESION =====
// PASO A: Iniciar o reanudar la sesión (OBLIGATORIO al principio).
session_start();

// PASO B: Inicializar el array de doctores 'lista_doctores' en la sesión si no existe aún
if (!isset($_SESSION["lista_doctores"])) {
    $_SESSION["lista_doctores"] = array();

    // CARGAR DOCTORES DE LA BD: se leen al entrar por primera vez.
    $doctoresExistentes = PA_ListaDoctores();

    if (is_array($doctoresExistentes)) {
        // leo de la base de datos los doctores que ya existan y los meto en sesión
        foreach ($doctoresExistentes as $indice => $doctor) {
            $nuevo_doctor = [];
            $nuevo_doctor["licencia_doctor"] = $doctor["id"];
            $nuevo_doctor["nombre_doctor"] = $doctor["nombre"];
            $nuevo_doctor["apellido_doctor"] = $doctor["apellidos"]; // ✅ Corregido a 'apellidos' (plural)

            $_SESSION["lista_doctores"][] = $nuevo_doctor;
        }
    }
}

// PASO D: Si se recibe una nueva alta de doctor se procesa
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // CAPTURAR POST: los name del formulario son los índices de $_POST.
    // 1. Capturamos los datos que viajan en el POST
    if (isset($_POST["licencia_doctor"], $_POST["nombre_doctor"], $_POST["apellido_doctor"])) {

        $licencia = trim($_POST["licencia_doctor"]);
        $nombre = trim($_POST["nombre_doctor"]);
        $apellido = trim($_POST["apellido_doctor"]);

        // VALIDAR CAMPOS: primero en PHP, antes de llamar a MySQL.
        // 2. Validamos cada campo del formulario antes de tocar la base de datos
        if ($licencia === "") {
            $mensaje = "Licencia vacía.";
        }
        else if (!ctype_digit($licencia)) {
            $mensaje = "Licencia inválida: debe ser numérica.";
        }
        else if ($nombre === "") {
            $mensaje = "Nombre vacío.";
        }
        else if ($apellido === "") {
            $mensaje = "Apellido vacío.";
        }
        else {
            // LLAMAR PA_AltaDoctor: la función prepara el CALL y devuelve el OUT.
            // 3. Llamamos a la base de datos / función y guardamos el resultado
            $resultado = PA_AltaDoctor($licencia, $nombre, $apellido);

            // CODIGOS DE RESULTADO: 0 bien; negativos = error del procedure.
            // 4. Evaluamos el código que nos devolvió el resultado
            if ($resultado === -1) {
                $mensaje = "Nombre vacío.";
            } 
            else if ($resultado === -2) {
                $mensaje = "Apellido vacío.";
            } 
            else if ($resultado === -3) {
                $mensaje = "Licencia vacía.";
            } 
            else if ($resultado === -4) {
                $mensaje = "Licencia duplicada.";
            } 
            else {
                // GUARDAR EN SESION: si el alta fue bien, se añade a la tabla.
                // 5. Si todo está bien, acumulamos el doctor en la sesión
                $nuevo_doctor = [
                    "licencia_doctor" => $licencia,
                    "nombre_doctor" => $nombre,
                    "apellido_doctor" => $apellido
                ];
                $_SESSION["lista_doctores"][] = $nuevo_doctor;

                $mensaje = "Doctor dado de alta correctamente.";
            }
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

    <!-- ===== RENDER TABLA: recorre la sesión y pinta una fila por doctor ===== -->
    <!-- PASO E: Renderizar la tabla si existen doctores guardados -->
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
                        <td><?php echo htmlspecialchars($doctor["licencia_doctor"]); ?></td>
                        <td><?php echo htmlspecialchars($doctor["nombre_doctor"]); ?></td>
                        <td><?php echo htmlspecialchars($doctor["apellido_doctor"]); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>
    <?php else: ?>
        <p>No hay doctores actualmente.</p>
    <?php endif; ?>

    <br><hr>
    <a href="portal_citas.html"> + Volver al portal</a>

</body>
</html>