LOAD DATA LOCAL INFILE 'index_entry_data.csv'
INTO TABLE index_entry
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES
(id, type, topic, tab, @lemma, papy_new, greek_new, phrase, @sort)
SET lemma = SUBSTRING(@lemma, 1, 255), sort = SUBSTRING(@sort, 1, 255), correction_id = 5;
