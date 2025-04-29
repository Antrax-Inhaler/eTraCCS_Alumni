<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GIS/Spatial Report</title>
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
    <h1>GIS/Spatial Report</h1>

    <!-- Alumni Heatmap Section -->
    <div class="section">
        <h2>Alumni Heatmap</h2>
        <p>Geographic concentration of alumni</p>
        <table>
            <thead>
                <tr>
                    <th>Country</th>
                    <th>City</th>
                    <th>Alumni Count</th>
                </tr>
            </thead>
            <tbody>
                @if (count($mapData['alumniHeatmap']) === 0)
                    <tr>
                        <td colspan="3" class="no-data">No alumni location data found</td>
                    </tr>
                @else
                    @foreach ($mapData['alumniHeatmap'] as $location)
                        <tr>
                            <td>{{ $location->country }}</td>
                            <td>{{ $location->city }}</td>
                            <td>{{ $location->alumni_count }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <!-- Industry Clusters Section -->
    <div class="section">
        <h2>Industry Clusters</h2>
        <p>Companies employing many alumni</p>
        <table>
            <thead>
                <tr>
                    <th>Industry</th>
                    <th>Employee Count</th>
                    <th>Location</th>
                </tr>
            </thead>
            <tbody>
                @if (count($mapData['industryClusters']) === 0)
                    <tr>
                        <td colspan="3" class="no-data">No industry cluster data found</td>
                    </tr>
                @else
                    @foreach ($mapData['industryClusters'] as $company)
                        <tr>
                            <td>{{ $company->industry }}</td>
                            <td>{{ $company->employee_count }}</td>
                            <td>{{ $company->latitude }}, {{ $company->longitude }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <!-- Migration Patterns Section -->
    <div class="section">
        <h2>Migration Patterns</h2>
        <p>Movement from campus to current locations</p>
        <table>
            <thead>
                <tr>
                    <th>Origin Campus</th>
                    <th>Destination</th>
                    <th>Country</th>
                    <th>Alumni Count</th>
                </tr>
            </thead>
            <tbody>
                @if (count($mapData['migrationPatterns']) === 0)
                    <tr>
                        <td colspan="4" class="no-data">No migration pattern data found</td>
                    </tr>
                @else
                    @foreach ($mapData['migrationPatterns'] as $pattern)
                        <tr>
                            <td>{{ $pattern->origin }}</td>
                            <td>{{ $pattern->destination }}</td>
                            <td>{{ $pattern->country }}</td>
                            <td>{{ $pattern->count }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</body>
</html>