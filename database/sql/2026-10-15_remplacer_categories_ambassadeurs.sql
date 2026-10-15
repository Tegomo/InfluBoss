-- =====================================================================================================
-- InfluBoss : remplacement des catégories d'ambassadeurs (15/10/2026)
--
-- Nouvelles catégories (clés stockées dans users.categories, libellés dans config/influboss.php) :
--   high_level            High Level
--   business              Business
--   story_tellers         Story Tellers
--   noso                  NOSO
--   anen                  ANEN
--   orange_money          Orange Money
--   pages_populaires      Pages populaires
--   lifestyle_mass_market Lifestyle Mass Market
--
-- Conversion des catégories déjà enregistrées :
--   business  -> business (inchangé)
--   lifestyle -> lifestyle_mass_market
--   humour, tech, musique, mode, beaute, sport, cuisine, voyage, gaming, education, actualite,
--   divertissement, famille, sante, art, autre -> retirées (aucun équivalent)
--
-- Compatible MySQL 5.7 / 8 et MariaDB (REPLACE uniquement). Gère les deux formats de stockage
-- ["a","b"] (MariaDB) et ["a", "b"] (MySQL 8). Rejouable sans effet de bord.
-- À exécuter APRÈS avoir déployé le code contenant la nouvelle liste, et APRÈS une sauvegarde de la base.
-- =====================================================================================================

-- 1. Aperçu avant modification
SELECT id, name, categories
FROM users
WHERE role = 'influencer' AND categories IS NOT NULL AND categories <> '[]';

START TRANSACTION;

-- 2. Correspondance : lifestyle -> lifestyle_mass_market
UPDATE users
SET categories = REPLACE(categories, '"lifestyle"', '"lifestyle_mass_market"')
WHERE categories LIKE '%"lifestyle"%';

-- 3. Retrait des anciennes catégories sans équivalent
--    (une valeur au début ou au milieu, puis en fin de liste, puis seule dans la liste)
UPDATE users SET categories = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(categories, '"humour", ', ''), '"humour",', ''), ', "humour"', ''), ',"humour"', ''), '"humour"', '') WHERE categories LIKE '%"humour"%';
UPDATE users SET categories = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(categories, '"tech", ', ''), '"tech",', ''), ', "tech"', ''), ',"tech"', ''), '"tech"', '') WHERE categories LIKE '%"tech"%';
UPDATE users SET categories = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(categories, '"musique", ', ''), '"musique",', ''), ', "musique"', ''), ',"musique"', ''), '"musique"', '') WHERE categories LIKE '%"musique"%';
UPDATE users SET categories = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(categories, '"mode", ', ''), '"mode",', ''), ', "mode"', ''), ',"mode"', ''), '"mode"', '') WHERE categories LIKE '%"mode"%';
UPDATE users SET categories = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(categories, '"beaute", ', ''), '"beaute",', ''), ', "beaute"', ''), ',"beaute"', ''), '"beaute"', '') WHERE categories LIKE '%"beaute"%';
UPDATE users SET categories = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(categories, '"sport", ', ''), '"sport",', ''), ', "sport"', ''), ',"sport"', ''), '"sport"', '') WHERE categories LIKE '%"sport"%';
UPDATE users SET categories = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(categories, '"cuisine", ', ''), '"cuisine",', ''), ', "cuisine"', ''), ',"cuisine"', ''), '"cuisine"', '') WHERE categories LIKE '%"cuisine"%';
UPDATE users SET categories = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(categories, '"voyage", ', ''), '"voyage",', ''), ', "voyage"', ''), ',"voyage"', ''), '"voyage"', '') WHERE categories LIKE '%"voyage"%';
UPDATE users SET categories = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(categories, '"gaming", ', ''), '"gaming",', ''), ', "gaming"', ''), ',"gaming"', ''), '"gaming"', '') WHERE categories LIKE '%"gaming"%';
UPDATE users SET categories = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(categories, '"education", ', ''), '"education",', ''), ', "education"', ''), ',"education"', ''), '"education"', '') WHERE categories LIKE '%"education"%';
UPDATE users SET categories = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(categories, '"actualite", ', ''), '"actualite",', ''), ', "actualite"', ''), ',"actualite"', ''), '"actualite"', '') WHERE categories LIKE '%"actualite"%';
UPDATE users SET categories = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(categories, '"divertissement", ', ''), '"divertissement",', ''), ', "divertissement"', ''), ',"divertissement"', ''), '"divertissement"', '') WHERE categories LIKE '%"divertissement"%';
UPDATE users SET categories = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(categories, '"famille", ', ''), '"famille",', ''), ', "famille"', ''), ',"famille"', ''), '"famille"', '') WHERE categories LIKE '%"famille"%';
UPDATE users SET categories = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(categories, '"sante", ', ''), '"sante",', ''), ', "sante"', ''), ',"sante"', ''), '"sante"', '') WHERE categories LIKE '%"sante"%';
UPDATE users SET categories = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(categories, '"art", ', ''), '"art",', ''), ', "art"', ''), ',"art"', ''), '"art"', '') WHERE categories LIKE '%"art"%';
UPDATE users SET categories = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(categories, '"autre", ', ''), '"autre",', ''), ', "autre"', ''), ',"autre"', ''), '"autre"', '') WHERE categories LIKE '%"autre"%';

-- 4. Ambassadeurs sans plus aucune catégorie : valeur vide
UPDATE users SET categories = NULL WHERE categories IN ('[]', '');

COMMIT;

-- 5. Contrôle : résultat final, et aucune clé hors de la nouvelle liste (la 2e requête doit renvoyer 0 ligne)
SELECT id, name, categories
FROM users
WHERE role = 'influencer' AND categories IS NOT NULL;

SELECT id, name, categories
FROM users
WHERE categories IS NOT NULL
  AND REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(categories,
      '"high_level"', ''), '"business"', ''), '"story_tellers"', ''), '"noso"', ''), '"anen"', ''),
      '"orange_money"', ''), '"pages_populaires"', ''), '"lifestyle_mass_market"', ''), ',', ''), ' ', ''), '[]', '') <> '';
