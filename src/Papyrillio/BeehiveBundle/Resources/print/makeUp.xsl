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
    java -Xms512m -Xmx1536m net.sf.saxon.Transform -o:content.xml -s:postscript/content_release.xml -xsl:makeUp.xsl
-->
    <xsl:output method="xml" media-type="text/xml" />
    
    <xsl:template match="table:table[not(@table:name='Allgemeines')]//table:table-cell[text:p/@text:style-name = 'blTableContentNumber']">
        <xsl:variable name="number" select="text:p"/>
        <xsl:variable name="line"   select="../table:table-cell/text:p[@text:style-name = 'blTableContentLine']"/>
        <xsl:choose>
            <xsl:when test="(string(papy:getCellValue($number)) and string(papy:getCellValue($line))) or (not(string(papy:getCellValue($number))) and not(string(papy:getCellValue($line))))">
                <xsl:copy>
                    <xsl:apply-templates select="@*|node()"/>
                </xsl:copy>
            </xsl:when>
            <xsl:when test="not(string(papy:getCellValue($number)))">
                <table:covered-table-cell/>
            </xsl:when>
            <xsl:when test="not(string(papy:getCellValue($line)))">
                <xsl:copy>
                    <xsl:copy-of select="@*"/>
                    <xsl:attribute name="table:number-columns-spanned" select="2"/>
                    <xsl:apply-templates select="node()"/>
                </xsl:copy>
            </xsl:when>
        </xsl:choose>
    </xsl:template>
    
    <xsl:template match="table:table[not(@table:name='Allgemeines')]//table:table-cell[text:p/@text:style-name = 'blTableContentLine']">
        <xsl:variable name="number" select="../table:table-cell/text:p[@text:style-name = 'blTableContentNumber']"/>
        <xsl:variable name="line"   select="text:p"/>
        <xsl:choose>
            <xsl:when test="(string(papy:getCellValue($number)) and string(papy:getCellValue($line))) or (not(string(papy:getCellValue($number))) and not(string(papy:getCellValue($line))))">
                <xsl:copy>
                    <xsl:apply-templates select="@*|node()"/>
                </xsl:copy>
            </xsl:when>
            <xsl:when test="not(string(papy:getCellValue($number)))">
                <xsl:copy>
                    <xsl:copy-of select="@*"/>
                    <xsl:attribute name="table:number-columns-spanned" select="2"/>
                    <xsl:apply-templates select="node()"/>
                </xsl:copy>
            </xsl:when>
            <xsl:when test="not(string(papy:getCellValue($line)))">
                <table:covered-table-cell/>
            </xsl:when>
        </xsl:choose>
    </xsl:template>
    
    <xsl:template match="table:table[not(@table:name='Allgemeines')]//table:table-cell/text:p[@text:style-name = ('blTableContentPage', 'blTableContentNumber', 'blTableContentLine')]">
        <text:p>
            <xsl:copy-of select="@*"/>
            <xsl:value-of select="papy:getCellValue(.)"/>
        </text:p>
    </xsl:template>
    
    <xsl:function name="papy:getCellValue">
        <xsl:param name="p"/>
        <xsl:variable name="styleName" select="$p/@text:style-name"/>
        <xsl:variable name="current" select="string($p)"/>
        <xsl:variable name="previous" select="string($p/../../preceding-sibling::table:table-row[1]/table:table-cell/text:p[@text:style-name = $styleName])"/>
        <xsl:variable name="precedingElement" select="string($p/../../preceding-sibling::element()[1]/name())"/>
        <xsl:message select="concat($styleName, ': ', $current, '/', $previous, '[', $precedingElement ,']')"/>
        <xsl:if test="not($current = $previous) or $precedingElement = 'text:soft-page-break'">
            <xsl:value-of select="$p"/>
        </xsl:if>
    </xsl:function>

    <!-- COPY -->
    <xsl:template match="@* | node()">
        <xsl:copy>
            <xsl:apply-templates select="@*|node()"/>
        </xsl:copy>
    </xsl:template>

</xsl:stylesheet>