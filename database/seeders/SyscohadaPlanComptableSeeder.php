<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JournalComptable;
use App\Models\CompteComptable;
use Illuminate\Support\Facades\DB;

class SyscohadaPlanComptableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Journaux Comptables Standard
        $journaux = [
            ['code' => 'VT', 'libelle' => 'Journal des Ventes'],
            ['code' => 'AC', 'libelle' => 'Journal des Achats'],
            ['code' => 'CA', 'libelle' => 'Journal de Caisse'],
            ['code' => 'BQ', 'libelle' => 'Journal de Banque'],
            ['code' => 'OD', 'libelle' => 'Opérations Diverses'],
        ];

        foreach ($journaux as $j) {
            JournalComptable::firstOrCreate(
                ['code' => $j['code'], 'boutique_id' => null],
                ['libelle' => $j['libelle']]
            );
        }

        // 2. Plan Comptable Général SYSCOHADA Révisé
        $comptes = [
            // Classe 1 : Ressources Durables
            ['numero' => '101000', 'libelle' => 'Capital social', 'classe' => 1, 'type' => 'passif', 'sens_normal' => 'credit'],
            ['numero' => '102000', 'libelle' => 'Capital individuel / Apport personnel', 'classe' => 1, 'type' => 'passif', 'sens_normal' => 'credit'],
            ['numero' => '111000', 'libelle' => 'Réserve légale', 'classe' => 1, 'type' => 'passif', 'sens_normal' => 'credit'],
            ['numero' => '121000', 'libelle' => 'Report à nouveau créditeur', 'classe' => 1, 'type' => 'passif', 'sens_normal' => 'credit'],
            ['numero' => '131000', 'libelle' => 'Résultat net : Bénéfice', 'classe' => 1, 'type' => 'passif', 'sens_normal' => 'credit'],
            ['numero' => '139000', 'libelle' => 'Résultat net : Perte', 'classe' => 1, 'type' => 'passif', 'sens_normal' => 'debit'],
            ['numero' => '162000', 'libelle' => 'Emprunts bancaires et financiers', 'classe' => 1, 'type' => 'passif', 'sens_normal' => 'credit'],

            // Classe 2 : Actif Immobilisé
            ['numero' => '211000', 'libelle' => 'Terrains', 'classe' => 2, 'type' => 'actif', 'sens_normal' => 'debit'],
            ['numero' => '213000', 'libelle' => 'Bâtiments et locaux commerciaux', 'classe' => 2, 'type' => 'actif', 'sens_normal' => 'debit'],
            ['numero' => '241000', 'libelle' => 'Matériel et outillage d\'exploitation', 'classe' => 2, 'type' => 'actif', 'sens_normal' => 'debit'],
            ['numero' => '244000', 'libelle' => 'Matériel de transport', 'classe' => 2, 'type' => 'actif', 'sens_normal' => 'debit'],
            ['numero' => '245000', 'libelle' => 'Matériel de bureau et informatique', 'classe' => 2, 'type' => 'actif', 'sens_normal' => 'debit'],
            ['numero' => '284000', 'libelle' => 'Amortissements du matériel', 'classe' => 2, 'type' => 'actif', 'sens_normal' => 'credit'],

            // Classe 3 : Stocks
            ['numero' => '311000', 'libelle' => 'Marchandises (Stock général)', 'classe' => 3, 'type' => 'actif', 'sens_normal' => 'debit'],
            ['numero' => '391000', 'libelle' => 'Dépréciations des stocks de marchandises', 'classe' => 3, 'type' => 'actif', 'sens_normal' => 'credit'],

            // Classe 4 : Comptes de Tiers
            ['numero' => '401100', 'libelle' => 'Fournisseurs de marchandises', 'classe' => 4, 'type' => 'passif', 'sens_normal' => 'credit'],
            ['numero' => '401200', 'libelle' => 'Fournisseurs de prestations & services', 'classe' => 4, 'type' => 'passif', 'sens_normal' => 'credit'],
            ['numero' => '411100', 'libelle' => 'Clients (Créances ventes à crédit)', 'classe' => 4, 'type' => 'actif', 'sens_normal' => 'debit'],
            ['numero' => '411200', 'libelle' => 'Clients douteux ou litigieux', 'classe' => 4, 'type' => 'actif', 'sens_normal' => 'debit'],
            ['numero' => '422000', 'libelle' => 'Personnel, rémunérations et salaires dus', 'classe' => 4, 'type' => 'passif', 'sens_normal' => 'credit'],
            ['numero' => '431000', 'libelle' => 'Sécurité sociale & cotisations', 'classe' => 4, 'type' => 'passif', 'sens_normal' => 'credit'],
            ['numero' => '443000', 'libelle' => 'État, TVA facturée sur ventes', 'classe' => 4, 'type' => 'passif', 'sens_normal' => 'credit'],
            ['numero' => '444000', 'libelle' => 'État, TVA déductible sur achats', 'classe' => 4, 'type' => 'actif', 'sens_normal' => 'debit'],
            ['numero' => '445000', 'libelle' => 'État, impôts, taxes et droits fiscaux', 'classe' => 4, 'type' => 'passif', 'sens_normal' => 'credit'],

            // Classe 5 : Trésorerie
            ['numero' => '521100', 'libelle' => 'Banque (Compte courant principal)', 'classe' => 5, 'type' => 'actif', 'sens_normal' => 'debit'],
            ['numero' => '531100', 'libelle' => 'Chèques et effets à encaisser', 'classe' => 5, 'type' => 'actif', 'sens_normal' => 'debit'],
            ['numero' => '571100', 'libelle' => 'Caisse principale boutique', 'classe' => 5, 'type' => 'actif', 'sens_normal' => 'debit'],
            ['numero' => '571200', 'libelle' => 'Caisse secondaire / Menue caisse', 'classe' => 5, 'type' => 'actif', 'sens_normal' => 'debit'],
            ['numero' => '581000', 'libelle' => 'Virements internes de fonds', 'classe' => 5, 'type' => 'actif', 'sens_normal' => 'debit'],

            // Classe 6 : Charges des activités ordinaires
            ['numero' => '601100', 'libelle' => 'Achats de marchandises', 'classe' => 6, 'type' => 'charge', 'sens_normal' => 'debit'],
            ['numero' => '603100', 'libelle' => 'Variation des stocks de marchandises', 'classe' => 6, 'type' => 'charge', 'sens_normal' => 'debit'],
            ['numero' => '605100', 'libelle' => 'Électricité, eau, carburant d\'exploitation', 'classe' => 6, 'type' => 'charge', 'sens_normal' => 'debit'],
            ['numero' => '605200', 'libelle' => 'Fournitures de bureau et consommables', 'classe' => 6, 'type' => 'charge', 'sens_normal' => 'debit'],
            ['numero' => '611000', 'libelle' => 'Transports sur achats', 'classe' => 6, 'type' => 'charge', 'sens_normal' => 'debit'],
            ['numero' => '612000', 'libelle' => 'Transports sur ventes / Livraisons', 'classe' => 6, 'type' => 'charge', 'sens_normal' => 'debit'],
            ['numero' => '613000', 'libelle' => 'Transports et déplacements du personnel', 'classe' => 6, 'type' => 'charge', 'sens_normal' => 'debit'],
            ['numero' => '622000', 'libelle' => 'Locations et loyers de boutique', 'classe' => 6, 'type' => 'charge', 'sens_normal' => 'debit'],
            ['numero' => '624000', 'libelle' => 'Entretien, réparations et maintenance', 'classe' => 6, 'type' => 'charge', 'sens_normal' => 'debit'],
            ['numero' => '625000', 'libelle' => 'Primes d\'assurance', 'classe' => 6, 'type' => 'charge', 'sens_normal' => 'debit'],
            ['numero' => '627000', 'libelle' => 'Publicité, marketing et promotions', 'classe' => 6, 'type' => 'charge', 'sens_normal' => 'debit'],
            ['numero' => '628000', 'libelle' => 'Téléphone, Internet et communications', 'classe' => 6, 'type' => 'charge', 'sens_normal' => 'debit'],
            ['numero' => '631000', 'libelle' => 'Frais bancaires et commissions', 'classe' => 6, 'type' => 'charge', 'sens_normal' => 'debit'],
            ['numero' => '641000', 'libelle' => 'Impôts, taxes et patentes', 'classe' => 6, 'type' => 'charge', 'sens_normal' => 'debit'],
            ['numero' => '651000', 'libelle' => 'Pertes sur créances irrécouvrables', 'classe' => 6, 'type' => 'charge', 'sens_normal' => 'debit'],
            ['numero' => '658000', 'libelle' => 'Autres charges diverses d\'exploitation', 'classe' => 6, 'type' => 'charge', 'sens_normal' => 'debit'],
            ['numero' => '661000', 'libelle' => 'Salaires et rémunérations du personnel', 'classe' => 6, 'type' => 'charge', 'sens_normal' => 'debit'],
            ['numero' => '664000', 'libelle' => 'Charges sociales et patronales', 'classe' => 6, 'type' => 'charge', 'sens_normal' => 'debit'],
            ['numero' => '681000', 'libelle' => 'Dotations aux amortissements d\'exploitation', 'classe' => 6, 'type' => 'charge', 'sens_normal' => 'debit'],

            // Classe 7 : Produits des activités ordinaires
            ['numero' => '701100', 'libelle' => 'Ventes de marchandises', 'classe' => 7, 'type' => 'produit', 'sens_normal' => 'credit'],
            ['numero' => '706000', 'libelle' => 'Prestations de services vendues', 'classe' => 7, 'type' => 'produit', 'sens_normal' => 'credit'],
            ['numero' => '707000', 'libelle' => 'Produits accessoires et emballages', 'classe' => 7, 'type' => 'produit', 'sens_normal' => 'credit'],
            ['numero' => '711000', 'libelle' => 'Subventions et aides d\'exploitation', 'classe' => 7, 'type' => 'produit', 'sens_normal' => 'credit'],
            ['numero' => '758000', 'libelle' => 'Autres produits d\'exploitation courante', 'classe' => 7, 'type' => 'produit', 'sens_normal' => 'credit'],
            ['numero' => '771000', 'libelle' => 'Intérêts et gains financiers', 'classe' => 7, 'type' => 'produit', 'sens_normal' => 'credit'],

            // Classe 8 : Hors Activités Ordinaires (HAO)
            ['numero' => '811000', 'libelle' => 'Charges exceptionnelles et pertes HAO', 'classe' => 8, 'type' => 'charge', 'sens_normal' => 'debit'],
            ['numero' => '821000', 'libelle' => 'Produits exceptionnels et gains HAO', 'classe' => 8, 'type' => 'produit', 'sens_normal' => 'credit'],
        ];

        foreach ($comptes as $compte) {
            CompteComptable::firstOrCreate(
                ['numero' => $compte['numero'], 'boutique_id' => null],
                [
                    'libelle' => $compte['libelle'],
                    'classe' => $compte['classe'],
                    'type' => $compte['type'],
                    'sens_normal' => $compte['sens_normal'],
                    'is_system' => true,
                    'is_active' => true,
                ]
            );
        }
    }
}
