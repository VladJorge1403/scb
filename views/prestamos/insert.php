<?php
@session_start();
include_once '../../models/conexion.php';
include_once '../../models/funciones.php';
include_once '../../controllers/funciones.php';

$idusuario     = $_POST['idusuario']     ?? null;
$idlibro       = $_POST['idlibro']       ?? null;
$fechaprestamo = $_POST['fechaprestamo'] ?? date('Y-m-d');
$fecharetorno  = $_POST['fecharetorno']  ?? '';
$estado        = $_POST['estado']        ?? 1;
$cantidad        = $_POST['cantidad']    ?? 1;
// Si el usuario logueado es Docente (idrol = 2), usamos siempre su idusuario
$idrol_sesion     = $_SESSION['idrol']     ?? null;
$idusuario_sesion = $_SESSION['idusuario'] ?? null;
if ($idrol_sesion == 2 && $idusuario_sesion) {
    $idusuario = $idusuario_sesion;
}

// Normalizar fechas a formato Y-m-d
if (!empty($fechaprestamo)) {
    $fechaprestamo = date('Y-m-d', strtotime($fechaprestamo));
}
if (!empty($fecharetorno)) {
    $fecharetorno = date('Y-m-d', strtotime($fecharetorno));
}

// Insertar préstamo
$insert = CRUD(
    "INSERT INTO prestamos(idusuario,idlibro,fechaprestamo,fecharetorno,estado,cantidad)
     VALUES('$idusuario','$idlibro','$fechaprestamo','$fecharetorno','$estado','$cantidad')",
    "i"
);
?>
<?php if ($insert): ?>
    <script>
        $(document).ready(function() {
            alertify.success("Préstamo registrado correctamente");
            $("#contenido-principal").load("./views/prestamos/principal.php");
        });
    </script>
<?php else: ?>
    <script>
        $(document).ready(function() {
            alertify.error("Error: el préstamo no se registró");
            $("#contenido-principal").load("./views/prestamos/principal.php");
        });
    </script>
<?php endif ?>