<?php
/**
 * Earthquake data.
 * Primary: USGS FDSN earthquake feed (GeoJSON).
 * URL: https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/all_day.geojson
 * Returns all earthquakes in the last 24 hours as GeoJSON FeatureCollection.
 * Refresh: 60s
 */

require_once __DIR__ . '/BaseApi.php';

BaseApi::headers(60);
BaseApi::checkRateLimit();

$cacheKey = 'earthquakes_day';
$cached = BaseApi::getCache($cacheKey, 50);
if ($cached) {
    BaseApi::respond($cached);
}

// Primary: USGS past day, all magnitudes
$response = BaseApi::fetch('https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/all_day.geojson');

if ($response) {
    $data = json_decode($response, true);
    if ($data && isset($data['features'])) {
        BaseApi::setCache($cacheKey, $response);
        BaseApi::respond($response);
    }
}

// Fallback: USGS past hour (smaller dataset, more likely to succeed)
$fallback = BaseApi::fetch('https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/all_hour.geojson');
if ($fallback) {
    BaseApi::setCache($cacheKey, $fallback);
    BaseApi::respond($fallback);
}

// Return empty GeoJSON FeatureCollection
BaseApi::respond(json_encode([
    'type' => 'FeatureCollection',
    'features' => [],
    'metadata' => ['generated' => time() * 1000, 'count' => 0, 'title' => 'No data available']
]));
