<?php
include('../../config.php');
include('../../../observers/Subject.php');
include('../../../observers/NotificacionObserver.php');
require '../../../config/email.php'; // ✅ Tu clase Email con PHPMailer

// PATRÓN OBSERVER
$subject = new Subject();
$notificacionObserver = new NotificacionObserver();
$subject->addObserver($notificacionObserver);

// Obtengo los datos del formulario
$id_materia = $_POST['id_materia'];
$titulo = $_POST['titulo'];
$descripcion = $_POST['descripcion'];
$fecha_entrega = $_POST['fecha_entrega'];
$hora_entrega = $_POST['hora_entrega'];

// Obtengo el nombre de la materia
$sentencia = $pdo->prepare("SELECT nombre_materia FROM materias WHERE id_materia = ?");
$sentencia->execute([$id_materia]);
$materia = $sentencia->fetch(PDO::FETCH_ASSOC);
$nombre_materia = $materia['nombre_materia'];

// Inserto la nueva tarea en la base de datos
$sentencia = $pdo->prepare("
    INSERT INTO tareas (id_materia, titulo, descripcion, fecha_entrega, hora_entrega, estado)
    VALUES (?, ?, ?, ?, ?, 'Pendiente')
");
$sentencia->execute([$id_materia, $titulo, $descripcion, $fecha_entrega, $hora_entrega]);

// Obtengo el ID de la tarea recién creada
$id_tarea = $pdo->lastInsertId();

// Notifico la creación de la tarea
$mensaje = "Se ha creado una nueva tarea en la materia $nombre_materia: $titulo";
$subject->notifyObservers(['mensaje' => $mensaje, 'id_tarea' => $id_tarea]);

// --- ✅ ENVÍO AUTOMÁTICO DE CORREO ---
session_start();

if (isset($_SESSION['sesion email'])) {
    $correo_usuario = $_SESSION['sesion email'];
    $asunto = "Nueva tarea en la materia: $nombre_materia";

    $mensaje_html = "
        <div style='font-family:Arial,sans-serif;color:#333;'>
            <h2>📘 Nueva tarea creada</h2>
            <p>Se ha creado una nueva tarea en la materia <b>$nombre_materia</b>.</p>
            <p><b>Título:</b> $titulo</p>
            <p><b>Descripción:</b> $descripcion</p>
            <p><b>Fecha de entrega:</b> $fecha_entrega</p>
            <p><b>Hora límite:</b> $hora_entrega</p>
            <hr>
            <p style='font-size:12px;color:#888;'>Este mensaje fue generado automáticamente por el Gestor Escolar.</p>
        </div>
    ";

    // Envío del correo con PHPMailer
    $email = new Email();
    $email->enviarCorreo($correo_usuario, $asunto, $mensaje_html);
}
// -------------------------------------

// Verifico si la tarea está próxima a vencer
$fecha_actual = new DateTime();
$fecha_entrega_dt = new DateTime($fecha_entrega);
$intervalo = $fecha_actual->diff($fecha_entrega_dt);

if ($intervalo->days <= 2 && $intervalo->invert == 0) {
    $mensaje_vencimiento = "La tarea '$titulo' de la materia '$nombre_materia' está próxima a vencer";
    $subject->notifyObservers(['mensaje' => $mensaje_vencimiento, 'id_tarea' => $id_tarea]);
}

// Mensaje de éxito
$_SESSION['mensaje'] = "✅ Se ha creado la tarea con éxito";
$_SESSION['icono'] = "success";

// Redirijo al listado de tareas
header('Location: ../../../admin/tareas/index.php');
exit();
?>
