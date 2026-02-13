@extends('pdf.layouts.base')

@section('title', 'Reçu de Paiement & État de Dette')

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
            margin-top: 15px;
        }

        .excel-table th,
        .excel-table td {
            border: 1px solid #000;
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
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .doc-type {
            background-color: #000;
            color: #fff;
            padding: 10px;
            text-align: center;
            font-size: 16pt;
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

        .font-bold {
            font-weight: bold;
        }

        .bg-totals {
            background-color: #f9f9f9;
        }

        .balance-label {
            background-color: #000;
            color: #fff;
            font-weight: bold;
            text-align: right;
            padding: 10px;
        }

        .balance-value {
            border: 2px solid #000;
            font-size: 16pt;
            font-weight: bold;
            text-align: right;
            padding: 10px;
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
                        {{ $boutique->adresse ?? '---' }}<br>
                        Tél: {{ $boutique->telephone ?? '---' }}<br>
                        Email: {{ $boutique->email ?? '---' }}
                    </div>
                </td>
                <td style="width: 40%; border: none; vertical-align: top;">
                    <div class="doc-type">REÇU DE CRÉDIT</div>
                    <div style="margin-top: 10px; text-align: right; font-weight: bold;">
                        Vente N° #{{ str_pad($vente->id, 6, '0', STR_PAD_LEFT) }}<br>
                        Date Vente: {{ \Carbon\Carbon::parse($vente->date_vente)->format('d/m/Y') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="excel-table">
        <tr>
            <td style="background-color: #f2f2f2; font-weight: bold; width: 20%;">CLIENT / DÉBITEUR</td>
            <td style="font-size: 12pt; font-weight: bold;">{{ $vente->client->nom ?? 'CLIENT DE PASSAGE' }}</td>
            <td style="background-color: #f2f2f2; font-weight: bold; width: 15%;">TÉLÉPHONE</td>
            <td>{{ $vente->client->telephone ?? '---' }}</td>
        </tr>
    </table>


    <div
        style="margin-top: 25px; font-weight: bold; text-decoration: underline; font-size: 9pt; text-transform: uppercase;">
        La liste des Produits Achetés :
    </div>
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
                    <td class="text-center">
                        {{ str_pad($detail->produit->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $detail->produit->nom . ' (' . ($detail->produit->reference ?? ' ') . ') ' }}</td>
                    <td class="text-center font-bold">{{ $detail->quantite }}</td>
                    <td class="text-right">{{ number_format($detail->prix_unitaire, 0, ',', ' ') }}</td>
                    <td class="text-right font-bold">{{ number_format($detail->montant_total, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
            @if ($vente->montant_avance > 0)
                <tr>
                    <td class="text-left font-bold" colspan="4">AVANCE PAYER</td>
                    <td class="text-right font-bold">{{ number_format($vente->montant_avance, 0, ',', ' ') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div
        style="margin-top: 25px; font-weight: bold; text-decoration: underline; font-size: 9pt; text-transform: uppercase;">
        Historique des Versements Effectués :
    </div>

    <table class="excel-table zebra">
        <thead>
            <tr>
                <th style="width: 25%;">DATE DU VERSEMENT</th>
                <th style="width: 45%;">MODE DE PAIEMENT</th>
                <th style="width: 30%;" class="text-right">MONTANT VERSÉ</th>
            </tr>
        </thead>
        <tbody>
            @if ($paiements && count($paiements) > 0)
                @foreach ($paiements as $paiement)
                    <tr>
                        <td class="text-center">
                            {{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y HH:mm') }}
                        </td>
                        <td class="text-center font-bold">{{ strtoupper($paiement->mode_paiement) }}</td>
                        <td class="text-right font-bold">{{ number_format($paiement->montant, 0, ',', ' ') }}
                            {{ $boutique->devise }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3" class="text-center italic" style="padding: 20px;">AUCUN VERSEMENT ENREGISTRÉ</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div style="margin-top: 5px; width: 360px; float: right;">
        <table class="excel-table" style="border: 2px solid #000;">
            <tr>
                <td style="background-color: #eee; font-weight: bold;">MONTANT TOTAL DE L'ACHAT</td>
                <td class="text-right font-bold">{{ number_format($vente->montant_total, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td style="background-color: #eee; font-weight: bold;">TOTAL DÉJÀ RÉGLÉ</td>
                <td class="text-right font-bold" style="color: green;">
                    {{ number_format($vente->montant_total - $vente->montant_restant, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td class="balance-label">RESTE À PAYER (SOLDE)</td>
                <td class="balance-value" style="color: red;">
                    {{ number_format($vente->montant_restant, 0, ',', ' ') }}
                    <small style="font-size: 8pt;">{{ $boutique->devise }}</small>
                </td>
            </tr>
        </table>
    </div>

    <div style="clear: both; margin-bottom: 0px;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 45%; text-align: center; vertical-align: top; height: 20px;">
                    <div style="font-size: 8pt; font-weight: bold; text-transform: uppercase;">Cachet Boutique & Date</div>
                </td>
                <td style="width: 10%; border: none;"></td>
                <td style="width: 45%; text-align: center; vertical-align: top;">
                    <div style="font-size: 8pt; font-weight: bold; text-transform: uppercase;">Signature Client</div>
                </td>
            </tr>
        </table>
    </div>

    <div
        style="margin-top: 0px; text-align: center; font-size: 8pt; color: #666; font-style: italic; border-top: 1px solid #ccc; padding-top: 10px;">
        {{ $boutique->footer_recu ?? 'Conservez ce reçu comme preuve de paiement de votre créance.' }}
    </div>
@endsection
