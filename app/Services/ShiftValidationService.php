<?php

namespace App\Services;

use App\Models\Shift;
use Illuminate\Support\Facades\Log;

class ShiftValidationService
{
    const MAX_TOTAL_MINUTES = 1440;

    const MIN_GAP_MINUTES = 1;

    public function validateShiftTiming(string $start, string $end, ?int $excludeId = null): array
    {
        $existingShifts = Shift::when($excludeId, function ($query) use ($excludeId) {
            return $query->where('id', '!=', $excludeId);
        })->get(['id', 'start', 'end']);

        $allShifts = $existingShifts->map(function ($shift) {
            return [
                'id' => $shift->id,
                'start' => $shift->start,
                'end' => $shift->end,
            ];
        })->toArray();

        $allShifts[] = [
            'id' => $excludeId ?? 'new',
            'start' => $start,
            'end' => $end,
        ];

        $normalizedShifts = $this->normalizeShiftTimeline($allShifts);

        $overlapResult = $this->checkOverlaps($normalizedShifts);
        if (!$overlapResult['valid']) {
            return $overlapResult;
        }

        $spanResult = $this->calculateTotalSpan($normalizedShifts);
        if (!$spanResult['valid']) {
            return $spanResult;
        }

        return ['valid' => true, 'message' => null];
    }

    private function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = explode(':', $time);
        return ((int) $hours * 60) + (int) $minutes;
    }

    private function normalizeShiftTimeline(array $shifts): array
    {
        $normalized = [];

        foreach ($shifts as $shift) {
            $startMin = $this->timeToMinutes($shift['start']);
            $endMin = $this->timeToMinutes($shift['end']);

            $normalized[] = [
                'id' => $shift['id'],
                'startMin' => $startMin,
                'endMin' => $endMin,
                'crossesMidnight' => $endMin < $startMin,
            ];
        }

        $earliestStart = min(array_column($normalized, 'startMin'));

        foreach ($normalized as &$shift) {
            if ($shift['crossesMidnight']) {
                $shift['endMin'] += 1440;
            }

            if ($shift['startMin'] < $earliestStart) {
                $shift['startMin'] += 1440;
            }
            if ($shift['endMin'] < $earliestStart && !$shift['crossesMidnight']) {
                $shift['endMin'] += 1440;
            }

            $shift['startMin'] -= $earliestStart;
            $shift['endMin'] -= $earliestStart;
        }

        usort($normalized, function ($a, $b) {
            return $a['startMin'] <=> $b['startMin'];
        });

        return $normalized;
    }

    private function checkOverlaps(array $normalizedShifts): array
    {
        $count = count($normalizedShifts);

        for ($i = 0; $i < $count - 1; $i++) {
            $current = $normalizedShifts[$i];
            $next = $normalizedShifts[$i + 1];

            if ($next['startMin'] <= $current['endMin']) {

                if ($next['startMin'] == $current['endMin']) {
                    return [
                        'valid' => false,
                        'message' => 'Shifts must have at least ' . self::MIN_GAP_MINUTES . ' minute gap between them.',
                    ];
                }

                return [
                    'valid' => false,
                    'message' => 'The shift overlaps with an existing shift.',
                ];
            }

            $gap = $next['startMin'] - $current['endMin'];
            if ($gap < self::MIN_GAP_MINUTES) {
                return [
                    'valid' => false,
                    'message' => 'Shifts must have at least ' . self::MIN_GAP_MINUTES . ' minute gap between them.',
                ];
            }
        }

        return ['valid' => true, 'message' => null];
    }

    private function calculateTotalSpan(array $normalizedShifts): array
    {
        if (empty($normalizedShifts)) {
            return ['valid' => true, 'message' => null];
        }

        $minStart = min(array_column($normalizedShifts, 'startMin'));
        $maxEnd = max(array_column($normalizedShifts, 'endMin'));

        $totalSpan = $maxEnd - $minStart;

        if ($totalSpan > self::MAX_TOTAL_MINUTES) {
            return [
                'valid' => false,
                'message' => 'The total shift duration cannot exceed 24 hours.',
            ];
        }

        return ['valid' => true, 'message' => null];
    }
}
