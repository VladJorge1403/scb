<?php
@session_start();

// ========== 1. INCLUDES BÁSICOS ==========
include_once '../../models/conexion.php';
include_once '../../models/funciones.php';
include_once '../../controllers/funciones.php';

// ========== 2. DATOS DE SESIÓN ==========
$idrol_sesion     = $_SESSION['idrol']     ?? null;
$idusuario_sesion = $_SESSION['idusuario'] ?? null;

// ========== 3. CAPTURAR FILTRO (FECHA) ==========
$valor = $_GET['valor'] ?? '';
$valor = trim($valor);

// ========== 4. ARMAR WHERE SEGÚN FECHA + USUARIO ==========
$condiciones = [];

// Si viene fecha, filtramos por fecha (igual que en principal.php)
if ($valor !== '') {
    $condiciones[] = "fechaprestamo='$valor'";
}

// Si es docente (idrol = 2), solo sus préstamos
if ($idrol_sesion == 2 && $idusuario_sesion) {
    $condiciones[] = "idusuario='$idusuario_sesion'";
}

// Construimos el WHERE final
$where = '';
if (count($condiciones) > 0) {
    $where = ' WHERE ' . implode(' AND ', $condiciones);
}

// Si no viene nada en $valor y es admin, verá todos los registros
$sql = "SELECT * FROM prestamos $where ORDER BY fechaprestamo DESC";

// Tu CRUD original, sin parámetros extra y con "s" minúscula
$dataPrestamos = CRUD($sql, "s") ?? [];

// ========== 5. CABECERAS PARA DECIRLE AL NAVEGADOR QUE ES UN EXCEL ==========
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");

// Nombre de archivo (sin / porque rompe el nombre en Windows)
$nombreArchivo = "Prestamos Libros Fecha " . ($valor !== '' ? $valor : date("Y-m-d")) . ".xls";
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
header("Pragma: no-cache");
header("Expires: 0");

// BOM para que Excel detecte UTF-8
echo "\xEF\xBB\xBF";

// Meta opcional
echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';

// ========== 6. GENERAR LA TABLA QUE VERÁ EXCEL ==========
$cont = 0;

echo "<table border='1' style='margin: 0 auto; width: 100%;border-collapse: collapse;'>";

// Encabezados
echo "<tr style='vertical-align:middle;text-align:center;'>";
echo "<th>N°</th>";
echo "<th>Docente</th>";
echo "<th>Libro</th>";
echo "<th>Fecha Préstamo</th>";
echo "<th>Fecha Devolución</th>";
echo "<th>Estado</th>";
echo "</tr>";

// ========== 7. SI NO HAY REGISTROS, MENSAJE EN EL EXCEL ==========
if (!is_array($dataPrestamos) || count($dataPrestamos) === 0) {
    echo "<tr style='vertical-align:middle;text-align:center;'>";
    if ($valor !== '') {
        echo "<td colspan='6'><b>No se encontraron préstamos para la fecha solicitada: " . htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') . "</b></td>";
    } else {
        echo "<td colspan='6'><b>No se encontraron préstamos para los criterios seleccionados.</b></td>";
    }
    echo "</tr>";
    echo "</table>";
    exit;
}

// ========== RECORRER Y PINTAR LOS PRÉSTAMOS ==========
foreach ($dataPrestamos as $result) {
    $cont++;

    // Nombre del docente
    $docenteCompleto = buscavalor(
        "docentes",
        "CONCAT(nombres,' ',apellidos)",
        "idusuario='" . $result['idusuario'] . "'"
    );

    // Título del libro
    $tituloLibro = buscavalor("libros", "titulo", "idlibro='" . $result['idlibro'] . "'");

    // Estado legible
    $estado = ($result['estado'] == 1) ? 'En Prestamo' : 'Finalizado';

    echo "<tr>";
    echo "<td style='vertical-align:middle;text-align:center;'>{$cont}</td>";
    echo "<td>{$docenteCompleto}</td>";
    echo "<td>{$tituloLibro}</td>";
    echo "<td style='vertical-align:middle;text-align:center;'>{$result['fechaprestamo']}</td>";
    echo "<td style='vertical-align:middle;text-align:center;'>{$result['fecharetorno']}</td>";
    echo "<td style='vertical-align:middle;text-align:center;'>{$estado}</td>";
    echo "</tr>";
}
echo "</table>";
