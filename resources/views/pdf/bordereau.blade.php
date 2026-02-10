@extends('pdf.layouts.base')

@section('title', 'Bordereau de Livraison')

@section('styles')
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1a1a1a;
            line-height: 1.6;
        }

        .bordereau-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #2c3e50;
        }

        .company-name {
            font-size: 26pt;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 5px 0;
            letter-spacing: -0.5px;
        }

        .company-tagline {
            font-size: 9pt;
            color: #e67e22;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin: 0 0 10px 0;
            font-weight: 600;
        }

        .company-details {
            font-size: 9pt;
            color: #7f8c8d;
            line-height: 1.8;
        }

        .document-title {
            font-size: 32pt;
            font-weight: 700;
            color: #e67e22;
            margin: 0;
            letter-spacing: -1px;
        }

        .document-meta {
            margin-top: 10px;
        }

        .document-number {
            font-size: 14pt;
            font-weight: 700;
            color: #34495e;
            margin: 5px 0;
        }

        .document-date {
            font-size: 9pt;
            color: #7f8c8d;
            text-transform: uppercase;
        }

        .client-section {
            margin: 30px 0;
            padding: 20px 0;
            border-top: 1px solid #ecf0f1;
            border-bottom: 1px solid #ecf0f1;
        }

        .section-label {
            font-size: 8pt;
            color: #95a5a6;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .client-name {
            font-size: 16pt;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .items-table {
            width: 100%;
            margin: 30px 0;
            border-collapse: collapse;
        }

        .items-table thead {
            background-color: #34495e;
            color: white;
        }

        .items-table th {
            padding: 12px 15px;
            font-size: 9pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
        }

        .items-table td {
            padding: 15px;
            border-bottom: 1px solid #ecf0f1;
            font-size: 10pt;
        }

        .item-name {
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 3px 0;
        }

        .item-code {
            font-size: 8pt;
            color: #95a5a6;
            font-style: italic;
        }

        .summary-section {
            margin-top: 40px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 15px;
            font-size: 10pt;
        }

        .summary-label {
            color: #7f8c8d;
            font-weight: 600;
        }

        .summary-value {
            color: #2c3e50;
            font-weight: 700;
        }

        .payment-row {
            color: #27ae60;
            border-bottom: 1px solid #ecf0f1;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }

        .total-row {
            background-color: #e67e22;
            color: white;
            padding: 20px 25px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .total-label {
            font-size: 10pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .total-amount {
            font-size: 28pt;
            font-weight: 700;
            margin: 0;
        }

        .currency {
            font-size: 12pt;
            opacity: 0.7;
            margin-left: 5px;
        }

        .notes-section {
            background-color: #fef5e7;
            border-left: 4px solid #e67e22;
            padding: 15px 20px;
            margin: 30px 0;
            font-size: 9pt;
            color: #7f8c8d;
            line-height: 1.8;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            padding-top: 30px;
            border-top: 2px dashed #bdc3c7;
        }

        .signature-box {
            text-align: center;
            flex: 1;
        }

        .signature-label {
            font-size: 9pt;
            color: #95a5a6;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 60px;
        }

        .footer-text {
            text-align: center;
            font-size: 8pt;
            color: #bdc3c7;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 30px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: 700;
        }
    </style>
@endsection

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: flex-start;" class="bordereau-header">
        <div>
            <h1 class="company-name">{{ $boutique->nom ?? 'Nom de votre Boutique' }}</h1>
            <p class="company-tagline">{{ $boutique->description_bordereau ?? 'Bordereau Commercial de Livraison' }}</p>
            <div class="company-details">
                <div>{{ $boutique->adresse ?? 'Adresse de la Boutique' }}</div>
                <div>Tél: {{ $boutique->telephone ?? '+000 00 00 00 00' }}</div>
                @if ($boutique->email)
                    <div>Email: {{ $boutique->email }}</div>
                @endif
            </div>
        </div>
        <div style="text-align: right;">
            <h2 class="document-title">
                {{ $vente->type_paiement === 'proforma' ? 'PROFORMA' : 'BORDEREAU' }}
            </h2>
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
