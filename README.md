# Plateforme Marketplace B2B/B2C - Mise en relation Entreprises & Clients

Ce projet est une plateforme de marketplace complète développée avec **Laravel 12**. Elle permet de mettre en relation des clients porteurs de projets (Développement, Design, Marketing, etc.) avec des entreprises prestataires qualifiées. L'application gère l'intégralité du flux, de la publication du projet au paiement final, en passant par la contractualisation et la messagerie en temps réel.

## 🚀 Fonctionnalités Principales

### 👥 Gestion des Rôles(Spatie) et Authentification (JWT)
L'application gère trois types d'utilisateurs distincts avec des espaces dédiés :
* **Clients** : Peuvent publier des projets, recevoir des offres et payer les prestations.
* **Entreprises** : Peuvent consulter les projets, soumettre des offres (devis) et gérer leurs contrats.
* **Administrateurs** : Disposent d'un tableau de bord pour gérer les utilisateurs, les litiges et les retraits.

### 📂 Gestion des Projets
* Publication de projets avec détails (titre, description, budget, délai, type).
* Catégories gérées : Développement Web, Mobile, Design Graphique, Marketing Digital, IA, Conseil, etc.
* Système de filtrage avancé pour les entreprises.

### 💼 Offres et Contrats
* Les entreprises soumettent des offres chiffrées sur les projets.
* Génération automatique de **Contrats** dès l'acceptation d'une offre.
* Suivi du statut du contrat (Signé, En cours, Terminé).

### 💳 Paiements Sécurisés (Stripe)
* Intégration de **Laravel Cashier (Stripe)**.
* Système de paiement par tranches (ex: acompte de 30% au démarrage, solde de 70% à la livraison).
* Portefeuille virtuel pour les entreprises et gestion des demandes de retrait.

### 💬 Messagerie Temps Réel
* Chat intégré entre Client et Entreprise via **Laravel Reverb** (WebSockets).
* Historique des conversations et statuts de lecture.

### ⚖️ Gestion des Litiges
* Système de déclaration de litiges sur les contrats en cours.
* Interface d'administration pour la résolution des conflits.

## 🛠 Prérequis Techniques

* PHP >= 8.2
* Composer
* Base de données (MySQL / MariaDB)
* Node.js & NPM (pour Vite)

## 📦 Installation

1.  **Cloner le dépôt**
    ```bash
    git clone [https://github.com/votre-username/plateforme-marketplace.git](https://github.com/votre-username/plateforme-marketplace.git)
    cd plateforme-marketplace
    ```

2.  **Installer les dépendances PHP**
    ```bash
    composer install
    ```

3.  **Configurer l'environnement**
    Copiez le fichier d'exemple et configurez vos accès (Base de données, Stripe, etc.) :
    ```bash
    cp .env.example .env
    ```
    *Assurez-vous de configurer les clés `STRIPE_KEY`, `STRIPE_SECRET`, et les configurations `REVERB` dans le fichier `.env`.*

4.  **Générer les clés de sécurité**
    Clé d'application et secret JWT :
    ```bash
    php artisan key:generate
    php artisan jwt:secret
    ```

5.  **Base de données**
    Exécutez les migrations et les seeders (si disponibles) :
    ```bash
    php artisan migrate --seed
    ```



## 🚀 Démarrage

Pour lancer l'application en local, vous aurez besoin de plusieurs terminaux :

 **Serveur Laravel**
    ```bash
    php artisan serve
    ````

L'API sera accessible via `http://127.0.0.1:8000/api`.

## 📚 Documentation API

L'API est sécurisée via JWT. Voici quelques points de terminaison clés :

### Authentification
* `POST /api/auth/client/login`
* `POST /api/auth/entreprise/login`
* `POST /api/auth/admin/login`

### Projets
* `GET /api/projets` : Liste des projets (filtrable).
* `POST /api/projets` : Créer un projet (Client uniquement).

### Contrats & Paiement
* `POST /api/contracts/{reference}/checkout` : Initier un paiement Stripe pour un contrat.
* `GET /api/paiement/succes/{reference}` : Callback de succès de paiement.

### Messagerie
* `GET /api/conversation/{receiverId}/{receiverType}` : Récupérer une conversation.
* `POST /api/messages` : Envoyer un message.

