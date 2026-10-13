# Fiche influenceur v2 — conception (Lot A)

Date : 2026-10-13. Statut : validé.

## Contexte

Le client a demandé une refonte de la fiche influenceur : catégorie/niche à la place de la date de naissance,
adresse, réseaux sociaux avec nombre d'abonnés par plateforme et historique d'évolution, historique des
campagnes avec résultats par campagne. Cette rubrique est le premier des cinq lots identifiés
(A fiche influenceur, B contrats et documents, C livrables, D automatisation des statistiques, E paiements).

## Décisions

1. **Comptes sociaux en table dédiée.** `social_accounts` : un enregistrement par (influenceur, plateforme)
   avec URL, handle, dernier nombre d'abonnés connu et date de dernière synchronisation. Plateformes :
   `instagram, tiktok, youtube, facebook, x, linkedin` (liste dans `config/influboss.php`). La clé legacy
   `twitter` est mappée sur `x`.
2. **Historique des abonnés.** `follower_snapshots` : un point par saisie ou synchronisation
   (`followers_count`, `collected_at`, `source` = `manual` | `api`, `recorded_by`). Point d'entrée unique :
   `SocialAccount::recordFollowers()`, réutilisé par le lot D (Apify).
3. **Catégories.** Liste fixe en configuration (`config/influboss.php`), plusieurs catégories par influenceur
   (colonne JSON `users.categories`), filtre dans la liste.
4. **Saisie des abonnés.** Réservée aux admins/super_admins et aux superviseurs liés à l'influenceur
   (pivot `supervisor_influencer`), via le formulaire création/édition et via un formulaire dédié sur la fiche.
   Nouvelle ability `UserPolicy::updateFollowers`.
5. **Date de naissance.** Colonne `users.birthdate` conservée, retirée des formulaires, de la fiche, de
   l'export et de l'import CSV des influenceurs. Les écrans `users/*` et `supervisors/*` sont inchangés.
6. **Migration des données.** Les liens existants (`users.social_links`, JSON parfois double-encodé, et
   colonnes `*_handle`) sont copiés dans `social_accounts` par une migration idempotente. Les anciennes
   colonnes sont conservées en lecture seule.

## Règles de gestion

- Formulaire création/édition : un snapshot n'est créé que si la valeur saisie diffère du dernier nombre
  connu (ou s'il n'existe aucun snapshot). Le formulaire dédié « Mettre à jour les abonnés » crée toujours un
  snapshot.
- Vider l'URL d'une plateforme dans le formulaire d'édition supprime le compte et son historique.
- Résultats par campagne (onglet Campagnes de la fiche) : calculés sur les publications **vérifiées** à
  partir du **dernier** snapshot `stats` de chaque publication (likes, commentaires, partages, vues,
  engagement, taux d'engagement), avec une ligne de totaux.
- Courbe d'évolution des abonnés : 12 derniers mois, une série par plateforme, valeur du mois = dernier
  snapshot du mois, report de la dernière valeur connue sinon.
- Export CSV : catégories en clés techniques séparées par `|` ; import acceptant clé ou libellé.

## Modèle de données

```
users ──< social_accounts ──< follower_snapshots
 + address              (platform, url, handle,        (followers_count, collected_at,
 + categories (json)     followers_count,               source manual|api, recorded_by)
                         last_synced_at)
                         unique (user_id, platform)
```

## Hors périmètre

Contrats, documents légaux, livrables, automatisation Apify, paiements (lots B à E). `ProfileController`
(vue inexistante) et contrôleurs `API/*` (non routés) ne sont pas modifiés.
