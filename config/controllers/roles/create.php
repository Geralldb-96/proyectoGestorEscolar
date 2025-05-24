<?php
include('../../config.php');

$nombre_rol = $_POST['nombre_rol'];
$nombre_rol = mb_strtoupper($nombre_rol, 'UTF-8');

if ($nombre_rol == '') {
    session_start();
    $_SESSION['mensaje'] = "El nombre del rol es requerido";
    $_SESSION['icono'] = "error";
    header('Location: ../../../admin/roles/create.php');
    exit();
} else {
    $sentencia = $pdo->prepare("INSERT INTO roles(nombre_rol, hora_creacion, estado) VALUES (:nombre_rol, :hora_creacion, :estado)");
    $sentencia->bindParam('nombre_rol', $nombre_rol);
    $sentencia->bindParam('hora_creacion', $fechaHora);
    $sentencia->bindParam('estado', $estadoRegistro);

    try {
        if ($sentencia->execute()) {
            session_start();
            $_SESSION['mensaje'] = "Se registro el rol de manera correcta";
            $_SESSION['icono'] = "success";
            header('Location: ../../../admin/roles/index.php');
        } else {
            session_start();
            $_SESSION['mensaje'] = "Error al registrar el rol";
            $_SESSION['icono'] = "error";
            header('Location: ../../../admin/roles/create.php');
        }
    } catch (PDOException $e) {
        session_start();
        $_SESSION['mensaje'] = "Este rol ya existe";
        $_SESSION['icono'] = "error";
        header('Location: ../../../admin/roles/create.php');
    }
}
