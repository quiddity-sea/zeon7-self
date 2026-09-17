<?php
/**
 * Upcoming and recent rocket launches.
 * Primary: The Space Devs Launch Library 2 (free tier, 15 requests/hour).
 * URL: https://ll.thespacedevs.com/2.3.0/launches/upcoming/?limit=25&format=json
 * Refresh: 15 minutes
 */

require_once __DIR__ . '/BaseApi.php';

BaseApi::headers(900);
BaseApi::checkRateLimit();

$cacheKey = 'launches_upcoming';
$cached = BaseApi::getCache($cacheKey, 800);
if ($cached) {
    BaseApi::respond($cached);
}

// The Space Devs — free tier (15 req/hr, no key needed)
$response = BaseApi::fetch(
    'https://ll.thespacedevs.com/2.3.0/launches/upcoming/?limit=25&format=json',
    null,
    [],
    12
);

$launches = [];

if ($response) {
    $data = json_decode($response, true);
    if (isset($data['results'])) {
        foreach ($data['results'] as $launch) {
            $pad = $launch['pad'] ?? [];
            $launches[] = [
                'id'          => $launch['id'] ?? '',
                'name'        => $launch['name'] ?? '',
                'status'      => $launch['status']['name'] ?? '',
                'net'         => $launch['net'] ?? '',
                'window_start'=> $launch['window_start'] ?? '',
                'window_end'  => $launch['window_end'] ?? '',
                'rocket'      => $launch['rocket']['configuration']['name'] ?? '',
                'provider'    => $launch['launch_service_provider']['name'] ?? '',
                'pad_name'    => $pad['name'] ?? '',
                'lat'         => isset($pad['latitude']) ? (float) $pad['latitude'] : null,
                'lon'         => isset($pad['longitude']) ? (float) $pad['longitude'] : null,
                'location'    => $pad['location']['name'] ?? '',
                'country'     => $pad['location']['country_code'] ?? '',
                'image'       => $launch['image'] ?? '',
                'mission'     => $launch['mission']['description'] ?? '',
            ];
        }
    }
}

$output = json_encode(['launches' => $launches, 'count' => count($launches), 'time' => time()]);
BaseApi::setCache($cacheKey, $output);
BaseApi::respond($output);
