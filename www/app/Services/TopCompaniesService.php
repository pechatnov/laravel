<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class TopCompaniesService
{
    /**
     * @return Collection<int, array{id: string, title: string, avgRating: float|null, reviewsCount: int}>
     */
    public function top(int $limit = 10): Collection
    {
        $morphClass = (new Company)->getMorphClass();

        $rows = DB::table('companies')
            ->leftJoin('reviews', function ($join) use ($morphClass): void {
                $join->on('reviews.reviewable_id', '=', 'companies.id')
                    ->where('reviews.reviewable_type', '=', $morphClass);
            })
            ->selectRaw('companies.id as id')
            ->selectRaw('companies.title as title')
            ->selectRaw('AVG(reviews.rating) as avg_rating')
            ->selectRaw('COUNT(reviews.id) as reviews_count')
            ->groupBy('companies.id', 'companies.title')
            ->orderByRaw('AVG(reviews.rating) IS NULL')
            ->orderByDesc(DB::raw('AVG(reviews.rating)'))
            ->orderByDesc(DB::raw('COUNT(reviews.id)'))
            ->limit($limit)
            ->get();

        return $rows->map(function ($row): array {
            $avg = $row->avg_rating;

            return [
                'id' => (string) $row->id,
                'title' => (string) $row->title,
                'avgRating' => $avg === null ? null : round((float) $avg, 1),
                'reviewsCount' => (int) $row->reviews_count,
            ];
        });
    }
}
