# Suivi des paiements — conception (Lot E)

Date : 2026-10-13. Statut : décisions prises en autonomie, à relire avec le client.

## Besoin client

« Facturation/Paiements : suivi des paiements effectués/en attente. »

## Existant

Le contrat porte un montant et des modalités de paiement en texte libre (lot B). La mission porte une
rémunération (`assignments.compensation`). Aucun enregistrement des versements.

## Décisions

1. **Table `payments`** : une ligne par versement prévu ou effectué. Champs : influenceur (obligatoire), contrat
   et mission (facultatifs, appartenant au même influenceur), libellé (ex. « Acompte 50 % »), montant en FCFA,
   date d'échéance, statut (`pending` en attente, `paid` payé, `cancelled` annulé), date de paiement, moyen
   (`bank_transfer` virement, `mobile_money`, `cash` espèces, `cheque` chèque, `other`), référence de transaction,
   justificatif (PDF/JPG/PNG sur le disque privé), notes. Soft delete.
2. **En retard** = en attente avec une échéance dépassée. Ce n'est pas un statut stocké : il est calculé et
   filtrable.
3. **Écrans**
   - Menu « Paiements » (« Mes paiements » pour un influenceur) : liste filtrable (statut dont « en retard »,
     moyen, ambassadeur, période d'échéance), totaux payé / en attente / en retard sur la sélection, export CSV.
   - Création et modification ; « Marquer payé » ouvre la modification pré-remplie (statut payé, date du jour).
   - Fiche contrat : échéancier des paiements du contrat avec montant du contrat, payé, en attente et reste à
     planifier.
   - Fiche influenceur : onglet « Paiements » avec ses totaux.
4. **Droits** : les admins et super_admins gèrent tout ; l'influenceur consulte ses paiements et télécharge ses
   justificatifs ; **les superviseurs n'ont pas accès** aux paiements (données financières).
5. Saisir un statut autre que « payé » efface la date de paiement ; « payé » exige une date de paiement.

## Hors périmètre

Génération de factures PDF, paiement en ligne, rapprochement bancaire, alertes sur les échéances de paiement.
