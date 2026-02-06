<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Document')</title>
    <style>
        * {
            margin: 10px 5px;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #1e293b;
        }

        @page {
            size: A4 @yield('orientation', 'portrait')

            ;
            /* margin: 25mm 25mm; */
        }

        .container {
            /* width: 100%;
            max-width: 100%; */
            /* margin: 25px 25px; */
        }

        /* Typography */
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-weight: 700;
            line-height: 1.2;
        }

        .text-xs {
            font-size: 7pt;
        }

        .text-sm {
            font-size: 8pt;
        }

        .text-base {
            font-size: 10pt;
        }

        .text-lg {
            font-size: 12pt;
        }

        .text-xl {
            font-size: 14pt;
        }

        .text-2xl {
            font-size: 18pt;
        }

        .text-3xl {
            font-size: 24pt;
        }

        /* Colors */
        .text-slate-900 {
            color: #0f172a;
        }

        .text-slate-600 {
            color: #475569;
        }

        .text-slate-400 {
            color: #94a3b8;
        }

        .text-slate-200 {
            color: #e2e8f0;
        }

        .text-amber-500 {
            color: #f59e0b;
        }

        .text-emerald-500 {
            color: #10b981;
        }

        .bg-slate-900 {
            background-color: #0f172a;
        }

        .bg-slate-50 {
            background-color: #f8fafc;
        }

        .bg-white {
            background-color: #ffffff;
        }

        /* Borders */
        .border-slate-900 {
            border-color: #0f172a;
        }

        .border-slate-100 {
            border-color: #f1f5f9;
        }

        /* Utilities */
        .uppercase {
            text-transform: uppercase;
        }

        .font-bold {
            font-weight: 700;
        }

        .font-black {
            font-weight: 900;
        }

        .italic {
            font-style: italic;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background-color: #f8fafc;
            padding: 8px 10px;
            text-align: left;
            font-weight: 900;
            font-size: 7pt;
            color: #94a3b8;
            text-transform: uppercase;
        }

        table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
        }

        /* Layout */
        .flex {
            display: flex;
        }

        .justify-between {
            justify-content: space-between;
        }

        .items-center {
            align-items: center;
        }

        .mb-2 {
            margin-bottom: 5mm;
        }

        .mb-4 {
            margin-bottom: 10mm;
        }

        .mb-6 {
            margin-bottom: 15mm;
        }

        .mt-4 {
            margin-top: 10mm;
        }

        .pb-2 {
            padding-bottom: 5mm;
        }

        .pb-4 {
            padding-bottom: 10mm;
        }

        .border-b {
            border-bottom: 1px solid #e2e8f0;
        }

        .border-b-2 {
            border-bottom: 2px solid #0f172a;
        }

        /* Custom */
        .page-break {
            page-break-after: always;
        }

        .no-page-break {
            page-break-inside: avoid;
        }
    </style>
    @yield('styles')
</head>

<body>
    <div class="container">
        @yield('content')
    </div>
</body>

</html>
