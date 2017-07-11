<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet exclude-result-prefixes="#all" version="2.0"
    xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
    xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0"
    xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0"
    xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0"
    xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
    xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0"
    xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0"
    xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0"
    xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0"
    xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0"
    xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0"
    xmlns:math="http://www.w3.org/1998/Math/MathML"
    xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0"
    xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0"
    xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer"
    xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events"
    xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:rpt="http://openoffice.org/2005/report"
    xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2"
    xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#"
    xmlns:officeooo="http://openoffice.org/2009/office"
    xmlns:tableooo="http://openoffice.org/2009/table"
    xmlns:drawooo="http://openoffice.org/2010/draw"
    xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0"
    xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0"
    xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0"
    xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0"
    xmlns:css3t="http://www.w3.org/TR/css3-text/"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:papy="Papyrillio"
    xmlns:xs="http://www.w3.org/2001/XMLSchema"
    xmlns:date="http://exslt.org/dates-and-times"
    xmlns:fm="http://www.filemaker.com/fmpxmlresult"
    xmlns:tei="http://www.tei-c.org/ns/1.0"
    xmlns:fn="http://www.xsltfunctions.com/"
    xmlns="http://www.tei-c.org/ns/1.0">

<!--
    java -Xms512m -Xmx1536m net.sf.saxon.Transform -o:postscript/headers.html -s:postscript/content_release.xml -xsl:exportPublicationHeadersPerPage.xsl
-->
    <xsl:output method="html" media-type="text/html" />
    
    <xsl:template match="/office:document-content">
        <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <title>BL print headers per page</title>
            </head>
            <body>
                <table>
                <xsl:for-each select="//node()[(name() = 'text:soft-page-break') or (name() = 'table:table' and matches(@table:name, 'Table\d+'))]">
                    <tr>
                        <td><xsl:value-of select="concat('S. ', position())"/></td>
                        <td>
                            <xsl:choose>
                                <xsl:when test="name() = 'text:soft-page-break'">
                                    <xsl:variable name="startElement" select="if(name(parent::element()) = ('table:table', 'text:h'))then(parent::element()/preceding-sibling::element()[1])else(preceding-sibling::element()[1])"/>
                                    <xsl:if test="position() &lt; 3">
                                        <xsl:text>Allgemeines</xsl:text>
                                        <xsl:if test="position() &gt; 1">
                                            <xsl:text>; </xsl:text>
                                        </xsl:if>
                                    </xsl:if>
                                    <xsl:choose>
                                        <xsl:when test="(name(parent::element()) = 'table:table') and preceding-sibling::text:soft-page-break">
                                            <xsl:value-of select="concat('[', normalize-space(parent::table:table/preceding-sibling::text:h[1]), ']')"/>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <xsl:value-of select="papy:scoopHeaders($startElement)"/>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    [<xsl:value-of select="name($startElement)"/>]
                                </xsl:when>
                                <xsl:otherwise>
                                </xsl:otherwise>
                            </xsl:choose>
                        </td>
                        <td style="font-size: smaller; color: light-steel-blue;"><xsl:value-of select="if(name() = 'text:soft-page-break')then('auto page break')else('manual page break')"/></td>
                    </tr>
                </xsl:for-each>
                </table>
            </body>
        </html>
    </xsl:template>
    
    <xsl:function name="papy:scoopHeaders">
        <xsl:param name="currentElement"/>
        
        
        <xsl:variable name="header" select="replace(papy:scoopHeadersR($currentElement), '^; ', '')"/>
        <xsl:variable name="headerTokens" select="tokenize($header, '; ')"/>
        
        <xsl:message select="concat('----', $header, '----')"/>

        <xsl:variable name="header">
            <xsl:for-each select="$headerTokens">
                
                <xsl:choose> <!-- Publication -->
                    <xsl:when test="position() &gt; 1">
                        <xsl:variable name="currentPosition" select="position()"/>
                        <xsl:variable name="previous" select="replace($headerTokens[$currentPosition - 1], ' \d+$', '')"/>
                        <xsl:variable name="current" select="replace(., ' \d+$', '')"/>
                        <xsl:message select="concat($currentPosition, ':', $current, '=', $previous)"/>
                        <xsl:choose>
                            <xsl:when test="$current = $previous">
                                <xsl:value-of select="replace(., '^.+ (\d+)$', '$1')"/>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:value-of select="."/>
                            </xsl:otherwise>
                        </xsl:choose>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="."/>
                    </xsl:otherwise>
                </xsl:choose>
                
                <xsl:choose> <!-- Separator (, or ;) -->
                    <xsl:when test="position() &lt; last()">
                        <xsl:variable name="currentPosition" select="position()"/>
                        <xsl:variable name="next" select="replace($headerTokens[$currentPosition + 1], ' \d+$', '')"/>
                        <xsl:variable name="current" select="replace(., ' \d+$', '')"/>
                        <xsl:choose>
                            <xsl:when test="$current = $next">
                                <xsl:text>, </xsl:text>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:text>; </xsl:text>
                            </xsl:otherwise>
                        </xsl:choose>
                    </xsl:when>
                </xsl:choose>
            </xsl:for-each>
        </xsl:variable>
        <xsl:value-of select="$header"/>
    </xsl:function>

    <xsl:function name="papy:scoopHeadersR">
        <xsl:param name="currentElement"/>
        <xsl:choose>
            <xsl:when test="name($currentElement) = 'text:sequence-decls'"/> <!-- top of office:text section -->
            <xsl:when test="name($currentElement) = 'text:soft-page-break'"/> <!-- soft page break between two adjacent tables -->
            
            <xsl:when test="name($currentElement) = 'table:table' and (count($currentElement//text:soft-page-break))">
                <xsl:value-of select="normalize-space($currentElement/preceding-sibling::text:h[1])"/>
            </xsl:when>
            <xsl:when test="name($currentElement) = 'table:table' and $currentElement//text:soft-page-break"/> <!-- soft page break within a table -->

            <xsl:when test="name($currentElement) = 'text:h'">
                <xsl:choose>
                    <xsl:when test="not($currentElement//text:soft-page-break) and $currentElement/preceding-sibling::element()">
                        <xsl:value-of select="concat(papy:scoopHeadersR($currentElement/preceding-sibling::element()[1]), '; ', normalize-space($currentElement))"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="normalize-space($currentElement)"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="$currentElement/preceding-sibling::element()">
                <xsl:value-of select="papy:scoopHeadersR($currentElement/preceding-sibling::element()[1])"/>
            </xsl:when>
        </xsl:choose>
    </xsl:function>

</xsl:stylesheet>