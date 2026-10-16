# LinkRegisterToVolume Command

Dieses Kommando verlinkt Register-Einträge mit Volume-Einträgen und erstellt automatisch fehlende Volumes.

## Beschreibung

Der Command führt folgende Schritte aus:

1. **Laden der Editions-Daten**: Parst die `idp.data/RDF/collection.rdf` Datei
2. **Verlinken existierender Volumes**: Führt zwei UPDATE-Statements aus:
   - Verlinkt Register über `ddb` mit Volumes
   - Verlinkt Register über `dclp` mit Volumes
3. **Finden unverknüpfter Einträge**: Identifiziert Register ohne Volume-Verknüpfung
4. **Erstellen fehlender Volumes**: Generiert neue Volume-Einträge basierend auf:
   - Dem hybrid-Identifier (z.B. `apf;70`)
   - Der kanonischen Zitierweise aus der RDF (z.B. `APF`)
   - Eine automatisch berechnete Sort-Nummer für alphabetische/numerische Ordnung

## Verwendung

### Dry-Run (Test-Modus)
```bash
php bin/console app:link-register-to-volume --dry-run
```

Zeigt an, welche Volumes erstellt würden, ohne Änderungen in der Datenbank vorzunehmen.

### Produktivmodus
```bash
php bin/console app:link-register-to-volume
```

Erstellt tatsächlich die fehlenden Volumes in der Datenbank.

## Ausgabe

Der Command gibt folgende Informationen aus:

### Beispiel Output:
```
========================================
Link Register to Volume Command
[DRY RUN MODE]
========================================

Step 1: Loading edition data from RDF...
  ✓ Loaded 461 editions

Step 2: Linking register entries with existing volumes...
  ✓ Linked 0 register entries

Step 3: Finding unlinked register entries...
  ✓ Found 157 unlinked volume identifiers

Step 4: Creating missing volume entries...
  ✓ NEW: apf;70 -> 'APF 70' (sort: 42001)
      preceding: apf;69 (sort: 42000)
      following: arctos;13 (sort: 42113)
  ⚠ Edition not found in RDF: bkt (from bkt;1)
  ...
  Skipped: 42 entries with missing edition data
  DRY RUN: Would create 62 new volumes
```

## Vorgänger/Nachfolger Validierung

Jeder neu erstellte Volume wird zusammen mit seinem alphabetisch/numerisch vorherigen und nachfolgenden Volume angezeigt. Dies ermöglicht eine schnelle Validierung der richtigen Sortierungsposition.

## Fehlerbehandlung

Wenn eine Edition nicht in der RDF-Datei vorhanden ist, wird sie übersprungen mit der Warnung:
```
⚠ Edition not found in RDF: <edition> (from <hybrid>)
```

Das ist normal für Editionen, die keine kanonische Zitierweise haben.

## Datenbank-Schema

Die `volume` Tabelle benötigt folgende Spalten:
- `id`: Primary Key
- `hybrid`: Eindeutiger Identifier (z.B. `apf;70`)
- `title`: Vollständiger Titel (z.B. `APF 70`)
- `sort`: Sortierungsnummer (Integer, für alphabetische/numerische Ordnung)

Die `register` Tabelle benötigt:
- `id`: Primary Key
- `ddb`: DDB-Identifier (optional)
- `dclp`: DCLP-Identifier (optional)
- `volume_id`: Fremdschlüssel zu `volume` (optional, wird gesetzt durch UPDATE)

## Performance

- Für ~150-200 fehlende Volumes dauert der Command ca. 2-5 Minuten
- Der RDF-Parse ist der zeitintensivste Schritt
- Alle Datenbankoperationen sind transaktional
