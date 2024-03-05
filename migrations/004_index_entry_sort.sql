/* new sort field */
ALTER TABLE `index_entry` ADD `sort` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `lemma`;


SELECT
  IF(type = 'Ghostword', 8, 4)*1000000 as type,
  IF(topic='Personennamen', 8, IF(topic='Könige, Kaiser, Konsuln', 16, IF(topic='Geographisches und Topographisches', 24, IF(topic='Monate und Tage', 32, IF(topic='Religion', 40, IF(topic='Zivil- und Militärverwaltung', 48, IF(topic='Steuern', 56, IF(topic='Berufsbezeichnungen', 64, IF(topic='Allgemeiner Wortindex', 72, IF(topic='Fundorte', 80, IF(topic='Sachen', 88, 0)))))))))))*10000 as topic,
  tab as tob_o,
  IF(tab='Lateinisch (und Demotisch)', 3000, IF(tab='Schadhaft', 7000, IF(tab='Koptisch', 5000, IF(tab='Lateinisch', 2000, CONVERT(tab, decimal))))) as tab,
  CONCAT(
  IF(type = 'Ghostword', 8, 4)*1000000
  +
  IF(topic='Personennamen', 8, IF(topic='Könige, Kaiser, Konsuln', 16, IF(topic='Geographisches und Topographisches', 24, IF(topic='Monate und Tage', 32, IF(topic='Religion', 40, IF(topic='Zivil- und Militärverwaltung', 48, IF(topic='Steuern', 56, IF(topic='Berufsbezeichnungen', 64, IF(topic='Allgemeiner Wortindex', 72, IF(topic='Fundorte', 80, IF(topic='Sachen', 88, 0)))))))))))*10000
  +
  IF(tab='Lateinisch (und Demotisch)', 3000, IF(tab='Schadhaft', 7000, IF(tab='Koptisch', 5000, IF(tab='Lateinisch', 2000, ORD(tab))))),
  '_',
  lemma) AS sort
FROM `index_entry`
WHERE 1
ORDER by sort;


/* DATA */

