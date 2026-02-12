@extends('pdf.layouts.base')

@section('title', 'Audit d\'Inventaire & Mouvements')

@section('styles')
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 8.5pt;
            color: #000;
        }

        .excel-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 15px;
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

        .header-section {
            border: 2px solid #000;
            padding: 10px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
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

        .text-red {
            color: #c00;
        }

        .text-green {
            color: #060;
        }

        .signature-section {
            margin-top: 50px;
            display: table;
            width: 100%;
        }

        .signature-box {
            display: table-cell;
            width: 33.33%;
            border: 1px solid #aaa;
            padding: 10px;
            text-align: center;
            height: 80px;
            vertical-align: top;
        }

        .signature-label {
            font-size: 8pt;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 40px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 7pt;
            color: #666;
            border-top: 1px dotted #ccc;
            padding-top: 10px;
        }
    </style>
@endsection

@section('content')
    <div class="header-section">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 50%; border: none;">
                    <div style="font-size: 14pt; font-weight: bold;">{{ $boutique->nom ?? 'BOUTIQUE' }}</div>
                    <div>{{ $boutique->adresse ?? '---' }}</div>
                    <div>Tél: {{ $boutique->telephone ?? '---' }}</div>
                </td>
                <td style="width: 50%; border: none; text-align: right; vertical-align: top;">
                    <div style="font-weight: bold; font-size: 11pt;">RAPPORT D'AUDIT DES MOUVEMENTS</div>
                    <div>Généré le: {{ date('d/m/Y H:i') }}</div>
                </td>
            </tr>
        </table>
    </div>

    @if ($filters['start_date'] || $filters['end_date'])
        <table class="excel-table" style="margin-bottom: 20px;">
            <tr>
                <td class="bg-grey font-bold" style="width: 20%;">PÉRIODE FILTRÉE</td>
                <td>
                    @if ($filters['start_date'] && $filters['end_date'])
                        Du {{ $filters['start_date'] }} au {{ $filters['end_date'] }}
                    @elseif($filters['start_date'])
                        Depuis le: {{ $filters['start_date'] }}
                    @else
                        Jusqu'au: {{ $filters['end_date'] }}
                    @endif
                </td>
            </tr>
        </table>
    @endif

    <table class="excel-table zebra">
        <thead>
            <tr>
                <th style="width: 12%;">DATE / HEURE</th>
                <th style="width: 25%;">DÉSIGNATION ARTICLE</th>
                <th style="width: 10%;" class="text-center">NATURE</th>
                <th style="width: 23%;">MOTIF / DESCRIPTION</th>
                <th style="width: 8%;" class="text-center">QTÉ</th>
                <th style="width: 10%;" class="text-right">PRIX UNIT.</th>
                <th style="width: 12%;" class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($inventaires as $item)
                @php
                    $pu =
                        $item->type === 'retrait'
                            ? $item->produit->stock->prix_vente ?? 0
                            : $item->produit->stock->prix_achat ?? 0;
                    $total = $item->quantite * $pu;
                @endphp
                <tr>
                    <td class="text-center">{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</td>
                    <td class="font-bold">{{ $item->produit->nom ?? 'N/A' }}</td>
                    <td class="text-center font-bold {{ $item->type === 'retrait' ? 'text-red' : 'text-green' }}">
                        {{ $item->type === 'retrait' ? 'SORTIE' : 'ENTRÉE' }}
                    </td>
                    <td style="font-size: 7.5pt; font-style: italic;">{{ $item->description }}</td>
                    <td class="text-center font-bold">{{ $item->type === 'retrait' ? '-' : '+' }}{{ $item->quantite }}
                    </td>
                    <td class="text-right">{{ number_format($pu, 0, ',', ' ') }}</td>
                    <td class="text-right font-bold">{{ number_format($total, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-grey">
                <td colspan="4" class="text-right font-bold">RÉSUMÉ DU FLUX DE STOCK</td>
                <td class="text-center font-bold {{ $stats['netMouvement'] >= 0 ? 'text-green' : 'text-red' }}">
                    {{ $stats['netMouvement'] > 0 ? '+' : '' }}{{ $stats['netMouvement'] }}
                </td>
                <td colspan="2" class="text-right font-bold">
                    VAL. ACQUISITION: {{ number_format($stats['valeurAchatEntrante'], 0, ',', ' ') }}
                    {{ $boutique->devise }}
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-label">Gestionnaire de Stock</div>
            <div style="font-size: 7pt; color: #999; margin-top: 30px;">Visa et Date</div>
        </div>
        <div class="signature-box">
            <div class="signature-label">Audit Interne</div>
            <div style="font-size: 7pt; color: #999; margin-top: 30px;">Validation et Cachet</div>
        </div>
        <div class="signature-box">
            <div class="signature-label">Direction</div>
            <div style="font-size: 7pt; color: #999; margin-top: 30px;">Approbation</div>
        </div>
    </div>

    <div class="footer">
        Document d'audit officiel généré par Ma Boutique le {{ date('d/m/Y à H:i') }} - Page 1/1
    </div>
@endsection
