SELECT

c.short, type, topic, tab, CONCAT(IF(papy_new = 1, '*', ''), IF(greek_new = 1, '✝', ''), phrase) AS phrase, CONCAT('(', i.id, ')') as index_entry_id

FROM compilation c JOIN `compilation_index_entry` ci ON ci.compilation_id = c.id JOIN index_entry i ON ci.index_entry_id = i.id

WHERE ci.compilation_id = 8

ORDER BY i.sort;