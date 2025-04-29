<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlumniLocation;
use App\Models\Company;
use App\Models\EmploymentHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GisMappingController extends Controller
{
    public function index(Request $request)
    {
        // Get all alumni locations with user data
        $alumniLocations = AlumniLocation::with(['user' => function($query) {
            $query->select('id', 'name', 'email', 'profile_photo_path');
        }])->get();

        // Get all companies
        $companies = Company::all();

        // Get employment data for heatmap
        $employmentData = EmploymentHistory::with(['company', 'user'])
            ->whereNotNull('company_id')
            ->get()
            ->groupBy('company_id');

        // Prepare data for the map
        $mapData = [
            'alumni' => $alumniLocations->map(function ($location) {
                return [
                    'id' => $location->user_id,
                    'name' => $location->user->name,
                    'email' => $location->user->email,
                    'photo' => $location->user->profile_photo_url,
                    'lat' => $location->latitude,
                    'lng' => $location->longitude,
                    'city' => $location->city,
                    'country' => $location->country
                ];
            }),
            'companies' => $companies->map(function ($company) use ($employmentData) {
                $employees = $employmentData->get($company->id, collect())->count();
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'lat' => $company->latitude,
                    'lng' => $company->longitude,
                    'industry' => $company->industry,
                    'employee_count' => $employees,
                    'city' => $company->city,
                    'country' => $company->country
                ];
            }),
            'industries' => $companies->groupBy('industry')->map->count(),
            'regions' => $alumniLocations->groupBy('country')->map->count()
        ];

        return Inertia::render('Admin/GisMapping', [
            'mapData' => $mapData,
            'filters' => $request->only(['industry', 'country', 'radius', 'search'])
        ]);
    }
}