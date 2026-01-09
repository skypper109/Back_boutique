@extends('layouts.admin')

@section('content')
<div class="dashboard-header">
    <div>
        <h1 class="page-title">Administrateurs Système</h1>
        <p class="text-muted">Gestion des accès de haut niveau pour l'ensemble du réseau Ma Boutique.</p>
    </div>
    <div class="header-actions">
        <div class="stat-badge">
            <span class="label">Effectif Admin</span>
            <span class="value">{{ $admins->total() }}</span>
        </div>
    </div>
</div>

<x-admin-card title="Comptes Administrateurs" icon="bi bi-shield-lock-fill">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Administrateur</th>
                    <th>Email</th>
                    <th style="text-align: center;">Privilèges</th>
                    <th style="text-align: center;">Date de création</th>
                    <th style="text-align: right;">Sécurité</th>
                </tr>
            </thead>
            <tbody>
                @foreach($admins as $admin)
                @if($admin->role == 'admin' || $admin->role == 'super_admin')
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div class="user-avatar" style="background: #eef2ff; color: var(--primary);">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <div style="font-weight: 700;">{{ $admin->name }}</div>
                        </div>
                    </td>
                    <td><span class="text-muted italic">{{ $admin->email }}</span></td>
                    <td style="text-align: center;">
                        <span class="badge badge-blue">{{ $admin->role }}</span>
                    </td>
                    <td style="text-align: center;">
                        <span class="text-muted smaller">{{ $admin->created_at->format('d/m/Y') }}</span>
                    </td>
                    <td style="text-align: right;">
                        <div class="action-buttons">
                            @if($admin->id !== Auth::id())
                            <form action="{{ route('admin.admins.destroy', $admin->id) }}" method="POST" onsubmit="return confirm('ALERTE: Suppression irréversible du compte administrateur. Confirmer ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn" title="Supprimer">
                                    <i class="bi bi-trash3 text-danger"></i>
                                </button>
                            </form>
                            @else
                            <span class="badge badge-success">MOI</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @endif

                @endforeach
            </tbody>
        </table>
    </div>
</x-admin-card>

<div style="margin-top: 24px;">
    {{ $admins->links() }}
</div>

<style>
    .stat-badge {
        background: white;
        padding: 10px 20px;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        display: flex;
        flex-direction: column;
        align-items: center;
        min-width: 120px;
    }

    .stat-badge .label {
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--text-muted);
        letter-spacing: 0.05em;
    }

    .stat-badge .value {
        font-size: 1.25rem;
        font-weight: 900;
        color: var(--primary);
    }

    .user-avatar {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }

    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 1px solid var(--border-color);
        background: white;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .action-btn:hover {
        border-color: var(--primary);
        background: #f8fafc;
        transform: translateY(-2px);
    }
</style>
@endsection