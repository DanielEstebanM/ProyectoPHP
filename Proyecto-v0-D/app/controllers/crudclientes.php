<?php

function crudBorrar($id)
{
    $db = AccesoDatos::getModelo();

    // Obtener la imagen del usuario antes de eliminarlo
    $cliente = $db->getCliente($id);
    if ($cliente && !empty($cliente->imagen) && file_exists($cliente->imagen)) {
        unlink($cliente->imagen); // Borrar la imagen del servidor
    }

    $resu = $db->borrarCliente($id);
    if ($resu) {
        $_SESSION['msg'] = " El usuario " . $id . " ha sido eliminado.";
    } else {
        $_SESSION['msg'] = " Error al eliminar el usuario " . $id . ".";
    }
}

function crudTerminar()
{
    AccesoDatos::closeModelo();
    session_destroy();
}

function crudAlta()
{
    $cli = new Cliente();
    $cli->imagen = ""; // Imagen vacía por defecto
    $orden = "Nuevo";
    include_once "app/views/formulario.php";
}

function crudDetalles($id)
{
    $db = AccesoDatos::getModelo();
    $cli = $db->getCliente($id);
    include_once "app/views/detalles.php";
}

function crudModificar($id)
{
    $db = AccesoDatos::getModelo();
    $cli = $db->getCliente($id);

    if (!$cli) {
        $_SESSION['msg'] = "Error: Usuario no encontrado.";
        return;
    }

    $cli = (object) [
        'id'         => $cli->id,
        'first_name' => $cli->first_name,
        'last_name'  => $cli->last_name,
        'email'      => $cli->email,
        'gender'     => $cli->gender,
        'ip_address' => $cli->ip_address,
        'telefono'   => $cli->telefono,
        'imagen'     => fotoCliente($cli->id) // Obtener la imagen del usuario
    ];

    $orden = "Modificar";
    include_once "app/views/formulario.php";
}

function validarDatos($email, $ip, $telefono, $id = null)
{
    $db = AccesoDatos::getModelo();

    // Verificar formato del correo y que no esté repetido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !$db->validarEmailUnico($email, $id)) {
        return "Correo electrónico inválido o ya en uso.";
    }

    // Verificar formato de IP
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return "Dirección IP no válida.";
    }

    // Verificar formato de teléfono (999-999-9999)
    if (!preg_match('/^\d{3}-\d{3}-\d{4}$/', $telefono)) {
        return "Formato de teléfono incorrecto. Debe ser 999-999-9999.";
    }

    return null;
}

function crudPostAlta()
{
    limpiarArrayEntrada($_POST);

    if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['email'])) {
        $_SESSION['msg'] = "Todos los campos obligatorios deben estar llenos.";
        $orden = "Nuevo";
        $cli = new Cliente();
        require_once "app/views/formulario.php";
        return;
    }

    $error = validarDatos($_POST['email'], $_POST['ip_address'], $_POST['telefono']);
    if ($error) {
        $_SESSION['msg'] = $error;
        $orden = "Nuevo";
        $cli = new Cliente();
        require_once "app/views/formulario.php";
        return;
    }

    $cli = new Cliente();
    $cli->first_name = $_POST['first_name'];
    $cli->last_name  = $_POST['last_name'];
    $cli->email      = $_POST['email'];
    $cli->gender     = $_POST['gender'];
    $cli->ip_address = $_POST['ip_address'];
    $cli->telefono   = $_POST['telefono'];

    $db = AccesoDatos::getModelo();

    if ($db->addCliente($cli)) {
        $ultimoId = $db->getUltimoId();
        if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            subirImagen($_FILES['photo'], $ultimoId);
            $_SESSION['msg'] = subirImagen($_FILES['photo'], $ultimoId);
            $orden = "Nuevo";
            require_once "app/views/formulario.php";
            return;
        }
        $_SESSION['msg'] = "El usuario ha sido creado.";
    } else {
        $_SESSION['msg'] = "Error al crear el usuario.";
    }
}

