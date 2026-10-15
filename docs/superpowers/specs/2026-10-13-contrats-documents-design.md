# Contrats et documents légaux — conception (Lot B)

Date : 2026-10-13. Statut : décisions prises en autonomie, à relire avec le client.

## Besoin client

- Contrat : dates de début et de fin, type (exclusif, ponctuel, ambassadeur…), montant et modalités de paiement,
  statut (actif, en négociation, expiré, résilié), document scanné joint, alertes automatiques avant expiration
  (30, 15 et 7 jours).
- Documents légaux : pièce d'identité, RIB…

## Décisions

1. **Plusieurs contrats par influenceur** (historique des renouvellements). Table `contracts` liée à `users.id`
   (`influencer_id`), soft delete comme le reste du projet.
2. **Référentiels en configuration** (`config/influboss.php`) : types (`exclusive`, `one_off`, `ambassador`,
   `other`), statuts (`negotiation`, `active`, `expired`, `terminated`), types de documents (`identity`, `rib`,
   `tax`, `other`), seuils d'alerte `[30, 15, 7]`. Colonnes `string` validées côté applicatif (pas d'enum DB).
3. **Montant** en FCFA (`decimal(15,2)`), **modalités de paiement** en texte libre. Le suivi des paiements
   effectifs relève du lot E.
4. **Fichiers sur le disque privé** `local` (`storage/app/private`), jamais sous `public/`. Téléchargement par une
   route authentifiée contrôlée par policy. Formats acceptés : PDF, JPG, PNG, 10 Mo maximum. Le nom d'origine est
   conservé pour le téléchargement.
5. **Droits**
   - Contrats : création, modification, suppression par admin et super_admin ; consultation et téléchargement
     aussi par les superviseurs liés à l'influenceur (pivot `supervisor_influencer`) et par l'influenceur lui-même.
   - Documents légaux (données sensibles) : admin et super_admin gèrent tout ; l'influenceur peut consulter et
     télécharger les siens ; **les superviseurs n'y ont pas accès**.
6. **Expiration automatique** : une commande quotidienne `contracts:check-expirations` passe en `expired` les
   contrats `active` dont la date de fin est dépassée, puis envoie les alertes.
7. **Alertes 30/15/7 jours** : pour un contrat actif dont la fin est dans N jours, on retient le plus petit seuil
   ≥ N. S'il n'a pas déjà été envoyé, une notification part et ce seuil ainsi que tous les seuils supérieurs sont
   marqués envoyés (table `contract_alerts`, unique par contrat et seuil). Une commande manquée ne provoque donc
   pas de rafale. Modifier la date de fin d'un contrat réinitialise ses alertes.
8. **Destinataires** : admins et super_admins actifs, plus les superviseurs liés à l'influenceur. Canaux : e-mail
   et notification en base. La cloche de la barre de navigation, aujourd'hui factice, affiche les notifications
   réelles non lues.
9. **Écrans** : menu « Contrats » (« Mes contrats » pour un influenceur) avec liste filtrable (statut, type,
   expirant sous 30 jours), création, fiche détail, modification ; onglets « Contrats » et « Documents » sur la
   fiche influenceur, avec envoi de document depuis l'onglet.

## Prérequis de mise en production

- Cron sur l'hébergement : `* * * * * cd /chemin/du/projet && php artisan schedule:run >> /dev/null 2>&1`.
- SMTP réel dans `.env` (`MAIL_MAILER=smtp`, hôte, identifiants, `MAIL_FROM_ADDRESS`). Sans cela les e-mails
  partent dans le journal, mais les notifications de la cloche fonctionnent.

## Hors périmètre

Paiements effectifs (lot E), signature électronique, alertes sur l'expiration des pièces d'identité (la date
d'expiration est saisissable et affichée, sans alerte).

## Mise à jour du 15/10/2026

Sur demande d Armand : contrats et paiements réservés aux admins (menus, pages, téléchargements, onglets de la fiche). Superviseurs et ambassadeurs n y ont plus accès ; les alertes d expiration ne partent qu aux admins. Le client (Orange) voit uniquement la période du contrat en cours sur la fiche ambassadeur.
