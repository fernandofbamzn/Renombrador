<?php
/**
 * Clase para analizar el nombre de fichero y extraer metadatos.
 * Se utilizarán expresiones regulares para identificar:
 * - Nombre de la serie o película.
 * - Temporada.
 * - Número de episodio (en formato numérico, dos dígitos).
 */
class FileParser {
    /**
     * Analiza el nombre de un fichero y devuelve un array asociativo con 'serie', 'temporada' y 'episodio'.
     * Si no se detecta, devuelve null.
     *
     * @param string $filename Nombre del fichero.
     * @return array|null
     */
    public function parse($filename) {
        // Extraemos el nombre sin la extensión
        $base = pathinfo($filename, PATHINFO_FILENAME);
        // Patrón que busca SxxExx o xxXxx, etc.
        $pattern = '/(?P<serie>.+)[\s._-]+(?:S(?P<temporada>\d{1,2})[Eex](?P<episodio>\d{1,2})|(?P<temporada2>\d{1,2})x(?P<episodio2>\d{2}))/i';
        if (preg_match($pattern, $base, $matches)) {
            $serie = trim($matches['serie']);
            $temporada = isset($matches['temporada']) ? $matches['temporada'] : $matches['temporada2'];
            $episodio = isset($matches['episodio']) ? $matches['episodio'] : $matches['episodio2'];
            return [
                'serie'      => $serie,
                'temporada'  => (int)$temporada,
                'episodio'   => (int)$episodio
            ];
        }
        return null;
    }
}
?>
