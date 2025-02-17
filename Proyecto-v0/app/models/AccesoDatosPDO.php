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

    public function getUsuario($usuario): ?array
    {
        $stmt_usuario = $this->dbh->prepare("SELECT * FROM User WHERE login = :usuario LIMIT 1");
        $stmt_usuario->bindParam(':usuario', $usuario, PDO::PARAM_STR);

        if ($stmt_usuario->execute()) {
            $usuarioDB = $stmt_usuario->fetch(PDO::FETCH_ASSOC);
            return $usuarioDB ?: null;
        }

        return null;
    }

    // Devuelvo cuantos filas tiene la tabla

    public function numClientes(): int
    {
        $result = $this->dbh->query("SELECT id FROM Clientes");
        $num = $result->rowCount();
        return $num;
    }


    // SELECT Devuelvo la lista de Usuarios
    public function getClientes($d, $o, $limite, $orden): array
    {
        $tuser = [];
        // Crea la sentencia preparada
        // echo "<h1> $primero : $cuantos  </h1>";
        $columnas_validas = ['id', 'first_name', 'email', 'gender', 'ip_address', 'telefono'];

        if (!in_array($orden, $columnas_validas)) {

            $orden = 'id'; // Si el campo de orden es inválido, usar 'id' por defecto

        }

        // Prepara la consulta SQL con LIMIT y ORDER BY
        $query = "SELECT * FROM clientes ORDER BY $orden $d LIMIT :o, :limite";
        $stmt_usuarios = $this->dbh->prepare($query);

        // Asociar los parámetros para evitar inyecciones SQL
        $stmt_usuarios->bindParam(':o', $o, PDO::PARAM_INT);
        $stmt_usuarios->bindParam(':limite', $limite, PDO::PARAM_INT);

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

                $response = @file_get_contents("http://ip-api.com/json/" . $cli->ip_address);
                $data = $response ? json_decode($response, true) : null;
                $cli->lat = $data['lat'] ?? null;
                $cli->long = $data['lon'] ?? null;
            }
        }
        return $cli;
    }

    public function emailExiste($email, $id = null)
    {
        $sql = "SELECT COUNT(*) FROM clientes WHERE email = ?";
        $params = [$email];

        if ($id !== null) {
            $sql .= " AND id <> ?";
            $params[] = $id;
        }

        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // UPDATE TODO
    public function modCliente($cli): bool
    {

        $this->subirFichero($cli->file, $cli->id);

        $query = "UPDATE Clientes 
                      SET first_name = :first_name, last_name = :last_name, email = :email, 
                          gender = :gender, ip_address = :ip_address, telefono = :telefono 
                      WHERE id = :id";

        $stmt_moduser = $this->dbh->prepare($query);

        $stmt_moduser->bindValue(':first_name', $cli->first_name);
        $stmt_moduser->bindValue(':last_name', $cli->last_name);
        $stmt_moduser->bindValue(':email', $cli->email);
        $stmt_moduser->bindValue(':gender', $cli->gender);
        $stmt_moduser->bindValue(':ip_address', $cli->ip_address);
        $stmt_moduser->bindValue(':telefono', $cli->telefono);
        $stmt_moduser->bindValue(':id', $cli->id);

        $stmt_moduser->execute();
        $resu = ($stmt_moduser->rowCount() == 1);
        return $resu;
    }


    //INSERT 
    public function addCliente($cli): bool
    {
        $query = "INSERT INTO Clientes (first_name, last_name, email, gender, ip_address, telefono) 
                  VALUES (:first_name, :last_name, :email, :gender, :ip_address, :telefono)";

        $stmt_crearcli = $this->dbh->prepare($query);

        $stmt_crearcli->bindValue(':first_name', $cli->first_name, PDO::PARAM_STR);
        $stmt_crearcli->bindValue(':last_name', $cli->last_name, PDO::PARAM_STR);
        $stmt_crearcli->bindValue(':email', $cli->email, PDO::PARAM_STR);
        $stmt_crearcli->bindValue(':gender', $cli->gender, PDO::PARAM_STR);
        $stmt_crearcli->bindValue(':ip_address', $cli->ip_address, PDO::PARAM_STR);
        $stmt_crearcli->bindValue(':telefono', $cli->telefono, PDO::PARAM_STR);

        $stmt_crearcli->execute();
        return ($stmt_crearcli->rowCount() == 1);
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

    // Evito que se pueda clonar el objeto. (SINGLETON)
    public function __clone()
    {
        trigger_error('La clonación no permitida', E_USER_ERROR);
    }

    private function subirFichero($file, $id): bool //Devuelve true o false
    {
        if ($file && isset($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {

            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            $maxFileSize = 50000 * 1024;

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $fileMimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $fileSize = filesize($file['tmp_name']);

            if (!in_array($fileMimeType, $allowedMimeTypes)) {
                throw new Exception("El archivo no es una imagen JPG o PNG. Por favor, introduzca una");
            }

            if ($fileSize > $maxFileSize) {
                throw new Exception("El tamaño es superior a 500 KB");
            }

            $uploadDir = __DIR__ . '/../uploads/';

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
}
