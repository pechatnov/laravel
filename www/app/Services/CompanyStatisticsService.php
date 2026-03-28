<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Review;
use Carbon\Carbon;

final class CompanyStatisticsService
{
    /**
     * @return array{
     *   avgRating: float|null,
     *   totalReviews: int,
     *   ratingDistribution: array<string, int>,
     *   latestReviewDate: string|null,
     *   avgContentLength: float|null
     * }
     */
    public function build(Company $company): array
    {
        $distribution = [];
        for ($i = 1; $i <= 10; $i++) {
            $distribution[(string) $i] = 0;
        }

        $base = Review::query()
            ->where('reviewable_type', $company->getMorphClass())
            ->where('reviewable_id', $company->getKey());

        $total = (int) $base->count();

        if ($total === 0) {
            return [
                'avgRating' => null,
                'totalReviews' => 0,
                'ratingDistribution' => $distribution,
                'latestReviewDate' => null,
                'avgContentLength' => null,
            ];
        }

        $avgRating = round((float) (clone $base)->avg('rating'), 1);

        $counts = Review::query()
            ->where('reviewable_type', $company->getMorphClass())
            ->where('reviewable_id', $company->getKey())
            ->selectRaw('rating, COUNT(*) as c')
            ->groupBy('rating')
            ->pluck('c', 'rating');

        foreach ($counts as $rating => $count) {
            $key = (string) (int) $rating;
            if (isset($distribution[$key])) {
                $distribution[$key] = (int) $count;
            }
        }

        $latestRaw = (clone $base)->max('updated_at');

        $latestCarbon = $latestRaw ? Carbon::parse($latestRaw) : null;

        $avgLen = round((float) Review::query()
            ->where('reviewable_type', $company->getMorphClass())
            ->where('reviewable_id', $company->getKey())
            ->selectRaw('AVG(CHAR_LENGTH(content)) as a')
            ->value('a'), 1);

        return [
            'avgRating' => $avgRating,
            'totalReviews' => $total,
            'ratingDistribution' => $distribution,
            'latestReviewDate' => $latestCarbon?->utc()->format('Y-m-d\TH:i:s\Z'),
            'avgContentLength' => $avgLen,
        ];
    }
}
