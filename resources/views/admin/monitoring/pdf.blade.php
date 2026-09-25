<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Monitoring {{ $period->translatedFormat('F Y') }}</title>
    <style>
        @page { margin: 8mm; size: A4 portrait; }
        * { box-sizing: border-box; }
        body { color: #191c1b; font-family: DejaVu Sans, sans-serif; font-size: 7px; margin: 0; }
        .monitoring-document { page-break-after: always; }
        .monitoring-document:last-child { page-break-after: auto; }
        .monitoring-document > header { border-bottom: .6px solid #c4c9ac; margin-bottom: 10px; padding-bottom: 10px; text-align: center; }
        .monitoring-document-meta { color: #5b5f5e; display: table; font-size: 5.5px; text-transform: uppercase; width: 100%; }
        .monitoring-document-meta span { display: table-cell; text-align: left; }
        .monitoring-document-meta span:last-child { color: #526600; text-align: right; }
        .monitoring-document h2 { font-size: 11px; margin: 8px 0 3px; text-transform: uppercase; }
        .monitoring-document > header p { color: #5b5f5e; font-size: 6px; margin: 0; }
        .monitoring-document-table-wrap { width: 100%; }
        .monitoring-document-section-title { font-size: 8px; margin: 16px 0 5px; }
        table { border-collapse: collapse; table-layout: fixed; width: 100%; }
        .monitoring-document-matrix { border: .7px solid #191c1b; }
        .monitoring-document-matrix thead { display: table-header-group; }
        .monitoring-document-matrix tr { page-break-inside: avoid; }
        .monitoring-document-matrix th,
        .monitoring-document-matrix td { border: .55px solid #4b504d; padding: 4px 2px; text-align: center; vertical-align: middle; }
        .monitoring-document-matrix th { background: #f2f4f2; font-size: 5.4px; font-weight: bold; text-transform: uppercase; }
        .monitoring-document-matrix td:nth-child(2),
        .monitoring-document-matrix th:nth-child(2) { text-align: left; }
        .monitoring-category-row th { height: 16px; }
        .monitoring-indicator-row th { height: 72px; overflow: visible; padding: 0; position: relative; }
        .monitoring-indicator-row th span { display: block; font-size: 5.2px; left: 50%; line-height: 1; position: absolute; top: 50%; transform: translate(-50%, -50%) rotate(-90deg); white-space: nowrap; }
        .monitoring-final-heading,
        .monitoring-final-value { background: #eff8c9 !important; font-weight: bold; }
        .monitoring-col-number { width: 4%; }
        .monitoring-col-name { width: 22%; }
        .monitoring-col-status { width: 11%; }
        .monitoring-col-attendance { width: 7%; }
        .monitoring-col-final { width: 8%; }
        .monitoring-document-summary { margin-top: 12px; width: 45%; }
        .monitoring-score-legend { border: .6px solid #191c1b; font-size: 5.8px; }
        .monitoring-score-legend th,
        .monitoring-score-legend td { border: .5px solid #4b504d; padding: 3px 4px; text-align: center; }
        .monitoring-score-legend th:first-child,
        .monitoring-score-legend td:first-child { text-align: left; width: 44%; }
        .monitoring-score-legend th { background: #f2f4f2; }
        .monitoring-score-legend .is-highlighted { background: #f5fbdc; font-weight: bold; }
        .monitoring-document-note { color: #5b5f5e; font-size: 5.4px; line-height: 1.35; margin: 6px 0 0; }
        .monitoring-document > footer { border-top: .5px dashed #c4c9ac; color: #5b5f5e; display: table; font-size: 5px; margin-top: 22px; padding-top: 7px; width: 100%; }
        .monitoring-document > footer span { display: table-cell; }
        .monitoring-document > footer span:last-child { text-align: right; }
    </style>
</head>
<body>
    @foreach($reports as $report)
        @include('admin.monitoring._report-document', compact('report', 'period'))
    @endforeach
</body>
</html>
