<?php
require '../../../../vendor/autoload.php';
// Asegúrate de que la ruta sea correcta (desde tareas hasta vendor)

use Dompdf\Dompdf;
use Dompdf\Options;

// Configuración inicial
$options = new Options();
$options->set('isRemoteEnabled', true); // Permite usar imágenes externas
$dompdf = new Dompdf($options);

// Conexión a base de datos
include '../../../config/config.php';

// Consulto las tareas
$sentencia = $pdo->query("
    SELECT t.id_tarea, t.titulo, m.nombre_materia, t.fecha_entrega, t.estado
    FROM tareas t
    INNER JOIN materias m ON t.id_materia = m.id_materia
    ORDER BY t.fecha_entrega ASC
");

// Armamos el HTML
$html = '
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        h2 { text-align: center; color: #007BFF; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #007BFF; color: white; }
    </style>
</head>
<body>
    <h2>📘 Reporte de Tareas</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Materia</th>
                <th>Fecha Entrega</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>';

// Agregar las filas dinámicamente
while ($fila = $sentencia->fetch(PDO::FETCH_ASSOC)) {
    $html .= "<tr>
                <td>{$fila['id_tarea']}</td>
                <td>{$fila['titulo']}</td>
                <td>{$fila['nombre_materia']}</td>
                <td>{$fila['fecha_entrega']}</td>
                <td>{$fila['estado']}</td>
              </tr>";
}

$html .= '
        </tbody>
    </table>
    <br>
    <p style="text-align:right; font-size:11px; color:#888;">
        Generado automáticamente por el Gestor Escolar - ' . date('d/m/Y H:i') . '
    </p>
</body>
</html>
';

// Generar y descargar PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('reporte_tareas.pdf', ['Attachment' => true]);
?>
