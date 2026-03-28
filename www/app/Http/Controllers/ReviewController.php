<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Company;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Review::query()->with(['author', 'reviewable'])->orderBy('created_at')->get()->map(fn (Review $r) => $this->reviewPayload($r))
        );
    }

    public function forUser(User $user): JsonResponse
    {
        $reviews = Review::query()
            ->where('reviewable_type', $user->getMorphClass())
            ->where('reviewable_id', $user->getKey())
            ->with(['author', 'reviewable'])
            ->orderByDesc('updated_at')
            ->get();

        return response()->json($reviews->map(fn (Review $r) => $this->reviewPayload($r))->values());
    }

    public function forCompany(Company $company): JsonResponse
    {
        $reviews = Review::query()
            ->where('reviewable_type', $company->getMorphClass())
            ->where('reviewable_id', $company->getKey())
            ->with(['author', 'reviewable'])
            ->orderByDesc('updated_at')
            ->get();

        return response()->json($reviews->map(fn (Review $r) => $this->reviewPayload($r))->values());
    }

    public function store(StoreReviewRequest $request): JsonResponse
    {
        $review = Review::query()->create([
            'user_id' => $request->validated('user_id'),
            'reviewable_type' => (string) $request->input('reviewable_type'),
            'reviewable_id' => $request->validated('reviewable_id'),
            'content' => $request->validated('content'),
            'rating' => $request->validated('rating'),
        ]);

        $review->load(['author', 'reviewable']);

        return response()->json($this->reviewPayload($review), 201);
    }

    public function show(Review $review): JsonResponse
    {
        $review->load(['author', 'reviewable']);

        return response()->json($this->reviewPayload($review));
    }

    public function update(UpdateReviewRequest $request, Review $review): JsonResponse
    {
        $data = $request->validated();
        if (array_key_exists('reviewable_type', $data)) {
            $data['reviewable_type'] = (string) $request->input('reviewable_type');
        }
        $review->fill($data);
        $review->save();
        $review->load(['author', 'reviewable']);

        return response()->json($this->reviewPayload($review));
    }

    public function destroy(Review $review): JsonResponse
    {
        $review->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function reviewPayload(Review $review): array
    {
        return [
            'id' => $review->id,
            'authorId' => $review->user_id,
            'reviewableType' => $this->reviewableTypeShort($review->reviewable_type),
            'reviewableId' => $review->reviewable_id,
            'content' => $review->content,
            'rating' => $review->rating,
            'createdAt' => $review->created_at?->utc()->format('Y-m-d\TH:i:s\Z'),
            'updatedAt' => $review->updated_at?->utc()->format('Y-m-d\TH:i:s\Z'),
        ];
    }

    private function reviewableTypeShort(string $type): string
    {
        return match ($type) {
            User::class => 'user',
            Company::class => 'company',
            default => $type,
        };
    }
}
