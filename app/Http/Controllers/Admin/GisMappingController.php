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
        // Get all alumni with their locations and current employment
        $alumni = User::with(['location', 'currentEmployment.company'])
            ->has('location')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'photo' => $user->profile_photo_url,
                    'lat' => $user->location->latitude,
                    'lng' => $user->location->longitude,
                    'city' => $user->location->city,
                    'country' => $user->location->country,
                    'current_company' => $user->currentEmployment ? [
                        'id' => $user->currentEmployment->company_id,
                        'name' => optional($user->currentEmployment->company)->name
                    ] : null,
                    'current_job_title' => $user->currentEmployment ? $user->currentEmployment->job_title : null
                ];
            });

        // Get all companies with their alumni employees
        $companies = Company::with(['employees' => function($query) {
            $query->select('users.id', 'users.name', 'users.email', 'users.profile_photo_path')
        ->wherePivotNull('end_date');
        }])->get()->map(function ($company) {
            return [
                'id' => $company->id,
                'name' => $company->name,
                'lat' => $company->latitude,
                'lng' => $company->longitude,
                'industry' => $company->industry,
                'city' => $company->city,
                'country' => $company->country,
                'employee_count' => $company->employees->count(),
                'employees' => $company->employees->map(function ($employee) {
                    return [
                        'id' => $employee->id,
                        'name' => $employee->name,
                        'email' => $employee->email,
                        'photo' => $employee->profile_photo_url
                    ];
                })
            ];
        });

        // Get employment data for heatmap
        $employmentData = EmploymentHistory::with(['company', 'user'])
            ->whereNotNull('company_id')
            ->whereNull('end_date')
            ->get()
            ->groupBy('company_id');

        // Prepare data for the map
        return Inertia::render('Admin/GisMapping', [
            'alumniData' => $alumni,
            'companiesData' => $companies,
            'employmentData' => $employmentData,
            'filters' => $request->only(['industry', 'country', 'radius', 'search'])
        ]);
    }
}