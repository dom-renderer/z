<?php

namespace App\Services;

use App\Models\Shift;
use Illuminate\Support\Facades\Log;

class ShiftValidationService
{
    /**
     * Maximum total shift span in minutes (24 hours)
     */
    const MAX_TOTAL_MINUTES = 1440;

    /**
     * Minimum gap between shifts in minutes
     */
    const MIN_GAP_MINUTES = 1;

    /**
     * Validate shift timing against existing shifts
     *
     * @param string $start Time in H:i format (e.g., "08:00")
     * @param string $end Time in H:i format (e.g., "17:00")
     * @param int|null $excludeId Shift ID to exclude (for updates)
     * @return array ['valid' => bool, 'message' => string|null]
     */
    public function validateShiftTiming(string $start, string $end, ?int $excludeId = null): array
    {
        // Get all existing shifts except the one being updated
        $existingShifts = Shift::when($excludeId, function ($query) use ($excludeId) {
            return $query->where('id', '!=', $excludeId);
        })->get(['id', 'start', 'end']);

        // Add the new/updated shift to the collection
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

        // Normalize shifts to a linear timeline
        $normalizedShifts = $this->normalizeShiftTimeline($allShifts);

        // Check for overlaps
        $overlapResult = $this->checkOverlaps($normalizedShifts);
        if (!$overlapResult['valid']) {
            return $overlapResult;
        }

        // Check total span
        $spanResult = $this->calculateTotalSpan($normalizedShifts);
        if (!$spanResult['valid']) {
            return $spanResult;
        }

        return ['valid' => true, 'message' => null];
    }

    /**
     * Convert time string to minutes from midnight
     *
     * @param string $time Time in H:i format
     * @return int Minutes from midnight (0-1439)
     */
    private function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = explode(':', $time);
        return ((int) $hours * 60) + (int) $minutes;
    }

    /**
     * Normalize shift timeline to handle midnight crossing
     *
     * @param array $shifts Array of shifts with 'start' and 'end' times
     * @return array Normalized shifts with 'startMin' and 'endMin' in linear timeline
     */
    private function normalizeShiftTimeline(array $shifts): array
    {
        $normalized = [];

        // Convert all times to minutes and detect midnight crossing
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

        // Find the earliest start time as reference point
        $earliestStart = min(array_column($normalized, 'startMin'));

        // Adjust all times relative to earliest start
        foreach ($normalized as &$shift) {
            // If shift crosses midnight, adjust end time
            if ($shift['crossesMidnight']) {
                $shift['endMin'] += 1440; // Add 24 hours to end time
            }

            // Adjust relative to earliest start
            if ($shift['startMin'] < $earliestStart) {
                $shift['startMin'] += 1440;
            }
            if ($shift['endMin'] < $earliestStart && !$shift['crossesMidnight']) {
                $shift['endMin'] += 1440;
            }

            // Normalize: make earliest start = 0
            $shift['startMin'] -= $earliestStart;
            $shift['endMin'] -= $earliestStart;
        }

        // Sort by start time
        usort($normalized, function ($a, $b) {
            return $a['startMin'] <=> $b['startMin'];
        });

        return $normalized;
    }

    /**
     * Check for overlaps between consecutive shifts
     *
     * @param array $normalizedShifts Sorted, normalized shifts
     * @return array ['valid' => bool, 'message' => string|null]
     */
    private function checkOverlaps(array $normalizedShifts): array
    {
        $count = count($normalizedShifts);

        for ($i = 0; $i < $count - 1; $i++) {
            $current = $normalizedShifts[$i];
            $next = $normalizedShifts[$i + 1];

            // Check if next shift starts before or at current shift ends
            if ($next['startMin'] <= $current['endMin']) {
                // Check if it's exactly touching (no gap)
                if ($next['startMin'] == $current['endMin']) {
                    return [
                        'valid' => false,
                        'message' => 'Shifts must have at least ' . self::MIN_GAP_MINUTES . ' minute gap between them.',
                    ];
                }

                // Overlapping
                return [
                    'valid' => false,
                    'message' => 'The shift overlaps with an existing shift.',
                ];
            }

            // Check minimum gap requirement
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

    /**
     * Calculate total span and ensure it's within 24 hours
     *
     * @param array $normalizedShifts Sorted, normalized shifts
     * @return array ['valid' => bool, 'message' => string|null]
     */
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
