@extends('layouts.admin')

@section('content')
<div class="dashboard-header">
    <div>
        <h1 class="page-title">Aperçu Global</h1>
        <p class="text-muted">Tableau de bord de l'administrateur général - Ma Boutique Pro</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.boutiques.index') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i>
            <span>Nouvelle Boutique</span>
        </a>
    </div>
</div>

<div class="stats-grid">
    <x-admin-stat
        type="dark"
        label="Volume d'Affaires Global"
        :value="number_format($totalSystemRevenue, 0, ',', ' ')"
        unit="FCFA" />

    <x-admin-stat
        type="white"
        label="Réseau de Boutiques"
        :value="$boutiquesCount"
        unit="Boutiques"
        :footer="'Système opérationnel'" />

    <x-admin-stat
        type="gradient"
        label="Effectif Total"
        :value="$usersCount"
        unit="Membres" />
</div>

<div class="grid-layout">
    <x-admin-card title="Performance par Boutique" icon="bi bi-trophy text-accent">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Classement</th>
                        <th>Boutique</th>
                        <th>Contact</th>
                        <th style="text-align: right;">Chiffre d'Affaires</th>
                        <th style="text-align: center;">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($boutiquePerformance as $index => $boutique)
                    <tr>
                        <td style="width: 100px;">
                            <span class="badge {{ $index == 0 ? 'badge-success' : ($index < 3 ? 'badge-blue' : '') }}">
                                #{{ $index + 1 }}
                            </span>
                        </td>
                        <td>
                            <div style="font-weight: 700;">{{ $boutique->nom }}</div>
                            <div class="text-muted small">{{ $boutique->adresse }}</div>
                        </td>
                        <td>
                            <div class="small">{{ $boutique->telephone }}</div>
                        </td>
                        <td style="text-align: right;">
                            <span style="font-weight: 800; color: var(--primary);">
                                {{ number_format($boutique->revenue ?? 0, 0, ',', ' ') }}
                            </span>
                            <span class="currency" style="font-size: 0.7rem; opacity: 0.6;">FCFA</span>
                        </td>
                        <td style="text-align: center;">
                            @if($boutique->is_active)
                            <span class="badge badge-success">Actif</span>
                            @else
                            <span class="badge badge-danger">Bloqué</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin-card>
</div>

@endsection
@endsection