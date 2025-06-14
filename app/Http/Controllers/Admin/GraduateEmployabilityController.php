<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EducationalBackground;
use App\Models\EmploymentHistory;
use App\Models\Campus; // Add this line
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GraduateEmployabilityExport;
use Barryvdh\DomPDF\Facade\Pdf;

class GraduateEmployabilityController extends Controller
{
    public function index(Request $request)
    {
        $batchYear = $request->input('batch_year');
        $campusId = $request->input('campus_id');
        $graduates = $this->getGraduatesData($batchYear, $campusId);
        
        $batchYears = EducationalBackground::where('is_primary', true)
            ->where('from_mindoro_state', true)
            ->distinct('year_graduated')
            ->orderBy('year_graduated', 'desc')
            ->pluck('year_graduated');

        $campuses = Campus::orderBy('campus_name')->get();

        return Inertia::render('Admin/GraduateEmployability', [
            'graduates' => $graduates,
            'filters' => [
                'batch_year' => $batchYear,
                'campus_id' => $campusId,
            ],
            'batchYears' => $batchYears,
            'campuses' => $campuses,
        ]);
    }

public function exportExcel(Request $request)
{
    $batchYear = $request->input('batch_year');
    $campusId = $request->input('campus_id');
    $graduates = $this->getGraduatesData($batchYear, $campusId);

    // Return empty response if no graduates found
    if ($graduates->isEmpty()) {
        return response()->json(['message' => 'No data available for export'], 404);
    }

    // Get campus details if selected
    $campus = $campusId ? Campus::find($campusId) : null;

    // Generate dynamic filename
    $filename = 'Graduate_Employability_Report';
    if ($batchYear) {
        $filename .= '_AY_' . $batchYear . '-' . (intval($batchYear) + 1);
    }
    if ($campus) {
        $filename .= '_' . str_replace(' ', '_', $campus->campus_name);
    }
    $filename .= '.xlsx';

    return Excel::download(
        new GraduateEmployabilityExport($graduates, $batchYear, $campus), 
        $filename
    );
}

public function exportPDF(Request $request)
{
    $batchYear = $request->input('batch_year');
    $campusId = $request->input('campus_id');
    $graduates = $this->getGraduatesData($batchYear, $campusId);

    // Return empty response if no graduates found
    if ($graduates->isEmpty()) {
        return response()->json(['message' => 'No data available for export'], 404);
    }

    // Get campus details if selected
    $campus = $campusId ? Campus::find($campusId) : null;

    $groupedGraduates = $graduates->groupBy('year_graduated');

    // Generate dynamic filename
    $filename = 'Graduate_Employability_Report';
    if ($batchYear) {
        $filename .= '_AY_' . $batchYear . '-' . (intval($batchYear) + 1);
    }
    if ($campus) {
        $filename .= '_' . str_replace(' ', '_', $campus->campus_name);
    }
    $filename .= '.pdf';

    $pdf = PDF::loadView('admin.reports.graduate_employability', [
        'groupedGraduates' => $groupedGraduates,
        'batchYear' => $batchYear,
        'campus' => $campus,
    ])->setPaper('a4', 'landscape');

    return $pdf->stream($filename);
}

    private function getGraduatesData($batchYear = null, $campusId = null)
    {
        return User::with([
                'educationalBackgrounds' => function ($query) {
                    $query->where('is_primary', true);
                },
                'educationalBackgrounds.campus', // Add this to load campus relationship
                'employmentHistories' => function ($query) {
                    $query->orderBy('start_date')->take(1);
                },
                'employmentHistories.company'
            ])
            ->where('is_verified', true)
            ->whereHas('educationalBackgrounds', function ($query) use ($batchYear, $campusId) {
            $query->where('is_primary', true)
                  ->where('from_mindoro_state', true);
            
            if ($batchYear) {
                $query->where('year_graduated', $batchYear);
            }
            
            if ($campusId) {
                $query->where('campus_id', $campusId);
            }
        })
            ->get()
            ->map(function ($user) {
                $education = $user->educationalBackgrounds->first();
                $employment = $user->employmentHistories->first();
                $company = $employment ? $employment->company : null;
                
                // Build full address string
                $address = null;
                if ($company) {
                    $addressParts = array_filter([
                        $company->barangay,
                        $company->city,
                        $company->province,
                        $company->region,
                        $company->country
                    ]);
                    $address = implode(', ', $addressParts);
                }
                
                // Determine employment status
                $employmentStatus = 'Waiting for response';
                $firstJobYear = 'N/A';
                
                if ($employment) {
                    $employmentStatus = 'Employed';
                    if ($employment->start_date) {
                        $firstJobYear = $employment->start_date->format('Y');
                    }
                }
                
                // Check if user is from Mindoro State University
                $isFromMindoroState = $education ? $education->from_mindoro_state : false;
            
                return [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'middle_initial' => $user->middle_initial,
                    'last_name' => $user->last_name,
                    'gender' => $user->gender,
                    'year_graduated' => $education ? $education->year_graduated : null,
                    'degree_earned' => $education ? $education->degree_earned : null,
                    'degree_type' => $education ? $education->degree_type : null,
                    'institution' => $education ? $education->institution : null,
                    'campus_id' => $education ? $education->campus_id : null,
                    'campus_name' => $education && $education->campus ? $education->campus->campus_name : null,
                    'campus_address' => $education && $education->campus ? $education->campus->campus_address : null,
                    'is_from_mindoro_state' => $isFromMindoroState,
                    'company' => $company,
                    'employment_history' => $employment,
                    'job_title' => $employment ? $employment->job_title : null,
                    'employment_status' => $employmentStatus,
                    'first_job_year' => $firstJobYear,
                    'company_address' => $address,
                    'company_city' => $company ? $company->city : null,
                    'company_country' => $company ? $company->country : null,
                    'has_employment_data' => !is_null($employment),
                ];
            });
    }
}