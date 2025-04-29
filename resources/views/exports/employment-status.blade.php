<!DOCTYPE html>
<html>
<head>
    <title>Employment Status Report</title>
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
    <h1>Employment Status Report</h1>
    
    <div class="section">
        <h2>Employment Summary</h2>
        <table>
            <tr>
                <th>Total Graduates</th>
                <td>{{ $totalGraduates }}</td>
            </tr>
            <tr>
                <th>Employment Rate</th>
                <td>{{ round($employmentRate * 100) }}%</td>
            </tr>
        </table>
    </div>
    
    <div class="section">
        <h2>Industry Distribution</h2>
        <table>
            <tr>
                <th>Industry</th>
                <th>Count</th>
            </tr>
            @foreach($industryDistribution as $industry)
            <tr>
                <td>{{ $industry->name }}</td>
                <td>{{ $industry->count }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    
    <!-- Add other sections similarly -->
</body>
</html>