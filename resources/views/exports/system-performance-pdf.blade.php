<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Performance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1, h2 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .section {
            margin-bottom: 40px;
        }
        .no-data {
            text-align: center;
            color: #999;
        }
    </style>
</head>
<body>
    <h1>System Performance Report</h1>

    <!-- User Engagement Metrics Section -->
    <div class="section">
        <h2>User Engagement Metrics</h2>
        <table>
            <tr>
                <th>Total Users</th>
                <td>{{ $metrics['activeUsers']->total_users }}</td>
            </tr>
            <tr>
                <th>Active Users (Last 30 Days)</th>
                <td>{{ $metrics['activeUsers']->active_users }}</td>
            </tr>
            <tr>
                <th>Never Logged In</th>
                <td>{{ $metrics['activeUsers']->never_logged_in }}</td>
            </tr>
        </table>
    </div>

    <!-- Content Interactions Section -->
    <div class="section">
        <h2>Content Interactions</h2>
        <table>
            <thead>
                <tr>
                    <th>Content Type</th>
                    <th>Items Created</th>
                    <th>Reactions</th>
                </tr>
            </thead>
            <tbody>
                @if (count($metrics['contentInteractions']) === 0)
                    <tr>
                        <td colspan="3" class="no-data">No content interaction data found</td>
                    </tr>
                @else
                    @foreach ($metrics['contentInteractions'] as $interaction)
                        <tr>
                            <td>{{ $interaction->type }}</td>
                            <td>{{ $interaction->content_count }}</td>
                            <td>{{ $interaction->reaction_count }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <!-- Messaging Activity Section -->
    <div class="section">
        <h2>Messaging Activity</h2>
        <table>
            <tr>
                <th>Total Chats</th>
                <td>{{ $metrics['messagingActivity']->total_chats }}</td>
            </tr>
            <tr>
                <th>Total Messages</th>
                <td>{{ $metrics['messagingActivity']->total_messages }}</td>
            </tr>
            <tr>
                <th>Active Messengers</th>
                <td>{{ $metrics['messagingActivity']->active_messengers }}</td>
            </tr>
        </table>
    </div>

    <!-- TAM Evaluation Section -->
    <div class="section">
        <h2>TAM Evaluation</h2>
        <table>
            <tr>
                <th>Ease of Use Rating (Avg)</th>
                <td>{{ number_format($metrics['tamEvaluation']->ease_of_use, 2) }}</td>
            </tr>
            <tr>
                <th>Usefulness Rating (Avg)</th>
                <td>{{ number_format($metrics['tamEvaluation']->usefulness, 2) }}</td>
            </tr>
            <tr>
                <th>Satisfaction Rating (Avg)</th>
                <td>{{ number_format($metrics['tamEvaluation']->satisfaction, 2) }}</td>
            </tr>
            <tr>
                <th>Intention to Use Rating (Avg)</th>
                <td>{{ number_format($metrics['tamEvaluation']->intention_to_use, 2) }}</td>
            </tr>
            <tr>
                <th>Total Feedback Count</th>
                <td>{{ $metrics['tamEvaluation']->feedback_count }}</td>
            </tr>
        </table>
    </div>

    <!-- Feature Usage Section -->
    <div class="section">
        <h2>Feature Usage</h2>
        <table>
            <thead>
                <tr>
                    <th>Feature</th>
                    <th>Usage Count</th>
                </tr>
            </thead>
            <tbody>
                @if (count($metrics['featureUsage']) === 0)
                    <tr>
                        <td colspan="2" class="no-data">No feature usage data found</td>
                    </tr>
                @else
                    @foreach ($metrics['featureUsage'] as $usage)
                        <tr>
                            <td>{{ $usage->type }}</td>
                            <td>{{ $usage->count }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</body>
</html>