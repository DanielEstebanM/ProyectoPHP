<?php

require_once 'app/controllers/auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    Autenticacion::login($_POST['usuario'], $_POST['password']);
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

?>

<div style="height: 600px;">

    <div style="max-width: 400px; width: 100%; justify-content: center; text-align: center;">

        <h2>INICIO SESIÓN</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">

            <div>
                <label for="usuario" class="form-label">Usuario:</label>
                <input type="text" name="usuario" id="usuario" required>
            </div>

            <div>
                <label for="password">Contraseña:</label>
                <input type="password" name="password" id="password" required>
            </div>

            <button type="submit">Iniciar sesión</button>

        </form>

    </div>

</div>