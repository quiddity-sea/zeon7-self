<?php
/**
 * Active wildfire / thermal hotspot data.
 * Primary: NASA FIRMS (Fire Information for Resource Management System).
 * URL: https://firms.modaps.eosdis.nasa.gov/api/area/csv/{MAP_KEY}/VIIRS_SNPP_NRT/world/1
 * Returns CSV of fire detections in the last 24 hours.
 * Free API key required: https://firms.modaps.eosdis.nasa.gov/api/
 * Refresh: 5 minutes
 */

require_once __DIR__ . '/BaseApi.php';

BaseApi::headers(300);
BaseApi::checkRateLimit();

$cacheKey = 'fires_global';
$cached = BaseApi::getCache($cacheKey, 240);
if ($cached) {
    BaseApi::respond($cached);
}

$firmsKey = $_ENV['NASA_FIRMS_KEY'] ?? '';
$fires = [];

if ($firmsKey) {
    // FIRMS CSV API
    $url = "https://firms.modaps.eosdis.nasa.gov/api/area/csv/{$firmsKey}/VIIRS_SNPP_NRT/world/1";
    $csvData = BaseApi::fetch($url, null, [], 15);

    if ($csvData) {
        $lines = explode("\n", $csvData);
        $header = str_getcsv(array_shift($lines));
        $latIdx = array_search('latitude', $header);
        $lonIdx = array_search('longitude', $header);
        $brightIdx = array_search('bright_ti4', $header);
        $confIdx = array_search('confidence', $header);
        $dateIdx = array_search('acq_date', $header);
        $timeIdx = array_search('acq_time', $header);
        $frpIdx = array_search('frp', $header);

        foreach (array_slice($lines, 0, 2000) as $line) {
            if (empty(trim($line))) continue;
            $cols = str_getcsv($line);
            if (empty($cols[$latIdx]) || empty($cols[$lonIdx])) continue;

            $fires[] = [
                'lat'        => (float) $cols[$latIdx],
                'lon'        => (float) $cols[$lonIdx],
                'brightness' => (float) ($cols[$brightIdx] ?? 0),
                'confidence' => $cols[$confIdx] ?? '',
                'date'       => $cols[$dateIdx] ?? '',
                'time'       => $cols[$timeIdx] ?? '',
                'frp'        => (float) ($cols[$frpIdx] ?? 0),
            ];
        }
    }
}

// If no FIRMS key or fetch failed, try MODIS open feed (no key needed but less data)
if (empty($fires)) {
    $modisUrl = 'https://firms.modaps.eosdis.nasa.gov/api/area/csv/MODIS_NRT/world/1/2024-01-01';
    // This endpoint may not work without key — we catch gracefully
    // For keyless operation, return empty with a message
    $fires = [];
}

$output = json_encode([
    'fires' => $fires,
    'count' => count($fires),
    'time' => time(),
    'source' => $firmsKey ? 'NASA FIRMS VIIRS' : 'none (add NASA_FIRMS_KEY to .env)'
]);
BaseApi::setCache($cacheKey, $output);
BaseApi::respond($output);
