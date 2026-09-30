# Project Context - LEAC

## 1. Contexte du projet
LEAC (Logiciel d'évaluation des activités de la CANAM) est une application Laravel destinee a la planification, au suivi, a la validation et au reporting des activites, avec une logique par exercice, objectif, resultat, extrant, departement et indicateur.

Le projet s'inscrit dans un besoin de pilotage de la performance et de gestion budgetaire pour la CANAM, avec des workflows metier et des capacites de tracabilite/audit.

## 2. Contexte technique du depot actuel
- Framework principal: Laravel (PHP)
- Interface: Blade + JS/CSS via Vite
- Tests: PHPUnit/Pest
- Domaines metier visibles: activites, exercices, budgets/reportings, structures/departements, validations
- Workflow principal constate: brouillon -> soumis -> valide
- Elements deja presents dans le code: gestion utilisateurs, notifications, politiques d'acces, historique de validation

## 3. Cadrage de livraison
- Livraison attendue: application (logiciel) modulaire, parametree et exploitable en production
- Delai global: 90 jours calendaires
- Lieu: CANAM

## 4. Articles fonctionnels a couvrir

| No | Designation | Description detaillee de l'article |
|---|---|---|
| 1 | Automatisation de la collecte des informations en provenance de diverses sources | Collecte et integration automatisees depuis: (1) le systeme Activ-Premium, (2) l'application de gestion SimSystems, (3) les donnees fournies par les differentes OGD necessitant un traitement au niveau de la Direction. |
| 2 | Fourniture des etats clairs et des reportings parametrables | Production de reportings et etats: (a) Budget des recettes/produits (par gestion), (b) Budget de recouvrement, (c) Budget des depenses techniques (par gestion), (d) Budget des depenses de fonctionnement (par gestion), (e) Budget des depenses d'investissement (par gestion), (f) Budget de tresorerie mensualise, (g) Documents de synthese previsionnels. |
| 3 | Module de facilitation d'evaluation permanente | Mise en place d'un monitoring dynamique avec indicateurs de suivi et vues d'evaluation continue. |
| 4 | Module de fourniture des documents a transmettre a la Direction Generale | Generation automatisee/semi-automatisee des documents, rapports et livrables a transmettre a la Direction Generale. |
| 5 | Module de gestion de la documentation | Gestion numerique des documents (classement, consultation, cycle de vie, accessibilite selon droits). |
| 6 | Module de gestion des budgets des missions | Prise en charge de la procedure de gestion des missions, de l'engagement jusqu'au paiement. |
| 7 | Module d'administration et de gestion de la securite | Parametrage des droits d'acces, tracabilite, sauvegarde, optimisation des performances, demandes de creation de compte, validation des demandes, creation/activation/desactivation/suppression de comptes, affectation/revocation des droits, consultation des journaux. |
| 8 | Autres specificites de l'application (logiciel) | Les fonctionnalites ne sont pas definitives et l'application doit rester ouverte a des analyses approfondies avec ajout possible de nouveaux modules. Les modules suivants sont consideres acquis, installes et configures: gestion des audits et de la tracabilite des operations, gestion des utilisateurs, gestion des acces par interface, gestion des parametres. Les bases de donnees sont interfacees. |

## 5. Principes de conception a respecter
- Modularite: chaque article doit pouvoir evoluer sans casser les autres domaines.
- Extensibilite: architecture ouverte a l'ajout de nouveaux modules fonctionnels.
- Interoperabilite: interfaces robustes avec les systemes externes et les bases interconnectees.
- Securite et conformite: controle d'acces, journalisation, auditabilite, sauvegarde.
- Gouvernance de la donnee: qualite, tracabilite des transformations, et disponibilite pour le reporting.

## 6. Criteres de succes (haut niveau)
- Les flux de collecte multi-sources sont fiables et automatisees.
- Les etats budgetaires et reportings parametrables sont disponibles selon les besoins metier.
- Le monitoring dynamique fournit une vision continue de la performance.
- Les documents pour la Direction Generale sont generables rapidement et de maniere standardisee.
- Les fonctions d'administration/securite couvrent le cycle de vie complet des comptes et des droits.
- Le socle applicatif reste evolutif pour les modules futurs.
