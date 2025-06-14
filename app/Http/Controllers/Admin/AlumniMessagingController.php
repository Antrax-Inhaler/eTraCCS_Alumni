<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlumniRecord;
use App\Models\User;
use App\Models\AlumniMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\AlumniInvitation;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
class AlumniMessagingController extends Controller
{
    public function index()
    {
        // Get alumni not in the system (no user record)
        $nonSystemAlumni = AlumniRecord::whereNotIn('id', function($query) {
            $query->select('alumni_record_id')
                  ->from('users')
                  ->whereNotNull('alumni_record_id');
        })
        ->whereNotNull('email')
        ->orderBy('last_name')
        ->paginate(20)
        ->through(function ($alumni) {
            return [
                'id' => $alumni->id,
                'name' => $alumni->first_name . ' ' . $alumni->last_name,
                'email' => $alumni->email,
                'contact_number' => $alumni->contact_number,
                'year_graduated' => $alumni->year_graduated,
                'degree_earned' => $alumni->degree_earned,
                'last_message_sent' => $alumni->messages()->latest()->first()?->created_at->format('M d, Y H:i'),
                'messages_count' => $alumni->messages()->count()
            ];
        });

        return Inertia::render('Admin/AlumniMessaging/Index', [
            'alumni' => $nonSystemAlumni,
            'stats' => [
                'total' => AlumniRecord::whereNotIn('id', function($query) {
                    $query->select('alumni_record_id')
                          ->from('users')
                          ->whereNotNull('alumni_record_id');
                })->whereNotNull('email')->count(),
                'with_email' => AlumniRecord::whereNotIn('id', function($query) {
                    $query->select('alumni_record_id')
                          ->from('users')
                          ->whereNotNull('alumni_record_id');
                })->whereNotNull('email')->count(),
                'with_phone' => AlumniRecord::whereNotIn('id', function($query) {
                    $query->select('alumni_record_id')
                          ->from('users')
                          ->whereNotNull('alumni_record_id');
                })->whereNotNull('contact_number')->count(),
            ]
        ]);
    }

    public function show(AlumniRecord $alumni)
    {
        $messages = $alumni->messages()
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($message) {
                return [
                    'id' => $message->id,
                    'subject' => $message->subject,
                    'content' => $message->content,
                    'sent_at' => $message->created_at->format('M d, Y H:i'),
                    'status' => $message->status,
                    'scheduled_at' => $message->scheduled_at?->format('M d, Y H:i'),
                    'method' => $message->method
                ];
            });

        return Inertia::render('Admin/AlumniMessaging/Show', [
            'alumni' => [
                'id' => $alumni->id,
                'name' => $alumni->first_name . ' ' . $alumni->last_name,
                'email' => $alumni->email,
                'contact_number' => $alumni->contact_number,
                'year_graduated' => $alumni->year_graduated,
                'degree_earned' => $alumni->degree_earned,
                'address' => $alumni->address,
                'campus' => $alumni->campus
            ],
            'messages' => $messages
        ]);
    }

public function send(Request $request, AlumniRecord $alumni)
{
    $validator = Validator::make($request->all(), [
        'subject' => 'required|string|max:255',
        'content' => 'required|string',
        'method' => 'required|in:email,sms,both',
        'scheduled_at' => 'nullable|date|after:now'
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $data = $validator->validated();

    $message = new AlumniMessage([
        'subject' => $data['subject'],
        'content' => $data['content'],
        'method' => $data['method'],
        'status' => $data['scheduled_at'] ? 'scheduled' : 'pending',
        'scheduled_at' => $data['scheduled_at']
    ]);

    $alumni->messages()->save($message);

    if (!isset($data['scheduled_at'])) {
        $this->dispatchMessage($alumni, $message);
    }

    return back()->with('success', 'Message has been ' . (isset($data['scheduled_at']) ? 'scheduled' : 'sent'));
}

public function sendBulk(Request $request)
{
    $request->validate([
        'subject' => 'required|string|max:255',
        'content' => 'required|string',
        'method' => 'required|in:email,sms,both',
        'alumni_ids' => 'required|array',
        'alumni_ids.*' => 'exists:alumni_records,id',
        'scheduled_at' => 'nullable|date|after:now'
    ]);

    $successCount = 0;
    $failedCount = 0;
    $errors = [];

    foreach ($request->alumni_ids as $alumniId) {
        try {
            $alumni = AlumniRecord::find($alumniId);
            
            // Check if the selected method is possible for this alumnus
            if (($request->method === 'email' || $request->method === 'both') && !$alumni->email) {
                throw new \Exception("No email address for alumnus ID: {$alumni->id}");
            }
            
            if (($request->method === 'sms' || $request->method === 'both') && !$alumni->contact_number) {
                throw new \Exception("No phone number for alumnus ID: {$alumni->id}");
            }

            $message = new AlumniMessage([
                'subject' => $request->subject,
                'content' => $request->content,
                'method' => $request->method,
                'status' => $request->scheduled_at ? 'scheduled' : 'pending',
                'scheduled_at' => $request->scheduled_at
            ]);

            $alumni->messages()->save($message);
            $successCount++;

            if (!$request->scheduled_at) {
                $this->dispatchMessage($alumni, $message);
            }
        } catch (\Exception $e) {
            $failedCount++;
            $errors[] = $e->getMessage();
        }
    }

    $response = [
        'success' => true,
        'message' => "Messages processed. Success: {$successCount}, Failed: {$failedCount}",
        'success_count' => $successCount,
        'failed_count' => $failedCount
    ];

    if ($failedCount > 0) {
        $response['errors'] = $errors;
    }

    return response()->json($response);
}
   // In your AlumniMessagingController
protected function dispatchMessage(AlumniRecord $alumni, AlumniMessage $message)
{
    Log::info('Attempting to send email to: ' . $alumni->email);

    try {
        if (in_array($message->method, ['email', 'both']) && $alumni->email) {
            Log::info('Dispatching AlumniInvitation Mailable for message ID: ' . $message->id);
            
            Mail::to($alumni->email)->sendNow(new AlumniInvitation($message));

            Log::info('Email sent successfully to: ' . $alumni->email);
        }
    } catch (\Exception $e) {
        Log::error('Failed to send email to ' . $alumni->email . ': ' . $e->getMessage());
        Log::error('Trace: ' . $e->getTraceAsString());
    }
}
    
}