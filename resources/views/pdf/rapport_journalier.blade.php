@extends('pdf.layouts.base')

@section('title', 'Rapport de Clôture & Caisse Journalière')

@section('fixed_footer')
    <div class="doc-footer-fixed">
        <div><strong>{{ $boutique->nom ?? 'MalCom' }}</strong> • Rapport de caisse officiel généré par MalCom Cloud v2.0</div>
    </div>
@endsection

@section('content')
    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 58%;">
                <div style="font-size: 15pt; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">
                    {{ $boutique->nom ?? 'MALCOM COMMERCE' }}
                </div>
                <div style="font-size: 7.5pt; color: #d97706; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-top: 2px;">
                    Clôture de Caisse & Audit Financier Journalier
                </div>
                <div style="font-size: 8pt; color: #475569; margin-top: 4px;">
                    {{ $boutique->adresse ?? '---' }} | Tél : {{ $boutique->telephone ?? '---' }}
                </div>
            </td>
            <td style="width: 42%; text-align: right;">
                <div class="doc-title-badge">
                    RAPPORT DE CAISSE
                </div>
                <table style="width: 100%; border: none; font-size: 8pt; margin-top: 2px;">
                    <tr>
                        <td style="text-align: right; color: #64748b; padding: 1px 4px;">Journée comptable :</td>
                        <td style="text-align: right; font-weight: 900; color: #0f172a;">
                            {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: right; color: #64748b; padding: 1px 4px;">Édition du rapport :</td>
                        <td style="text-align: right; font-weight: bold; color: #475569;">{{ date('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Executive KPI Row -->
    <table style="width: 100%; border-collapse: separate; border-spacing: 6px 0; margin-bottom: 12px;">
        <tr>
            <td style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px 8px; width: 25%;">
                <div style="font-size: 6pt; font-weight: bold; color: #64748b; text-transform: uppercase;">VENTES TOTALES</div>
                <div style="font-size: 11pt; font-weight: 900; color: #0f172a; margin-top: 2px;">
                    {{ number_format($totaux['ventes_net'], 0, ',', ' ') }} <span style="font-size: 6.5pt; font-weight: normal;">{{ $boutique->devise ?? 'FCFA' }}</span>
                </div>
            </td>
            <td style="background-color: #fff1f2; border: 1px solid #fecdd3; border-radius: 4px; padding: 6px 8px; width: 25%;">
                <div style="font-size: 6pt; font-weight: bold; color: #9f1239; text-transform: uppercase;">DÉPENSES DU JOUR</div>
                <div style="font-size: 11pt; font-weight: 900; color: #be123c; margin-top: 2px;">
                    {{ number_format($totaux['depenses'], 0, ',', ' ') }} <span style="font-size: 6.5pt; font-weight: normal;">{{ $boutique->devise ?? 'FCFA' }}</span>
                </div>
            </td>
            <td style="background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 4px; padding: 6px 8px; width: 25%;">
                <div style="font-size: 6pt; font-weight: bold; color: #065f46; text-transform: uppercase;">SOLDE NET OPÉRATIONNEL</div>
                <div style="font-size: 11pt; font-weight: 900; color: #047857; margin-top: 2px;">
                    {{ number_format($totaux['benefice_net'], 0, ',', ' ') }} <span style="font-size: 6.5pt; font-weight: normal;">{{ $boutique->devise ?? 'FCFA' }}</span>
                </div>
            </td>
            <td style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px; padding: 6px 8px; width: 25%;">
                <div style="font-size: 6pt; font-weight: bold; color: #1e40af; text-transform: uppercase;">VOLUME TRANSACTIONS</div>
                <div style="font-size: 11pt; font-weight: 900; color: #1e40af; margin-top: 2px;">
                    {{ $stats['ventes_cash'] + $stats['ventes_credit'] }} <span style="font-size: 6.5pt; font-weight: normal;">ventes</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Section I: Ventes du Jour -->
    <div style="font-size: 8pt; font-weight: 900; color: #0f172a; text-transform: uppercase; margin-top: 8px; margin-bottom: 3px;">
        I. Registre des Ventes Effectuées
    </div>
    <table class="table-data" style="margin-top: 2px; margin-bottom: 10px;">
        <thead>
            <tr>
                <th style="width: 7%; text-align: center;">ID</th>
                <th style="width: 22%; text-align: left;">CLIENT</th>
                <th style="width: 10%; text-align: center;">PAIEMENT</th>
                <th style="width: 33%; text-align: left;">ARTICLES VENDUS</th>
                <th style="width: 8%; text-align: center;">QTÉ</th>
                <th style="width: 10%; text-align: right;">REMISE</th>
                <th style="width: 10%; text-align: right;">NET ENCAISSÉ</th>
            </tr>
        </thead>
        <tbody>
            @if ($stats['ventes_cash'] > 0)
                @foreach ($ventes as $v)
                    <tr>
                        <td style="text-align: center; font-size: 7.5pt; color: #475569;">#{{ $v->id }}</td>
                        <td>
                            <strong style="color: #0f172a;">{{ $v->client->nom == 'ANONYME' ? 'Client de passage' : $v->client->nom }}</strong>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge badge-success">COMPTANT</span>
                        </td>
                        <td style="font-size: 7pt; color: #334155;">
                            @foreach ($v->detailVentes as $dv)
                                <div>• {{ $dv->produit->nom ?? 'Article' }} (x{{ $dv->quantite }})</div>
                            @endforeach
                        </td>
                        <td style="text-align: center; font-weight: bold; color: #0f172a;">{{ $v->detailVentes->sum('quantite') }}</td>
                        <td style="text-align: right; color: {{ ($v->remise ?? 0) > 0 ? '#e11d48' : '#94a3b8' }};">
                            {{ number_format($v->remise ?? 0, 0, ',', ' ') }}
                        </td>
                        <td style="text-align: right; font-weight: bold; color: #0f172a;">
                            {{ number_format($v->montant_total, 0, ',', ' ') }}
                        </td>
                    </tr>
                @endforeach
            @endif

            @if ($stats['ventes_credit'] > 0)
                @foreach ($ventes_credit as $vc)
                    <tr>
                        <td style="text-align: center; font-size: 7.5pt; color: #475569;">#{{ $vc->id }}</td>
                        <td>
                            <strong style="color: #0f172a;">{{ $vc->client->nom == 'ANONYME' ? 'Client de passage' : $vc->client->nom }}</strong>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge badge-warning">CRÉDIT</span>
                        </td>
                        <td style="font-size: 7pt; color: #334155;">
                            @foreach ($vc->detailVentes as $dvc)
                                <div>• {{ $dvc->produit->nom ?? 'Article' }} (x{{ $dvc->quantite }})</div>
                            @endforeach
                        </td>
                        <td style="text-align: center; font-weight: bold; color: #0f172a;">{{ $vc->detailVentes->sum('quantite') }}</td>
                        <td style="text-align: right; color: {{ ($vc->remise ?? 0) > 0 ? '#e11d48' : '#94a3b8' }};">
                            {{ number_format($vc->remise ?? 0, 0, ',', ' ') }}
                        </td>
                        <td style="text-align: right; font-weight: bold; color: #d97706;">
                            {{ number_format($vc->montant_total, 0, ',', ' ') }}
                        </td>
                    </tr>
                @endforeach
            @endif

            @if ($stats['ventes_cash'] == 0 && $stats['ventes_credit'] == 0)
                <tr>
                    <td colspan="7" style="text-align: center; padding: 12px; color: #94a3b8; font-style: italic;">
                        Aucune vente enregistrée sur cette date.
                    </td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9;">
                <td colspan="6" style="text-align: right; font-weight: bold; color: #0f172a; padding: 6px 8px;">
                    TOTAL CHIFFRE D'AFFAIRES DU JOUR :
                </td>
                <td style="text-align: right; font-weight: 900; color: #0f172a; padding: 6px 8px; font-size: 9.5pt;">
                    {{ number_format($totaux['ventes_net'], 0, ',', ' ') }} {{ $boutique->devise ?? 'FCFA' }}
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Section II: Dépenses & Décaissements -->
    <div style="font-size: 8pt; font-weight: 900; color: #0f172a; text-transform: uppercase; margin-top: 8px; margin-bottom: 3px;">
        II. Dépenses d'Exploitation & Décaissements
    </div>
    <table class="table-data" style="margin-top: 2px; margin-bottom: 10px;">
        <thead>
            <tr>
                <th style="width: 12%; text-align: center;">HEURE</th>
                <th style="width: 28%; text-align: left;">CATÉGORIE</th>
                <th style="width: 42%; text-align: left;">MOTIF / JUSTIFICATIF</th>
                <th style="width: 18%; text-align: right;">MONTANT ({{ $boutique->devise ?? 'FCFA' }})</th>
            </tr>
        </thead>
        <tbody>
            @if (count($depenses) > 0)
                @foreach ($depenses as $depense)
                    <tr>
                        <td style="text-align: center; font-size: 7.5pt; color: #475569;">
                            {{ \Carbon\Carbon::parse($depense->created_at)->format('H:i') }}
                        </td>
                        <td>
                            <strong style="color: #0f172a; text-transform: uppercase;">{{ $depense->type ?? 'DIVERS' }}</strong>
                        </td>
                        <td style="font-size: 7pt; color: #475569;">{{ $depense->description }}</td>
                        <td style="text-align: right; font-weight: bold; color: #be123c;">
                            - {{ number_format($depense->montant, 0, ',', ' ') }}
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" style="text-align: center; padding: 10px; color: #94a3b8; font-style: italic;">
                        Aucune dépense enregistrée sur cette date.
                    </td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9;">
                <td colspan="3" style="text-align: right; font-weight: bold; color: #be123c; padding: 6px 8px;">
                    TOTAL DÉPENSES OPÉRATIONNELLES :
                </td>
                <td style="text-align: right; font-weight: 900; color: #be123c; padding: 6px 8px; font-size: 9.5pt;">
                    - {{ number_format($totaux['depenses'], 0, ',', ' ') }} {{ $boutique->devise ?? 'FCFA' }}
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Dual Signatures Block (Pinned to bottom of the last page) -->
    <div class="signatures-pinned-bottom">
        <table class="signatures-table">
            <tr>
                <td>
                    <div class="signature-title">Visa du Caissier / Responsable de Magasin</div>
                    <div class="signature-line"></div>
                </td>
                <td>
                    <div class="signature-title">Visa de la Direction Générale / Contrôle Interne</div>
                    <div class="signature-line"></div>
                </td>
            </tr>
        </table>
    </div>
@endsection
