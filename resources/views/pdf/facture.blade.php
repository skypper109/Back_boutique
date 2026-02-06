@extends('pdf.layouts.base')

@section('title', 'Facture')

@section('styles')
    <style>
        .facture-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            /* margin-bottom: 20mm;
                    padding-bottom: 15mm; */
            border-bottom: 2px solid #0f172a;
        }

        .facture-table {
            width: 100%;
            margin-bottom: 20mm;
        }

        .facture-table thead tr {
            background-color: #f8fafc;
        }

        .facture-table th {
            padding: 8px 10px;
            font-size: 7pt;
            font-weight: 900;
            color: #94a3b8;
            text-transform: uppercase;
        }

        .facture-table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 9pt;
        }

        .total-box {
            background-color: #0f172a;
            color: white;
            padding: 15px 20px;
            border-radius: 20px;
            text-align: right;
        }

        .note-box {
            background-color: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 15px;
            padding: 12px;
            font-size: 8pt;
            color: #94a3b8;
            font-style: italic;
        }
    </style>
@endsection

@section('content')
    <div class="facture-header">
        <div style="text-align: left;">
            <h1 style="font-size: 24pt; font-weight: 700; margin-bottom: 5px;">{{ $boutique->nom ?? '-----' }}</h1>
            <p
                style="font-size: 7pt; font-weight: 700; color: #f59e0b; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 10px;">
                {{ $boutique->description_facture ?? 'HAUTE COLLECTION' }}
            </p>
            <div style="font-size: 8pt; font-weight: 700; color: #94a3b8; text-transform: uppercase;">
                <p style="margin: 2px 0;">{{ $boutique->adresse ?? '-----' }}</p>
                <p style="margin: 2px 0;">Tél: {{ $boutique->telephone ?? '-----' }}</p>
            </div>
        </div>
        <div style="text-align: right;">
            <h2 style="font-size: 24pt; font-weight: 900; margin-bottom: 5px;">FACTURE</h2>
            <div style="margin-top: 8px;">
                <p style="font-size: 12pt; font-weight: 900; margin: 2px 0;">N° {{ $vente->id }}/{{ date('Y') }}</p>
                <p style="font-size: 7pt; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin: 2px 0;">
                    {{ \Carbon\Carbon::parse($vente->date_vente)->format('d / m / Y') }}
                </p>
            </div>
        </div>
    </div>

    <div
        style="display: flex; justify-content: space-between; margin-bottom: 20mm; padding-bottom: 10mm; border-bottom: 1px solid #f1f5f9;">
        <div>
            <span style="font-size: 7pt; font-weight: 900; color: #e2e8f0; text-transform: uppercase;">Destinataire</span>
            <p style="font-size: 14pt; font-weight: 700; margin-top: 3px; text-transform: uppercase;">
                {{ $vente->client->nom ?? 'CLIENT DE PASSAGE' }}</p>
        </div>
        <div style="text-align: right;">
            <span style="font-size: 7pt; font-weight: 900; color: #e2e8f0; text-transform: uppercase;">Paiement</span>
            <p style="font-size: 9pt; font-weight: 900; color: #10b981; text-transform: uppercase; margin-top: 3px;">
                {{ $vente->type_paiement === 'credit' ? 'CRÉDIT' : 'CASH / RÉGLÉ' }}
            </p>
        </div>
    </div>

    <table class="facture-table">
        <thead>
            <tr>
                <th style="text-align: left;">Désignation</th>
                <th style="text-align: center; width: 60px;">Qté</th>
                <th style="text-align: right; width: 80px;">Prix Unit.</th>
                <th style="text-align: right; width: 100px;">Montant</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($vente->detailVentes as $detail)
                <tr>
                    <td>
                        <p style="font-weight: 700; margin: 0;">{{ $detail->produit->nom }}</p>
                        <p style="font-size: 7pt; color: #94a3b8; font-style: italic; margin: 2px 0;">Premium Selection</p>
                    </td>
                    <td style="text-align: center; font-weight: 700;">{{ $detail->quantite }}</td>
                    <td style="text-align: right;">{{ number_format($detail->prix_unitaire, 0, ',', ' ') }}</td>
                    <td style="text-align: right; font-weight: 900;">
                        {{ number_format($detail->montant_total, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30mm;">
        <div style="flex: 1; margin-right: 30px;">
            <div class="note-box">
                {{ $boutique->footer_facture ?? 'Note: Aucun échange ne sera accepté sans ce ticket de caisse original. Les articles ne sont ni repris ni remboursés après 48h.' }}
            </div>
        </div>
        <div style="width: 200px;">
            <div
                style="display: flex; justify-content: space-between; font-size: 8pt; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 5px; padding: 0 8px;">
                <span>Sous-Total</span>
                <span style="color: #0f172a;">{{ number_format($vente->montant_total, 0, ',', ' ') }}</span>
            </div>
            @if ($vente->montant_remis > 0)
                <div
                    style="display: flex; justify-content: space-between; font-size: 8pt; font-weight: 900; color: #10b981; text-transform: uppercase; margin-bottom: 8px; padding: 0 8px 8px; border-bottom: 1px solid #f1f5f9;">
                    <span>Remise</span>
                    <span>- {{ number_format($vente->montant_remis, 0, ',', ' ') }}</span>
                </div>
            @endif
            <div class="total-box">
                <span
                    style="display: block; font-size: 7pt; font-weight: 900; color: #f59e0b; text-transform: uppercase; margin-bottom: 3px;">NET
                    A PAYER</span>
                <p style="font-size: 24pt; font-weight: 900; margin: 0;">
                    {{ number_format($vente->montant_total - ($vente->montant_remis ?? 0), 0, ',', ' ') }}
                    <span style="font-size: 9pt; opacity: 0.4;">{{ $boutique->devise ?? 'CFA' }}</span>
                </p>
            </div>
        </div>
    </div>

    <p
        style="text-align: center; font-size: 6pt; color: #e2e8f0; text-transform: uppercase; letter-spacing: 0.3em; margin-top: 20mm;">
        DOCUMENT OFFICIEL DE GESTION - MERCI DE VOTRE CONFIANCE
    </p>
@endsection
