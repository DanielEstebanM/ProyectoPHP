<hr>
<form method="POST" enctype="multipart/form-data">

    <div>
        <label for="photo">Imagen:</label>
        <br>
        <img style="border-radius: 50%;" width="100" height="120"
            src="<?= isset($cli->id) ? fotoCliente($cli->id) : '' ?>"
            alt="<?= htmlspecialchars($cli->first_name, ENT_QUOTES, 'UTF-8') ?>" 
            <?= ($orden == "Nuevo") ? "style='display: none;'" : '' ?>>
        <br>
        <input type="file" name="photo" />
    </div>
    <br>

    <label for="id">Id:</label>
    <input type="text" name="id" readonly value="<?= ($orden == "Nuevo") ? (isset($_POST['id']) ? $_POST['id'] : '') : $cli->id ?>">

    <label for="first_name">Nombre:</label>
    <input type="text" id="first_name" name="first_name" value="<?= ($orden == "Nuevo") ? (isset($_POST['first_name']) ? $_POST['first_name'] : '') : $cli->first_name ?>">

    <label for="last_name">Apellido:</label>
    <input type="text" id="last_name" name="last_name" value="<?= ($orden == "Nuevo") ? (isset($_POST['last_name']) ? $_POST['last_name'] : '') : $cli->last_name ?>">

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" value="<?= ($orden == "Nuevo") ? (isset($_POST['email']) ? $_POST['email'] : '') : $cli->email ?>">

    <label for="gender">Género:</label>
    <input type="text" id="gender" name="gender" value="<?= ($orden == "Nuevo") ? (isset($_POST['gender']) ? $_POST['gender'] : '') : $cli->gender ?>">

    <label for="ip_address">Dirección IP:</label>
    <input type="text" id="ip_address" name="ip_address" value="<?= ($orden == "Nuevo") ? (isset($_POST['ip_address']) ? $_POST['ip_address'] : '') : $cli->ip_address ?>">

    <label for="telefono">Teléfono:</label>
    <input type="text" id="telefono" name="telefono" value="<?= ($orden == "Nuevo") ? (isset($_POST['telefono']) ? $_POST['telefono'] : '') : $cli->telefono ?>">


    <input type="submit" name="orden" value="<?= $orden ?>">
    <input type="submit" name="orden" value="Volver">
</form>
<br>
<hr>
<br>

<?php
if ($orden == "Modificar") {
?>

    <div style="justify-content: center; text-align: center;">
        <?php

        // Obtener el ID del cliente anterior
        $anterior = $db->getClienteAnterior($cli->id);
        // Obtener el ID del cliente siguiente
        $siguiente = $db->getClienteSiguiente($cli->id);

        ?>

        <?php if ($anterior): ?>
            <button onclick="location.href='?orden=Modificar&id=<?= $anterior ?>'">Anterior</button>
        <?php endif; ?>
        <?php if ($siguiente): ?>
            <button onclick="location.href='?orden=Modificar&id=<?= $siguiente ?>'">Siguiente</button>
        <?php endif; ?>
    </div>

<?php
}
?>