<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Http\Request;

class ResumeController extends Controller
{
    public function index()
    {
        return view('resume.index');
    }

    public function downloadPdf()
    {
        $pdf = Pdf::loadView('resume.pdf');
        return $pdf->download('Joven_Andrei_Lagahit_Resume.pdf');
    }
}