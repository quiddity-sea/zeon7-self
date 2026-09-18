<?php
/**
 * Traffic congestion data.
 * Source: TomTom Traffic Flow API (free tier: 2,500 requests/day).
 * Fallback: OpenStreetMap major road network.
 *
 * For the initial build without a TomTom key, we return major road
 * segments from OpenStreetMap for the current viewport (passed as query params).
 * Refresh: 5 minutes
 */

require_once __DIR__ . '/BaseApi.php';

BaseApi::headers(300);
BaseApi::checkRateLimit();

// Get viewport bounds from query params
$south = (float) ($_GET['south'] ?? 50.0);
$west  = (float) ($_GET['west'] ?? -1.0);
$north = (float) ($_GET['north'] ?? 52.0);
$east  = (float) ($_GET['east'] ?? 1.0);

// Clamp bbox to reasonable size
$latSpan = min(abs($north - $south), 2.0);
$lonSpan = min(abs($east - $west), 2.0);

$cacheKey = "traffic_{$south}_{$west}_{$north}_{$east}";
$cached = BaseApi::getCache($cacheKey, 240);
if ($cached) {
    BaseApi::respond($cached);
}

// Fetch major roads from Overpass
$bbox = "{$south},{$west},{$north},{$east}";
$query = "[out:json][timeout:10];way[\"highway\"~\"motorway|trunk|primary\"]({$bbox});out geom 200;";
$response = BaseApi::fetch(
    'https://overpass-api.de/api/interpreter?data=' . urlencode($query),
    null,
    ['Accept: application/json'],
    12
);

$roads = [];

if ($response) {
    $data = json_decode($response, true);
    if (isset($data['elements'])) {
        foreach ($data['elements'] as $el) {
            if (empty($el['geometry'])) continue;
            $coords = array_map(function ($pt) {
                return [$pt['lon'], $pt['lat']];
            }, $el['geometry']);

            $roads[] = [
                'id'      => $el['id'],
                'name'    => $el['tags']['name'] ?? $el['tags']['ref'] ?? 'Unknown',
                'type'    => $el['tags']['highway'] ?? '',
                'coords'  => $coords,
            ];
        }
    }
}

$output = json_encode(['roads' => $roads, 'count' => count($roads), 'time' => time(), 'source' => empty($roads) ? 'none' : 'overpass']);
BaseApi::setCache($cacheKey, $output);
BaseApi::respond($output);
