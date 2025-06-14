<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyController extends Controller
{
    /**
     * Display the specified company.
     *
     * @param \App\Models\Company $company
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company)
    {
        // Eager load the employmentHistories relationship
        $company->load('employmentHistories.user'); // Load related users for each employment history

        return Inertia::render('Admin/Company/Show', [
            'company' => $company,
        ]);
    }
}