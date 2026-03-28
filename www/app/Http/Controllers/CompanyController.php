<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Services\CompanyStatisticsService;
use App\Services\TopCompaniesService;
use App\Support\ApiJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function __construct(
        private readonly CompanyStatisticsService $statisticsService,
        private readonly TopCompaniesService $topCompaniesService,
    ) {}

    public function topRated(): JsonResponse
    {
        return response()->json($this->topCompaniesService->top(10)->values());
    }

    public function statistics(Company $company): JsonResponse
    {
        return response()->json($this->statisticsService->build($company));
    }

    public function index(): JsonResponse
    {
        return response()->json(
            Company::query()->orderBy('created_at')->get()->map(fn (Company $c) => $this->companyPayload($c))
        );
    }

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $path = $request->file('logo')->store('logos', 'public');

        $company = Company::query()->create([
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'logo_path' => $path,
        ]);

        return response()->json($this->companyPayload($company), 201);
    }

    public function show(Company $company): JsonResponse
    {
        return response()->json($this->companyPayload($company));
    }

    public function update(UpdateCompanyRequest $request, Company $company): JsonResponse
    {
        $data = $request->safe()->except(['logo']);

        if ($request->hasFile('logo')) {
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $company->fill($data);
        $company->save();

        return response()->json($this->companyPayload($company->fresh()));
    }

    public function destroy(Company $company): JsonResponse
    {
        if ($company->logo_path) {
            Storage::disk('public')->delete($company->logo_path);
        }

        $company->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function companyPayload(Company $company): array
    {
        return [
            'id' => $company->id,
            'title' => $company->title,
            'description' => $company->description,
            'logoUrl' => ApiJson::publicFileUrl($company->logo_path),
            'createdAt' => $company->created_at?->utc()->format('Y-m-d\TH:i:s\Z'),
            'updatedAt' => $company->updated_at?->utc()->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
