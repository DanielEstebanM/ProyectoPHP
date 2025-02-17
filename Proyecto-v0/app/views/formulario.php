<?php

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

?>

<div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <div>
            <label for="photo">Imagen:</label>
            <br>
            <img style="border-radius: 50%;" width="100" height="120" src="<?= isset($cli->id) ? fotoCliente($cli->id) : '' ?>" alt="<?= htmlspecialchars($cli->first_name, ENT_QUOTES, 'UTF-8') ?> Photo">
            <input type="file" name="photo" />
        </div>

        <div>
            <label for="id">ID:</label>
            <input type="text" name="id" readonly value="<?= $cli->id ?>"/>
        </div>

        <div>
            <label for="first_name">Nombre:</label>
            <input type="text" id="first_name" name="first_name" value="<?= $cli->first_name; ?>"/>
        </div>

        <div>
            <label for="last_name">Apellido:</label>
            <input type="text" id="last_name" name="last_name" value="<?= $cli->last_name; ?>"/>
        </div>

        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= $cli->email; ?>"/>
        </div>

        <div>
            <label for="gender">Género:</label>
            <input type="text" id="gender" name="gender" value="<?= $cli->gender; ?>"/>
        </div>

        <div>
            <label for="ip_address">Dirección IP:</label>
            <input type="text" id="ip_address" name="ip_address" value="<?= $cli->ip_address; ?>"/>
        </div>

        <div>
            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" value="<?= $cli->telefono; ?>"/>
        </div>

        <!-- Botones de envío -->
        <div>
            <button onclick="location.href='./'">
                Volver
            </button>
            <input type="submit" name="orden" value="<?= $orden ?>"/>
        </div>

    </form>

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

</div>