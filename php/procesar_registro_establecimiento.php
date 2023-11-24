<?php
include('../config/conexion.php');

try {
    if (isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
        // Recoger datos del formulario
        $nombreEstablecimiento = isset($_POST['nombre_establecimiento']) ? $_POST['nombre_establecimiento'] : null;
        $localidad = $_POST['localidad'];
        $direccion = $_POST['direccion'];
        $telefono = $_POST['telefono'];
        $nit = $_POST['nit'];
        $tipoEstablecimiento = $_POST['tipo_establecimiento'];
        $informacionAdicional = $_POST['informacion_adicional'];

        // Archivos
        $archivos = $_FILES['photos']['name'];
        $carpeta_destino = 'Imagen_guardar/';

        foreach ($archivos as $key => $archivo) {
            $nombre_archivo = $archivo;
            $archivo_temporal = $_FILES['photos']['tmp_name'][$key];
            $ruta_destino = $carpeta_destino . $nombre_archivo;

            move_uploaded_file($archivo_temporal, $ruta_destino);
        }

        // Insertar datos en la base de datos
        $sql = "INSERT INTO registro_de_establecimiento (Nombre_del_establecimiento, Direccion_de_establecimiento, Id_Usuario, Telefono, Informacion_adicional, Nit, localidad, id_tipo_de_establecimiento)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssisssss", $nombreEstablecimiento, $direccion, $_SESSION['user_id'], $telefono, $informacionAdicional, $nit, $localidad, $tipoEstablecimiento);

        if ($stmt->execute()) {
            // Obtener el ID del establecimiento recién insertado
            $idEstablecimiento = $stmt->insert_id;

            // Guardar información de las imágenes en la base de datos
            foreach ($archivos as $key => $archivo) {
                $nombre_archivo = $archivo;
                $ruta_destino = $carpeta_destino . $nombre_archivo;

                // Insertar información de la imagen en la base de datos
                $sqlImagen = "INSERT INTO imagenes_establecimiento (id_establecimiento, nombre_archivo, ruta_destino)
                    VALUES (?, ?, ?)";
                $stmtImagen = $conexion->prepare($sqlImagen);
                $stmtImagen->bind_param("iss", $idEstablecimiento, $nombre_archivo, $ruta_destino);
                $stmtImagen->execute();
            }

            echo '<div class="alert alert-success" role="alert">
                    ¡El establecimiento se registró correctamente! Serás redireccionado en 3 segundos.
                 </div>';
            header("refresh:3;url=../index.php");  // Redirige a index.php después de 3 segundos
            exit();
        } else {
            throw new Exception("Error al registrar el establecimiento: " . $stmt->error);
        }
    } else {
        throw new Exception("Error: No hay datos para procesar o el usuario no está autenticado.");
    }
} catch (Exception $e) {
    echo '<div class="alert alert-danger" role="alert">';
    echo 'Ha ocurrido un error al procesar la solicitud. Detalles del error: ' . $e->getMessage();
    echo '</div>';
    header("refresh:3;url=../main.php");  // Redirige a main.php después de 3 segundos
    exit;
}
?>
