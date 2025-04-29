<?php

// app/Http/Controllers/Report/AdvancementController.php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\EducationalBackground;
use App\Models\TrainingAttended;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Exports\AdvancementExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;


class AdvancementController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only([
            'search',
            'degreeType',
            'yearFrom',
            'yearTo',
            'trainingType',
            'institution'
        ]);

        return Inertia::render('Reports/Advancement/Index', [
            'filters' => $filters,
            'initialData' => $this->getReportData($filters)
        ]);
    }

    protected function getReportData(array $filters = [])
    {
        // Advanced Degrees (additional baccalaureate)
        $advancedDegrees = EducationalBackground::with('user')
            ->whereHas('user.educationalBackgrounds', function($query) {
                $query->where('degree_earned', '!=', 'BS Computer Science'); // Adjust based on your primary degree
            })
            ->when($filters['search'] ?? null, function($query, $search) {
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->when($filters['degreeType'] ?? null, function($query, $type) {
                $query->where('degree_earned', 'like', "%{$type}%");
            })
            ->when($filters['yearFrom'] ?? null, function($query, $year) {
                $query->where('year_graduated', '>=', $year);
            })
            ->when($filters['yearTo'] ?? null, function($query, $year) {
                $query->where('year_graduated', '<=', $year);
            })
            ->orderBy('year_graduated', 'desc')
            ->paginate(10, ['*'], 'degreesPage')
            ->withQueryString();

        // Advanced Studies (Master's/PhD)
        $advancedStudies = EducationalBackground::with('user')
            ->where(function($query) {
                $query->where('degree_earned', 'like', '%Master%')
                      ->orWhere('degree_earned', 'like', '%PhD%')
                      ->orWhere('degree_earned', 'like', '%Doctor%');
            })
            ->when($filters['search'] ?? null, function($query, $search) {
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->when($filters['institution'] ?? null, function($query, $institution) {
                $query->where('institution', 'like', "%{$institution}%");
            })
            ->orderBy('year_graduated', 'desc')
            ->paginate(10, ['*'], 'studiesPage')
            ->withQueryString();

        // Professional Certifications
        $certifications = TrainingAttended::with('user')
            ->where('training_name', 'like', '%certification%')
            ->when($filters['search'] ?? null, function($query, $search) {
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->when($filters['trainingType'] ?? null, function($query, $type) {
                $query->where('training_name', 'like', "%{$type}%");
            })
            ->orderBy('year_attended', 'desc')
            ->paginate(10, ['*'], 'certsPage')
            ->withQueryString();

        // Training Participation
        $trainingStats = TrainingAttended::selectRaw('
                training_name,
                COUNT(*) as participant_count,
                MIN(year_attended) as earliest_year,
                MAX(year_attended) as latest_year
            ')
            ->groupBy('training_name')
            ->orderBy('participant_count', 'desc')
            ->get();

        return [
            'advancedDegrees' => $advancedDegrees,
            'advancedStudies' => $advancedStudies,
            'certifications' => $certifications,
            'trainingStats' => $trainingStats,
            'degreeTypes' => EducationalBackground::distinct()->pluck('degree_earned'),
            'trainingTypes' => TrainingAttended::distinct()->pluck('training_name')
        ];
    }

public function export(Request $request)
{
    $filters = $request->only([
        'search',
        'degreeType',
        'yearFrom',
        'yearTo',
        'trainingType',
        'institution',
        'reportType'
    ]);

    $data = $this->getReportData($filters);
    
    if ($request->format === 'excel') {
        return Excel::download(new AdvancementExport($data, $filters['reportType']), 'advancement-report.xlsx');
    }
    
    $pdf = Pdf::loadView('exports.advancement', [
        'data' => $data,
        'reportType' => $filters['reportType'],
        'filters' => $filters
    ]);
    return $pdf->download('advancement-report.pdf');
}
}