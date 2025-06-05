<?php
/**
 * Clase sencilla para gestionar la caché de respuestas API.
 */
class CacheManager {
    protected $cacheDir;

    public function __construct($cacheDir = CACHE_DIR) {
        $this->cacheDir = $cacheDir;
        if (!file_exists($this->cacheDir)) {
            if (!mkdir($this->cacheDir, 0777, true)) {
                if (DEBUG_MODE) {
                    error_log("DEBUG: No se pudo crear el directorio de caché: " . $this->cacheDir);
                }
                throw new Exception("Error al crear el directorio de caché.");
            }
        }
    }

    /**
     * Guarda datos en caché.
     *
     * @param string $key Clave de la caché.
     * @param mixed $data Datos a guardar.
     * @param int $ttl Tiempo de vida en segundos.
     */
    public function set($key, $data, $ttl = 3600) {
        $cacheData = [
            'expires' => time() + $ttl,
            'data'    => $data
        ];
        $file = $this->getCacheFile($key);
        if (file_put_contents($file, serialize($cacheData)) === false && DEBUG_MODE) {
            error_log("DEBUG: Error escribiendo la caché en " . $file);
        }
    }

    /**
     * Recupera datos de caché.
     *
     * @param string $key Clave de la caché.
     * @return mixed|null Los datos si existen y no han expirado, o null.
     */
    public function get($key) {
        $file = $this->getCacheFile($key);
        if (!file_exists($file)) {
            if (DEBUG_MODE) { error_log("DEBUG: No existe caché para clave " . $key); }
            return null;
        }
        $cacheData = unserialize(file_get_contents($file));
        if ($cacheData['expires'] < time()) {
            unlink($file);
            if (DEBUG_MODE) { error_log("DEBUG: Caché expirada para clave " . $key); }
            return null;
        }
        return $cacheData['data'];
    }

    protected function getCacheFile($key) {
        return $this->cacheDir . '/' . md5($key) . '.cache';
    }
}
?>
