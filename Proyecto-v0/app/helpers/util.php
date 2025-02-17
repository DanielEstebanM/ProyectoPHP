<?php

/*
 *  Funciones para limpiar la entrada de posibles inyecciones
 */

function limpiarEntrada(string $entrada):string{
    $salida = trim($entrada); // Elimina espacios antes y después de los datos
    $salida = strip_tags($salida); // Elimina marcas
    return $salida;
}
// Función para limpiar todos elementos de un array
function limpiarArrayEntrada(array &$entrada){
 
    foreach ($entrada as $key => $value ) {
        $entrada[$key] = limpiarEntrada($value);
    }
}

function fotoCliente(int $id): string
{

    if (isset($id)) {


        $uploadDir = __DIR__ . '/../uploads/';
        $publicDir = 'app/uploads/';
        $fileName = sprintf('%08d', $id);

        $allowedExtensions = ['jpg', 'png'];

        foreach ($allowedExtensions as $ext) {

            $filePath = $uploadDir . $fileName . '.' . $ext;

            if (file_exists($filePath)) {

                return $publicDir . $fileName . '.' . $ext;
            }
        }

        return 'https://robohash.org/' . $id;
    }

    return false;
}

function subirImagen(array $file, int $id): ?string
{
    if (isset($file['tmp_name']) && $file['error'] === UPLOAD_ERR_OK) {

        $uploadDir = 'app/uploads/';
        $tmpName = $file['tmp_name'];
        $fileName = sprintf("%08d", $id);
        
        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png'
        ];

        $fileMimeType = mime_content_type($tmpName);
        if (!array_key_exists($fileMimeType, $allowedMimeTypes)) {
            $_SESSION['error'] = "Tipo de archivo no permitido. Solo se permiten imágenes JPG y PNG.";
            return null;
        }

        $fileExtension = $allowedMimeTypes[$fileMimeType];
        $uploadFile = $uploadDir . $fileName . '.' . $fileExtension;

        if ($file['size'] > 500 * 1024) {
            $_SESSION['error'] = "El archivo excede el tamaño máximo permitido (500 KB).";
            return null;
        }

        foreach (glob($uploadDir . $fileName . ".*") as $existingFile) {
            unlink($existingFile);
        }

        if (move_uploaded_file($tmpName, $uploadFile)) {
            return $uploadFile;
        } else {
            $_SESSION['error'] = "Error al cargar la imagen.";
        }
    }

    return null;

}

function bandera(string $ip): string
{
    $apiUrl = "http://ip-api.com/json/" . $ip;
    $response = @file_get_contents($apiUrl);

    if ($response) {

        $data = json_decode($response, true);

        if (isset($data['countryCode'])) {

            return "https://flagpedia.net/data/flags/icon/40x30/" . strtolower($data['countryCode']) . ".png";
        }
    }

    return "https://flagpedia.net/data/flags/icon/40x30/un.png";
}