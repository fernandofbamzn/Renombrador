// scripts.js

// Global cache para datos de API
let apiCache = {};

/* ------------------- Funciones de Extracción ------------------- */
/**
 * Extrae el nombre de la serie o película a partir del nombre del fichero.
 */
function extractSeriesName(fileName) {
  let match = fileName.match(/^(.+?)\s*-\s*Temporada\s*\d+/i);
  if (match) return match[1].trim();
  const nameWithoutExtension = fileName.split('.').slice(0, -1).join('.');
  match = nameWithoutExtension.match(/^(.+?)(?:\.|_|-|\s)(?:S\d{2}E\d{2}|\d+x\d+)/i);
  if (match) return match[1].replace(/\./g, ' ').trim();
  return nameWithoutExtension.split(/\d/)[0].replace(/\./g, ' ').trim();
}

/**
 * Extrae la temporada y el número de episodio del nombre del fichero.
 * Para casos como "Temporada 3 ... [Cap.302]" se verifica si el número
 * del capítulo tiene 3 dígitos y comienza con el mismo dígito de la temporada;
 * en ese caso se toma el resto (por ejemplo, "302" se interpreta como "3" y "02").
 */
function extractSeasonEpisodeInfo(fileName) {
  const patterns = [
    /(\d+)x(\d+)/i,
    /S(\d{2})E(\d{2})/i,
    /Season\s*(\d+)\s*Episode\s*(\d+)/i,
    /(\d{1,2})\s*-\s*(\d{1,2})/,
    /Temporada\s*(\d+).*Cap\.?\s*(\d+)/i
  ];
  for (const pattern of patterns) {
    const match = fileName.match(pattern);
    if (match) {
      // Por defecto, el primer grupo es la temporada y el segundo el episodio.
      let seasonNumber = match[1];
      let episodeNumber = match[2];
      // Si el número del episodio tiene 3 dígitos y comienza con el número de temporada,
      // se asume que el primer dígito es redundante (ej: "302" se interpreta como "02")
      if (episodeNumber.length === 3 && episodeNumber.startsWith(seasonNumber)) {
        episodeNumber = episodeNumber.substring(1);
      }
      // Aplicar formato según configuración: temporada con SEASON_DIGITS y episodio con EPISODE_DIGITS
      seasonNumber = seasonNumber.padStart(window.SEASON_DIGITS, '0');
      episodeNumber = episodeNumber.padStart(window.EPISODE_DIGITS, '0');
      return { seasonNumber, episodeNumber };
    }
  }
  return null;
}

/**
 * Verifica si en el nombre del fichero se encuentra un título de episodio.
 */
