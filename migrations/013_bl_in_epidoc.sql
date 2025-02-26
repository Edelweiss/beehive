SELECT hgv, GROUP_CONCAT(short, ' ', page SEPARATOR '; ') AS bl, SUM(entries) as entries FROM

(SELECT hgv, short, GROUP_CONCAT(compilationPage SEPARATOR ', ') AS page, COUNT(*) AS entries FROM 

(SELECT hgv, short, compilationPage, IF(compilationPage, CONCAT('<item hgv="', hgv, '" bl="', short, '" page="', compilationPage, '"/>'), CONCAT('<item hgv="', hgv, '" bl="', short, '"/>')) AS epidoc from correction c JOIN correction_register cr ON cr.correction_id = c.id JOIN register r ON cr.register_id = r.id JOIN compilation comp ON c.compilation_id = comp.id WHERE hgv IS NOT NULL AND comp.collection = 'BL' GROUP BY hgv, short, compilationPage ORDER BY hgv, comp.volume, c.compilationPage+0) AS s

GROUP BY hgv, short) AS S2

GROUP BY hgv;
