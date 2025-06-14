<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\AlumniDataImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Inertia;
use App\Models\AlumniRecord;
use App\Models\EducationalBackground;
use App\Models\EmploymentHistory;
use App\Models\Company;
use App\Models\ProfessionalExam;
use App\Models\UnemployedDetail;
use App\Models\BsitProgramSuggestion;
use Illuminate\Support\Str;

class AlumniImportController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Alumni/Import');
    }

    public function store(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            $import = new AlumniDataImport();
            Excel::import($import, $request->file('excel_file'));
            
            return response()->json([
                'success' => true,
                'message' => 'Alumni data imported successfully',
                'stats' => $import->getStats()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error importing file: ' . $e->getMessage()
            ], 500);
        }
    }
}