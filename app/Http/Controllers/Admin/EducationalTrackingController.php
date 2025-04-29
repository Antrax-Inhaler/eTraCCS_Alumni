<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EducationalBackground;
use App\Models\ProfessionalExam;
use App\Models\TrainingAttended;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EducationalTrackingController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with([
            'educationalBackgrounds',
            'professionalExams',
            'trainingAttendeds'
        ])
        ->where('is_verified', true)
        ->orderBy('name');

        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Degree level filter
        if ($request->has('degree_level')) {
            $query->whereHas('educationalBackgrounds', function($q) use ($request) {
                $q->where('degree_earned', 'like', "%{$request->degree_level}%");
            });
        }

        // Certification filter
        if ($request->has('certification')) {
            $query->whereHas('professionalExams', function($q) use ($request) {
                $q->where('exam_name', 'like', "%{$request->certification}%");
            });
        }

        // Training filter
        if ($request->has('training')) {
            $query->whereHas('trainingAttendeds', function($q) use ($request) {
                $q->where('training_name', 'like', "%{$request->training}%");
            });
        }

        $alumni = $query->get();

        return Inertia::render('Admin/EducationalTracking', [
            'alumni' => $alumni->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_photo_url' => $user->profile_photo_url, // Using Jetstream's default
                    'primary_degree' => $user->educationalBackgrounds
                        ->where('is_primary', true)
                        ->first(),
                    'additional_degrees' => $user->educationalBackgrounds
                        ->where('is_primary', false),
                    'advanced_studies' => $user->educationalBackgrounds
                        ->filter(function ($edu) {
                            return in_array(strtolower($edu->degree_earned), ['master', 'phd', 'doctorate']);
                        }),
                    'professional_exams' => $user->professionalExams,
                    'trainings_attended' => $user->trainingsAttended
                ];
            }),
            'filters' => $request->only(['search', 'degree_level', 'certification', 'training'])
        ]);
    }
}