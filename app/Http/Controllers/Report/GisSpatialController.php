<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\AlumniLocation;
use App\Models\EducationalBackground;
use App\Models\User;
use App\Models\Company; // Use the Company model instead of CompanyLocation
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Exports\GisSpatialExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class GisSpatialController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only([
            'year_from',
            'year_to',
            'degree_program',
            'country',
            'city'
        ]);

        return Inertia::render('Reports/GisSpatial/Index', [
            'filters' => $filters,
            'mapData' => $this->getMapData($filters),
            'degreePrograms' => EducationalBackground::distinct()->pluck('degree_earned'),
            'countries' => AlumniLocation::distinct()->pluck('country'),
            'cities' => AlumniLocation::distinct()->pluck('city')
        ]);
    }

    protected function getMapData(array $filters = [])
    {
        // Alumni Heatmap Data
        $alumniHeatmap = AlumniLocation::selectRaw('
            alumni_locations.latitude,
            alumni_locations.longitude,
            alumni_locations.country,
            alumni_locations.city,
            COUNT(*) as alumni_count
        ')
        ->join('users', 'users.id', '=', 'alumni_locations.user_id')
        ->join('educational_backgrounds', 'educational_backgrounds.user_id', '=', 'users.id')
        ->when($filters['year_from'] ?? null, function ($query, $year) {
            $query->where('year_graduated', '>=', $year);
        })
        ->when($filters['year_to'] ?? null, function ($query, $year) {
            $query->where('year_graduated', '<=', $year);
        })
        ->when($filters['degree_program'] ?? null, function ($query, $degree) {
            $query->where('degree_earned', 'like', '%'.$degree.'%');
        })
        ->when($filters['country'] ?? null, function ($query, $country) {
            $query->where('alumni_locations.country', 'like', '%'.$country.'%');
        })
        ->when($filters['city'] ?? null, function ($query, $city) {
            $query->where('alumni_locations.city', 'like', '%'.$city.'%');
        })
        ->groupBy('alumni_locations.latitude', 'alumni_locations.longitude', 'alumni_locations.country', 'alumni_locations.city')
        ->get();

        // Industry Clusters Data (Using companies table)
        $industryClusters = Company::selectRaw('
            companies.latitude,
            companies.longitude,
            companies.industry,
            COUNT(employment_histories.id) as employee_count
        ')
        ->join('employment_histories', 'employment_histories.company_id', '=', 'companies.id')
        ->join('users', 'users.id', '=', 'employment_histories.user_id')
        ->join('educational_backgrounds', 'educational_backgrounds.user_id', '=', 'users.id')
        ->when($filters['year_from'] ?? null, function ($query, $year) {
            $query->where('year_graduated', '>=', $year);
        })
        ->when($filters['year_to'] ?? null, function ($query, $year) {
            $query->where('year_graduated', '<=', $year);
        })
        ->when($filters['degree_program'] ?? null, function ($query, $degree) {
            $query->where('degree_earned', 'like', '%'.$degree.'%');
        })
        ->groupBy('companies.latitude', 'companies.longitude', 'companies.industry')
        ->orderByDesc('employee_count')
        ->get();

        // Migration Patterns Data
        $migrationPatterns = User::selectRaw('
            educational_backgrounds.campus as origin,
            alumni_locations.city as destination,
            alumni_locations.country,
            COUNT(*) as count
        ')
        ->join('educational_backgrounds', 'educational_backgrounds.user_id', '=', 'users.id')
        ->join('alumni_locations', 'alumni_locations.user_id', '=', 'users.id')
        ->when($filters['year_from'] ?? null, function ($query, $year) {
            $query->where('year_graduated', '>=', $year);
        })
        ->when($filters['year_to'] ?? null, function ($query, $year) {
            $query->where('year_graduated', '<=', $year);
        })
        ->when($filters['degree_program'] ?? null, function ($query, $degree) {
            $query->where('degree_earned', 'like', '%'.$degree.'%');
        })
        ->groupBy('origin', 'destination', 'alumni_locations.country')
        ->orderByDesc('count')
        ->get();

        return [
            'alumniHeatmap' => $alumniHeatmap,
            'industryClusters' => $industryClusters,
            'migrationPatterns' => $migrationPatterns
        ];
    }

    public function export(Request $request)
    {
        $filters = $request->only([
            'year_from',
            'year_to',
            'degree_program',
            'country',
            'city'
        ]);

        $data = $this->getMapData($filters);

        // Determine the export format (default is Excel)
        $format = $request->input('format', 'excel'); // 'excel' or 'pdf'

        if ($format === 'pdf') {
            // Generate PDF
            $pdf = Pdf::loadView('exports.gis-spatial-pdf', [
                'mapData' => $data,
            ]);

            return $pdf->download('gis-spatial-report.pdf');
        }

        // Default to Excel export
        return Excel::download(
            new GisSpatialExport($data),
            'gis-spatial-report.xlsx'
        );
    }
}