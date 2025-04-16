<?php
// Iniciar sesión para poder pasar mensajes entre páginas
session_start();

// Incluir la configuración de la base de datos
require_once '../../config/database.php';

// Verificar que se recibió un ID válido
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    // Primero verificamos si existen pagos asociados a este crédito
    $check_pagos = "SELECT COUNT(*) as total FROM pagos WHERE id_credito = $id";
    $result_pagos = $conn->query($check_pagos);
    $tiene_pagos = false;

    if ($result_pagos && $row_pagos = $result_pagos->fetch_assoc()) {
        $tiene_pagos = ($row_pagos['total'] > 0);
    }

    if ($tiene_pagos) {
        // Si hay pagos, primero hay que eliminarlos
        $delete_pagos = "DELETE FROM pagos WHERE id_credito = $id";
        $conn->query($delete_pagos);
    }

    // Ahora eliminamos el crédito
    $delete_credito = "DELETE FROM creditos WHERE id = $id";

    if ($conn->query($delete_credito) === TRUE) {
        // Éxito en la eliminación - pasamos el mensaje por GET para mostrarlo
        $_SESSION['mensaje'] = "Crédito eliminado correctamente";
        $_SESSION['tipo_mensaje'] = "exito";
        header("Location: index_creditos.php");
        exit;
    } else {
        // Error en la eliminación
        $_SESSION['mensaje'] = "No se pudo eliminar el crédito: " . $conn->error;
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: index_creditos.php");
        exit;
    }
} else {
    // ID no válido
    $_SESSION['mensaje'] = "ID de crédito no válido";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: index_creditos.php");
    exit;
}
