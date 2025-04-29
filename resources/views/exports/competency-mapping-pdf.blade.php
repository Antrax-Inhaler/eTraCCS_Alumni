<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Competency Mapping Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <h1>Competency Mapping Report</h1>

    <h2>Skills Utilization Report</h2>
    <table>
        <thead>
            <tr>
                <th>Competency</th>
                <th>Count</th>
                <th>Average Score</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($skillsReport as $item)
                <tr>
                    <td>{{ $item->competencies_learned }}</td>
                    <td>{{ $item->count }}</td>
                    <td>{{ number_format($item->avg_score, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Curriculum Relevance Report</h2>
    <table>
        <thead>
            <tr>
                <th>Industry</th>
                <th>Job Title</th>
                <th>Required Competencies</th>
                <th>Job Count</th>
                <th>Average Score</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($curriculumReport as $item)
                <tr>
                    <td>{{ $item->industry }}</td>
                    <td>{{ $item->job_title }}</td>
                    <td>{{ $item->required_competencies }}</td>
                    <td>{{ $item->job_count }}</td>
                    <td>{{ number_format($item->avg_score, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>