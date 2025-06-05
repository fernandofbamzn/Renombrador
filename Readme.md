# Renombrador de Ficheros de Series y Películas

Este proyecto es una aplicación web en PHP diseñada para facilitar el renombrado de archivos de video de series y películas. Su objetivo es listar y navegar por directorios, analizar nombres de ficheros para extraer información (como el nombre de la serie, la temporada y el número de episodio), conectarse a la API de TMDB para obtener datos adicionales y, a partir de una estructura predefinida con tokens, generar nuevos nombres de fichero que el usuario pueda revisar y corregir antes de aplicar el cambio.

---

## Funcionalidades

- **Listado y navegación de ficheros**  
  La aplicación recorre directorios y filtra archivos según su extensión (por defecto, formatos de video como MP4, AVI, MKV, MOV).

- **Análisis de nombres de ficheros**  
  Utilizando expresiones regulares, el sistema intenta extraer datos relevantes (nombre de serie, temporada y episodio) del nombre original del fichero. Si la extracción falla, se notifica al usuario para intervenir manualmente.

- **Conexión con la API de TMDB**  
  Permite realizar búsquedas y obtener detalles tanto de películas como de series de TV. La información se almacena en caché para reducir llamadas innecesarias a la API y mejorar el rendimiento.

- **Generación de nuevos nombres de ficheros**  
  A partir de un formato predefinido (por ejemplo, `{serie} {temporada}x{episodio} - {titulo}.{extension}`), la aplicación genera el nuevo nombre de fichero utilizando los datos extraídos y/o completados mediante la API.

- **Interfaz de usuario intuitiva**  
  Se muestra una lista de ficheros encontrados con propuestas de renombrado (incluyendo checkboxes para selección y textboxes para permitir ajustes manuales). Además, se permite al usuario ver información extraída de la API mediante pop-ups o ventanas de detalles.

---

## Estructura del Proyecto

