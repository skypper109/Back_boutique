@extends('layouts.admin')

@section('content')
<div class="dashboard-header">
    <div style="display: flex; align-items: center; gap: 24px;">
        <a href="{{ route('admin.boutiques.index') }}" class="action-btn" title="Retour">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="page-title">{{ $boutique->nom }}</h1>
            <div style="display: flex; gap: 10px; margin-top: 4px;">
                <span class="badge badge-blue"><i class="bi bi-geo-alt"></i> {{ $boutique->adresse }}</span>
                <span class="badge badge-success"><i class="bi bi-telephone"></i> {{ $boutique->telephone }}</span>
            </div>
        </div>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.boutiques.users', $boutique->id) }}" class="btn btn-primary">
            <i class="bi bi-people-fill"></i>
            <span>Gérer le Personnel</span>
        </a>
    </div>
</div>

<div class="stats-grid">
    <x-admin-stat
        type="dark"
        label="Performance Cumulative"
        :value="number_format($totalRevenue, 0, ',', ' ')"
        unit="FCFA" />

    <x-admin-stat
        type="white"
        label="Volume de Transactions"
        :value="number_format($salesCount, 0, ',', ' ')"
        unit="Ventes" />

    @php
    $cardStyle = !$boutique->is_active ? 'background: linear-gradient(135deg, #ef4444, #991b1b);' : '';
    @endphp
    <div class="stat-card {{ $boutique->is_active ? 'gradient' : 'dark' }}" >
        <p class="stat-label" style="opacity: 0.8;">État Opérationnel</p>
        <div class="stat-value">
            <h2 style="font-size: 1.5rem;">{{ $boutique->is_active ? 'BOUTIQUE OUVERTE' : 'ACCÈS BLOQUÉ' }}</h2>
        </div>
        <div style="margin-top: 15px;">
            <form action="{{ route('admin.boutiques.toggle-status', $boutique->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); width: 100%; justify-content: center;">
                    <i class="bi {{ $boutique->is_active ? 'bi-lock-fill' : 'bi-unlock-fill' }}"></i>
                    <span>{{ $boutique->is_active ? 'Suspendre l\'accès' : 'Rétablir l\'accès' }}</span>
                </button>
            </form>
        </div>
    </div>
</div>

<div class="grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 40px;">
    <x-admin-card title="Top Produits" icon="bi bi-star-fill text-accent">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Désignation</th>
                        <th style="text-align: center;">Vendus</th>
                        <th style="text-align: right;">Revenu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topProducts as $tp)
                    <tr>
                        <td style="font-weight: 700;">{{ $tp->produit->nom }}</td>
                        <td style="text-align: center;">
                            <span class="badge badge-blue">{{ $tp->total_qty }}</span>
                        </td>
                        <td style="text-align: right;">
                            <span style="font-weight: 800;">{{ number_format($tp->total_amount, 0, ',', ' ') }}</span>
                            <span class="currency-small">CFA</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 40px;" class="text-muted italic">Aucun produit vendu pour le moment.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin-card>

    <x-admin-card title="Flux de Ventes Récent" icon="bi bi-clock-history">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Agent</th>
                        <th>Moment</th>
                        <th style="text-align: right;">Valeur</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSales as $sale)
                    <tr>
                        <td>
                            <div style="font-weight: 600;">{{ $sale->user->name ?? 'Système' }}</div>
                        </td>
                        <td>
                            <div class="text-muted smaller">{{ $sale->created_at->diffForHumans() }}</div>
                        </td>
                        <td style="text-align: right;">
                            <div style="font-weight: 800; color: var(--primary);">{{ number_format($sale->montant_total, 0, ',', ' ') }}</div>
                            <span class="currency-small">CFA</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 40px;" class="text-muted italic">Historique de ventes vide.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin-card>
</div>

<style>
    .text-accent {
        color: var(--accent);
    }

    .currency-small {
        font-size: 0.7rem;
        font-weight: 700;
        opacity: 0.6;
        margin-left: 2px;
    }

    .italic {
        font-style: italic;
    }
</style>
@endsection