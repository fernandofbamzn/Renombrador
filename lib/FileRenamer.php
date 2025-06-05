<?php
/**
 * Clase para generar nuevos nombres de fichero usando tokens.
 * Formato por defecto: "{serie} {temporada}x{episodio} - {titulo}.{extension}"
 */
class FileRenamer {
    protected $format;

    public function __construct($format = '{serie} {temporada}x{episodio} - {titulo}.{extension}') {
        $this->format = $format;
    }

    /**
     * Genera el nuevo nombre de fichero sustituyendo los tokens por los valores dados.
     *
     * @param array $data Array con keys: 'serie', 'temporada', 'episodio', 'titulo', 'extension'
     * @return string Nuevo nombre generado.
     */
    public function generateName($data) {
        $newName = $this->format;
        // Asegurar que 'episodio' tenga dos dígitos
        if (isset($data['episodio'])) {
            $data['episodio'] = str_pad($data['episodio'], 2, '0', STR_PAD_LEFT);
        }
        // Reemplazar tokens de forma unificada
        $newName = strtr($newName, [
            '{serie}'      => $data['serie'] ?? '',
            '{temporada}'  => $data['temporada'] ?? '',
            '{episodio}'   => $data['episodio'] ?? '',
            '{titulo}'     => $data['titulo'] ?? '',
            '{extension}'  => $data['extension'] ?? ''
        ]);
        return $newName;
    }

    /**
     * Permite actualizar el formato de renombrado.
     *
     * @param string $format Nuevo formato.
     */
    public function setFormat($format) {
        $this->format = $format;
    }
}
?>