function crudPostModificar()
{
    limpiarArrayEntrada($_POST);

    if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['email'])) {
        $_SESSION['msg'] = "Todos los campos obligatorios deben estar llenos.";
        $orden = "Modificar";
        
        // Definir $cli antes de incluir el formulario
        $cli = new Cliente();
        $cli->id         = $_POST['id'] ?? null;
        $cli->first_name = $_POST['first_name'] ?? "";
        $cli->last_name  = $_POST['last_name'] ?? "";
        $cli->email      = $_POST['email'] ?? "";
        $cli->gender     = $_POST['gender'] ?? "";
        $cli->ip_address = $_POST['ip_address'] ?? "";
        $cli->telefono   = $_POST['telefono'] ?? "";
        $cli->imagen     = "";

        require_once "app/views/formulario.php";
        return;
    }

    $db = AccesoDatos::getModelo();
    $clienteActual = $db->getCliente($_POST['id']);

    if (!$clienteActual) {
        $_SESSION['msg'] = "Error: Usuario no encontrado.";
        return;
    }

    // Definir el objeto Cliente con los valores actuales
    $cli = new Cliente();
    $cli->id         = $_POST['id'];
    $cli->first_name = $_POST['first_name'];
    $cli->last_name  = $_POST['last_name'];
    $cli->email      = $_POST['email'];
    $cli->gender     = $_POST['gender'];
    $cli->ip_address = $_POST['ip_address'];
    $cli->telefono   = $_POST['telefono'];
    $cli->imagen     = $clienteActual->imagen; // Mantener la imagen actual por defecto

    // Validaciones
    if (!filter_var($cli->email, FILTER_VALIDATE_EMAIL) || !$db->validarEmailUnico($cli->email, $cli->id)) {
        $_SESSION['msg'] = "Correo electrónico inválido o ya en uso.";
        $orden = "Modificar";
        require_once "app/views/formulario.php";
        return;
    }

    if (!filter_var($cli->ip_address, FILTER_VALIDATE_IP)) {
        $_SESSION['msg'] = "Dirección IP no válida.";
        $orden = "Modificar";
        require_once "app/views/formulario.php";
        return;
    }

    if (!preg_match('/^\d{3}-\d{3}-\d{4}$/', $cli->telefono)) {
        $_SESSION['msg'] = "Formato de teléfono incorrecto. Debe ser 999-999-9999.";
        $orden = "Modificar";
        require_once "app/views/formulario.php";
        return;
    }

    // Variable para detectar si se cambió la imagen
    $imagenModificada = false;

    // Manejo de la imagen
    if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $resultadoImagen = subirImagen($_FILES['photo'], $cli->id);

        // Si el resultado es un mensaje de error, detener la modificación
        if ($resultadoImagen === "Tipo de archivo no permitido. Solo se permiten imágenes JPG y PNG." ||
            $resultadoImagen === "El archivo excede el tamaño máximo permitido (500 KB)." ||
            $resultadoImagen === "Error al cargar la imagen.") {
            
            $_SESSION['msg'] = $resultadoImagen;
            $orden = "Modificar";
            require_once "app/views/formulario.php";
            return;
        }

        // Si la imagen se subió correctamente, eliminar la anterior y actualizar el campo de imagen
        if ($resultadoImagen) {
            if (!empty($clienteActual->imagen) && file_exists($clienteActual->imagen)) {
                unlink($clienteActual->imagen);
            }
            $cli->imagen = $resultadoImagen;
            $imagenModificada = true;
        }
    }

    // Intentar actualizar el cliente en la base de datos
    if ($db->modCliente($cli)) {
        $_SESSION['msg'] = "El usuario ha sido modificado.";
    } else {
        if (!$imagenModificada) {
            $_SESSION['msg'] = "Error al modificar el usuario.";
        }
    }
}
