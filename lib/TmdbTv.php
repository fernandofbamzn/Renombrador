<?php
    require_once __DIR__ . '/TmdbClient.php'; // Asegurar que la clase base se carga primero
/**
 * Clase para trabajar con series TV en TMDB.
 */
class TmdbTv extends TmdbClient {
    /**
     * Busca series TV por título.
     *
     * @param string $query Título de la serie.
     * @param array $params Parámetros adicionales (idioma, página, etc.)
     * @return array Resultados de la búsqueda.
     */
    public function search($query, $params = []) {
        $params['query'] = $query;
        return $this->get('/search/tv', $params);
    }

    /**
     * Obtiene detalles de una temporada de una serie.
     *
     * @param int $tvId ID de la serie.
     * @param int $seasonNumber Número de temporada.
     * @param array $params Parámetros adicionales (idioma, etc.)
     * @return array Detalles de la temporada.
     */
    public function getSeasonDetails($tvId, $seasonNumber, $params = []) {
        return $this->get("/tv/{$tvId}/season/{$seasonNumber}", $params);
    }
}
?>
