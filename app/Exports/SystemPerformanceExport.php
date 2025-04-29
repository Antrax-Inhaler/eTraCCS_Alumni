<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SystemPerformanceExport implements WithMultipleSheets
{
    protected $data;
    
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function sheets(): array
    {
        return [
            new SystemPerformanceSheet($this->prepareEngagementData(), 'User Engagement'),
            new SystemPerformanceSheet($this->prepareTAMData(), 'TAM Evaluation')
        ];
    }
    
    protected function prepareEngagementData()
    {
        return [
            ['Metric', 'Value'],
            ['Total Users', $this->data['activeUsers']->total_users],
            ['Active Users (30 days)', $this->data['activeUsers']->active_users],
            ['Never Logged In', $this->data['activeUsers']->never_logged_in],
            ['Total Messages', $this->data['messagingActivity']->total_messages],
            ['Active Messengers', $this->data['messagingActivity']->active_messengers],
            [],
            ['Content Type', 'Items Created', 'Reactions']
        ] + $this->data['contentInteractions']->map(function($item) {
            return [
                $item->type,
                $item->content_count,
                $item->reaction_count,
            ];
        })->toArray();
    }
    
    protected function prepareTAMData()
    {
        return [
            ['Metric', 'Rating (out of 5)'],
            ['Ease of Use', round($this->data['tamEvaluation']->ease_of_use, 1)],
            ['Usefulness', round($this->data['tamEvaluation']->usefulness, 1)],
            ['Satisfaction', round($this->data['tamEvaluation']->satisfaction, 1)],
            ['Intention to Use', round($this->data['tamEvaluation']->intention_to_use, 1)],
            ['Feedback Count', $this->data['tamEvaluation']->feedback_count],
            [],
            ['Feature', 'Usage Count']
        ] + $this->data['featureUsage']->map(function($item) {
            return [
                $item->type,
                $item->count,
            ];
        })->toArray();
    }
}