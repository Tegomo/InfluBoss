# Livrables par mission et charte de marque — conception (Lot C)

Date : 2026-10-13. Statut : décisions prises en autonomie, à relire avec le client.

## Besoin client (« cahier des charges contractant »)

- Livrables attendus (nombre de posts, stories, vidéos…) et deadline par livrable.
- Guidelines de marque (ton, visuels, hashtags obligatoires, mentions) et produits/services à promouvoir.
- Statut de chaque livrable : à faire, en cours, livré, validé.

## Existant

Une mission (`assignments`) n'a qu'une échéance, un nombre de publications requises (`required_posts`) et des
instructions en texte libre. Le brief (`contents.guidelines`) est un JSON libre. Rien ne relie une publication à
ce qu'elle était censée livrer.

## Décisions

1. **Table `deliverables`** rattachée à la mission : type (`post`, `story`, `reel`, `video`, `live`, `other`),
   plateforme facultative (clés de `influboss.platforms`), quantité, date limite facultative, description, statut
   (`todo`, `in_progress`, `delivered`, `validated`). Référentiels dans `config/influboss.php`.
2. **Une publication peut être rattachée à un livrable** (`publications.deliverable_id`, facultatif, même mission
   obligatoire). Le formulaire de publication propose les livrables de la mission choisie.
3. **Statut calculé automatiquement** à chaque création, modification, vérification, rejet ou suppression d'une
   publication rattachée :
   - publications vérifiées ≥ quantité → « Validé » ;
   - publications soumises non rejetées ≥ quantité → « Livré » ;
   - au moins une publication soumise → « En cours » ;
   - sinon « À faire », sauf si le livrable avait été passé manuellement « En cours ».
   Les admins et superviseurs peuvent aussi changer le statut à la main (ex. livrable remis hors plateforme).
4. **`assignments.required_posts` = somme des quantités** des livrables dès qu'il en existe, pour que la
   complétion automatique de mission existante reste cohérente.
5. **Droits** : admins, superviseur de la mission et superviseurs liés à l'influenceur ajoutent, modifient et
   suppriment les livrables ; l'influenceur les consulte sur sa mission.
6. **Charte de marque au niveau de la campagne** (partagée par toutes les missions) : ton, consignes visuelles,
   hashtags obligatoires, mentions obligatoires (listes), produits/services à promouvoir. Saisie dans les
   formulaires de campagne, affichée sur la campagne et sur chaque mission. Les consignes propres à une mission
   restent dans `assignments.instructions`.
7. **Retard** : un livrable dont la date limite est passée et qui n'est ni livré ni validé affiche « En retard ».

## Hors périmètre

Rappels automatiques avant échéance de livrable, vérification automatique des hashtags dans les publications,
création des livrables directement dans le formulaire de création de mission (ils s'ajoutent sur la page mission).
