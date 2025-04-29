<!DOCTYPE html>
<html>
<head>
    <title>Employability Metrics Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .section { margin-bottom: 30px; }
    </style>
</head>
<body>
    <h1>Employability Metrics Report</h1>
    
    <div class="section">
        <h2>Employability Index</h2>
        <p>Average Score: {{ number_format($metrics['indexReport']->avg_score ?? 0, 2) }}</p>
        <p>Based on {{ $metrics['indexReport']->count ?? 0 }} graduates</p>
    </div>
    
    <div class="section">
        <h2>Department Performance</h2>
        <table>
            <tr>
                <th>Degree Program</th>
                <th>Average Score</th>
                <th>Graduate Count</th>
            </tr>
            @foreach($metrics['departmentPerformance'] as $dept)
            <tr>
                <td>{{ $dept->degree_earned }}</td>
                <td>{{ number_format($dept->avg_score, 2) }}</td>
                <td>{{ $dept->count }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    
    <div class="section">
        <h2>Trend Analysis</h2>
        <table>
            <tr>
                <th>Year</th>
                <th>Average Score</th>
                <th>Graduate Count</th>
            </tr>
            @foreach($metrics['trendAnalysis'] as $trend)
            <tr>
                <td>{{ $trend->year_graduated }}</td>
                <td>{{ number_format($trend->avg_score, 2) }}</td>
                <td>{{ $trend->count }}</td>
            </tr>
            @endforeach
        </table>
    </div>
</body>
</html>