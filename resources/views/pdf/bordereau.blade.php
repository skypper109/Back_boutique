@extends('pdf.layouts.base')

@section('title', 'Bordereau De Livraison')

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

        .font-bold {
            font-weight: bold;
        }
    </style>
@endsection

@section('content')
    <div class="document-meta">
        <div class="document-number">N° {{ str_pad($vente->id, 6, '0', STR_PAD_LEFT) }}/{{ date('Y') }}</div>
        <div class="document-date">Le {{ \Carbon\Carbon::parse($vente->date_vente)->format('d/m/Y') }}</div>
    </div>
    </div>
    </div>

    <div style="display: flex; justify-content: space-between;" class="client-section">
        <div style="flex: 1;">
            <div class="section-label">Destinataire</div>
            <div class="client-name">{{ $vente->client->nom ?? 'Client de Passage' }}</div>
        </div>
        <div style="text-align: right;">
            <div class="section-label">Contact Client</div>
            <div style="font-size: 11pt; font-weight: 600; color: #2c3e50; margin-top: 5px;">
                {{ $vente->client->telephone ?? 'N/A' }}
            </div>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Désignation</th>
                <th style="width: 15%; text-align: center;">Qté</th>
                <th style="width: 17.5%; text-align: right;">Prix Unit.</th>
                <th style="width: 17.5%; text-align: right;">Montant</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($vente->detailVentes as $detail)
                <tr>
                    <td>
                        <div class="item-name">{{ $detail->produit->nom }}</div>
                        <div class="item-code">Code Produit: #P-{{ str_pad($detail->produit->id, 6, '0', STR_PAD_LEFT) }}
                        </div>
                    </td>
                    <td class="text-center font-bold">{{ $detail->quantite }}</td>
                    <td class="text-right">{{ number_format($detail->prix_unitaire, 0, ',', ' ') }}</td>
                    <td class="text-right font-bold">{{ number_format($detail->montant_total, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div style="flex: 1; margin-right: 40px;">
            <div class="notes-section">
                <strong style="display: block; margin-bottom: 8px; color: #2c3e50;">Note Importante</strong>
                {{ $boutique->footer_bordereau ?? 'Ce document reste la propriété de la boutique jusqu\'au règlement intégral. Tout défaut de paiement peut entraîner des poursuites judiciaires.' }}
            </div>
        </div>
        <div style="width: 280px;">
            <div class="summary-section">
                <div class="summary-row">
                    <span class="summary-label">Montant Total</span>
                    <span class="summary-value">{{ number_format($vente->montant_total, 0, ',', ' ') }}</span>
                </div>
                @if ($vente->type_paiement !== 'proforma' && $vente->montant_restant < $vente->montant_total)
                    <div class="summary-row payment-row">
                        <span class="summary-label">Montant Réglé</span>
                        <span class="summary-value">-
                            {{ number_format($vente->montant_total - $vente->montant_restant, 0, ',', ' ') }}</span>
                    </div>
                @endif
                <div class="total-row">
                    <div class="total-label">
                        {{ $vente->type_paiement === 'proforma' ? 'Somme à Payer' : 'Reste à Payer' }}
                    </div>
                    <div class="total-amount">
                        {{ number_format($vente->montant_restant, 0, ',', ' ') }}
                        <span class="currency">{{ $boutique->devise ?? 'CFA' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-label">Signature Autorisée</div>
        </div>
        <div class="signature-box">
            <div class="signature-label">Signature du Client (Accusé)</div>
        </div>
    </div>

    <div class="footer-text">
        Document Commercial Officiel • {{ strtoupper($boutique->nom ?? 'Ma Boutique') }}
    </div>
@endsection
