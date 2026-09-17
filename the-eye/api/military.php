<?php
/**
 * Military/government aircraft feed.
 * Source: adsb.lol military filter (https://api.adsb.lol/v2/mil)
 * Refresh: 15s
 */

require_once __DIR__ . '/BaseApi.php';

BaseApi::headers(15);
BaseApi::checkRateLimit();

$cacheKey = 'military_all';
$cached = BaseApi::getCache($cacheKey, 12);
if ($cached) {
    BaseApi::respond($cached);
}

$baseUrl = $_ENV['ADSB_LOL_BASE_URL'] ?? 'https://api.adsb.lol';
$response = BaseApi::fetch("{$baseUrl}/v2/mil");

if ($response) {
    $data = json_decode($response, true);
    if ($data && isset($data['ac'])) {
        // Normalise to states format
        $states = [];
        foreach ($data['ac'] as $ac) {
            $states[] = [
                'hex'       => $ac['hex'] ?? '',
                'callsign'  => trim($ac['flight'] ?? ''),
                'lat'       => $ac['lat'] ?? null,
                'lon'       => $ac['lon'] ?? null,
                'alt'       => $ac['alt_baro'] ?? $ac['alt_geom'] ?? null,
                'speed'     => $ac['gs'] ?? null,
                'track'     => $ac['track'] ?? null,
                'type'      => $ac['t'] ?? '',
                'category'  => $ac['category'] ?? '',
                'squawk'    => $ac['squawk'] ?? '',
                'military'  => true
            ];
        }
        $output = json_encode(['military' => $states, 'count' => count($states), 'time' => time()]);
        BaseApi::setCache($cacheKey, $output);
        BaseApi::respond($output);
    }
}

BaseApi::respond(json_encode(['military' => [], 'count' => 0, 'time' => time()]));
