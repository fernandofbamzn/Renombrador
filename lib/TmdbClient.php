<?php
/**
 * Clase base para conectarse a la API de TMDB.
 */
class TmdbClient {
    protected $apiKey;
    protected $baseUrl;

    public function __construct($apiKey = TMDB_API_KEY, $baseUrl = TMDB_API_URL) {
        $this->apiKey  = $apiKey;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Realiza una petición GET a la API de TMDB usando cURL.
     *
     * @param string $endpoint Endpoint (ej. '/discover/movie')
     * @param array $params Parámetros de consulta.
     * @return mixed Respuesta decodificada.
     * @throws Exception en caso de error.
     */
    protected function get($endpoint, $params = []) {
        $params['api_key'] = $this->apiKey;
        $query = http_build_query($params);
        $url = $this->baseUrl . $endpoint . '?' . $query;

        if (DEBUG_MODE) {
            error_log("DEBUG: Llamada a API: " . $url);
        }

        // Usando cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            if (DEBUG_MODE) { error_log("DEBUG: cURL error: " . $error_msg); }
            throw new Exception("Error en la petición a TMDB: " . $error_msg);
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200) {
            if (DEBUG_MODE) { error_log("DEBUG: HTTP error code: " . $httpCode); }
            throw new Exception("HTTP error al realizar la petición a TMDB: Código " . $httpCode);
        }
        $data = json_decode($response, true);
        return $data;
    }
}
?>
