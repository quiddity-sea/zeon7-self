<?php
/**
 * Satellite orbital data.
 * Source: Celestrak GP elements — JSON format.
 * Falls back to TLE text if JSON returns rate-limit message.
 * Refresh: 2 hours (Celestrak updates every 2 hours, aggressive polling = IP ban)
 */

require_once __DIR__ . '/BaseApi.php';

BaseApi::headers(7200);
BaseApi::checkRateLimit();

$cacheKey = 'satellites_active';
// Cache for 110 minutes — slightly under Celestrak's 2-hour update cycle
$cached = BaseApi::getCache($cacheKey, 6600);
if ($cached) {
    BaseApi::respond($cached);
}

$satellites = [];

// Fetch from Celestrak — JSON format GP elements
$response = BaseApi::fetch(
    'https://celestrak.org/NORAD/elements/gp.php?GROUP=active&FORMAT=json',
    null,
    ['Accept: application/json'],
    20  // longer timeout, this is a large response
);

// Validate response is actually JSON (Celestrak returns plain-text rate-limit messages sometimes)
if ($response && ltrim($response)[0] === '[') {
    $data = json_decode($response, true);
    if (is_array($data)) {
        $limit = min((int) ($_GET['limit'] ?? 500), 2000);
        foreach (array_slice($data, 0, $limit) as $sat) {
            $satellites[] = [
                'norad_id'       => $sat['NORAD_CAT_ID'] ?? '',
                'name'           => $sat['OBJECT_NAME'] ?? '',
                'epoch'          => $sat['EPOCH'] ?? '',
                'mean_motion'    => (float) ($sat['MEAN_MOTION'] ?? 0),
                'eccentricity'   => (float) ($sat['ECCENTRICITY'] ?? 0),
                'inclination'    => (float) ($sat['INCLINATION'] ?? 0),
                'ra_asc_node'    => (float) ($sat['RA_OF_ASC_NODE'] ?? 0),
                'arg_pericenter' => (float) ($sat['ARG_OF_PERICENTER'] ?? 0),
                'mean_anomaly'   => (float) ($sat['MEAN_ANOMALY'] ?? 0),
                'bstar'          => (float) ($sat['BSTAR'] ?? 0),
                'rev_num'        => (int)   ($sat['REV_AT_EPOCH'] ?? 0),
                'tle_line1'      => $sat['TLE_LINE1'] ?? '',
                'tle_line2'      => $sat['TLE_LINE2'] ?? '',
            ];
        }
    }
}

// Fallback: try TLE text format for a smaller, well-known group (ISS + Starlink)
if (empty($satellites)) {
    // Use stations.txt (ISS, Chinese Space Station, etc.) as a smaller fallback
    $tleTxt = BaseApi::fetch('https://celestrak.org/NORAD/elements/stations.txt', null, [], 10);
    if ($tleTxt && strpos($tleTxt, '1 ') !== false) {
        $lines = array_filter(array_map('trim', explode("\n", trim($tleTxt))));
        $lines = array_values($lines);
        for ($i = 0; $i + 2 < count($lines); $i += 3) {
            if (strpos($lines[$i + 1], '1 ') === 0 && strpos($lines[$i + 2], '2 ') === 0) {
                $satellites[] = [
                    'norad_id'  => trim(substr($lines[$i + 1], 2, 5)),
                    'name'      => trim($lines[$i]),
                    'tle_line1' => $lines[$i + 1],
                    'tle_line2' => $lines[$i + 2],
                ];
            }
        }
    }
}

$output = json_encode(['satellites' => $satellites, 'count' => count($satellites), 'time' => time(), 'source' => empty($satellites) ? 'none' : (isset($data) ? 'celestrak-json' : 'celestrak-tle')]);
if (!empty($satellites)) {
    BaseApi::setCache($cacheKey, $output);
}
BaseApi::respond($output);

