<?php
/**
 * Clase para listar y navegar por directorios y ficheros.
 */
class FileNavigator {
    /**
     * Lista ficheros en un directorio filtrando por extensiones (por defecto, VIDEO_EXTENSIONS).
     *
     * @param string $dir Ruta del directorio.
     * @param array $extensions Extensiones permitidas.
     * @return array Lista de ficheros encontrados (con ruta completa).
     */
    public function listFiles($dir, $extensions = VIDEO_EXTENSIONS, $recursive = true) {
        $files = [];
        if (!is_dir($dir)) {
            return $files;
        }
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                if ($recursive) {
                    $files = array_merge($files, $this->listFiles($path, $extensions, true));
                }
                // Si no es recursivo, no se incluyen archivos de subdirectorios
            } else {
                $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
                if (in_array($ext, $extensions)) {
                    $files[] = $path;
                }
            }
        }
        return $files;
    }
    
}
?>
