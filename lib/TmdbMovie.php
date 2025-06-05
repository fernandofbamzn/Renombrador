<?php
    require_once __DIR__ . '/TmdbClient.php'; // Asegurar que la clase base se carga primero
/**
 * Clase para trabajar con películas en TMDB.
 */
class TmdbMovie extends TmdbClient {
    /**
     * Busca películas por título.
     *
     * @param string $query Título de la película.
     * @param array $params Parámetros adicionales (idioma, página, etc.)
     * @return array Resultados de la búsqueda.
     */
    public function search($query, $params = []) {
        $params['query'] = $query;
        return $this->get('/search/movie', $params);
    }

    /**
     * Obtiene detalles de una película por ID.
     *
     * @param int $movieId ID de la película.
     * @param array $params Parámetros adicionales (append_to_response, idioma, etc.)
     * @return array Detalles de la película.
     */
    public function getDetails($movieId, $params = []) {
        return $this->get("/movie/{$movieId}", $params);
    }
}
?>
