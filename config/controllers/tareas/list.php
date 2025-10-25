<?php
include('../../config.php');

// PATRON ESTRATEGY

interface Strategy
{
    public function execute();
}

class TareasByTitle implements Strategy
{
    public function execute()
    {
        global $pdo;
        $sentencia = $pdo->prepare("
            SELECT t.*, m.nombre_materia AS materia, a.ruta_archivo 
            FROM tareas t 
            LEFT JOIN materias m ON t.id_materia = m.id_materia
            LEFT JOIN archivos a ON t.id_tarea = a.id_tarea
            ORDER BY titulo
        ");
        $sentencia->execute();
        $tareas = $sentencia->fetchAll(PDO::FETCH_ASSOC);
        return $tareas;
    }
}

class TareasByDescription implements Strategy
{
    public function execute()
    {
        global $pdo;
        $sentencia = $pdo->prepare("
            SELECT t.*, m.nombre_materia AS materia, a.ruta_archivo 
            FROM tareas t 
            LEFT JOIN materias m ON t.id_materia = m.id_materia
            LEFT JOIN archivos a ON t.id_tarea = a.id_tarea
            ORDER BY descripcion
        ");
        $sentencia->execute();
        $tareas = $sentencia->fetchAll(PDO::FETCH_ASSOC);
        return $tareas;
    }
}

class TareasByEstado implements Strategy
{
    public function execute()
    {
        global $pdo;
        $sentencia = $pdo->prepare("
            SELECT t.*, m.nombre_materia AS materia, a.ruta_archivo 
            FROM tareas t 
            LEFT JOIN materias m ON t.id_materia = m.id_materia
            LEFT JOIN archivos a ON t.id_tarea = a.id_tarea
            ORDER BY estado
        ");
        $sentencia->execute();
        $tareas = $sentencia->fetchAll(PDO::FETCH_ASSOC);
        return $tareas;
    }
}

class TareasByMateria implements Strategy
{
    public function execute()
    {
        global $pdo;
        $sentencia = $pdo->prepare("
            SELECT t.*, m.nombre_materia AS materia, a.ruta_archivo 
            FROM tareas t 
            LEFT JOIN materias m ON t.id_materia = m.id_materia
            LEFT JOIN archivos a ON t.id_tarea = a.id_tarea
            ORDER BY materia
        ");
        $sentencia->execute();
        $tareas = $sentencia->fetchAll(PDO::FETCH_ASSOC);
        return $tareas;
    }
}

class TareasFecha implements Strategy
{
    public function execute()
    {
        global $pdo;
        $sentencia = $pdo->prepare("
            SELECT t.*, m.nombre_materia AS materia, a.ruta_archivo 
            FROM tareas t 
            LEFT JOIN materias m ON t.id_materia = m.id_materia
            LEFT JOIN archivos a ON t.id_tarea = a.id_tarea
            ORDER BY fecha_entrega
        ");
        $sentencia->execute();
        $tareas = $sentencia->fetchAll(PDO::FETCH_ASSOC);
        return $tareas;
    }
}

class ContextTareas
{
    private $strategy;

    public function __construct(Strategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public function executeStrategy()
    {
        return $this->strategy->execute();
    }
}




$order = $_GET['order'] ?? 'title';
$format = $_GET['format'] ?? 'json';

$strategyMap = [
    'title' => TareasByTitle::class,
    'description' => TareasByDescription::class,
    'estado' => TareasByEstado::class,
    'materia' => TareasByMateria::class,
    'fecha_entrega' => TareasFecha::class,
];

$strategyClass = $strategyMap[$order] ?? TareasByTitle::class;
$context = new ContextTareas(new $strategyClass());
$tareas = $context->executeStrategy();

$tareasMap = array_map(function ($tarea) {
    return [
        'id' => $tarea['id_tarea'],
        'titulo' => $tarea['titulo'],
        'descripcion' => $tarea['descripcion'],
        'estado' => $tarea['estado'],
        'materia' => $tarea['materia'],
        'ruta_archivo' => $tarea['ruta_archivo'],
        'fecha_entrega' => $tarea['fecha_entrega'],
        'hora_entrega' => $tarea['hora_entrega'],
    ];
}, $tareas);

// Exportación
if ($format === 'pdf') {
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);

    $fechaGeneracion = date('d/m/Y H:i');
    $html = '<h2 style="text-align:center;">Listado de Tareas</h2>';
    $html .= '<table border="1" cellspacing="0" cellpadding="5" width="100%">';
    $html .= '<thead><tr>
                <th>ID</th><th>Título</th><th>Descripción</th>
                <th>Estado</th><th>Materia</th>
                <th>Fecha Entrega</th><th>Hora Entrega</th>
              </tr></thead><tbody>';
    foreach ($tareasMap as $t) {
        $html .= "<tr>
                    <td>{$t['id']}</td>
                    <td>{$t['titulo']}</td>
                    <td>{$t['descripcion']}</td>
                    <td>{$t['estado']}</td>
                    <td>{$t['materia']}</td>
                    <td>{$t['fecha_entrega']}</td>
                    <td>{$t['hora_entrega']}</td>
                  </tr>";
    }
    $html .= '</tbody></table>';
    $html .= "<p style='text-align:right; font-size:10px;'>Generado el $fechaGeneracion</p>";

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream("tareas.pdf", ["Attachment" => true]);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['data' => $tareasMap]);
