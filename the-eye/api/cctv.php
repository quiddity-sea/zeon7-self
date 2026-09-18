<?php
/**
 * Public CCTV / webcam camera positions.
 * Source: Windy.com webcams API (free tier, 25 results per request).
 * Alternative: OpenStreetMap Overpass query for surveillance=* tagged nodes.
 *
 * We use the Overpass API to get public webcam positions globally.
 * Refresh: 10 minutes (camera positions don't change)
 */

require_once __DIR__ . '/BaseApi.php';

BaseApi::headers(600);
BaseApi::checkRateLimit();

$cacheKey = 'cctv_global';
$cached = BaseApi::getCache($cacheKey, 500);
if ($cached) {
    BaseApi::respond($cached);
}

// Query OpenStreetMap Overpass API for webcam/surveillance nodes
// This returns real camera positions from OSM data
$overpassQuery = '[out:json][timeout:15];node["surveillance"="public"]["surveillance:type"="camera"](around:500000,51.5,-0.1);out body 500;';
$overpassUrl = 'https://overpass-api.de/api/interpreter?data=' . urlencode($overpassQuery);

$response = BaseApi::fetch($overpassUrl, null, ['Accept: application/json'], 15);

$cameras = [];

if ($response) {
    $data = json_decode($response, true);
    if (isset($data['elements'])) {
        foreach ($data['elements'] as $el) {
            if (empty($el['lat']) || empty($el['lon'])) continue;
            $cameras[] = [
                'id'   => $el['id'],
                'lat'  => (float) $el['lat'],
                'lon'  => (float) $el['lon'],
                'name' => $el['tags']['name'] ?? $el['tags']['description'] ?? 'Public Camera',
                'type' => $el['tags']['surveillance:type'] ?? 'camera',
                'zone' => $el['tags']['surveillance:zone'] ?? 'public',
            ];
        }
    }
}

$output = json_encode(['cameras' => $cameras, 'count' => count($cameras), 'time' => time()]);
BaseApi::setCache($cacheKey, $output);
BaseApi::respond($output);
