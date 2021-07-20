declare namespace tei = "http://www.tei-c.org/ns/1.0";
declare namespace hgv = "HGV";
declare option saxon:output "method=xml";
declare option saxon:output "indent=yes";
declare variable $idpData external;

(: java -Xms512m -Xmx1536m net.sf.saxon.Query -q:idno_via_ddb.xql idpData=.../idp.data > idno_via_ddb.xml :)

<list>{
  for $doc in collection(concat($idpData, '/DDB_EpiDoc_XML?select=*.xml;recurse=yes'))

    let $hgv := $doc/tei:TEI/tei:teiHeader/tei:fileDesc/tei:publicationStmt/tei:idno[@type='HGV']
    let $tm  := $doc/tei:TEI/tei:teiHeader/tei:fileDesc/tei:publicationStmt/tei:idno[@type='TM']
    let $ddb := $doc/tei:TEI/tei:teiHeader/tei:fileDesc/tei:publicationStmt/tei:idno[@type='ddb-hybrid']

    return <item><idno type="hgv">{data($hgv)}</idno><idno type="tm">{data($tm)}</idno><idno type="ddb">{data($ddb)}</idno></item>

}</list>