# Ma Boutique - Ensemble, plus loin 🚀

**Ma Boutique** est une solution backend robuste et évolutive conçue pour la gestion complète de boutiques et de réseaux de commerces. Développée avec Laravel, elle offre une API puissante pour centraliser les opérations commerciales, de la vente à la comptabilité analytique.

## 🌟 Concept

L'application est pensée pour répondre aux défis des commerçants modernes :

- **Centralisation** : Gérez plusieurs boutiques depuis une interface unique.
- **Transparence** : Suivi en temps réel des stocks, des ventes et des flux financiers.
- **Accessibilité** : Automatisation des rapports et notifications pour rester informé, où que vous soyez.

## ✨ Fonctionnalités Clés

### 📦 Gestion des Stocks & Produits

- **Inventaire Dynamique** : Suivi précis des quantités disponibles par boutique.
- **Réapprovisionnements** : Historique des entrées en stock et alertes de rupture.
- **Import/Export** : Gestion simplifiée des catalogues via CSV.

### 💰 Ventes & Facturation

- **Ventes Omnicanales** : Gestion des ventes directes et proformas.
- **Facturation PDF** : Génération automatique de factures, bordereaux et devis proforma professionnels.
- **Historique Détaillé** : Consultation des ventes passées avec filtres avancés (jour, mois, année).

### 💳 Crédits & Paiements Clients

- **Suivi des Dettes** : Gestion rigoureuse des ventes à crédit.
- **Paiements Partiels** : Enregistrement des paiements et mise à jour automatique du solde client.
- **Relevés de Compte** : Génération d'historiques de paiements pour chaque client.

### Analyse & Reporting

- **Tableau de Bord** : Indicateurs de performance (CA, marge, top ventes).
- **Dépenses & Frais** : Suivi des charges opérationnelles pour calculer la rentabilité réelle.
- **Rapports Quotidiens (Daily Reports)** : Génération et envoi automatique de rapports de fin de journée via **WhatsApp** et **Email**.

## 🛠 Stack Technique

- **Framework** : Laravel 10 (PHP 8.2+)
- **Base de données** : MySQL
- **Authentification** : Laravel Sanctum
- **Génération PDF** : Laravel DomPDF
- **Intégrations** : API WhatsApp pour les notifications.

## Installation Rapide

1. **Cloner le projet**

   ```bash
   git clone https://github.com/skypper109/Back_boutique.git
   cd Back_boutique
   ```

2. **Installer les dépendances**

   ```bash
   composer install
   ```

3. **Configuration**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   *Note : Configurez votre base de données dans le fichier `.env`.*

4. **Migrations & Seeders**

   ```bash
   php artisan migrate --seed
   ```

5. **Lancer le serveur**

   ```bash
   php artisan serve
   ```

---
© 2026 MalCom - Tous droits réservés.
