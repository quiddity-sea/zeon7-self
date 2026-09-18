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

// Fallback 1: stations (space stations - ISS, CSS, etc.)
if (empty($satellites)) {
    $tleTxt = BaseApi::fetch('https://celestrak.org/NORAD/elements/gp.php?GROUP=stations&FORMAT=tle', null, [
        'Accept: text/plain',
    ], 12);
    if ($tleTxt && strlen($tleTxt) > 50 && strpos($tleTxt, '1 ') !== false) {
        $lines = array_values(array_filter(array_map('trim', explode("\n", trim($tleTxt)))));
        for ($i = 0; $i + 2 < count($lines); $i += 3) {
            if (strlen($lines[$i + 1]) > 40 && strlen($lines[$i + 2]) > 40) {
                $satellites[] = [
                    'norad_id'  => trim(substr($lines[$i + 1], 2, 5)),
                    'name'      => trim($lines[$i]),
                    'tle_line1' => $lines[$i + 1],
                    'tle_line2' => $lines[$i + 2],
                    'mean_motion'    => 0,
                    'eccentricity'   => 0,
                    'inclination'    => 0,
                    'ra_asc_node'    => 0,
                    'arg_pericenter' => 0,
                    'mean_anomaly'   => 0,
                    'bstar'          => 0,
                    'rev_num'        => 0,
                    'epoch'          => '',
                ];
            }
        }
    }
}

// Fallback 2: Try visual satellites (a different endpoint on Celestrak for visible passes)
// This uses a smaller dataset and is less likely to be rate-limited
if (empty($satellites)) {
    $tleTxt = BaseApi::fetch('https://celestrak.org/NORAD/elements/gp.php?GROUP=visual&FORMAT=tle', null, [
        'Accept: text/plain',
    ], 12);
    if ($tleTxt && strlen($tleTxt) > 50 && strpos($tleTxt, '1 ') !== false) {
        $lines = array_values(array_filter(array_map('trim', explode("\n", trim($tleTxt)))));
        for ($i = 0; $i + 2 < count($lines); $i += 3) {
            if (strlen($lines[$i + 1]) > 40 && strlen($lines[$i + 2]) > 40) {
                $satellites[] = [
                    'norad_id'  => trim(substr($lines[$i + 1], 2, 5)),
                    'name'      => trim($lines[$i]),
                    'tle_line1' => $lines[$i + 1],
                    'tle_line2' => $lines[$i + 2],
                    'mean_motion'    => 0,
                    'eccentricity'   => 0,
                    'inclination'    => 0,
                    'ra_asc_node'    => 0,
                    'arg_pericenter' => 0,
                    'mean_anomaly'   => 0,
                    'bstar'          => 0,
                    'rev_num'        => 0,
                    'epoch'          => '',
                ];
            }
        }
    }
}

$source = 'none';
if (!empty($satellites)) {
    if (isset($data)) $source = 'celestrak-json';
    elseif (!empty($tleTxt) && strpos($tleTxt ?? '', 'ISS') !== false) $source = 'celestrak-stations';
    else $source = 'celestrak-visual';
}

$output = json_encode(['satellites' => $satellites, 'count' => count($satellites), 'time' => time(), 'source' => $source]);
if (!empty($satellites)) {
    BaseApi::setCache($cacheKey, $output);
}
BaseApi::respond($output);

