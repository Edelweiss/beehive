
SELECT m.lfd, m.index_entry_id, m.correction_id, m.title, m.text, m.position, m.volume, m.reffo, m.verweise, m.title_source, b.compilation_id, b.short, b.volume, b.page, b.edition_id, b.title, b.text, b.position, b.correction_id, b.description
  FROM matchmaker m
  JOIN `vbeehive` b ON m.title = b.title AND m.text = b.text AND m.position = b.position AND m.volume = b.volume AND m.page = b.page
  WHERE 1
  
/* GROUP */

SELECT m.lfd, m.index_entry_id, m.correction_id, m.title, m.text, m.position, m.volume, m.reffo, m.verweise, m.title_source, count(*) matches_found, GROUP_CONCAT(b.correction_id, ' - ', b.description)
  FROM matchmaker m
  JOIN `vBeehive` b ON 
    m.title = b.title AND 
    m.text = b.text AND 
    m.position = b.position AND 
    m.volume = b.volume AND 
    m.page = b.page
  GROUP BY m.lfd, m.index_entry_id, m.correction_id, m.title, m.text, m.position, m.volume, m.reffo, m.verweise, m.title_source
  HAVING
    matches_found = 1
  ORDER BY matches_found DESC
  
/* UPDATE */

UPDATE matchmaker AS mm INNER JOIN (
SELECT m.lfd, m.index_entry_id, m.correction_id, m.title, m.text, m.position, m.volume, m.reffo, m.verweise, m.title_source, count(*) matches_found, b.correction_id x
  FROM matchmaker m
  JOIN `vBeehive` b ON 
    m.title = b.title AND 
    m.text = b.text AND 
    m.position = b.position AND 
    m.volume = b.volume AND 
    m.page = b.page
  GROUP BY m.lfd, m.index_entry_id, m.correction_id, m.title, m.text, m.position, m.volume, m.reffo, m.verweise, m.title_source
  HAVING
    matches_found = 1
  ORDER BY matches_found DESC
) AS mm_bh ON  mm.lfd = mm_bh.lfd
SET mm.correction_id = mm_bh.x

/* bereits zugeordnet */

SELECT CONCAT(count(*), ' von 42643 zugeordnet (', count(*)/42643,')') AS matches_matched FROM `matchmaker` WHERE correction_id IS NOT NULL;


/* Multizuordnungen als Vorschlagsliste */
SELECT
  m.lfd,
  m.index_entry_id, i.type, i.topic, i.tab, i.phrase,
  m.correction_id, b.correction_id, b.description,
  m.title, b.title, m.text, b.text, m.position, b.position, m.volume, b.volume, m.page, b.page, m.reffo, m.verweise, m.title_source
  FROM matchmaker m
  JOIN index_entry i ON
    m.index_entry_id = i.id
  JOIN `vBeehive` b ON
    m.title = b.title AND
    m.text = b.text AND
    m.position = b.position AND
    m.volume = b.volume AND
    m.page = b.page
  WHERE
    m.correction_id is NULL
  ORDER BY m.lfd ASC
