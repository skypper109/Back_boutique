@extends('pdf.layouts.base')

@section('title', 'Rapport Journalier')

@section('styles')
    <style>
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15mm;
            padding-bottom: 10mm;
            border-bottom: 3px solid #0f172a;
        }

        .section-title {
            font-size: 14pt;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            margin: 15mm 0 8mm 0;
            padding-bottom: 3mm;
            border-bottom: 2px solid #f59e0b;
        }

        .report-table {
            width: 100%;
            margin-bottom: 15mm;
        }

        .report-table thead tr {
            background-color: #0f172a;
        }

        .report-table th {
            padding: 8px 6px;
            font-size: 6pt;
            font-weight: 900;
            color: white;
            text-transform: uppercase;
        }

        border-collapse: collapse;
        table-layout: fixed;
        margin-top: 10px;
        margin-bottom: 20px;
        }

        .excel-table th,
        .excel-table td {
            border: 1px solid #000;
            padding: 5px;
            word-wrap: break-word;
        }

        .excel-table th {
            background-color: #e0e0e0;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5pt;
        }

        .header-box {
            border: 2px solid #000;
            padding: 10px;
            margin-bottom: 15px;
        }

        .section-title {
            background-color: #444;
            color: #fff;
            padding: 5px 10px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9pt;
            margin-top: 20px;
        }

        .zebra tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .bg-grey {
            background-color: #eee;
        }
    </style>
@endsection

