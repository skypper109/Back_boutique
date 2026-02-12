@extends('pdf.layouts.base')

@section('title', 'Facture Commerciale')

@section('styles')
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 10pt;
            color: #000;
        }

        .excel-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 20px;
        }

        .excel-table th,
        .excel-table td {
            border: 1px solid #666;
            padding: 8px;
            word-wrap: break-word;
        }

        .excel-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
        }

        .header-box {
            border: 2px solid #000;
            padding: 15px;
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 20pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .doc-type {
            background-color: #000;
            color: #fff;
            padding: 10px;
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
        }

        .zebra tr:nth-child(even) {
            background-color: #fafafa;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .bg-grey {
            background-color: #f9f9f9;
        }

        .font-bold {
            font-weight: bold;
        }

        .totals-table {
            width: 250px;
            float: right;
            margin-top: 10px;
            border-collapse: collapse;
        }

        .totals-table td {
            border: 1px solid #666;
            padding: 8px;
        }
    </style>
@endsection

@section('content')
    <div class="header-box">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 60%; border: none; vertical-align: top;">
                    <div class="company-name">{{ $boutique->nom ?? 'MA BOUTIQUE' }}</div>
                    <div style="font-size: 9pt;">
                        {{ $boutique->adresse ?? 'Adresse non spécifiée' }}<br>
                        Tél: {{ $boutique->telephone ?? '---' }}<br>
                        Email: {{ $boutique->email ?? '---' }}
                    </div>
                </td>
                <td style="width: 40%; border: none; vertical-align: top;">
                    <div class="doc-type">FACTURE</div>
                    <div style="margin-top: 10px; text-align: right; font-weight: bold;">
                        N° #{{ str_pad($vente->id, 6, '0', STR_PAD_LEFT) }}<br>
                        Date: {{ \Carbon\Carbon::parse($vente->date_vente)->format('d/m/Y') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="excel-table">
        <tr>
            <td style="background-color: #f2f2f2; font-weight: bold; width: 15%;">CLIENT</td>
            <td>{{ $vente->client->nom ?? 'CLIENT DE PASSAGE' }}</td>
            <td style="background-color: #f2f2f2; font-weight: bold; width: 15%;">STATUS</td>
            <td class="text-center font-bold"
                style="{{ $vente->type_paiement === 'credit' ? 'color: red;' : 'color: green;' }}">
                {{ $vente->type_paiement === 'credit' ? 'EN CRÉDIT' : 'PAYÉ / RÉGLÉ' }}
            </td>
        </tr>
    </table>

    <table class="excel-table zebra">
        <thead>
            <tr>
                <th style="width: 10%;">REF</th>
                <th style="width: 50%;">DÉSIGNATION ARTICLE</th>
                <th style="width: 10%;" class="text-center">QTÉ</th>
                <th style="width: 15%;" class="text-right">P.U.</th>
                <th style="width: 15%;" class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($vente->detailVentes as $detail)
                <tr>
                    <td class="text-center">{{ str_pad($detail->produit->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $detail->produit->nom }}</td>
                    <td class="text-center font-bold">{{ $detail->quantite }}</td>
                    <td class="text-right">{{ number_format($detail->prix_unitaire, 0, ',', ' ') }}</td>
                    <td class="text-right font-bold">{{ number_format($detail->montant_total, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="clear: both;">
        <table class="totals-table">
            <tr>
                <td class="bg-grey font-bold">SOUS-TOTAL</td>
                <td class="text-right">{{ number_format($vente->montant_total, 0, ',', ' ') }}</td>
            </tr>
            @if ($vente->montant_remis > 0)
                <tr>
                    <td class="bg-grey font-bold">REMISE (-)</td>
                    <td class="text-right text-red" style="color: red;">
                        {{ number_format($vente->montant_remis, 0, ',', ' ') }}</td>
                </tr>
            @endif
            <tr style="background-color: #000; color: #fff;">
                <td class="font-bold">NET À PAYER</td>
                <td class="text-right font-bold" style="font-size: 12pt;">
                    {{ number_format($vente->montant_total - ($vente->montant_remis ?? 0), 0, ',', ' ') }}
                    <small style="font-size: 8pt;">{{ $boutique->devise ?? 'CFA' }}</small>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 150px;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 50%; border: 1px dashed #ccc; padding: 20px; text-align: center; vertical-align: top;">
                    <div style="font-size: 8pt; font-weight: bold; text-transform: uppercase; margin-bottom: 50px;">Cachet &
                        Signature Boutique</div>
                </td>
                <td style="width: 50%; border: 1px dashed #ccc; padding: 20px; text-align: center; vertical-align: top;">
                    <div style="font-size: 8pt; font-weight: bold; text-transform: uppercase; margin-bottom: 50px;">
                        Signature Client (Bon pour accord)</div>
                </td>
            </tr>
        </table>
    </div>

    <div
        style="margin-top: 30px; text-align: center; font-size: 8pt; color: #666; border-top: 1px solid #ccc; padding-top: 10px;">
        {{ $boutique->footer_facture ?? 'Merci de votre confiance. Les marchandises vendues ne sont ni reprises ni échangées.' }}
    </div>
@endsection
