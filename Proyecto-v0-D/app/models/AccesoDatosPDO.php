<?php

/*
 * Acceso a datos con BD Usuarios : 
 * Usando la librería PDO *******************
 * Uso el Patrón Singleton :Un único objeto para la clase
 * Constructor privado, y métodos estáticos 
 */
class AccesoDatos
{

    private static $modelo = null;
    private $dbh = null;

    public static function getModelo()
    {
        if (self::$modelo == null) {
            self::$modelo = new AccesoDatos();
        }
        return self::$modelo;
    }

    // Constructor privado  Patron singleton

    private function __construct()
    {
        try {
            $dsn = "mysql:host=" . DB_SERVER . ";dbname=" . DATABASE . ";charset=utf8";
            $this->dbh = new PDO($dsn, DB_USER, DB_PASSWD);
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Error de conexión " . $e->getMessage();
            exit();
        }
    }

    // Cierro la conexión anulando todos los objectos relacioanado con la conexión PDO (stmt)
    public static function closeModelo()
    {
        if (self::$modelo != null) {
            $obj = self::$modelo;
            // Cierro la base de datos
            $obj->dbh = null;
            self::$modelo = null; // Borro el objeto.
        }
    }


    // Devuelvo cuantos filas tiene la tabla

    public function numClientes(): int
    {
        $result = $this->dbh->query("SELECT id FROM Clientes");
        $num = $result->rowCount();
        return $num;
    }


    // SELECT Devuelvo la lista de Usuarios
    public function getClientes($primero, $cuantos): array
    {
        $tuser = [];
        // Crea la sentencia preparada
        // echo "<h1> $primero : $cuantos  </h1>";
        $stmt_usuarios  = $this->dbh->prepare("select * from Clientes limit $primero,$cuantos");
        // Si falla termina el programa
        $stmt_usuarios->setFetchMode(PDO::FETCH_CLASS, 'Cliente');

        if ($stmt_usuarios->execute()) {
            while ($user = $stmt_usuarios->fetch()) {
                $tuser[] = $user;
            }
        }
        // Devuelvo el array de objetos
        return $tuser;
    }


    // SELECT Devuelvo un usuario o false
    public function getCliente(int $id)
    {
        $cli = false;
        $stmt_cli   = $this->dbh->prepare("select * from Clientes where id=:id");
        $stmt_cli->setFetchMode(PDO::FETCH_CLASS, 'Cliente');
        $stmt_cli->bindParam(':id', $id);
        if ($stmt_cli->execute()) {
            if ($obj = $stmt_cli->fetch()) {
                $cli = $obj;
            }
        }
        return $cli;
    }

    // UPDATE TODO
    public function modCliente($cli): bool
    {

        $this->subirFichero($cli->file, $cli->id);

        $stmt_moduser   = $this->dbh->prepare("update Clientes set first_name=:first_name,last_name=:last_name" .
            ",email=:email,gender=:gender, ip_address=:ip_address,telefono=:telefono WHERE id=:id");
        $stmt_moduser->bindValue(':first_name', $cli->first_name);
        $stmt_moduser->bindValue(':last_name', $cli->last_name);
        $stmt_moduser->bindValue(':email', $cli->email);
        $stmt_moduser->bindValue(':gender', $cli->gender);
        $stmt_moduser->bindValue(':ip_address', $cli->ip_address);
        $stmt_moduser->bindValue(':telefono', $cli->telefono);
        $stmt_moduser->bindValue(':id', $cli->id);

        if (!$this->validarEmailUnico($cli->email, $cli->id)) {
            throw new Exception("El correo electrónico ya está en uso.");
        }

        if (!$this->validarTelefono($cli->telefono)) {
            throw new Exception("Formato de teléfono inválido. Use 999-999-9999.");
        }

        if (!$this->validarIP($cli->ip_address)) {
            throw new Exception("Dirección IP inválida.");
        }


        $stmt_moduser->execute();
        $resu = ($stmt_moduser->rowCount() == 1);
        return $resu;
    }


    //INSERT 
    public function addCliente($cli): bool
    {

        // El id se define automáticamente por autoincremento.
        $stmt_crearcli  = $this->dbh->prepare(
            "INSERT INTO `Clientes`( `first_name`, `last_name`, `email`, `gender`, `ip_address`, `telefono`)" .
                "Values(?,?,?,?,?,?)"
        );
        $stmt_crearcli->bindValue(1, $cli->first_name);
        $stmt_crearcli->bindValue(2, $cli->last_name);
        $stmt_crearcli->bindValue(3, $cli->email);
        $stmt_crearcli->bindValue(4, $cli->gender);
        $stmt_crearcli->bindValue(5, $cli->ip_address);
        $stmt_crearcli->bindValue(6, $cli->telefono);
        $stmt_crearcli->execute();
        $resu = ($stmt_crearcli->rowCount() == 1);
        return $resu;
    }


    //DELETE 
    public function borrarCliente(int $id): bool
    {


        $stmt_boruser   = $this->dbh->prepare("delete from Clientes where id =:id");

        $stmt_boruser->bindValue(':id', $id);
        $stmt_boruser->execute();
        $resu = ($stmt_boruser->rowCount() == 1);
        return $resu;
    }

    // Evito que se pueda clonar el objeto. (SINGLETON)
    public function __clone()
    {
        trigger_error('La clonación no permitida', E_USER_ERROR);
    }

    public function getClienteAnterior(int $id)
    {
        $stmt = $this->dbh->prepare("SELECT id FROM Clientes WHERE id < :id ORDER BY id DESC LIMIT 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() ?: null;
    }

    public function getClienteSiguiente(int $id)
    {
        $stmt = $this->dbh->prepare("SELECT id FROM Clientes WHERE id > :id ORDER BY id ASC LIMIT 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() ?: null;
    }

    public function subirFichero($file, $id): bool
    {
        if ($file && isset($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            $maxFileSize = 500 * 1024;

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $fileMimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($fileMimeType, $allowedMimeTypes)) {
                throw new Exception("El archivo debe ser una imagen JPG o PNG.");
            }

            if (filesize($file['tmp_name']) > $maxFileSize) {
                throw new Exception("El tamaño de la imagen no debe superar 500 KB.");
            }

            $uploadDir = __DIR__ . 'app/uploads/';
            $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = sprintf('%08d', $id) . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new Exception("Error al subir la imagen.");
            }

            return true;
        }

        return false;
    }

    public function getUltimoId()
    {
        $cli = false;
        $stmt_idultimocli   = $this->dbh->prepare("select id from clientes order by id desc limit 1");
        if ($stmt_idultimocli->execute()) {
            $result = $stmt_idultimocli->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                return (int)$result['id'];
            }
        }
        return null;
    }

    public function validarEmailUnico($email, $id = null): bool
    {
        $sql = "SELECT COUNT(*) FROM Clientes WHERE email = :email";
        if ($id) {
            $sql .= " AND id <> :id";
        }
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindValue(':email', $email);
        if ($id) {
            $stmt->bindValue(':id', $id);
        }
        $stmt->execute();
        return $stmt->fetchColumn() == 0;
    }

    public function validarTelefono($telefono): bool
    {
        return preg_match('/^\d{3}-\d{3}-\d{4}$/', $telefono);
    }

    public function validarIP($ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP);
    }
}
