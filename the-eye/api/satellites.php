<?php
/**
 * Satellite orbital data.
 * Source: Celestrak GP (General Perturbations) elements in JSON.
 * URL: https://celestrak.org/NORAD/elements/gp.php?GROUP=active&FORMAT=json
 * This returns TLE-equivalent orbital elements for ALL active satellites (~10,000+).
 * We limit to the first 500 for performance on initial load.
 * Refresh: 5 minutes (orbital elements change slowly)
 */

require_once __DIR__ . '/BaseApi.php';

BaseApi::headers(300);
BaseApi::checkRateLimit();

$cacheKey = 'satellites_active';
$cached = BaseApi::getCache($cacheKey, 240);
if ($cached) {
    BaseApi::respond($cached);
}

// Fetch from Celestrak — JSON format GP elements
$response = BaseApi::fetch(
    'https://celestrak.org/NORAD/elements/gp.php?GROUP=active&FORMAT=json',
    null,
    [],
    15  // longer timeout, this is a large response
);

$satellites = [];

if ($response) {
    $data = json_decode($response, true);
    if (is_array($data)) {
        // Limit to first 500 satellites. Client can request more via query param.
        $limit = min((int) ($_GET['limit'] ?? 500), 2000);
        foreach (array_slice($data, 0, $limit) as $sat) {
            $satellites[] = [
                'norad_id'    => $sat['NORAD_CAT_ID'] ?? '',
                'name'        => $sat['OBJECT_NAME'] ?? '',
                'epoch'       => $sat['EPOCH'] ?? '',
                'mean_motion' => (float) ($sat['MEAN_MOTION'] ?? 0),
                'eccentricity'=> (float) ($sat['ECCENTRICITY'] ?? 0),
                'inclination' => (float) ($sat['INCLINATION'] ?? 0),
                'ra_asc_node' => (float) ($sat['RA_OF_ASC_NODE'] ?? 0),
                'arg_pericenter' => (float) ($sat['ARG_OF_PERICENTER'] ?? 0),
                'mean_anomaly'=> (float) ($sat['MEAN_ANOMALY'] ?? 0),
                'bstar'       => (float) ($sat['BSTAR'] ?? 0),
                'rev_num'     => (int)   ($sat['REV_AT_EPOCH'] ?? 0),
                'tle_line1'   => $sat['TLE_LINE1'] ?? '',
                'tle_line2'   => $sat['TLE_LINE2'] ?? '',
            ];
        }
    }
}

// Fallback: try Celestrak TLE text format and parse manually
if (empty($satellites)) {
    $tleTxt = BaseApi::fetch('https://celestrak.org/NORAD/elements/stations.txt');
    if ($tleTxt) {
        $lines = explode("\n", trim($tleTxt));
        for ($i = 0; $i + 2 < count($lines); $i += 3) {
            $satellites[] = [
                'name'      => trim($lines[$i]),
                'tle_line1' => trim($lines[$i + 1]),
                'tle_line2' => trim($lines[$i + 2]),
            ];
        }
    }
}

$output = json_encode(['satellites' => $satellites, 'count' => count($satellites), 'time' => time()]);
BaseApi::setCache($cacheKey, $output);
BaseApi::respond($output);
