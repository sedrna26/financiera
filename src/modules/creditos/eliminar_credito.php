<?php
require_once '../../config/database.php';

// Verificar si se recibió el ID del crédito
if (!isset($_GET['id'])) {
    header("Location: index_creditos.php?error=ID no especificado");
    exit();
}

$credito_id = intval($_GET['id']);

// 1. Obtener el cliente asociado al crédito antes de eliminarlo
$query_cliente = "SELECT cliente_id FROM creditos WHERE id = ?";
$stmt_cliente = $conn->prepare($query_cliente);
$stmt_cliente->bind_param("i", $credito_id);
$stmt_cliente->execute();
$result_cliente = $stmt_cliente->get_result();
$cliente_id = $result_cliente->fetch_assoc()['cliente_id'];

// 2. Eliminar el crédito
$delete_query = "DELETE FROM creditos WHERE id = ?";
$stmt_delete = $conn->prepare($delete_query);
$stmt_delete->bind_param("i", $credito_id);
$stmt_delete->execute();

// 3. Verificar si el cliente aún tiene créditos
$check_creditos = "SELECT COUNT(*) AS total FROM creditos WHERE cliente_id = ?";
$stmt_check = $conn->prepare($check_creditos);
$stmt_check->bind_param("i", $cliente_id);
$stmt_check->execute();
$resultado = $stmt_check->get_result()->fetch_assoc();

// 4. Actualizar estado del cliente si no tiene créditos
if ($resultado['total'] == 0) {
    $update_cliente = "UPDATE clientes SET estado = 'Inactivo' WHERE id_cliente = ?";
    $stmt_update = $conn->prepare($update_cliente);
    $stmt_update->bind_param("i", $cliente_id);
    $stmt_update->execute();
}

header("Location: index_creditos.php?mensaje=Crédito eliminado");
exit();