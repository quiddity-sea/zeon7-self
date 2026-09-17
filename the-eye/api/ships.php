<?php
/**
 * Live ship position data via AIS.
 * Source: MarineTraffic density tiles aren't openly available without a paid API.
 * Free alternative: Use the public vesselfinder/marinetraffic embed data or
 * AISHub (https://www.aishub.net/api) with free tier.
 *
 * For the initial build, we use a curated approach:
 * Fetch from the public Danish Maritime Authority AIS feed (publicly available)
 * and supplement with AIS data from aisstream.io when API key is available.
 *
 * Refresh: 30s
 */

require_once __DIR__ . '/BaseApi.php';

BaseApi::headers(30);
BaseApi::checkRateLimit();

$cacheKey = 'ships_all';
$cached = BaseApi::getCache($cacheKey, 25);
if ($cached) {
    BaseApi::respond($cached);
}

// Use barentswatch.no open AIS API (Norwegian Coastal Administration, free)
// This provides AIS data for Norwegian waters — a real working free AIS feed.
$response = BaseApi::fetch(
    'https://live.ais.barentswatch.no/v1/latest/combined?modelType=Full',
    null,
    ['Accept: application/json'],
    10
);

$ships = [];

if ($response) {
    $data = json_decode($response, true);
    if (is_array($data)) {
        foreach (array_slice($data, 0, 2000) as $vessel) {
            if (empty($vessel['latitude']) || empty($vessel['longitude'])) continue;
            $ships[] = [
                'mmsi'      => $vessel['mmsi'] ?? '',
                'name'      => $vessel['name'] ?? 'Unknown',
                'lat'       => (float) $vessel['latitude'],
                'lon'       => (float) $vessel['longitude'],
                'speed'     => $vessel['speedOverGround'] ?? 0,
                'course'    => $vessel['courseOverGround'] ?? 0,
                'heading'   => $vessel['trueHeading'] ?? 0,
                'type'      => $vessel['shipType'] ?? 0,
                'dest'      => $vessel['destination'] ?? '',
                'length'    => $vessel['dimensionA'] ?? 0,
            ];
        }
    }
}

$output = json_encode(['ships' => $ships, 'count' => count($ships), 'time' => time()]);
BaseApi::setCache($cacheKey, $output);
BaseApi::respond($output);
