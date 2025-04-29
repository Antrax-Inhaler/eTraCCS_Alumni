<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function getCompanyLocationsWithUsers(Request $request)
    {
        $query = Company::query()
            ->with(['employmentHistories.user' => function($query) {
                $query->select('id', 'first_name', 'middle_initial', 'last_name', 'profile_photo_path');
            }])
            ->whereHas('employmentHistories.user');

        // Apply filters
        if ($request->has('industry') && $request->industry !== 'all') {
            $query->where('industry', $request->industry);
        }

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('industry', 'like', $searchTerm)
                  ->orWhereHas('employmentHistories.user', function($q) use ($searchTerm) {
                      $q->where(function($userQuery) use ($searchTerm) {
                          $userQuery->where('first_name', 'like', $searchTerm)
                                   ->orWhere('last_name', 'like', $searchTerm)
                                   ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$searchTerm]);
                      });
                  });
            });
        }
        $companies = $query->get()->map(function ($company) {
            $users = $company->employmentHistories
                ->pluck('user')
                ->filter()
                ->unique('id')
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'encrypted_id' => Crypt::encryptString($user->id),
                        'full_name' => trim("{$user->first_name} {$user->middle_initial} {$user->last_name}"),
                        'profile_photo_url' => $user->profile_photo_url,
                    ];
                })
                ->values();

            return [
                'id' => $company->id,
                'name' => $company->name,
                'latitude' => is_numeric($company->latitude) ? (float) $company->latitude : null,
                'longitude' => is_numeric($company->longitude) ? (float) $company->longitude : null,
                'industry' => $company->industry,
                'users' => $users,
            ];
        });

        $validCompanies = $companies->filter(function ($company) {
            return $company['latitude'] !== null && 
                   $company['longitude'] !== null &&
                   count($company['users']) > 0;
        });

        // Get unique industries for filter dropdown
        $industries = Company::distinct('industry')->pluck('industry')->filter();

        return inertia('Map/Index', [
            'companies' => $validCompanies->values(),
            'filters' => [
                'industry' => $request->input('industry', 'all'),
                'search' => $request->input('search', ''),
            ],
            'industries' => $industries,
        ]);
    }
}