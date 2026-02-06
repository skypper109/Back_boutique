@extends('pdf.layouts.base')

@section('title', 'Reçu de Crédit')

@section('styles')
    <style>
        .recu-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15mm;
            padding-bottom: 10mm;
            border-bottom: 2px solid #0f172a;
        }

        .payments-table {
            width: 100%;
            margin: 10mm 0;
            border-collapse: collapse;
        }

        .payments-table tr {
            border-bottom: 1px solid #f1f5f9;
        }

        .payments-table td {
            padding: 6px 8px;
            font-size: 8pt;
        }
    </style>
@endsection

@section('content')
    <div class="recu-header">
        <div>
            <h1 style="font-size: 20pt; font-weight: 700; margin-bottom: 5px;">{{ $boutique->nom ?? '-----' }}</h1>
            <p
                style="font-size: 7pt; font-weight: 700; color: #f59e0b; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 8px;">
                {{ $boutique->description_recu ?? 'REÇU DE PAIEMENT' }}
            </p>
            <div style="font-size: 7pt; font-weight: 700; color: #94a3b8; text-transform: uppercase;">
                <p style="margin: 2px 0;">{{ $boutique->adresse ?? '-----' }}</p>
                <p style="margin: 2px 0;">Tél: {{ $boutique->telephone ?? '-----' }}</p>
            </div>
        </div>
        <div style="text-align: right;">
            <h2 style="font-size: 20pt; font-weight: 900; margin-bottom: 5px;">REÇU</h2>
            <div style="margin-top: 6px;">
                <p style="font-size: 10pt; font-weight: 900; margin: 2px 0;">Vente N° {{ $vente->id }}</p>
                <p style="font-size: 7pt; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin: 2px 0;">
                    {{ \Carbon\Carbon::parse($vente->date_vente)->format('d/m/Y') }}
                </p>
            </div>
        </div>
    </div>

    <div style="background-color: #f8fafc; padding: 12px; border-radius: 10px; margin-bottom: 10mm;">
        <p style="font-size: 7pt; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 5px;">Client
        </p>
        <p style="font-size: 12pt; font-weight: 700; margin: 0;">{{ $vente->client->nom ?? 'CLIENT DE PASSAGE' }}</p>
        @if ($vente->client && $vente->client->telephone)
            <p style="font-size: 8pt; color: #64748b; margin: 2px 0;">{{ $vente->client->telephone }}</p>
        @endif
    </div>

    <h3
        style="font-size: 8pt; font-weight: 900; color: #cbd5e1; text-transform: uppercase; margin-bottom: 8px; text-decoration: underline;">
        Relevé des versements
    </h3>

    @if ($paiements && count($paiements) > 0)
        <table class="payments-table">
            @foreach ($paiements as $paiement)
                <tr>
                    <td style="color: #64748b; font-style: italic;">
                        {{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/y') }} -
                        {{ $paiement->mode_paiement }}
                    </td>
                    <td style="text-align: right; font-weight: 900;">
                        {{ number_format($paiement->montant, 0, ',', ' ') }} {{ $boutique->devise ?? 'CFA' }}
                    </td>
                </tr>
            @endforeach
        </table>
    @else
        <p style="font-size: 8pt; color: #cbd5e1; font-style: italic; text-align: center; padding: 20px 0;">
            En attente de versement
        </p>
    @endif

    <div style="margin-top: 15mm; padding-top: 10mm; border-top: 1px solid #f1f5f9;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 5mm;">
            <div style="flex: 1;">
                <div style="background-color: #f8fafc; padding: 10px; border-radius: 10px; font-size: 8pt;">
                    {{ $boutique->footer_recu ?? 'Merci pour votre confiance. Ce reçu fait foi de paiement.' }}
                </div>
            </div>
            <div style="width: 180px; margin-left: 20px;">
                <div
                    style="display: flex; justify-content: space-between; font-size: 8pt; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 5px; padding: 0 8px;">
                    <span>Total Général</span>
                    <span style="color: #0f172a;">{{ number_format($vente->montant_total, 0, ',', ' ') }}</span>
                </div>
                <div
                    style="display: flex; justify-content: space-between; font-size: 8pt; font-weight: 900; color: #10b981; text-transform: uppercase; margin-bottom: 8px; padding: 0 8px 8px; border-bottom: 1px solid #f1f5f9;">
                    <span>Déjà Réglé</span>
                    <span>- {{ number_format($vente->montant_total - $vente->montant_restant, 0, ',', ' ') }}</span>
                </div>
                <div
                    style="background-color: #0f172a; color: white; padding: 12px 15px; border-radius: 15px; text-align: right;">
                    <span
                        style="display: block; font-size: 7pt; font-weight: 900; color: #f59e0b; text-transform: uppercase; margin-bottom: 3px;">RESTE
                        A PAYER</span>
                    <p style="font-size: 20pt; font-weight: 900; margin: 0;">
                        {{ number_format($vente->montant_restant, 0, ',', ' ') }}
                        <span style="font-size: 8pt; opacity: 0.4;">{{ $boutique->devise ?? 'CFA' }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div
        style="display: flex; justify-content: space-between; padding-top: 15mm; border-top: 1px dashed #e2e8f0; margin-top: 10mm;">
        <div style="text-align: center; flex: 1;">
            <p style="font-size: 8pt; font-weight: 900; text-transform: uppercase; color: #cbd5e1; margin-bottom: 50px;">Le
                Responsable</p>
        </div>
        <div style="text-align: center; flex: 1;">
            <p style="font-size: 8pt; font-weight: 900; text-transform: uppercase; color: #cbd5e1; margin-bottom: 50px;">Le
                Client (Bon pour accord)</p>
        </div>
    </div>

    <p
        style="text-align: center; font-size: 6pt; color: #e2e8f0; text-transform: uppercase; letter-spacing: 0.3em; margin-top: 10mm;">
        DOCUMENT OFFICIEL - {{ strtoupper($boutique->nom ?? 'MA BOUTIQUE') }}
    </p>
@endsection
