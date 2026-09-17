<?php
/**
 * Global radio stations with geolocation.
 * Source: radio-browser.info (free, no key required).
 * API: https://de1.api.radio-browser.info/json/stations/search
 * Refresh: 10 minutes
 */

require_once __DIR__ . '/BaseApi.php';

BaseApi::headers(600);
BaseApi::checkRateLimit();

// Optional: filter by country or coordinates
$country = $_GET['country'] ?? '';
$limit = min((int) ($_GET['limit'] ?? 500), 2000);

$cacheKey = "radio_{$country}_{$limit}";
$cached = BaseApi::getCache($cacheKey, 500);
if ($cached) {
    BaseApi::respond($cached);
}

// radio-browser.info has multiple mirrors
$mirrors = [
    'https://de1.api.radio-browser.info',
    'https://nl1.api.radio-browser.info',
    'https://at1.api.radio-browser.info',
];

$params = [
    'limit'           => $limit,
    'hidebroken'      => 'true',
    'has_geo_info'    => 'true',
    'order'           => 'clickcount',
    'reverse'         => 'true',
];
if ($country) {
    $params['country'] = $country;
}

$queryString = http_build_query($params);
$stations = [];

foreach ($mirrors as $mirror) {
    $response = BaseApi::fetch("{$mirror}/json/stations/search?{$queryString}");
    if ($response) {
        $data = json_decode($response, true);
        if (is_array($data)) {
            foreach ($data as $st) {
                if (empty($st['geo_lat']) || empty($st['geo_long'])) continue;
                $stations[] = [
                    'id'       => $st['stationuuid'] ?? '',
                    'name'     => $st['name'] ?? 'Unknown',
                    'lat'      => (float) $st['geo_lat'],
                    'lon'      => (float) $st['geo_long'],
                    'country'  => $st['country'] ?? '',
                    'url'      => $st['url_resolved'] ?? $st['url'] ?? '',
                    'codec'    => $st['codec'] ?? '',
                    'bitrate'  => (int) ($st['bitrate'] ?? 0),
                    'tags'     => $st['tags'] ?? '',
                    'votes'    => (int) ($st['votes'] ?? 0),
                    'favicon'  => $st['favicon'] ?? '',
                ];
            }
            break; // Got data, stop trying mirrors
        }
    }
}

$output = json_encode(['stations' => $stations, 'count' => count($stations), 'time' => time()]);
BaseApi::setCache($cacheKey, $output);
BaseApi::respond($output);
