<?php
/**
 * Live ship position data via AIS.
 *
 * Primary source: Digitraffic Finland AIS (Fintraffic) — free, open, no auth required.
 *   https://meri.digitraffic.fi/api/ais/v1/locations
 *   Covers Baltic Sea, North Sea + international vessels reporting to Finnish waters.
 *   License: CC BY 4.0 / digitraffic.fi
 *
 * Fallback: BarentsWatch (Norwegian waters) — requires OAuth2 keys in .env.
 *
 * Refresh: 30 seconds
 */

require_once __DIR__ . '/BaseApi.php';

BaseApi::headers(30);
BaseApi::checkRateLimit();

$cacheKey = 'ships_all';
$cached = BaseApi::getCache($cacheKey, 25);
if ($cached) {
    BaseApi::respond($cached);
}

$ships = [];

// --- Primary: Digitraffic Finland AIS (free, open REST JSON, no auth) ---
$response = BaseApi::fetch(
    'https://meri.digitraffic.fi/api/ais/v1/locations',
    null,
    [
        'Accept: application/json',
        'Digitraffic-User: ForeverBox-TheEye (eye.foreverbox.co.uk)',
    ],
    15
);

if ($response) {
    $data = json_decode($response, true);

    // Digitraffic returns a GeoJSON FeatureCollection
    if (isset($data['features']) && is_array($data['features'])) {
        foreach (array_slice($data['features'], 0, 3000) as $feature) {
            $coords = $feature['geometry']['coordinates'] ?? null;
            if (!$coords || count($coords) < 2) continue;
            $props = $feature['properties'] ?? [];
            $ships[] = [
                'mmsi'    => $props['mmsi'] ?? '',
                'name'    => $props['name'] ?? 'Unknown',
                'lat'     => (float) $coords[1],
                'lon'     => (float) $coords[0],
                'speed'   => $props['sog'] ?? 0,
                'course'  => $props['cog'] ?? 0,
                'heading' => $props['heading'] ?? 0,
                'type'    => $props['shipType'] ?? 0,
                'dest'    => $props['destination'] ?? '',
                'length'  => 0,
            ];
        }
    }
    // Also handle flat array format
    elseif (is_array($data) && isset($data[0]['mmsi'])) {
        foreach (array_slice($data, 0, 3000) as $vessel) {
            $lat = $vessel['lat'] ?? null;
            $lon = $vessel['lon'] ?? null;
            if (!$lat || !$lon) continue;
            $ships[] = [
                'mmsi'    => $vessel['mmsi'] ?? '',
                'name'    => $vessel['name'] ?? 'Unknown',
                'lat'     => (float) $lat,
                'lon'     => (float) $lon,
                'speed'   => $vessel['sog'] ?? 0,
                'course'  => $vessel['cog'] ?? 0,
                'heading' => $vessel['heading'] ?? 0,
                'type'    => $vessel['shipType'] ?? 0,
                'dest'    => $vessel['destination'] ?? '',
                'length'  => 0,
            ];
        }
    }
}

$output = json_encode([
    'ships'  => $ships,
    'count'  => count($ships),
    'time'   => time(),
    'source' => empty($ships) ? 'none' : 'digitraffic-fi',
]);

if (!empty($ships)) {
    BaseApi::setCache($cacheKey, $output);
}
BaseApi::respond($output);
