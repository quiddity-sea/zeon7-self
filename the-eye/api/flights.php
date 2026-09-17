<?php
/**
 * Live flight data proxy.
 * Primary: OpenSky Network (https://opensky-network.org/api/states/all)
 * Fallback: adsb.lol (https://api.adsb.lol/v2/all)
 * Refresh: 10s
 */

require_once __DIR__ . '/BaseApi.php';

BaseApi::headers(10);
BaseApi::checkRateLimit();

$cacheKey = 'flights_all';
$cached = BaseApi::getCache($cacheKey, 8);
if ($cached) {
    BaseApi::respond($cached);
}

// Primary: OpenSky
$user = $_ENV['OPENSKY_USERNAME'] ?? '';
$pass = $_ENV['OPENSKY_PASSWORD'] ?? '';
$auth = ($user && $pass) ? "{$user}:{$pass}" : null;

$response = BaseApi::fetch('https://opensky-network.org/api/states/all', $auth);

if ($response) {
    $data = json_decode($response, true);
    if ($data && isset($data['states'])) {
        BaseApi::setCache($cacheKey, $response);
        BaseApi::respond($response);
    }
}

// Fallback: adsb.lol
$baseUrl = $_ENV['ADSB_LOL_BASE_URL'] ?? 'https://api.adsb.lol';
$fallback = BaseApi::fetch("{$baseUrl}/v2/all");

if ($fallback) {
    // adsb.lol returns a different format — normalise to OpenSky format
    $adsbData = json_decode($fallback, true);
    $states = [];

    if (isset($adsbData['ac'])) {
        foreach ($adsbData['ac'] as $ac) {
            $states[] = [
                $ac['hex'] ?? '',           // icao24
                $ac['flight'] ?? '',        // callsign
                '',                          // origin_country
                time(),                      // time_position
                time(),                      // last_contact
                $ac['lon'] ?? null,          // longitude
                $ac['lat'] ?? null,          // latitude
                $ac['alt_baro'] ?? null,     // baro_altitude
                ($ac['alt_baro'] ?? 1) == 0, // on_ground
                $ac['gs'] ?? null,           // velocity (ground speed in knots -> m/s)
                $ac['track'] ?? null,        // true_track
                null,                        // vertical_rate
                null,                        // sensors
                $ac['alt_geom'] ?? null,     // geo_altitude
                $ac['squawk'] ?? '',         // squawk
                false,                       // spi
                0                            // position_source
            ];
        }
    }

    $normalised = json_encode(['time' => time(), 'states' => $states]);
    BaseApi::setCache($cacheKey, $normalised);
    BaseApi::respond($normalised);
}

// Both failed
BaseApi::respond(json_encode(['time' => time(), 'states' => []]));
