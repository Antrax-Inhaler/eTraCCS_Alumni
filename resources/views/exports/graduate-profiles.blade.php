<!-- resources/views/exports/graduate-profiles.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <title>Graduate Profile Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .stats { margin-bottom: 30px; }
        .stat-item { display: inline-block; margin-right: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Graduate Profile Report</h1>
        <p>Generated on: {{ now()->format('F j, Y') }}</p>
    </div>
    
    <div class="stats">
        <h2>Summary Statistics</h2>
        <div class="stat-item"><strong>Total Graduates:</strong> {{ number_format($statistics['totalGraduates']) }}</div>
        <div class="stat-item"><strong>Unique Degrees:</strong> {{ count($statistics['degreesDistribution']) }}</div>
    </div>
    
    <h2>Graduate Details</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Degree</th>
                <th>Year Graduated</th>
                <th>Campus</th>
                <th>Institution</th>
            </tr>
        </thead>
        <tbody>
            @foreach($graduates->items() as $graduate)
                <tr>
                    <td>{{ $graduate->user->first_name }} {{ $graduate->user->middle_initial }} {{ $graduate->user->last_name }}</td>
                    <td>{{ $graduate->user->email }}</td>
                    <td>{{ $graduate->degree_earned }}</td>
                    <td>{{ $graduate->year_graduated }}</td>
                    <td>{{ $graduate->campus ?? 'N/A' }}</td>
                    <td>{{ $graduate->institution }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="margin-top: 20px; text-align: center;">
        Page <span class="pageNumber"></span> of <span class="totalPages"></span>
    </div>
</body>
</html>