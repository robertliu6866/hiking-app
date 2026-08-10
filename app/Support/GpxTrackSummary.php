<?php

namespace App\Support;

class GpxTrackSummary
{
    public static function fromFile(string $path, int $maxPoints = 80): ?array
    {
        if (! is_file($path)) {
            return null;
        }

        $xml = simplexml_load_file($path);

        if (! $xml) {
            return null;
        }

        $xml->registerXPathNamespace('gpx', 'https://www.topografix.com/GPX/1/1');

        $trackPoints = $xml->xpath('//gpx:trkpt') ?: [];

        if ($trackPoints === []) {
            return null;
        }

        $points = [];
        $profile = [];
        $distance = 0.0;
        $minElevation = INF;
        $maxElevation = -INF;
        $previous = null;

        foreach ($trackPoints as $trackPoint) {
            $point = [
                'lat' => (float) $trackPoint['lat'],
                'lng' => (float) $trackPoint['lon'],
                'ele' => (float) $trackPoint->ele,
            ];

            $minElevation = min($minElevation, $point['ele']);
            $maxElevation = max($maxElevation, $point['ele']);

            if ($previous) {
                $distance += self::distanceBetween($previous, $point);
            }

            $points[] = $point;
            $profile[] = [
                'distance' => round($distance / 1000, 2),
                'ele' => round($point['ele'], 1),
            ];
            $previous = $point;
        }

        return [
            'name' => (string) ($xml->metadata->name ?? 'GPX 軌跡'),
            'source' => (string) ($xml->metadata->link->text ?? 'GPX'),
            'distance' => round($distance / 1000, 1).' km',
            'climb' => number_format((int) round($maxElevation - $minElevation)).' m',
            'highest' => number_format((int) round($maxElevation)).' m',
            'points' => self::samplePoints($points, $maxPoints),
            'profile' => self::sampleProfile($profile, $maxPoints),
        ];
    }

    private static function samplePoints(array $points, int $maxPoints): array
    {
        if (count($points) <= $maxPoints) {
            return array_map(fn (array $point) => [$point['lng'], $point['lat'], $point['ele']], $points);
        }

        $sampled = [];
        $lastIndex = count($points) - 1;

        for ($index = 0; $index < $maxPoints; $index++) {
            $sourceIndex = (int) round(($index / ($maxPoints - 1)) * $lastIndex);
            $point = $points[$sourceIndex];
            $sampled[] = [$point['lng'], $point['lat'], $point['ele']];
        }

        return $sampled;
    }

    private static function sampleProfile(array $profile, int $maxPoints): array
    {
        if (count($profile) <= $maxPoints) {
            return $profile;
        }

        $sampled = [];
        $lastIndex = count($profile) - 1;

        for ($index = 0; $index < $maxPoints; $index++) {
            $sourceIndex = (int) round(($index / ($maxPoints - 1)) * $lastIndex);
            $sampled[] = $profile[$sourceIndex];
        }

        return $sampled;
    }

    private static function distanceBetween(array $from, array $to): float
    {
        $earthRadius = 6371000;
        $latDelta = deg2rad($to['lat'] - $from['lat']);
        $lngDelta = deg2rad($to['lng'] - $from['lng']);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($from['lat']))
            * cos(deg2rad($to['lat']))
            * sin($lngDelta / 2) ** 2;

        return 2 * $earthRadius * atan2(sqrt($a), sqrt(1 - $a));
    }
}
