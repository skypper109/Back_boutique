@extends('layouts.admin')

@section('content')
    <div class="dashboard-header">
        <div>
            <h1 class="page-title">Réseau de Boutiques</h1>
            <p class="text-muted">Gérez les établissements et contrôlez les accès au système.</p>
        </div>
        <div class="header-actions">
            <button onclick="document.getElementById('createModal').style.display='flex'" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i>
                <span>Nouvelle Boutique</span>
            </button>
        </div>
    </div>

    <x-admin-card title="Liste des Établissements" icon="bi bi-shop-window">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Boutique & Propriétaire</th>
                        <th style="text-align: center;">Statut</th>
                        <th style="text-align: center;">Quota</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($boutiques as $boutique)
                        <tr>
                            <td>
                                <div class="boutique-info-cell">
                                    <div class="boutique-name">{{ $boutique->nom }}</div>
                                    <div class="boutique-meta">
                                        <span><i class="bi bi-geo-alt"></i> {{ $boutique->adresse }}</span>
                                        <span class="separator">|</span>
                                        <span class="owner-pill">
                                            <i class="bi bi-person"></i>
                                            {{ $boutique->creator ? $boutique->creator->name : 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                @if ($boutique->is_active)
                                    <span class="badge badge-success">Actif</span>
                                @else
                                    <span class="badge badge-danger">Inactif</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if ($boutique->creator)
                                    @php
                                        $count = \App\Models\Boutique::where('user_id', $boutique->user_id)->count();
                                        $limit = $boutique->creator->boutique_limit;
                                        $color = $count >= $limit ? 'text-danger' : 'text-primary';
                                    @endphp
                                    <div class="quota-badge {{ $color }}">
                                        <strong>{{ $count }}</strong> / {{ $limit }}
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <div class="action-buttons">
                                    <a href="{{ route('admin.boutiques.show', $boutique->id) }}" class="action-btn"
                                        title="Dashboard">
                                        <i class="bi bi-speedometer2"></i>
                                    </a>
                                    <a href="{{ route('admin.boutiques.users', $boutique->id) }}" class="action-btn"
                                        title="Personnel">
                                        <i class="bi bi-people"></i>
                                    </a>
                                    <form action="{{ route('admin.boutiques.toggle-status', $boutique->id) }}"
                                        method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="action-btn"
                                            title="{{ $boutique->is_active ? 'Bloquer' : 'Activer' }}">
                                            <i
                                                class="bi {{ $boutique->is_active ? 'bi-lock text-danger' : 'bi-unlock text-success' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.boutiques.destroy', $boutique->id) }}" method="POST"
                                        style="display: inline;"
                                        onsubmit="return confirm('ALERTE: Cela supprimera DEFINITIVEMENT la boutique et ses données. Confirmer ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn" title="Supprimer">
                                            <i class="bi bi-trash3 text-danger"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin-card>

    <div id="createModal" class="modal">
        <div class="modal-content">
            <div class="card-title">
                <span>DÉPLOIEMENT NOUVELLE BOUTIQUE</span>
                <button onclick="document.getElementById('createModal').style.display='none'" class="text-muted"
                    style="background: none; border: none; cursor: pointer;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.boutiques.store') }}" method="POST">
                @csrf
                <div class="section-divider">Infos Boutique</div>
                <div class="form-group">
                    <label class="form-label">Nom Commercial</label>
                    <input type="text" name="nom" required class="form-control"
                        placeholder="Ex: Boutique Bamako Centre">
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Adresse</label>
                        <input type="text" name="adresse" required class="form-control" placeholder="Ville, Quartier">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" required class="form-control" placeholder="+223 ...">
                    </div>
                </div>

                <div class="section-divider">Compte Administrateur Initial</div>
                <div class="form-group">
                    <label class="form-label">Email Admin</label>
                    <input type="email" name="email_admin" required class="form-control" placeholder="admin@boutique.com">
                </div>

                <div class="form-group">
                    <label class="form-label">Mot de Passe Provisoire</label>
                    <input type="password" name="password_admin" required class="form-control" placeholder="••••••••">
                    <small class="text-muted">Ce compte aura les pleins pouvoirs sur la boutique.</small>
                </div>

                <div class="grid-2" style="margin-top: 32px;">
                    <button type="button" onclick="document.getElementById('createModal').style.display='none'"
                        class="btn" style="background: #f1f5f9; color: var(--text-main);">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Valider le Déploiement
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .boutique-info-cell {
            padding: 4px 0;
        }

        .boutique-name {
            font-weight: 800;
            font-size: 1.05rem;
            color: var(--secondary-900);
            margin-bottom: 4px;
        }

        .boutique-meta {
            font-size: 0.75rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .separator {
            color: #e2e8f0;
        }

        .owner-pill {
            background: #f1f5f9;
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 600;
            color: var(--primary);
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .quota-badge {
            font-size: 0.85rem;
            font-weight: 600;
            padding: 4px 10px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            display: inline-block;
        }

        .section-divider {
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--primary);
            letter-spacing: 0.1em;
            margin: 24px 0 12px 0;
            padding-bottom: 4px;
            border-bottom: 2px solid #eef2ff;
        }

        .action-buttons {
            display: flex;
            gap: 6px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background: white;
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .action-btn:hover {
            border-color: var(--primary);
            background: #f8fafc;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        @media (max-width: 768px) {
            .header-actions {
                width: 100%;
            }

            .header-actions .btn {
                width: 100%;
                justify-content: center;
            }

            .table-wrapper {
                overflow-x: auto;
            }
        }
    </style>
@endsection
