<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Graduate Employability Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 20px;
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            line-height: 1.2;
        }

        .university-name {
            font-size: 14pt;
            font-weight: bold;

        }

        .campus-info {
            font-size: 11pt;
        }

        .report-title {
            font-size: 12pt;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
        }

        .academic-year {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .degree-program {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9pt;
            page-break-inside: auto;
        }

        thead th {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            background-color: #f2f2f2;
            font-weight: bold;
        }

        tbody td {
            border: 1px solid #000;
            padding: 4px;
        }

        tbody tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .page-break {
            page-break-after: always;
        }

        .badge {
            display: inline-block;
            padding: 1px 4px;
            font-size: 8pt;
            font-weight: bold;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 3px;
            margin-left: 3px;
        }

        .badge-primary {
            color: #fff;
            background-color: #007bff;
        }

        .badge-secondary {
            color: #fff;
            background-color: #6c757d;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .no-data {
            text-align: center;
            font-style: italic;
            padding: 20px;
            border: 1px solid #ddd;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    @if(count($groupedGraduates) > 0)
        @foreach($groupedGraduates as $year => $graduates)
            <div class="header">
                <p>Republic of the Philippines</p>
                <p class="university-name">Mindoro State University</p>
                <p class="campus-info">{{ $campus ? $campus->campus_name : 'Main Campus' }}</p>
                <p class="campus-info">{{ $campus ? $campus->campus_address : 'Alcate, Victoria, Oriental Mindoro' }}</p>
                
                <p class="report-title">EMPLOYABILITY OF GRADUATES</p>
                <p class="academic-year">A.Y. {{ $year }}-{{ intval($year) + 1 }}</p>
                <p class="degree-program">Bachelor of Science in Information Technology</p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th width="5%">No.</th>
                        <th width="10%">Year Graduated</th>
                        <th width="12%">First Name</th>
                        <th width="10%">Middle Name</th>
                        <th width="12%">Last Name</th>
                        <th width="8%">Gender</th>
                        <th width="15%">Company/Institution</th>
                        <th width="15%">Address of Employment</th>
                        <th width="13%">Position</th>
                        <th width="10%">Year Employed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($graduates as $index => $graduate)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">{{ $graduate['year_graduated'] }}-{{ intval($graduate['year_graduated']) + 1 }}</td>
                            <td>{{ $graduate['first_name'] }}</td>
                            <td>{{ $graduate['middle_initial'] }}</td>
                            <td>{{ $graduate['last_name'] }}</td>
                            <td class="text-center">{{ $graduate['gender'] }}</td>
                            <td>
                                {{ $graduate['company'] ? $graduate['company']['name'] : ($graduate['institution'] ?? 'N/A') }}
                                @if($graduate['is_from_mindoro_state'])
                                    <span class="badge badge-primary">MSU</span>
                                @endif
                                @if($graduate['campus_name'])
                                    <span class="badge badge-secondary">{{ $graduate['campus_name'] }}</span>
                                @endif
                            </td>
                            <td>{{ $graduate['company'] ? ($graduate['company_city'] . ', ' . $graduate['company_country']) : 'N/A' }}</td>
                            <td>{{ $graduate['job_title'] ?? 'N/A' }}</td>
                            <td class="text-center">{{ $graduate['first_job_year'] ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if(!$loop->last)
                <div class="page-break"></div>
            @endif
        @endforeach
    @else
        <div class="no-data">
            No graduate data available for the selected filters
        </div>
    @endif
</body>
</html>