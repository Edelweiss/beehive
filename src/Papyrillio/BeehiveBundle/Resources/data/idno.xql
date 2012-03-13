declare namespace tei = "http://www.tei-c.org/ns/1.0";
declare namespace hgv = "HGV";
declare option saxon:output "method=xml";
declare option saxon:output "indent=yes";

<list>{
  for $doc in collection("/Users/Admin/idp.data/HGV_meta_EpiDoc?select=*.xml;recurse=yes")

    let $hgv := $doc/tei:TEI/tei:teiHeader/tei:fileDesc/tei:publicationStmt/tei:idno[@type='filename']
    let $tm  := $doc/tei:TEI/tei:teiHeader/tei:fileDesc/tei:publicationStmt/tei:idno[@type='TM']
    let $ddb := $doc/tei:TEI/tei:teiHeader/tei:fileDesc/tei:publicationStmt/tei:idno[@type='ddb-hybrid']
    
    return <item><idno type="hgv">{data($hgv)}</idno><idno type="tm">{data($tm)}</idno><idno type="ddb">{data($ddb)}</idno></item>
    
}</list>