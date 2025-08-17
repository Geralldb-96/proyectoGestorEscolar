<?php
use Dom\Entity;

include('config/config.php');
$email = $_POST['email'];
$password = $_POST['password'];

// Busco al usuario en la base de datos por su email
$sql = "SELECT * FROM usuarios WHERE email = :email AND estado = 1";
$query = $pdo->prepare($sql);
$query->execute([':email' => $email]);

$user = $query->fetch(PDO::FETCH_ASSOC);

// Si encuentro el usuario
if ($user) {
    $loginCorrecto = false;

    // 1. Primero intento validar con password_hash (bcrypt/argon2)
    if (password_verify($password, $user['password'])) {
        $loginCorrecto = true;

    // 2. Si no, pruebo con SHA-1 (formato antiguo)
    } elseif (strtoupper(sha1($password)) === strtoupper($user['password'])) {
        $loginCorrecto = true;

        // ⚠️ Migrar el hash a password_hash
        $nuevoHash = password_hash($password, PASSWORD_DEFAULT);
        $sqlUpdate = "UPDATE usuarios SET password = :pass WHERE id = :id";
        $update = $pdo->prepare($sqlUpdate);
        $update->execute([
            ':pass' => $nuevoHash,
            ':id'   => $user['id']
        ]);
    }

    if ($loginCorrecto) {
        session_start();
        $_SESSION['mensaje'] = "Bienvenido al sistema";
        $_SESSION['icono']   = "success";
        $_SESSION['sesion email'] = $email;
        $_SESSION['name']    = $user['nombres'];

        // Busco el rol del usuario para guardarlo en sesión
        $sql_role = "SELECT * FROM roles WHERE id_rol = :rol_id";
        $query = $pdo->prepare($sql_role);
        $query->execute([':rol_id' => $user['rol_id']]);
        $role = $query->fetch(PDO::FETCH_ASSOC);

        $_SESSION['role'] = $role ? $role['nombre_rol'] : '';

        // Redirijo según el rol
        if ($_SESSION['role'] === 'ADMINISTRADOR') {
            header('Location: admin/index.php');
        } else {
            header('Location: admin/home.php');
        }
        exit();
    } else {
        // ⚠️ Usuario existe pero contraseña incorrecta
        session_start();
        $_SESSION['mensaje'] = "Usuario encontrado pero la contraseña es incorrecta";
        $_SESSION['icono']   = "error";
        header('Location: index.php');
        exit();
    }
} else {
    // ⚠️ Usuario no encontrado
    session_start();
    $_SESSION['mensaje'] = "El usuario no existe en el sistema";
    $_SESSION['icono']   = "error";
    header('Location: index.php');
    exit();
}
