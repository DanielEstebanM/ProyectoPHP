<?php
// Obtener el ID del cliente anterior
$anterior = $db->getClienteAnterior($cli->id);
// Obtener el ID del cliente siguiente
$siguiente = $db->getClienteSiguiente($cli->id);
?>

<hr>
<button onclick="location.href='./'" style="background-color: green; color: white;"> Volver </button>
<br><br>
<table>
    <tr>
        <td>id:</td>
        <td><input type="number" name="id" value="<?= $cli->id ?>" readonly> </td>
        <td rowspan="7" style="text-align: center;">
        <img style="border-radius: 50%;" width="180" height="200" src="<?= fotoCliente($cli->id) ?>" alt="Foto del cliente">
        </td>
    </tr>
    <tr>
        <td>Nombre:</td>
        <td><input type="text" name="first_name" value="<?= $cli->first_name ?>" readonly> </td>
    </tr>
    </tr>
    <tr>
        <td>Apellido:</td>
        <td><input type="text" name="last_name" value="<?= $cli->last_name ?>" readonly></td>
    </tr>
    </tr>
    <tr>
        <td>Correo:</td>
        <td><input type="email" name="email" value="<?= $cli->email ?>" readonly></td>
    </tr>
    </tr>
    <tr>
        <td>Género:</td>
        <td><input type="text" name="gender" value="<?= $cli->gender ?>" readonly></td>
    </tr>
    </tr>
    <tr>
        <td>Dirección IP:
        <img src="<?= bandera($cli->ip_address) ?>" alt="Flag" width="30" />
        </td>
        <td><input type="text" name="ip_address" value="<?= $cli->ip_address ?>" readonly></td>
    </tr>
    </tr>
    <tr>
        <td>Teléfono:</td>
        <td><input type="tel" name="telefono" value="<?= $cli->telefono ?>" readonly></td>
    </tr>
    </tr>
</table>

<div style="text-align: center;">
    <?php if ($anterior): ?>
        <button onclick="location.href='?orden=Detalles&id=<?= $anterior ?>'">Anterior</button>
    <?php endif; ?>
    <?php if ($siguiente): ?>
        <button onclick="location.href='?orden=Detalles&id=<?= $siguiente ?>'">Siguiente</button>
    <?php endif; ?>
    <!-- <button onclick="location.href='index.php?orden=GenerarPDF&id=<?= $cli->id ?>'">
        Generar PDF
    </button> -->
</div>