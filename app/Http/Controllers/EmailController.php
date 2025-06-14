<?php

namespace App\Http\Controllers;

use App\Mail\UserMessageMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AlumniRecord;
use App\Models\AlumniMessage;
class EmailController extends Controller
{
// EmailController.php

public function send(Request $request)
{
    $request->validate([
        'user_ids' => 'array',
        'user_ids.*' => 'exists:users,id',
        'alumni_ids' => 'array',
        'alumni_ids.*' => 'exists:alumni_records,id',
        'message' => 'required|string',
    ]);

    $subject = "Your Message from Admin";

    // System users
    $users = User::whereIn('id', $request->user_ids ?? [])->get();
    foreach ($users as $user) {
        try {
            Mail::to($user->email)->send(new UserMessageMail($request->message));

            AlumniMessage::create([
                'user_id' => $user->id,
                'subject' => $subject,
                'content' => $request->message,
                'method' => 'email',
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            AlumniMessage::create([
                'user_id' => $user->id,
                'subject' => $subject,
                'content' => $request->message,
                'method' => 'email',
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);
        }
    }

    // Non-system alumni
    $alumniRecords = AlumniRecord::whereIn('id', $request->alumni_ids ?? [])->get();
    foreach ($alumniRecords as $alum) {
        try {
            Mail::to($alum->email)->send(new UserMessageMail($request->message));

            AlumniMessage::create([
                'alumni_record_id' => $alum->id,
                'subject' => $subject,
                'content' => $request->message,
                'method' => 'email',
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            AlumniMessage::create([
                'alumni_record_id' => $alum->id,
                'subject' => $subject,
                'content' => $request->message,
                'method' => 'email',
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);
        }
    }

    return back()->with('success', 'Email sent to selected recipients!');
}
public function fetchRecipients(Request $request)
{
    $search = $request->input('search', '');
    $perPage = $request->input('per_page', 10);

    // Regular users query
    $usersQuery = User::whereNotNull('email')
        ->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        });

    // Alumni not in system query
    $alumniQuery = AlumniRecord::whereNotIn('id', function ($query) {
            $query->select('alumni_record_id')
                  ->from('users')
                  ->whereNotNull('alumni_record_id');
        })
        ->whereNotNull('email')
        ->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        });

    // Get paginated results
    $users = $usersQuery->get(['id', 'name', 'email'])->map(function ($user) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'type' => 'user'
        ];
    });

    $nonSystemAlumni = $alumniQuery->get()->map(function ($alumni) {
        return [
            'id' => $alumni->id,
            'name' => $alumni->first_name . ' ' . $alumni->last_name,
            'email' => $alumni->email,
            'type' => 'alumni'
        ];
    });

    // Combine and paginate manually since we have two different sources
    $combined = $users->merge($nonSystemAlumni)->sortBy('name')->values();
    
    // Manual pagination
    $page = $request->input('page', 1);
    $offset = ($page - 1) * $perPage;
    $paginatedItems = $combined->slice($offset, $perPage)->values();
    
    $paginatedCollection = new \Illuminate\Pagination\LengthAwarePaginator(
        $paginatedItems,
        $combined->count(),
        $perPage,
        $page,
        ['path' => $request->url(), 'query' => $request->query()]
    );

    return response()->json([
        'recipients' => $paginatedCollection
    ]);
}
// EmailController.php

public function show($id)
{
    $alumni = AlumniRecord::findOrFail($id);

    // Load latest 5 messages
    $messages = AlumniMessage::where('alumni_record_id', $id)
                ->orderByDesc('sent_at')
                ->paginate(5);

    return response()->json([
        'alumni' => [
            'id' => $alumni->id,
            'first_name' => $alumni->first_name,
            'last_name' => $alumni->last_name,
            'email' => $alumni->email,
            'contact_number' => $alumni->contact_number,
            'degree_earned' => $alumni->degree_earned,
            'year_graduated' => $alumni->year_graduated,
        ],
        'messages' => $messages
    ]);
}
public function sendSingle(Request $request)
{
    $request->validate([
        'recipient_id' => 'required|integer',
        'recipient_type' => 'required|in:user,alumni',
        'subject' => 'required|string',
        'content' => 'required|string',
        'method' => 'required|in:email,sms,both',
        'scheduled_at' => 'nullable|date',
    ]);

    // Handle sending logic here...

    return response()->json(['success' => true]);
}
public function showUserMessages($id)
{
    $user = User::findOrFail($id);

    $messages = AlumniMessage::where('user_id', $id)
                ->orderByDesc('sent_at')
                ->paginate(5);

    return response()->json([
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ],
        'messages' => $messages
    ]);
}
}
