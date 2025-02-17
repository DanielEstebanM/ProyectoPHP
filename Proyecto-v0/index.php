<?php
session_start();
define ('FPAG', 10); // Número de filas por página

require_once 'app/helpers/util.php';
require_once 'app/config/configDB.php';
require_once 'app/models/Cliente.php';
require_once 'app/models/AccesoDatosPDO.php';
require_once 'app/controllers/crudclientes.php';
require_once 'app/controllers/autenticacion.php';



//---- PAGINACIÓN ----
$midb = AccesoDatos::getModelo();
$totalfilas = $midb->numClientes();
$posfin = ($totalfilas % FPAG == 0) ? $totalfilas - FPAG : $totalfilas - $totalfilas % FPAG;

if (!isset($_SESSION['posini'])) {
    $_SESSION['posini'] = 0;
}
$posAux = $_SESSION['posini'];

// Borro cualquier mensaje previo
$_SESSION['msg'] = "";

ob_start(); // La salida se guarda en el búfer

if ($_SERVER['REQUEST_METHOD'] == "GET") {

    // Proceso las órdenes de navegación
    if (isset($_GET['nav'])) {
        switch ($_GET['nav']) {
            case "Primero":
                $posAux = 0;
                break;
            case "Siguiente":
                $posAux += FPAG;
                if ($posAux > $posfin) $posAux = $posfin;
                break;
            case "Anterior":
                $posAux -= FPAG;
                if ($posAux < 0) $posAux = 0;
                break;
            case "Ultimo":
                $posAux = $posfin;
                break;
        }
        $_SESSION['posini'] = $posAux;
    }

    // Procesar órdenes CRUD clientes
    if (isset($_GET['orden'])) {

        switch ($_GET['orden']) {

            case "Nuevo":
                crudAlta();
                break;
            case "Borrar":
                if (isset($_GET['id'])) crudBorrar($_GET['id']);
                break;
            case "Modificar":
                if (isset($_GET['id'])) crudModificar($_GET['id']);
                break;
            case "Detalles":
                if (isset($_GET['id'])) crudDetalles($_GET['id']);
                break;
            case "Terminar":
                session_destroy();
                header("Location: index.php");
                exit();
                break;
        }
    }
}
// POST Formulario de alta o de modificación
else {
    if (isset($_POST['orden'])) {
        switch ($_POST['orden']) {
            case "Nuevo":
                if (function_exists('crudPostAlta')) {
                    // Validación: comprobar si los campos están vacíos
                    if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['email']) || empty($_POST['gender']) || empty($_POST['ip_address']) || empty($_POST['telefono'])) {
                        $_SESSION['msg'] = "Por favor, complete todos los campos.";
                        include_once "app/views/formulario.php"; // Volver a mostrar el formulario
                        exit();
                    }
                    crudPostAlta();
                } else {
                    $_SESSION['msg'] = "Error: función crudPostAlta no encontrada.";
                    header("Location: ./");
                    exit();
                }
                break;

            case "Modificar":
                if (function_exists('crudPostModificar')) {
                    crudPostModificar();
                } else {
                    $_SESSION['msg'] = "Error: función crudPostModificar no encontrada.";
                    header("Location: ./");
                    exit();
                }
                break;

            case "Detalles":
                // No hago nada
                break;
        }
    }
}

// Si no hay nada en el búfer, cargo la vista por defecto
if (ob_get_length() == 0) {
    $db = AccesoDatos::getModelo();
    $posini = $_SESSION['posini'];
    $tclientes = $db->getClientes("ASC", $posini, FPAG, "id");

    require_once "app/views/list.php";
}

$contenido = ob_get_clean();
$msg = $_SESSION['msg'];

// Muestro la página principal con el contenido generado
require_once "app/views/principal.php";
