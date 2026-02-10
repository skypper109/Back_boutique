@extends('pdf.layouts.base')

@section('title', 'Reçu de Paiement')

@section('styles')
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1a1a1a;
            line-height: 1.6;
        }

        .receipt-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #16a085;
        }

        .company-name {
            font-size: 22pt;
            font-weight: 700;
            color: #16a085;
            margin: 0 0 5px 0;
            letter-spacing: -0.5px;
        }

        .company-tagline {
            font-size: 9pt;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin: 0 0 8px 0;
        }

        .company-details {
            font-size: 8pt;
            color: #7f8c8d;
            line-height: 1.8;
        }

        .receipt-title {
            font-size: 28pt;
            font-weight: 700;
            color: #16a085;
            margin: 0;
            letter-spacing: -1px;
        }

        .receipt-meta {
            margin-top: 8px;
        }

        .receipt-number {
            font-size: 12pt;
            font-weight: 700;
            color: #34495e;
            margin: 3px 0;
        }

        .receipt-date {
            font-size: 8pt;
            color: #7f8c8d;
            text-transform: uppercase;
        }

        .customer-box {
            background-color: #ecf8f6;
            border-left: 4px solid #16a085;
            padding: 15px 20px;
            margin: 25px 0;
            border-radius: 4px;
        }

        .customer-label {
            font-size: 8pt;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .customer-name {
            font-size: 14pt;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .customer-contact {
            font-size: 9pt;
            color: #7f8c8d;
            margin-top: 3px;
        }

        .section-title {
            font-size: 10pt;
            font-weight: 700;
            color: #34495e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 25px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #ecf0f1;
        }

        .payments-table {
            width: 100%;
            margin: 15px 0;
            border-collapse: collapse;
        }

        .payments-table tr {
            border-bottom: 1px solid #ecf0f1;
        }

        .payments-table td {
            padding: 12px 10px;
            font-size: 10pt;
        }

        .payment-date {
            color: #7f8c8d;
            font-size: 9pt;
        }

        .payment-method {
            display: inline-block;
            background-color: #d5f4e6;
            color: #0e6655;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 8pt;
            font-weight: 600;
            margin-left: 8px;
        }

        .payment-amount {
            text-align: right;
            font-weight: 700;
            color: #16a085;
            font-size: 11pt;
        }

        .no-payments {
            text-align: center;
            padding: 30px 20px;
            color: #95a5a6;
            font-style: italic;
            font-size: 10pt;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .summary-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #ecf0f1;
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

        .paid-row {
            color: #27ae60;
            border-bottom: 1px solid #ecf0f1;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }

        .balance-row {
            background-color: #16a085;
            color: white;
            padding: 18px 20px;
            border-radius: 8px;
            margin-top: 12px;
        }

        .balance-label {
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .balance-amount {
            font-size: 24pt;
            font-weight: 700;
            margin: 0;
        }

        .currency {
            font-size: 11pt;
            opacity: 0.7;
            margin-left: 5px;
        }

        .notes-section {
            background-color: #f8f9fa;
            border-left: 4px solid #16a085;
            padding: 12px 18px;
            margin: 25px 0;
            font-size: 9pt;
            color: #7f8c8d;
            line-height: 1.8;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 25px;
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
            margin-bottom: 50px;
        }

        .footer-text {
            text-align: center;
            font-size: 8pt;
            color: #bdc3c7;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 25px;
        }

        .text-right {
            text-align: right;
        }
    </style>
@endsection

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: flex-start;" class="receipt-header">
        <div>
            <h1 class="company-name">{{ $boutique->nom ?? 'Nom de votre Boutique' }}</h1>
            <p class="company-tagline">{{ $boutique->description_recu ?? 'Reçu de Versement / Crédit' }}</p>
            <div class="company-details">
                <div>{{ $boutique->adresse ?? 'Adresse de la Boutique' }}</div>
                <div>Tél: {{ $boutique->telephone ?? '+000 00 00 00 00' }}</div>
                @if ($boutique->email)
                    <div>Email: {{ $boutique->email }}</div>
                @endif
            </div>
        </div>
        <div style="text-align: right;">
            <h2 class="receipt-title">REÇU</h2>
            <div class="receipt-meta">
                <div class="receipt-number">Vente N° {{ str_pad($vente->id, 6, '0', STR_PAD_LEFT) }}</div>
                <div class="receipt-date">Le {{ \Carbon\Carbon::parse($vente->date_vente)->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    <div class="customer-box">
        <div class="customer-label">Reçu de</div>
        <div class="customer-name">{{ $vente->client->nom ?? 'Client de Passage' }}</div>
        @if ($vente->client && $vente->client->telephone)
            <div class="customer-contact">{{ $vente->client->telephone }}</div>
        @endif
    </div>

    <h3 class="section-title">Historique des Versements</h3>

    @if ($paiements && count($paiements) > 0)
        <table class="payments-table">
            @foreach ($paiements as $paiement)
                <tr>
                    <td style="width: 60%;">
                        <span class="payment-date">Le
                            {{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}</span>
                        <span class="payment-method">{{ strtoupper($paiement->mode_paiement) }}</span>
                    </td>
                    <td class="payment-amount">
                        {{ number_format($paiement->montant, 0, ',', ' ') }} {{ $boutique->devise ?? 'CFA' }}
                    </td>
                </tr>
            @endforeach
        </table>
    @else
        <div class="no-payments">
            Aucun versement enregistré pour le moment
        </div>
    @endif

    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 30px;">
        <div style="flex: 1; margin-right: 30px;">
            <div class="notes-section">
                <strong style="display: block; margin-bottom: 8px; color: #2c3e50;">Conditions de Règlement</strong>
                {{ $boutique->footer_recu ?? 'Merci pour votre versement. Ce reçu fait foi de paiement partiel ou total de votre dette. Veuillez le conserver précieusement.' }}
            </div>
        </div>
        <div style="width: 260px;">
            <div class="summary-section">
                <div class="summary-row">
                    <span class="summary-label">Montant Total</span>
                    <span class="summary-value">{{ number_format($vente->montant_total, 0, ',', ' ') }}</span>
                </div>
                <div class="summary-row paid-row">
                    <span class="summary-label">Total Déjà Réglé</span>
                    <span class="summary-value">-
                        {{ number_format($vente->montant_total - $vente->montant_restant, 0, ',', ' ') }}</span>
                </div>
                <div class="balance-row">
                    <div class="balance-label">Reste à Payer</div>
                    <div class="balance-amount">
                        {{ number_format($vente->montant_restant, 0, ',', ' ') }}
                        <span class="currency">{{ $boutique->devise ?? 'CFA' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-label">Le Responsable</div>
        </div>
        <div class="signature-box">
            <div class="signature-label">Accusé de Réception Client</div>
        </div>
    </div>

    <div class="footer-text">
        Reçu de Paiement Officiel • {{ strtoupper($boutique->nom ?? 'Ma Boutique') }}
    </div>
@endsection
