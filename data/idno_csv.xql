declare namespace tei = "http://www.tei-c.org/ns/1.0";
declare namespace rdf = "http://www.w3.org/1999/02/22-rdf-syntax-ns#";
declare namespace hgv = "HGV";
declare option saxon:output "method=text";
declare variable $idpData external;

(: java -Xms512m -Xmx1536m net.sf.saxon.Query -q:idno_csv.xql idpData=../idp.data > idno.csv :)


(for $doc in collection(concat($idpData, '/HGV_meta_EpiDoc?select=*.xml;recurse=yes'))

  let $hgv := string($doc//tei:publicationStmt/tei:idno[@type = 'filename'])
  let $tm  := string($doc//tei:publicationStmt/tei:idno[@type = 'TM'])
  let $ddb := string($doc//tei:publicationStmt/tei:idno[@type = 'ddb-hybrid'][1])

  let $dclpFile := concat($idpData, '/DCLP/', ceiling(number($tm) div 1000), '/', $tm, '.xml')
  let $dclpEpiDoc := if(doc-available($dclpFile))then(doc($dclpFile))else() 
  let $dclp := if($dclpEpiDoc)then(string($dclpEpiDoc//tei:publicationStmt/tei:idno[@type = 'dclp-hybrid']))else()

  (:return ({data($hgv)},{data($tm)},{data($ddb)},{if($dclp)then(data($dclp))else()}):)
  return text{(string-join((data($hgv), data($tm), data($ddb), if($dclp)then(data($dclp))else()), '|'), '&#xa;')}

(:,
for $doc in collection(concat($idpData, '/DCLP?select=*.xml;recurse=yes'))[.//tei:div[@type = 'edition'][@xml:lang = ('grc', 'la', 'egy-Egyd', 'cop', 'ara')]]

  let $tm   := $doc/tei:TEI/tei:teiHeader/tei:fileDesc/tei:publicationStmt/tei:idno[@type = 'TM']
  let $dclp := $doc/tei:TEI/tei:teiHeader/tei:fileDesc/tei:publicationStmt/tei:idno[@type = 'dclp-hybrid']
  let $numberServer := concat('http://papyri.info/dclp/', $tm, '/rdf') (: http://papyri.info/dclp/89508/rdf :)
  let $dclpRdf := if(doc-available($numberServer))then(doc($numberServer))else(trace($numberServer, 'Numberserver not available')) (: cl: dieser Zugriff mit vorhergehendem Test kostet ca. eine halbe Stunde :)

  return if(not($dclpRdf//rdf:Description[starts-with(@rdf:about, 'http://papyri.info/hgv')]))then(<item><idno type="tm">{data($tm)}</idno><idno type="dclp">{data($dclp)}</idno></item>)else():)
 )