UPDATE index_entry SET sort = '8720949_εἰσεκδέχομαι' WHERE id = 118;
UPDATE index_entry SET sort = '8727952_ἐπιτέλλω “befehlen, auf etwas vertrauen”' WHERE id = 30;
UPDATE index_entry SET sort = '8727956_ἔρις (ghost für pap.)' WHERE id = 12;
UPDATE index_entry SET sort = '8720950_ζυτουργεῖον' WHERE id = 108;
UPDATE index_entry SET sort = '8720950_ζυτουργεῖον (kommt in Papyri nicht vor, wohl in anderen Texten attestiert)' WHERE id = 107;
UPDATE index_entry SET sort = '8720954_κα[ι]ν̣όπ[ο]κα (Hapax) abgelehnt' WHERE id = 89;
UPDATE index_entry SET sort = '8720954_κάλκιος' WHERE id = 84;
UPDATE index_entry SET sort = '8720955_λίθαργος' WHERE id = 133;
UPDATE index_entry SET sort = '8720959_οὐλέριος (ου[λ]αιριον Pap.)' WHERE id = 11;
UPDATE index_entry SET sort = '8720960_πάρολκον' WHERE id = 19;
UPDATE index_entry SET sort = '8720963_συνενοικέω' WHERE id = 131;
UPDATE index_entry SET sort = '8640967_χαρτάριος' WHERE id = 25;
UPDATE index_entry SET sort = '8240920_Θεαβεννεύς' WHERE id = 128;
UPDATE index_entry SET sort = '8240920_Θεαβέννιος' WHERE id = 129;
UPDATE index_entry SET sort = '8240920_Θετηλέννις' WHERE id = 126;
UPDATE index_entry SET sort = '8240932_Τωτκῶις' WHERE id = 145;
UPDATE index_entry SET sort = '8080079_Opter' WHERE id = 139;
UPDATE index_entry SET sort = '8087944_Ἀκακίας' WHERE id = 122;
UPDATE index_entry SET sort = '8087944_Ἀνοῦθος' WHERE id = 90;
UPDATE index_entry SET sort = '8087944_Ἀόφις' WHERE id = 119;
UPDATE index_entry SET sort = '8080918_Ζῶνος' WHERE id = 47;
UPDATE index_entry SET sort = '8080922_Κοπίθων' WHERE id = 80;
UPDATE index_entry SET sort = '8080928_Π̣αγχναῦ{υ}τις' WHERE id = 35;
UPDATE index_entry SET sort = '8080928_Παθερμοίτης' WHERE id = 114;
UPDATE index_entry SET sort = '8080928_Παλῆτις' WHERE id = 54;
UPDATE index_entry SET sort = '8080928_Παταχῆμις' WHERE id = 83;
UPDATE index_entry SET sort = '8080931_Σενεκθος' WHERE id = 113;
UPDATE index_entry SET sort = '8080931_Στέψις' WHERE id = 45;
UPDATE index_entry SET sort = '8080931_Σῶνος' WHERE id = 115;
UPDATE index_entry SET sort = '8080932_Τράτος' WHERE id = 26;
UPDATE index_entry SET sort = '8080932_Τέμενος' WHERE id = 132;
UPDATE index_entry SET sort = '8080934_Φα̣ρασῆς' WHERE id = 123;
UPDATE index_entry SET sort = '8080934_Φαλῆτις' WHERE id = 60;
UPDATE index_entry SET sort = '8080934_Φανῆτις' WHERE id = 50;
UPDATE index_entry SET sort = '8080934_Φιλοτίμιος' WHERE id = 143;
UPDATE index_entry SET sort = '8080936_Ψέσιος' WHERE id = 124;
UPDATE index_entry SET sort = '8400914_Βαχεῖον' WHERE id = 78;
UPDATE index_entry SET sort = '8480085_Und noch einer' WHERE id = 2;
UPDATE index_entry SET sort = '8480960_προσεπίτροπος' WHERE id = 140;
UPDATE index_entry SET sort = '4727937_ἁγιαστήριον (neu für pap)' WHERE id = 14;
UPDATE index_entry SET sort = '4727936_ἀπέκδικος' WHERE id = 16;
UPDATE index_entry SET sort = '4727936_ἀπότακτος' WHERE id = 94;
UPDATE index_entry SET sort = '4727936_ἀργενταρίτης' WHERE id = 106;
UPDATE index_entry SET sort = '4720949_εἰσενδείκνυμι' WHERE id = 134;
UPDATE index_entry SET sort = '4727952_ἐχιναλικός (?)' WHERE id = 138;
UPDATE index_entry SET sort = '4720950_ζευτλωφακᾶς (l. σευτλοφακᾶς oder σευτλοφαγᾶς) / ζευτλωφάκος (l. σευτλοφάγος)' WHERE id = 75;
UPDATE index_entry SET sort = '4720954_καθαροποιΐα' WHERE id = 142;
UPDATE index_entry SET sort = '4720954_καμηλίων (καμιλω Pap.)' WHERE id = 17;
UPDATE index_entry SET sort = '4720954_κτήτωρ' WHERE id = 98;
UPDATE index_entry SET sort = '4720955_λιγύριον' WHERE id = 87;
UPDATE index_entry SET sort = '4720956_μεγαλοπρεπέστατος' WHERE id = 95;
UPDATE index_entry SET sort = '4720957_νούμερος' WHERE id = 29;
UPDATE index_entry SET sort = '4720959_οὐδός (οδω Pap.) [neu für pap.]' WHERE id = 13;
UPDATE index_entry SET sort = '4720959_οὐσία' WHERE id = 101;
UPDATE index_entry SET sort = '4720960_πλαγία' WHERE id = 110;
UPDATE index_entry SET sort = '4720963_συμμίσθωσις (συνμ- Pap.)' WHERE id = 79;
UPDATE index_entry SET sort = '4720967_χωρίον' WHERE id = 100;
UPDATE index_entry SET sort = '4640946_βοέμπορος' WHERE id = 137;
UPDATE index_entry SET sort = '4640950_ζευτλωφακᾶς (l. σευτλοφακᾶς oder σευτλοφαγᾶς) / ζευτλωφάκος (l. σευτλοφάγος)' WHERE id = 81;
UPDATE index_entry SET sort = '4640963_σαγματᾶς, “Sattler”' WHERE id = 86;
UPDATE index_entry SET sort = '4240914_Βάχθιος (Bḥdt, Apollonopolis)' WHERE id = 77;
UPDATE index_entry SET sort = '4240924_Μαστι̣γ̣γ̣ι̣φ(όρου) oder Μαστι̣γ̣ν̣[ο]φ(όρ̣ου)' WHERE id = 136;
UPDATE index_entry SET sort = '4240936_Ψινόλ' WHERE id = 102;
UPDATE index_entry SET sort = '4248044_Ὤκεως' WHERE id = 105;
UPDATE index_entry SET sort = '4080053_5' WHERE id = 8;
UPDATE index_entry SET sort = '4080054_6' WHERE id = 9;
UPDATE index_entry SET sort = '4087944_Ἀκαβίας' WHERE id = 121;
UPDATE index_entry SET sort = '4087944_Ἀμπελίου' WHERE id = 104;
UPDATE index_entry SET sort = '4080913_Αὐρήλιος' WHERE id = 92;
UPDATE index_entry SET sort = '4080914_Βαστειῶς' WHERE id = 82;
UPDATE index_entry SET sort = '4080918_Ζαννος' WHERE id = 46;
UPDATE index_entry SET sort = '4080920_Θινπατκναῦς (Var. für Θινπατχναῦς)' WHERE id = 40;
UPDATE index_entry SET sort = '4080920_Θινπατκναῦτις (Var. für Θινπατχναῦτις)' WHERE id = 41;
UPDATE index_entry SET sort = '4080920_Θινπατχναῦς' WHERE id = 36;
UPDATE index_entry SET sort = '4080922_Κανβέλις' WHERE id = 34;
UPDATE index_entry SET sort = '4080922_Κλιτομένο̣υ̣ς' WHERE id = 112;
UPDATE index_entry SET sort = '4080924_Μαρνίτης' WHERE id = 135;
UPDATE index_entry SET sort = '4080925_Νεσεῦς' WHERE id = 43;
UPDATE index_entry SET sort = '4080928_Πατρικία' WHERE id = 96;
UPDATE index_entry SET sort = '4080928_Παχναῦτις' WHERE id = 37;
UPDATE index_entry SET sort = '4080934_Φαλήυις new name' WHERE id = 63;
UPDATE index_entry SET sort = '4080934_Φλάουιος' WHERE id = 93;
UPDATE index_entry SET sort = '4487936_ἀπέκδικος' WHERE id = 15;
UPDATE index_entry SET sort = '4487936_ἀργενταρίτης' WHERE id = 99;
UPDATE index_entry SET sort = '4487940_ἄρχων' WHERE id = 97;
UPDATE index_entry SET sort = '4480954_κελλαρίτης' WHERE id = 103;
UPDATE index_entry SET sort = '4480957_νούμερος' WHERE id = 28;
UPDATE index_entry SET sort = '4480960_παλαιστρατιώτης' WHERE id = 91;

/* sort NOT NULL */

ALTER TABLE `index_entry` CHANGE `sort` `sort` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 

/* UNIQUE INDEX */

………… muss manuell gemacht werden