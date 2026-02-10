<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .header p {
            margin: 5px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 25px 0;
        }

        .summary-box {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            border-left: 4px solid;
        }

        .summary-box.ventes {
            border-left-color: #10b981;
        }

        .summary-box.depenses {
            border-left-color: #ef4444;
        }

        .summary-box.benefice {
            border-left-color: #f59e0b;
        }

        .summary-label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .summary-value {
            font-size: 24px;
            font-weight: 900;
            color: #0f172a;
        }

        .summary-currency {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 4px;
        }

        .stats {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .stats-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .stats-row:last-child {
            border-bottom: none;
        }

        .stats-label {
            color: #64748b;
            font-weight: 600;
        }

        .stats-value {
            color: #0f172a;
            font-weight: 700;
        }

        .attachment-notice {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .attachment-notice p {
            margin: 0;
            color: #92400e;
            font-size: 14px;
        }

        .footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
        }

        .footer a {
            color: #0f172a;
            text-decoration: none;
            font-weight: 600;
        }

        @media only screen and (max-width: 600px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📊 Rapport Journalier</h1>
            <p>{{ $boutique->nom }}</p>
            <p style="font-size: 18px; margin-top: 10px;">{{ $report->date->format('d/m/Y') }}</p>
        </div>

        <div class="content">
            <div class="greeting">
                <p>Bonjour,</p>
                <p>Voici le rapport journalier de votre boutique pour le
                    <strong>{{ $report->date->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</strong>.
                </p>
            </div>

            <div class="summary-grid">
                <div class="summary-box ventes">
                    <div class="summary-label">Total Ventes</div>
                    <div class="summary-value">{{ number_format($report->total_ventes, 0, ',', ' ') }}</div>
                    <div class="summary-currency">{{ $boutique->devise ?? 'CFA' }}</div>
                </div>
                <div class="summary-box depenses">
                    <div class="summary-label">Total Dépenses</div>
                    <div class="summary-value">{{ number_format($report->total_depenses, 0, ',', ' ') }}</div>
                    <div class="summary-currency">{{ $boutique->devise ?? 'CFA' }}</div>
                </div>
                <div class="summary-box benefice">
                    <div class="summary-label">Bénéfice Net</div>
                    <div class="summary-value">{{ number_format($report->benefice_net, 0, ',', ' ') }}</div>
                    <div class="summary-currency">{{ $boutique->devise ?? 'CFA' }}</div>
                </div>
            </div>

            <div class="stats">
                <div class="stats-row">
                    <span class="stats-label">Nombre de ventes</span>
                    <span class="stats-value">{{ $report->nombre_ventes }}</span>
                </div>
                <div class="stats-row">
                    <span class="stats-label">Nombre de dépenses</span>
                    <span class="stats-value">{{ $report->nombre_depenses }}</span>
                </div>
                <div class="stats-row">
                    <span class="stats-label">Vente moyenne</span>
                    <span class="stats-value">
                        {{ $report->nombre_ventes > 0 ? number_format($report->total_ventes / $report->nombre_ventes, 0, ',', ' ') : 0 }}
                        {{ $boutique->devise ?? 'CFA' }}
                    </span>
                </div>
            </div>

            <div class="attachment-notice">
                <p>📎 <strong>Le rapport détaillé en PDF est joint à cet email.</strong></p>
                <p style="margin-top: 8px; font-size: 13px;">Le PDF contient tous les détails des ventes et dépenses de
                    la journée.</p>
            </div>

            <p style="margin-top: 30px; color: #64748b; font-size: 14px;">
                Ce rapport a été généré automatiquement le {{ now()->format('d/m/Y à H:i') }}.
            </p>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} {{ $boutique->nom }}. Tous droits réservés.</p>
            <p style="margin-top: 10px;">
                {{ $boutique->adresse ?? '' }}
                @if ($boutique->telephone)
                    | Tél: {{ $boutique->telephone }}
                @endif
            </p>
        </div>
    </div>
</body>

</html>
