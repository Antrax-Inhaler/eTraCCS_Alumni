<?php
// app/Http/Controllers/Report/GraduateProfileController.php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EducationalBackground;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Exports\GraduateProfileExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class GraduateProfileController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only([
            'search',
            'graduationYearFrom',
            'graduationYearTo',
            'degree',
            'campus'
        ]);

        return Inertia::render('Reports/GraduateProfile/Index', [
            'filters' => $filters,
            'initialData' => $this->getReportData($filters)
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'graduationYearFrom' => 'nullable|digits:4',
            'graduationYearTo' => 'nullable|digits:4',
            'degree' => 'nullable|string',
            'campus' => 'nullable|string'
        ]);

        return response()->json([
            'data' => $this->getReportData($validated)
        ]);
    }

    protected function getReportData(array $filters = [])
    {
        $query = EducationalBackground::with('user')
            ->selectRaw('
                educational_backgrounds.*,
                COUNT(*) OVER() as total_count,
                users.first_name,
                users.last_name,
                users.middle_initial,
                users.email
            ')
            ->join('users', 'users.id', '=', 'educational_backgrounds.user_id')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('users.first_name', 'like', '%'.$search.'%')
                      ->orWhere('users.last_name', 'like', '%'.$search.'%')
                      ->orWhere('users.email', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['graduationYearFrom'] ?? null, function ($query, $year) {
                $query->where('year_graduated', '>=', $year);
            })
            ->when($filters['graduationYearTo'] ?? null, function ($query, $year) {
                $query->where('year_graduated', '<=', $year);
            })
            ->when($filters['degree'] ?? null, function ($query, $degree) {
                $query->where('degree_earned', 'like', '%'.$degree.'%');
            })
            ->when($filters['campus'] ?? null, function ($query, $campus) {
                $query->where('campus', 'like', '%'.$campus.'%');
            })
            ->orderBy('year_graduated', 'desc')
            ->paginate(25)
            ->withQueryString();

        // Add statistics
        $stats = [
            'totalGraduates' => User::has('educationalBackgrounds')->count(),
            'degreesDistribution' => EducationalBackground::selectRaw('degree_earned, COUNT(*) as count')
                ->groupBy('degree_earned')
                ->get(),
            'yearlyGraduation' => EducationalBackground::selectRaw('year_graduated, COUNT(*) as count')
                ->groupBy('year_graduated')
                ->orderBy('year_graduated')
                ->get(),
            'campusDistribution' => EducationalBackground::selectRaw('campus, COUNT(*) as count')
                ->groupBy('campus')
                ->get()
        ];

        return [
            'graduates' => $query,
            'statistics' => $stats
        ];
    }
    public function export(Request $request)
{
    $filters = $request->only([
        'search',
        'graduationYearFrom',
        'graduationYearTo',
        'degree',
        'campus'
    ]);

    $data = $this->getReportData($filters);
    
    if ($request->format === 'excel') {
        return Excel::download(new GraduateProfileExport($data), 'graduate-profiles.xlsx');
    }
    
    $pdf = Pdf::loadView('exports.graduate-profiles', $data);
    return $pdf->download('graduate-profiles.pdf');
}
}