@section('content')
    <div class="header-box">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 60%; border: none;">
                    <div style="font-size: 14pt; font-weight: bold;">{{ $boutique->nom }}</div>
                    <div>{{ $boutique->adresse }}</div>
                    <div>Tél: {{ $boutique->telephone }}</div>
                </td>
                <td style="width: 40%; border: none; text-align: right; vertical-align: top;">
                    <div style="font-weight: bold; font-size: 11pt;">RAPPORT D'AUDIT JOURNALIER</div>
                    <div style="font-size: 12pt; font-weight: bold; margin-top: 5px;">
                        {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">I. ÉTAT DES VENTES DE LA JOURNÉE</div>
    <table class="excel-table zebra">
        <thead>
            <tr>
                <th style="width: 5%;">ID</th>
                <th style="width: 15%;">CLIENT</th>
                <th style="width: 8%;" class="text-center">PAYE</th>
                <th style="width: 35%;">ARTICLES VENDUS</th>
                <th style="width: 7%;" class="text-center">QTÉ</th>
                <th style="width: 10%;" class="text-right">BRUT</th>
                <th style="width: 8%;" class="text-right">REM.</th>
                <th style="width: 12%;" class="text-right">NET PERÇU</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ventes as $vente)
                <tr>
                    <td class="text-center">{{ $vente->id }}</td>
                    <td>{{ $vente->client->nom ?? 'CLIENT PASSAGE' }}</td>
                    <td class="text-center font-bold" style="font-size: 7pt;">
                        {{ $vente->type_paiement === 'credit' ? 'CRÉDIT' : 'CASH' }}
                    </td>
                    <td>
                        @foreach ($vente->detailVentes as $detail)
                            • {{ $detail->produit->nom }} (x{{ $detail->quantite }})<br>
                        @endforeach
                    </td>
                    <td class="text-center font-bold">{{ $vente->detailVentes->sum('quantite') }}</td>
                    <td class="text-right">{{ number_format($vente->montant_total, 0, ',', ' ') }}</td>
                    <td class="text-right text-red">{{ number_format($vente->montant_remis ?? 0, 0, ',', ' ') }}</td>
                    <td class="text-right font-bold bg-grey">
                        {{ number_format($vente->montant_total - ($vente->montant_remis ?? 0), 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-grey">
                <td colspan="7" class="text-right font-bold">TOTAL VENTES NETTES (A)</td>
                <td class="text-right font-bold" style="font-size: 10pt;">
                    {{ number_format($totaux['ventes_net'], 0, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="section-title">II. ÉTAT DES DÉPENSES DE LA JOURNÉE</div>
    <table class="excel-table zebra">
        <thead>
            <tr>
                <th style="width: 10%;">HEURE</th>
                <th style="width: 25%;">CATÉGORIE</th>
                <th style="width: 45%;">MOTIF / DESCRIPTION</th>
                <th style="width: 20%;" class="text-right">MONTANT DÉPENSÉ</th>
            </tr>
        </thead>
        <tbody>
            @if (count($depenses) > 0)
                @foreach ($depenses as $depense)
                    <tr>
                        <td class="text-center">{{ \Carbon\Carbon::parse($depense->created_at)->format('H:i') }}</td>
                        <td class="font-bold">{{ $depense->category->name ?? 'DIVERS' }}</td>
                        <td>{{ $depense->description }}</td>
                        <td class="text-right font-bold red" style="color: #c00;">
                            {{ number_format($depense->amount, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" class="text-center italic" style="padding: 20px;">AUCUNE DÉPENSE ENREGISTRÉE CE JOUR
                    </td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="bg-grey">
                <td colspan="3" class="text-right font-bold">TOTAL DÉPENSES (B)</td>
                <td class="text-right font-bold" style="font-size: 10pt; color: #c00;">
                    {{ number_format($totaux['depenses'], 0, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="section-title">III. RÉSUMÉ FINANCIRE & PERFORMANCE</div>
    <table class="excel-table">
        <tr>
            <td class="bg-grey font-bold" style="width: 50%;">CHIFFRE D'AFFAIRE TOTAL (CASH + CRÉDIT)</td>
            <td class="text-right font-bold" style="width: 50%; font-size: 10pt;">
                {{ number_format($totaux['ventes_net'], 0, ',', ' ') }} {{ $boutique->devise }}</td>
        </tr>
        <tr>
            <td class="bg-grey font-bold">CHARGES D'EXPLOITATION (DÉPENSES)</td>
            <td class="text-right font-bold text-red" style="color: #c00;">-
                {{ number_format($totaux['depenses'], 0, ',', ' ') }} {{ $boutique->devise }}</td>
        </tr>
        <tr style="background-color: #000; color: #fff;">
            <td class="font-bold" style="font-size: 11pt;">BÉNÉFICE NET DE LA JOURNÉE (A - B)</td>
            <td class="text-right font-bold" style="font-size: 14pt;">
                {{ number_format($totaux['benefice_net'], 0, ',', ' ') }}
                <small style="font-size: 9pt;">{{ $boutique->devise }}</small>
            </td>
        </tr>
    </table>

    <table class="excel-table" style="margin-top: 20px;">
        <tr>
            <th colspan="2">STATISTIQUES OPÉRATIONNELLES</th>
        </tr>
        <tr>
            <td style="width: 50%;">Nombre de factures éditées :</td>
            <td class="text-center font-bold">{{ $stats['nombre_ventes'] }}</td>
        </tr>
        <tr>
            <td>Nombre de bons de dépense :</td>
            <td class="text-center font-bold">{{ $stats['nombre_depenses'] }}</td>
        </tr>
    </table>

    <div style="margin-top: 50px;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 50%; border: 2px solid #000; padding: 15px; height: 100px; vertical-align: top;">
                    <div style="font-size: 8pt; font-weight: bold; text-transform: uppercase;">Visa du Gérant / Caissier :
                    </div>
                    <div style="margin-top: 10px; font-size: 7pt; font-style: italic;">{{ $boutique->nom }}</div>
                </td>
                <td style="width: 50%; border: 2px solid #000; padding: 15px; height: 100px; vertical-align: top;">
                    <div style="font-size: 8pt; font-weight: bold; text-transform: uppercase;">Visa de la Direction / Audit
                        :</div>
                </td>
            </tr>
        </table>
    </div>

    <p
        style="text-align: center; font-size: 6pt; color: #666; text-transform: uppercase; letter-spacing: 0.3em; margin-top: 15px;">
        RAPPORT GÉNÉRÉ AUTOMATIQUEMENT LE {{ date('d/m/Y à H:i') }}
    </p>
@endsection
