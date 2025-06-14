<?php

namespace App\Imports;

use App\Models\AlumniRecord;
use App\Models\EducationalBackground;
use App\Models\EmploymentHistory;
use App\Models\Company;
use App\Models\ProfessionalExam;
use App\Models\UnemployedDetail;
use App\Models\BsitProgramSuggestion;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Str;

class AlumniDataImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    private $stats = [
        'alumni' => 0,
        'education' => 0,
        'employment' => 0,
        'companies' => 0,
        'exams' => 0,
        'unemployed' => 0,
        'suggestions' => 0
    ];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Parse the name
            $nameParts = $this->parseName($row['name_last_name_first_name_mi']);
            
            // Create or update alumni record
            $alumni = AlumniRecord::updateOrCreate(
                [
                    'first_name' => $nameParts['first_name'],
                    'last_name' => $nameParts['last_name'],
                    'year_graduated' => $row['year_graduated']
                ],
                [
                    'middle_initial' => $nameParts['middle_initial'],
                    'sex' => strtolower($row['sex']),
                    'civil_status' => $row['civil_status'],
                    'address' => $row['present_address'],
                    'contact_number' => $row['contact_no'],
                    'facebook_name' => $row['facebook_name'],
                    'instagram' => $row['instagram'],
                    'degree_earned' => 'BS Information Technology',
                    'campus' => 'Main Campus',
                    'photo_path' => $row['please_upload_here_2x2_photo_which_we_can_post_in_the_bsit_alumni_profile'] ?? null
                ]
            );
            $this->stats['alumni']++;

            // Handle educational background
            if (!empty($row['are_you_currently_taking_or_have_taken_graduate_studies'])) {
                $this->createEducationalBackground($alumni, $row);
                $this->stats['education']++;
            }

            // Handle professional exams
            if (!empty($row['professional_examinationcertificationcompetencies_taken'])) {
                $this->createProfessionalExams($alumni, $row);
                $this->stats['exams']++;
            }

            // Handle employment data
            if (strtolower($row['are_you_currently_employed']) === 'yes') {
                $this->createEmploymentHistory($alumni, $row);
                $this->stats['employment']++;
                $this->stats['companies']++;
            } else {
                $this->createUnemployedDetail($alumni, $row);
                $this->stats['unemployed']++;
            }

            // Handle program suggestions
            if (!empty($row['what_changes_should_be_made_to_improve_the_competitive_edge_of_bsit_main_campus_graduates'])) {
                $this->createProgramSuggestion($alumni, $row);
                $this->stats['suggestions']++;
            }
        }
    }

    private function parseName($fullName)
    {
        $parts = explode(',', $fullName);
        $lastName = trim($parts[0] ?? '');
        $remaining = trim($parts[1] ?? '');
        
        $firstParts = explode(' ', $remaining);
        $firstName = trim($firstParts[0] ?? '');
        $middleInitial = count($firstParts) > 1 ? substr(trim($firstParts[1]), 0, 1) : null;
        
        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'middle_initial' => $middleInitial
        ];
    }

    private function createEducationalBackground($alumni, $row)
    {
        EducationalBackground::create([
            'alumni_record_id' => $alumni->id,
            'degree_earned' => $row['name_of_courseprogram'] ?? null,
            'institution' => $row['name_of_school_university'] ?? null,
            'is_graduate_study' => 1,
            'reason_for_study' => $this->mapReasonForStudy($row['if_yes_what_are_your_reasons']),
            'from_mindoro_state' => Str::contains(strtolower($row['name_of_school_university'] ?? ''), 'mindoro state'),
            'year_graduated' => $this->extractYear($row['year_graduated'] ?? null),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function createProfessionalExams($alumni, $row)
    {
        $exams = explode(',', $row['professional_examinationcertificationcompetencies_taken']);
        
        foreach ($exams as $exam) {
            $exam = trim($exam);
            if (!empty($exam)) {
                ProfessionalExam::create([
                    'user_id' => null, // Will be linked when user registers
                    'alumni_record_id' => $alumni->id,
                    'exam_name' => $exam,
                    'exam_date' => now(), // Default to current date if not specified
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    private function createEmploymentHistory($alumni, $row)
    {
        // First create or find company
        $company = Company::firstOrCreate(
            ['name' => $row['company_name']],
            [
                'city' => $this->extractCity($row['place_of_employment_city_province_and_country'] ?? ''),
                'province' => $this->extractProvince($row['place_of_employment_city_province_and_country'] ?? ''),
                'country' => $this->extractCountry($row['place_of_employment_city_province_and_country'] ?? ''),
                'industry' => $row['nature_of_industry'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Create employment history
        EmploymentHistory::create([
            'alumni_record_id' => $alumni->id,
            'company_id' => $company->id,
            'job_title' => $row['job_levelposition'] ?? null,
            'employment_type' => $this->mapEmploymentType($row['employment_status'] ?? ''),
            'industry' => $row['nature_of_industry'] ?? null,
            'current_salary' => $this->mapSalaryRange($row['monthly_salary_current_job'] ?? ''),
            'job_source' => $this->mapJobSource($row['how_did_you_find_your_first_job'] ?? ''),
            'difficulties' => $this->mapDifficulties($row['what_difficulties_did_you_encounter_while_looking_for_your_first_job'] ?? ''),
            'competencies' => $row['which_competencies_learned_in_college_were_most_useful_in_your_job'] ?? null,
            'job_maintenance' => $row['means_of_maintaining_your_job'] ?? null,
            'has_promotion' => strtolower($row['have_you_received_any_promotion_in_your_current_or_past_job']) === 'yes',
            'has_awards' => strtolower($row['have_you_received_any_awardsrecognitions_in_your_job']) === 'yes',
            'awards_details' => $row['if_yes_please_specify_the_awards_or_recognitions'] ?? null,
            'months_to_first_job' => $this->mapTimeToJob($row['how_long_did_it_take_to_land_your_first_job_after_graduation'] ?? ''),
            'curriculum_relevance' => $this->mapRelevance($row['how_relevant_is_the_bsit_curriculum_to_your_current_job'] ?? ''),
            'nature_of_industry' => $row['nature_of_industry'] ?? null,
            'is_first_job' => false, // We can't determine this from current data
            'start_date' => now()->subYears(1), // Default to 1 year ago if not specified
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function createUnemployedDetail($alumni, $row)
    {
        UnemployedDetail::create([
            'alumni_record_id' => $alumni->id,
            'unemployment_reasons' => $this->mapUnemploymentReasons($row['what_are_your_reasons_for_not_being_employed'] ?? ''),
            'has_awards' => strtolower($row['have_you_received_any_awards_or_recognition_even_if_not_currently_employed']) === 'yes',
            'awards_details' => $row['if_yes_please_specify_the_awards_or_recognitions'] ?? null,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function createProgramSuggestion($alumni, $row)
    {
        BsitProgramSuggestion::create([
            'alumni_record_id' => $alumni->id,
            'suggestion_type' => 'other',
            'description' => $row['what_changes_should_be_made_to_improve_the_competitive_edge_of_bsit_main_campus_graduates'] ?? null,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    // Helper mapping methods would go here...
private function mapReasonForStudy($reason)
{
    if (empty($reason)) return null;
    
    $reasons = explode(';', $reason);
    $mappedReasons = [];
    
    foreach ($reasons as $r) {
        $r = trim(strtolower($r));
        switch ($r) {
            case 'personal interest':
            case 'personal interest;':
                return 'academic_interest';
            case 'employment opportunity':
                return 'job_requirement';
            case 'parental decision':
            case 'influence of peers':
            case 'scholarship availability':
                return 'other';
            case 'affordable tuition':
                return 'academic_interest';
            default:
                return 'other';
        }
    }
    
    return 'other';
}

private function mapEmploymentType($type)
{
    $type = strtolower(trim($type));
    
    switch ($type) {
        case 'permanent':
            return 'full-time';
        case 'contractual':
            return 'contract';
        case 'probationary':
        case 'regular':
            return 'full-time';
        case 'part-time':
            return 'part-time';
        case 'freelance':
            return 'freelance';
        default:
            return 'full-time';
    }
}

private function mapSalaryRange($salary)
{
    $salary = trim($salary);
    
    if (strpos($salary, 'Below') !== false) {
        return 'below_10k';
    } elseif (strpos($salary, '10,000') !== false && strpos($salary, '15,000') !== false) {
        return '10k_15k';
    } elseif (strpos($salary, '15,001') !== false && strpos($salary, '20,000') !== false) {
        return '15k_20k';
    } elseif (strpos($salary, '20,001') !== false && strpos($salary, '30,000') !== false) {
        return '20k_30k';
    } elseif (strpos($salary, '30,001') !== false && strpos($salary, '50,000') !== false) {
        return '30k_50k';
    } elseif (strpos($salary, 'Above') !== false) {
        return 'above_50k';
    } else {
        return 'below_10k';
    }
}

private function mapJobSource($source)
{
    $source = strtolower(trim($source));
    
    if (strpos($source, 'online') !== false || strpos($source, 'portal') !== false) {
        return 'online_portals';
    } elseif (strpos($source, 'walk') !== false) {
        return 'walk_in';
    } elseif (strpos($source, 'referral') !== false) {
        return 'referral';
    } elseif (strpos($source, 'ojt') !== false) {
        return 'university_fair';
    } elseif (strpos($source, 'social') !== false) {
        return 'social_media';
    } elseif (strpos($source, 'government') !== false) {
        return 'government';
    } else {
        return 'other';
    }
}

private function mapDifficulties($difficulties)
{
    if (empty($difficulties)) return null;
    
    $difficulties = strtolower($difficulties);
    $result = [];
    
    if (strpos($difficulties, 'lack of experience') !== false) {
        $result[] = 'lack_of_experience';
    }
    if (strpos($difficulties, 'high competition') !== false) {
        $result[] = 'high_competition';
    }
    if (strpos($difficulties, 'mismatch') !== false) {
        $result[] = 'qualification_mismatch';
    }
    if (strpos($difficulties, 'personal') !== false) {
        $result[] = 'personal_issues';
    }
    if (strpos($difficulties, 'lack of opportunities') !== false) {
        $result[] = 'lack_of_opportunities';
    }
    
    if (empty($result)) {
        return 'other';
    }
    
    return implode(',', $result);
}

private function mapTimeToJob($time)
{
    $time = trim(strtolower($time));
    
    if (strpos($time, 'less than') !== false) {
        return 'less_than_1_month';
    } elseif (strpos($time, '1 to 3') !== false) {
        return '1_to_3_months';
    } elseif (strpos($time, '4 to 6') !== false) {
        return '4_to_6_months';
    } elseif (strpos($time, '7 to 12') !== false) {
        return '7_to_12_months';
    } elseif (strpos($time, 'more than') !== false) {
        return 'more_than_1_year';
    } else {
        return 'never_employed';
    }
}

private function mapRelevance($relevance)
{
    $relevance = strtolower(trim($relevance));
    
    if (strpos($relevance, 'not relevant') !== false) {
        return 1;
    } elseif (strpos($relevance, 'somewhat') !== false) {
        return 3;
    } elseif (strpos($relevance, 'relevant') !== false && !strpos($relevance, 'very')) {
        return 4;
    } elseif (strpos($relevance, 'very relevant') !== false) {
        return 5;
    } else {
        return 3; // Default to somewhat relevant
    }
}

private function mapUnemploymentReasons($reasons)
{
    if (empty($reasons)) return null;
    
    $reasons = strtolower($reasons);
    $result = [];
    
    if (strpos($reasons, 'seeking') !== false) {
        $result[] = 'seeking';
    }
    if (strpos($reasons, 'studying') !== false) {
        $result[] = 'studying';
    }
    if (strpos($reasons, 'family') !== false) {
        $result[] = 'family_responsibilities';
    }
    if (strpos($reasons, 'health') !== false) {
        $result[] = 'health_issues';
    }
    if (strpos($reasons, 'not interested') !== false) {
        $result[] = 'not_interested';
    }
    if (strpos($reasons, 'freelance') !== false) {
        $result[] = 'other';
    }
    
    if (empty($result)) {
        return 'other';
    }
    
    return implode(',', $result);
}

private function extractCity($location)
{
    if (empty($location)) return null;
    
    $parts = explode(',', $location);
    return trim($parts[0] ?? '');
}

private function extractProvince($location)
{
    if (empty($location)) return null;
    
    $parts = explode(',', $location);
    return trim($parts[1] ?? '');
}

private function extractCountry($location)
{
    if (empty($location)) return 'Philippines'; // Default country
    
    $parts = explode(',', $location);
    $country = trim(end($parts));
    
    // Handle special cases
    if (strpos($location, 'United Kingdom') !== false) {
        return 'United Kingdom';
    }
    
    return $country ?: 'Philippines';
}

private function extractYear($date)
{
    if (empty($date)) return null;
    
    // If it's already a year
    if (is_numeric($date) && strlen($date) === 4) {
        return $date;
    }
    
    // Try to extract year from date string
    if (preg_match('/\b\d{4}\b/', $date, $matches)) {
        return $matches[0];
    }
    
    return null;
}
    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getStats()
    {
        return $this->stats;
    }
}