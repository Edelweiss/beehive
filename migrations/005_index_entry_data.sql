# Download from Google
# Delete rows and columns
# Rename to index_entry_data.csv
# scp index_entry_data.csv ubuntu@129.206.4.62:/var/www/beehive_dev/data/
# mysql -u beehive_dev -p beehive_dev

DELETE FROM index_entry WHERE id > 145;

LOAD DATA LOCAL INFILE 'index_entry_data.csv'
INTO TABLE index_entry
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES
(id, type, topic, tab, @lemma, papy_new, greek_new, phrase, @sort)
SET lemma = SUBSTRING(@lemma, 1, 255), sort = SUBSTRING(@sort, 1, 255), correction_id = 5;

SHOW WARNINGS;