function episodeTitleInFileName(fileName) {
  const patterns = [
    /(?:\d+x\d+|\S\d{2}\S\d{2})[_\s.-]+(.+?)(?:\(|\.(?:mp4|mkv|avi))/i,
    /(?:\d+x\d+|\S\d{2}\S\d{2})[_\s.-]+(.+)/i
  ];
  return patterns.some(pattern => pattern.test(fileName));
}

/**
 * Extrae el título del episodio del nombre del fichero, limpiando detalles no deseados.
 */
function extractEpisodeTitle(fileName) {
  const patterns = [
    /(?:\d+x\d+|\S\d{2}\S\d{2})[_\s.-]+(.+?)(?:\(|\.(?:mp4|mkv|avi))/i,
    /(?:\d+x\d+|\S\d{2}\S\d{2})[_\s.-]+(.+)/i
  ];
  for (const pattern of patterns) {
    const match = fileName.match(pattern);
    if (match) {
      return match[1].replace(/\s*\[.*\]$/, '').replace(/\./g, ' ').trim();
    }
  }
  return '';
}

/* ------------------- Funciones de Interfaz ------------------- */
/**
 * Detecta y rellena la información en una fila (solo actualiza si el input está vacío).
 */
function detectFileInfo(button) {
  const row = button.closest('tr');
  const originalName = row.querySelector('.original-name').textContent;
  if (window.DEBUG_MODE) console.log("DEBUG: Detectando info para: " + originalName);
  
  const seriesInput = row.querySelector('.detected-series');
  if (!seriesInput.value.trim()) {
    seriesInput.value = extractSeriesName(originalName);
    if (window.DEBUG_MODE) console.log("DEBUG: Serie detectada: " + seriesInput.value);
  }
  
  const info = extractSeasonEpisodeInfo(originalName);
  const seasonInput = row.querySelector('.detected-season');
  const episodeInput = row.querySelector('.detected-episode');
  if (info) {
    if (!seasonInput.value.trim()) seasonInput.value = info.seasonNumber;
    if (!episodeInput.value.trim()) episodeInput.value = info.episodeNumber;
    if (window.DEBUG_MODE) console.log("DEBUG: Temporada/Episodio detectados: " + info.seasonNumber + "x" + info.episodeNumber);
  } else {
    if (!seasonInput.value.trim()) seasonInput.value = prompt("No se detectó temporada en " + originalName + ". Ingrese manualmente:");
    if (!episodeInput.value.trim()) episodeInput.value = prompt("No se detectó el episodio en " + originalName + ". Ingrese manualmente:");
  }
  
  const titleInput = row.querySelector('.detected-title');
  if (!titleInput.value.trim() && episodeTitleInFileName(originalName)) {
    titleInput.value = extractEpisodeTitle(originalName);
    if (window.DEBUG_MODE) console.log("DEBUG: Título detectado: " + titleInput.value);
  }
}

/* Botón "Seleccionar Todos" */
document.getElementById('selectAllBtn').addEventListener('click', function() {
  const checkboxes = document.querySelectorAll('.file-checkbox');
  const allChecked = Array.from(checkboxes).every(chk => chk.checked);
  checkboxes.forEach(chk => chk.checked = !allChecked);
  if (window.DEBUG_MODE) console.log("DEBUG: 'Seleccionar Todos' activado. Nuevo estado: " + (!allChecked));
});

/* Botón global para detectar info en filas seleccionadas */
document.getElementById('detectAllBtn').addEventListener('click', function() {
  const checkboxes = document.querySelectorAll('.file-checkbox:checked');
  checkboxes.forEach(chk => {
    const row = chk.closest('tr');
    detectFileInfo(row.querySelector('.detect-btn'));
  });
});


/* ------------------- Funciones de API ------------------- */
async function getSeasonInfo(seriesId, seasonNumber) {
  if (window.DEBUG_MODE) console.log("DEBUG: Llamando a get_season_info.php con series_id=" + seriesId + ", season=" + seasonNumber);
  const response = await fetch(`get_season_info.php?series_id=${seriesId}&season=${seasonNumber}`);
  if (!response.ok) throw new Error('Error al obtener información de la temporada');
  const data = await response.json();
  if (data.error) throw new Error(data.error);
  if (window.DEBUG_MODE) console.log("DEBUG: Datos de temporada recibidos:", data);
  return data;
}

async function fetchSeriesData(seriesName) {
  if (window.DEBUG_MODE) console.log("DEBUG: Llamando a get_series_info.php para: " + seriesName);
  const response = await fetch(`get_series_info.php?series=${encodeURIComponent(seriesName)}`);
  if (!response.ok) throw new Error('Error al obtener datos de la serie');
  const data = await response.json();
  if (data.error) throw new Error(`TMDB Error: ${data.error}`);
  if (window.DEBUG_MODE) console.log("DEBUG: Datos de serie recibidos:", data);
  return data;  
}

async function fetchEpisodeInfo(seriesId, seasonNumber, episodeNumber) {
  if (window.DEBUG_MODE) console.log("DEBUG: Llamando a get_episode_info.php con series_id=" + seriesId + ", season=" + seasonNumber + ", episode=" + episodeNumber);
  const response = await fetch(`get_episode_info.php?series_id=${seriesId}&season=${seasonNumber}&episode=${episodeNumber}`);
  if (!response.ok) throw new Error('Error al obtener información del episodio');
  const data = await response.json();
  if (data.error) throw new Error(`TMDB Error: ${data.error}`);
  if (window.DEBUG_MODE) console.log("DEBUG: Datos del episodio recibidos:", data);
  return data;
}

/**
 * Muestra un diálogo para que el usuario seleccione la serie. Ahora se muestra además el nombre del fichero.
 * @param {Array} seriesResults Resultados de la búsqueda.
 * @param {string} fileName Nombre del fichero asociado.
 * @returns {Promise} Resuelve con la serie seleccionada.
 */
async function selectSeries(seriesResults, fileName) {
  if (!seriesResults || seriesResults.length === 0) {
    alert('No se encontraron series con ese criterio.');
    return null;
  }
  if (seriesResults.length === 1) return seriesResults[0];
  if (!fileName) { fileName = "Sin nombre"; }
  const selectedIndex = await new Promise(resolve => {
    const fileInfo = `<p style="margin-bottom:10px;">Fichero: ${fileName}</p>`;
    const listHtml = seriesResults.map((series, index) =>
      `<li><button onclick="window.selectSeriesIndex(${index})">
      ${series.name} (${series.first_air_date ? series.first_air_date.split('-')[0] : 'N/A'})
      </button></li>`
    ).join('');
    const dialog = document.createElement('dialog');
    dialog.innerHTML = `<h2>Seleccione la serie:</h2>${fileInfo}<ul>${listHtml}</ul>`;
    document.body.appendChild(dialog);
    dialog.showModal();
    window.selectSeriesIndex = index => {
      resolve(index);
      dialog.close();
      document.body.removeChild(dialog);
      delete window.selectSeriesIndex;
    };
  });
  return seriesResults[selectedIndex];
}


/* Botón para recuperar info vía API (por fila) */
async function retrieveApiInfo(button) {
  const row = button.closest('tr');
  const originalName = row.querySelector('.original-name').textContent;
  const titleInput = row.querySelector('.detected-title');
  if (titleInput.value.trim()) {
    alert("El título ya está definido para " + originalName);
    return;
  }
  let series = row.querySelector('.detected-series').value || prompt("No se detectó la serie para " + originalName + ". Ingrésela:");
  let season = row.querySelector('.detected-season').value || prompt("No se detectó la temporada para " + originalName + ". Ingrésela:");
  let episode = row.querySelector('.detected-episode').value || prompt("No se detectó el episodio para " + originalName + ". Ingréselo:");
  if (!series || !season || !episode) {
    return alert("Faltan datos para recuperar la info de API.");
  }
  try {
    const seriesData = await fetchSeriesData(series);
    // Se pasa el nombre del fichero al diálogo para que el usuario sepa a cuál se refiere
    const selectedSeries = await selectSeries(seriesData.results, originalName);
    if (!selectedSeries) return;
    const cacheKey = selectedSeries.id + '_' + season;
    let seasonInfo;
    if (apiCache[cacheKey]) {
      seasonInfo = apiCache[cacheKey];
      if (window.DEBUG_MODE) console.log("DEBUG: Usando caché para la temporada: " + cacheKey);
    } else {
      seasonInfo = await getSeasonInfo(selectedSeries.id, season);
      apiCache[cacheKey] = seasonInfo;
    }
    if (seasonInfo && seasonInfo.episodes) {
      const ep = seasonInfo.episodes.find(ep => ep.episode_number.toString().padStart(window.EPISODE_DIGITS, '0') === episode);
      if (ep && ep.name) {
        titleInput.value = ep.name;
        alert("Información recuperada para " + originalName);
      } else {
        titleInput.value = prompt("No se encontró el título del episodio " + episode + " para " + originalName + ". Ingréselo:");
      }
    }
  } catch (error) {
    console.error('Error recuperando info para', originalName, error);
    alert("Error: " + error.message);
  }
}

async function retrieveApiInfoBatch() {
  const rows = Array.from(document.querySelectorAll('#fileList tbody tr'));
  // Agrupar por clave: serie + "_" + temporada
  const groups = {};
  rows.forEach(row => {
    if (!row.querySelector('.file-checkbox').checked) return;
    const series = row.querySelector('.detected-series').value || row.querySelector('.original-name').textContent;
    const season = row.querySelector('.detected-season').value;
    const key = series.trim().toLowerCase() + "_" + season;
    if (!groups[key]) groups[key] = [];
    groups[key].push(row);
  });
  
  for (const key in groups) {
    // Asumimos que la información de la serie se encuentra en la primera fila del grupo
    const firstRow = groups[key][0];
    let series = firstRow.querySelector('.detected-series').value || prompt("No se detectó la serie para " + firstRow.querySelector('.original-name').textContent + ". Ingrésela:");
    let season = firstRow.querySelector('.detected-season').value || prompt("No se detectó la temporada para " + firstRow.querySelector('.original-name').textContent + ". Ingrésela:");
    if (!series || !season) continue;
    try {
      const seriesData = await fetchSeriesData(series);
      const selectedSeries = await selectSeries(seriesData.results, firstRow.querySelector('.original-name').textContent);
      if (!selectedSeries) continue;
      const cacheKey = selectedSeries.id + '_' + season;
      let seasonInfo;
      if (apiCache[cacheKey]) {
        seasonInfo = apiCache[cacheKey];
        if (window.DEBUG_MODE) console.log("DEBUG: (Batch) Usando caché para: " + cacheKey);
      } else {
        seasonInfo = await getSeasonInfo(selectedSeries.id, season);
        apiCache[cacheKey] = seasonInfo;
      }
      if (seasonInfo && seasonInfo.episodes) {
        groups[key].forEach(row => {
          const episodeInput = row.querySelector('.detected-episode');
          const titleInput = row.querySelector('.detected-title');
          const episode = episodeInput.value;
          const ep = seasonInfo.episodes.find(ep => ep.episode_number.toString().padStart(window.EPISODE_DIGITS, '0') === episode);
          if (ep && ep.name) {
            titleInput.value = ep.name;
          } else {
            titleInput.value = prompt("No se encontró el título del episodio " + episode + " para " + row.querySelector('.original-name').textContent + ". Ingréselo:");
          }
        });
      }
    } catch (error) {
      console.error("Error en búsqueda batch para grupo " + key, error);
    }
  }
}


/* ------------------- Sincronización entre Pestañas ------------------- */
/**
 * Copia las filas seleccionadas de la pestaña "Carga de Info" a la tabla de "Generación de Nombres".
 */
function populateNewNames() {
  const pattern = document.getElementById('pattern').value;
  const selectedRows = [];
  document.querySelectorAll('#fileList tbody tr').forEach(function(row) {
    if (row.querySelector('.file-checkbox').checked) {
      selectedRows.push(row);
    }
  });
  const tbody = document.querySelector('#newNameList tbody');
  tbody.innerHTML = ''; // Limpiar la tabla
  selectedRows.forEach(function(row) {
    const original = row.querySelector('.original-name').textContent;
    const series = row.querySelector('.detected-series').value;
    let season = row.querySelector('.detected-season').value;
    let episode = row.querySelector('.detected-episode').value;
    const title = row.querySelector('.detected-title').value;
    const extension = original.substring(original.lastIndexOf('.'));
    
    // Si falta algún dato, lo dejamos vacío para que el patrón se complete en blanco
    // Reemplazamos los tokens en el patrón
    let newName = pattern
      .replace(/{serie}/g, series)
      .replace(/{temporada}/g, season)
      .replace(/{episodio}/g, episode)
      .replace(/{titulo}/g, title)
      .replace(/{extension}/g, extension);

    const tr = document.createElement('tr');
    tr.innerHTML = '<td>' + original + '</td>' +
               '<td><input type="text" name="new_names[]" class="new-name" value="' + newName + '" style="width:100%; padding:8px; font-size:1.1em;">' +
               '<input type="hidden" name="files[]" value="' + original + '"></td>';

    tbody.appendChild(tr);
  });
}

/* ------------------- Cambio de Pestaña ------------------- */
document.querySelectorAll('.tab-links a').forEach(function(link) {
  link.addEventListener('click', function(e) {
    e.preventDefault();
    const target = this.getAttribute('href');
    document.querySelector('.tab.active').classList.remove('active');
    document.querySelector(target).classList.add('active');
    document.querySelector('.tab-links li.active').classList.remove('active');
    this.parentElement.classList.add('active');
    if (target === '#tab2') { populateNewNames(); }
  });
});

// Agregar evento al botón "Generar Nombres" para recalcular cuando se pulse
document.getElementById('generatePatternButton').addEventListener('click', populateNewNames);

/* ------------------- Botones de Tokens ------------------- */
document.querySelectorAll('.tokenBtn').forEach(function(btn) {
  btn.addEventListener('click', function() {
    const token = this.getAttribute('data-token');
    const patternInput = document.getElementById('pattern');
    patternInput.value += ' ' + token;
    if (window.DEBUG_MODE) console.log("DEBUG: Token agregado: " + token);
  });
});
