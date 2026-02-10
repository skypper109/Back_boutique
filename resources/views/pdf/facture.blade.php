@extends('pdf.layouts.base')

@section('title', 'Facture')

@section('styles')
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1a1a1a;
            line-height: 1.6;
        }

        .invoice-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #2c3e50;
        }

        .company-info {
            margin-bottom: 15px;
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
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin: 0 0 10px 0;
        }

        .company-details {
            font-size: 9pt;
            color: #7f8c8d;
            line-height: 1.8;
        }

        .invoice-title {
            font-size: 32pt;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
            letter-spacing: -1px;
        }

        .invoice-meta {
            margin-top: 10px;
        }

        .invoice-number {
            font-size: 14pt;
            font-weight: 700;
            color: #34495e;
            margin: 5px 0;
        }

        .invoice-date {
            font-size: 9pt;
            color: #7f8c8d;
            text-transform: uppercase;
        }

        .billing-section {
            margin: 30px 0;
            padding: 20px 0;
            border-top: 1px solid #ecf0f1;
            border-bottom: 1px solid #ecf0f1;
        }

        .billing-label {
            font-size: 8pt;
            color: #95a5a6;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .billing-name {
            font-size: 16pt;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .payment-status {
            font-size: 10pt;
            font-weight: 700;
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 5px;
        }

        .payment-cash {
            background-color: #d4edda;
            color: #155724;
        }

        .payment-credit {
            background-color: #fff3cd;
            color: #856404;
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

        .items-table tbody tr:hover {
            background-color: #f8f9fa;
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

        .discount-row {
            color: #27ae60;
            border-bottom: 1px solid #ecf0f1;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }

        .total-row {
            background-color: #2c3e50;
            color: white;
            padding: 20px 25px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .total-label {
            font-size: 10pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
            margin-bottom: 5px;
        }

        .total-amount {
            font-size: 28pt;
            font-weight: 700;
            margin: 0;
        }

        .currency {
            font-size: 12pt;
            opacity: 0.6;
            margin-left: 5px;
        }

        .notes-section {
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
            padding: 15px 20px;
            margin: 30px 0;
            font-size: 9pt;
            color: #7f8c8d;
            line-height: 1.8;
        }

        .footer-text {
            text-align: center;
            font-size: 8pt;
            color: #bdc3c7;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
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
    <div style="display: flex; justify-content: space-between; align-items: flex-start;" class="invoice-header">
        <div class="company-info">
            <h1 class="company-name">{{ $boutique->nom ?? 'Nom de votre Boutique' }}</h1>
            <p class="company-tagline">{{ $boutique->description_facture ?? 'Qualité Supérieure & Service Premium' }}</p>
            <div class="company-details">
                <div>{{ $boutique->adresse ?? 'Adresse de la Boutique' }}</div>
                <div>Tél: {{ $boutique->telephone ?? '+000 00 00 00 00' }}</div>
                @if ($boutique->email)
                    <div>Email: {{ $boutique->email }}</div>
                @endif
            </div>
        </div>
        <div style="text-align: right;">
            <h2 class="invoice-title">FACTURE</h2>
            <div class="invoice-meta">
                <div class="invoice-number">#{{ str_pad($vente->id, 6, '0', STR_PAD_LEFT) }}</div>
                <div class="invoice-date">Le {{ \Carbon\Carbon::parse($vente->date_vente)->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: space-between;" class="billing-section">
        <div style="flex: 1;">
            <div class="billing-label">Facturé à</div>
            <div class="billing-name">{{ $vente->client->nom ?? 'Client de Passage' }}</div>
            @if ($vente->client && $vente->client->telephone)
                <div style="font-size: 9pt; color: #7f8c8d; margin-top: 3px;">{{ $vente->client->telephone }}</div>
            @endif
        </div>
        <div style="text-align: right;">
            <div class="billing-label">Statut du Paiement</div>
            <span class="payment-status {{ $vente->type_paiement === 'credit' ? 'payment-credit' : 'payment-cash' }}">
                {{ $vente->type_paiement === 'credit' ? 'EN CRÉDIT' : 'PAYÉ / RÉGLÉ' }}
            </span>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Désignation</th>
                <th style="width: 15%; text-align: center;">Quantité</th>
                <th style="width: 17.5%; text-align: right;">Prix Unitaire</th>
                <th style="width: 17.5%; text-align: right;">Montant</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($vente->detailVentes as $detail)
                <tr>
                    <td>
                        <div class="item-name">{{ $detail->produit->nom }}</div>
                        <div class="item-code">Réf: {{ str_pad($detail->produit->id, 6, '0', STR_PAD_LEFT) }}</div>
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
                <strong style="display: block; margin-bottom: 8px; color: #2c3e50;">Conditions Générales</strong>
                {{ $boutique->footer_facture ?? 'Tout échange ou retour doit être effectué dans les 48h suivant l\'achat, sur présentation de ce ticket original. Les articles soldés ne sont ni repris ni échangés.' }}
            </div>
        </div>
        <div style="width: 280px;">
            <div class="summary-section">
                <div class="summary-row">
                    <span class="summary-label">Sous-Total</span>
                    <span class="summary-value">{{ number_format($vente->montant_total, 0, ',', ' ') }}</span>
                </div>
                @if ($vente->montant_remis > 0)
                    <div class="summary-row discount-row">
                        <span class="summary-label">Remise</span>
                        <span class="summary-value">- {{ number_format($vente->montant_remis, 0, ',', ' ') }}</span>
                    </div>
                @endif
                <div class="total-row">
                    <div class="total-label">Montant Net à Payer</div>
                    <div class="total-amount">
                        {{ number_format($vente->montant_total - ($vente->montant_remis ?? 0), 0, ',', ' ') }}
                        <span class="currency">{{ $boutique->devise ?? 'CFA' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-text">
        Merci de votre confiance • {{ strtoupper($boutique->nom ?? 'Ma Boutique') }}
    </div>
@endsection
