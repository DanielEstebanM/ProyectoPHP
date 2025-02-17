<?php

function crudBorrar($id)
{
    if ($_SESSION['rol'] == 1) {
        $db = AccesoDatos::getModelo();
        $resu = $db->borrarCliente($id);
        $_SESSION['msg'] = $resu ? "El usuario $id ha sido eliminado." : "Error al eliminar el usuario $id.";
    } else {
        $_SESSION['error'] = "No puedes eliminar usuarios.";
        header("Location: ?orden=Lista");
    }
}

function crudTerminar()
{
    AccesoDatos::closeModelo();
    session_destroy();
}

function crudAlta()
{
    if ($_SESSION['rol'] == 1) {
        $cli = new Cliente();
        $orden = "Nuevo";
        include_once "app/views/formulario.php";
    } else {

        // Redirigir a una página de acceso denegado
        $_SESSION['error'] = "No puedes crear un usuario.";
        header("Location: ?orden=Lista");
    }
}

function crudDetalles($id)
{
    $db = AccesoDatos::getModelo();
    $cli = $db->getCliente($id);
    include_once "app/views/detalles.php";
}

function validarDatos($email, $ip, $telefono, $id = null)
{
    $db = AccesoDatos::getModelo();

    // Verificar si algún campo está vacío
    if (empty($nombre) || empty($apellido) || empty($email) || empty($genero) || empty($ip) || empty($telefono)) {
        return "Todos los campos son obligatorios.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "El email no tiene un formato válido.";
    }

    if ($db->emailExiste($email, $id)) {
        return "El email ya está registrado.";
    }

    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return "La dirección IP no es válida.";
    }

    if (!preg_match('/^\d{3}-\d{3}-\d{4}$/', $telefono)) {
        return "El teléfono debe tener el formato 999-999-9999.";
    }

    return null;

    if (isset($_POST['orden'])) {
        $db = AccesoDatos::getModelo();
        $orden = $_POST['orden'];

        if ($orden == "Nuevo" || $orden == "Modificar") {
            $id = $_POST['id'] ?? null;
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $email = trim($_POST['email']);
            $gender = trim($_POST['gender']);
            $ip_address = trim($_POST['ip_address']);
            $telefono = trim($_POST['telefono']);

            $error = validarDatos($email, $ip_address, $telefono, $id);

            if ($error) {
                $_SESSION['error'] = $error;
                header("Location: ?orden=$orden" . ($id ? "&id=$id" : ""));
                exit();
            }

            if ($orden == "Nuevo") {
                $db->addCliente($first_name, $last_name, $email, $gender, $ip_address, $telefono);
            } else {
                $db->modCliente($id, $first_name, $last_name, $email, $gender, $ip_address, $telefono);
            }

            $_SESSION['msg'] = "Operación realizada con éxito.";
            header("Location: ./");
            exit();
        }
    }

    function crudModificar($id)
    {
        if ($_SESSION['rol'] == 1) {
            $db = AccesoDatos::getModelo();
            $cli = $db->getCliente($id);
            $orden = "Modificar";
            include_once "app/views/formulario.php";
        } else {
            $_SESSION['error'] = "No puedes modificar un usuario.";
            echo "No puedes modificar un usuario.";
            header("Location: ?orden=Lista");
        }
    }

    function crudPostAlta()
    {
        if ($_SESSION['rol'] == 1) {
            limpiarArrayEntrada($_POST);
            $db = AccesoDatos::getModelo();

            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $email = trim($_POST['email']);
            $gender = trim($_POST['gender']);
            $ip_address = trim($_POST['ip_address']);
            $telefono = trim($_POST['telefono']);

            // Validar datos
            $validacion = validarDatos($email, $ip_address, $telefono);
            if ($validacion !== null) {
                $_SESSION['msg'] = $validacion;
                include_once "app/views/formulario.php";
                exit;
            }

            if ($db->emailExiste($email)) {
                $_SESSION['msg'] = "El correo electrónico ya está registrado.";
                include_once "app/views/formulario.php";
                exit;
            }

            $cli = new Cliente();
            $cli->first_name = $first_name;
            $cli->last_name = $last_name;
            $cli->email = $email;
            $cli->gender = $gender;
            $cli->ip_address = $ip_address;
            $cli->telefono = $telefono;

            $resu = $db->addCliente($cli);

            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {

                $photo = $_FILES['photo'];
                $uploadResult = subirImagen($photo, $lastInsertId);

                if ($uploadResult !== null) {

                    $cli->file = $uploadResult;
                }
            }

            $_SESSION['msg'] = $resu ? "Cliente añadido correctamente." : "Error al añadir el cliente.";
            header("Location: index.php");
            exit;
        } else {
        }
    }

    function crudPostModificar()
    {
        limpiarArrayEntrada($_POST);
        $db = AccesoDatos::getModelo();

        $id = $_POST['id'];
        $nombre = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);
        $email = trim($_POST['email']);
        $genero = trim($_POST['genero']);
        $ip = trim($_POST['ip']);
        $telefono = trim($_POST['telefono']);

        // Validar datos
        $validacion = validarDatos($email, $ip, $telefono, $nombre, $apellido, $genero, $id);
        if ($validacion !== null) {
            $_SESSION['msg'] = $validacion;
            include_once "app/views/formulario.php";
            exit;
        }

        if ($db->emailExiste($email, $id)) {
            $_SESSION['msg'] = "El correo electrónico ya está registrado.";
            include_once "app/views/formulario.php";
            exit;
        }

        $cli = new Cliente();
        $cli->id = $id;
        $cli->nombre = $nombre;
        $cli->apellido = $apellido;
        $cli->email = $email;
        $cli->genero = $genero;
        $cli->ip = $ip;
        $cli->telefono = $telefono;
        $resu = $db->modCliente($cli);

        $_SESSION['msg'] = $resu ? "Cliente modificado correctamente." : "Error al modificar el cliente.";
        header("Location: index.php");
        exit;
    }
}
