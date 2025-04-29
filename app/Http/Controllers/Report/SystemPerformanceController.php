<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ContentItem;
use App\Models\Reaction;
use App\Models\Chat;
use App\Models\Message;
use App\Models\SystemFeedback;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Exports\SystemPerformanceExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class SystemPerformanceController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only([
            'date_from',
            'date_to',
            'user_type',
            'content_type'
        ]);

        return Inertia::render('Reports/SystemPerformance/Index', [
            'filters' => $filters,
            'metrics' => $this->getPerformanceData($filters)
        ]);
    }

    protected function getPerformanceData(array $filters = [])
    {
        // User Engagement Metrics
        $activeUsers = User::selectRaw('
            COUNT(*) as total_users,
            SUM(CASE WHEN last_seen_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as active_users,
            SUM(CASE WHEN last_seen_at IS NULL THEN 1 ELSE 0 END) as never_logged_in
        ')->first();

        // Content Interactions (Revised to exclude non-existent 'views' column)
        $contentInteractions = ContentItem::selectRaw('
        type,
        COUNT(*) as content_count,
        COUNT(reactions.id) as reaction_count
    ')
    ->leftJoin('reactions', 'reactions.content_item_id', '=', 'content_items.id')
    ->when($filters['date_from'] ?? null, function ($query, $date) {
        $query->where('content_items.created_at', '>=', $date);
    })
    ->when($filters['date_to'] ?? null, function ($query, $date) {
        $query->where('content_items.created_at', '<=', $date);
    })
    ->when($filters['content_type'] ?? null, function ($query, $type) {
        $query->where('content_items.type', $type);
    })
    ->groupBy('type')
    ->get();

        // Messaging Activity
        $messagingActivity = Chat::selectRaw('
            COUNT(*) as total_chats,
            (SELECT COUNT(*) FROM messages) as total_messages,
            (SELECT COUNT(DISTINCT sender_id) FROM messages) as active_messengers
        ')
        ->when($filters['date_from'] ?? null, function ($query, $date) {
            $query->where('created_at', '>=', $date);
        })
        ->when($filters['date_to'] ?? null, function ($query, $date) {
            $query->where('created_at', '<=', $date);
        })
        ->first();

        // TAM Evaluation Report
        $tamEvaluation = SystemFeedback::selectRaw('
            AVG(ease_of_use_rating) as ease_of_use,
            AVG(usefulness_rating) as usefulness,
            AVG(satisfaction_rating) as satisfaction,
            AVG(intention_to_use_rating) as intention_to_use,
            COUNT(*) as feedback_count
        ')
        ->when($filters['date_from'] ?? null, function ($query, $date) {
            $query->where('created_at', '>=', $date);
        })
        ->when($filters['date_to'] ?? null, function ($query, $date) {
            $query->where('created_at', '<=', $date);
        })
        ->first();

        // Feature Usage
        $featureUsage = ContentItem::selectRaw('
            type,
            COUNT(*) as count
        ')
        ->groupBy('type')
        ->orderByDesc('count')
        ->get();

        return [
            'activeUsers' => $activeUsers,
            'contentInteractions' => $contentInteractions,
            'messagingActivity' => $messagingActivity,
            'tamEvaluation' => $tamEvaluation,
            'featureUsage' => $featureUsage,
            'contentTypes' => ContentItem::select('type')->distinct()->pluck('type')
        ];
    }

    public function export(Request $request)
    {
        $filters = $request->only([
            'date_from',
            'date_to',
            'user_type',
            'content_type'
        ]);

        $data = $this->getPerformanceData($filters);

        // Determine the export format (default is Excel)
        $format = $request->input('format', 'excel'); // 'excel' or 'pdf'

        if ($format === 'pdf') {
            // Generate PDF
            $pdf = Pdf::loadView('exports.system-performance-pdf', [
                'metrics' => $data,
            ]);

            return $pdf->download('system-performance-report.pdf');
        }

        // Default to Excel export
        return Excel::download(
            new SystemPerformanceExport($data),
            'system-performance-report.xlsx'
        );
    }
}