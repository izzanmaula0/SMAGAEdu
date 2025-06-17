<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math">

    <xsl:output method="text" omit-xml-declaration="yes" indent="no"/>
    
    <!-- Root element handling -->
    <xsl:template match="/">
        <xsl:apply-templates/>
    </xsl:template>
    
    <!-- Match the oMath element -->
    <xsl:template match="m:oMath">
        <xsl:apply-templates/>
    </xsl:template>
    
    <!-- Handle fractions -->
    <xsl:template match="m:f">
        \frac{<xsl:apply-templates select="m:num"/>}{<xsl:apply-templates select="m:den"/>}
    </xsl:template>
    
    <!-- Handle numerator and denominator -->
    <xsl:template match="m:num|m:den">
        <xsl:apply-templates/>
    </xsl:template>
    
    <!-- Handle superscript -->
    <xsl:template match="m:sSup">
        {<xsl:apply-templates select="m:e[1]"/>}^{<xsl:apply-templates select="m:sup"/>}
    </xsl:template>
    
    <!-- Handle subscript -->
    <xsl:template match="m:sSub">
        {<xsl:apply-templates select="m:e[1]"/>}_{<xsl:apply-templates select="m:sub"/>}
    </xsl:template>
    
    <!-- Handle both subscript and superscript -->
    <xsl:template match="m:sSubSup">
        {<xsl:apply-templates select="m:e[1]"/>}_{<xsl:apply-templates select="m:sub"/>}^{<xsl:apply-templates select="m:sup"/>}
    </xsl:template>
    
    <!-- Handle roots -->
    <xsl:template match="m:rad">
        <xsl:choose>
            <xsl:when test="m:degHide='on'">
                \sqrt{<xsl:apply-templates select="m:e"/>}
            </xsl:when>
            <xsl:otherwise>
                \sqrt[<xsl:apply-templates select="m:deg"/>]{<xsl:apply-templates select="m:e"/>}
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <!-- Handle text -->
    <xsl:template match="m:t">
        <xsl:choose>
            <!-- Special characters translation -->
            <xsl:when test=".='π'">
                \pi
            </xsl:when>
            <xsl:when test=".='θ'">
                \theta
            </xsl:when>
            <xsl:when test=".='Σ'">
                \Sigma
            </xsl:when>
            <xsl:when test=".='∫'">
                \int
            </xsl:when>
            <xsl:when test=".='∞'">
                \infty
            </xsl:when>
            <xsl:when test=".='±'">
                \pm
            </xsl:when>
            <xsl:when test=".='≤'">
                \leq
            </xsl:when>
            <xsl:when test=".='≥'">
                \geq
            </xsl:when>
            <xsl:when test=".='≠'">
                \neq
            </xsl:when>
            <xsl:when test=".='×'">
                \times
            </xsl:when>
            <xsl:when test=".='÷'">
                \div
            </xsl:when>
            <xsl:when test=".='→'">
                \rightarrow
            </xsl:when>
            <xsl:when test=".='←'">
                \leftarrow
            </xsl:when>
            <xsl:when test=".='α'">
                \alpha
            </xsl:when>
            <xsl:when test=".='β'">
                \beta
            </xsl:when>
            <xsl:when test=".='γ'">
                \gamma
            </xsl:when>
            <xsl:when test=".='δ'">
                \delta
            </xsl:when>
            <xsl:when test=".='ε'">
                \epsilon
            </xsl:when>
            <xsl:when test=".='λ'">
                \lambda
            </xsl:when>
            <xsl:when test=".='μ'">
                \mu
            </xsl:when>
            <xsl:when test=".='Δ'">
                \Delta
            </xsl:when>
            <xsl:when test=".='∂'">
                \partial
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="."/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <!-- Handle function applications like sin, cos, etc. -->
    <xsl:template match="m:func">
        <xsl:apply-templates select="m:funcPr"/>
        <xsl:apply-templates select="m:fName"/><xsl:apply-templates select="m:e"/>
    </xsl:template>
    
    <xsl:template match="m:fName">
        <xsl:apply-templates/>
    </xsl:template>
    
    <!-- Handle limits -->
    <xsl:template match="m:limLow">
        \underset{<xsl:apply-templates select="m:lim"/>}{<xsl:apply-templates select="m:e[1]"/>}
    </xsl:template>
    
    <xsl:template match="m:limUpp">
        \overset{<xsl:apply-templates select="m:lim"/>}{<xsl:apply-templates select="m:e[1]"/>}
    </xsl:template>
    
    <!-- Handle integrals with limits -->
    <xsl:template match="m:nary">
        <xsl:variable name="operator">
            <xsl:choose>
                <xsl:when test="m:naryPr/m:chr='∫'">\int</xsl:when>
                <xsl:when test="m:naryPr/m:chr='∑'">\sum</xsl:when>
                <xsl:when test="m:naryPr/m:chr='∏'">\prod</xsl:when>
                <xsl:otherwise>\int</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        
        <xsl:value-of select="$operator"/>
        <xsl:if test="m:sub">
            _{<xsl:apply-templates select="m:sub"/>}
        </xsl:if>
        <xsl:if test="m:sup">
            ^{<xsl:apply-templates select="m:sup"/>}
        </xsl:if>
        <xsl:apply-templates select="m:e"/>
    </xsl:template>
    
    <!-- Handle grouping parentheses -->
    <xsl:template match="m:d">
        <xsl:variable name="open">
            <xsl:choose>
                <xsl:when test="m:dPr/m:begChr='('">
                    (
                </xsl:when>
                <xsl:when test="m:dPr/m:begChr='['">
                    [
                </xsl:when>
                <xsl:when test="m:dPr/m:begChr='{'">
                    \{
                </xsl:when>
                <xsl:when test="m:dPr/m:begChr='|'">
                    |
                </xsl:when>
                <xsl:otherwise>
                    (
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        
        <xsl:variable name="close">
            <xsl:choose>
                <xsl:when test="m:dPr/m:endChr=')'">
                    )
                </xsl:when>
                <xsl:when test="m:dPr/m:endChr=']'">
                    ]
                </xsl:when>
                <xsl:when test="m:dPr/m:endChr='}'">
                    \}
                </xsl:when>
                <xsl:when test="m:dPr/m:endChr='|'">
                    |
                </xsl:when>
                <xsl:otherwise>
                    )
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        
        <xsl:value-of select="$open"/><xsl:apply-templates select="m:e"/><xsl:value-of select="$close"/>
    </xsl:template>
    
    <!-- Handle rows and columns in matrices -->
    <xsl:template match="m:m">
        \begin{pmatrix}
        <xsl:for-each select="m:mr">
            <xsl:for-each select="m:e">
                <xsl:apply-templates/>
                <xsl:if test="position() != last()"> &amp; </xsl:if>
            </xsl:for-each>
            <xsl:if test="position() != last()"> \\ </xsl:if>
        </xsl:for-each>
        \end{pmatrix}
    </xsl:template>
    
    <!-- Basic element template -->
    <xsl:template match="m:e">
        <xsl:apply-templates/>
    </xsl:template>
    
    <!-- Handle runs with r elements -->
    <xsl:template match="m:r">
        <xsl:apply-templates/>
    </xsl:template>
    
    <!-- Default template for any unmatched elements -->
    <xsl:template match="*">
        <xsl:apply-templates/>
    </xsl:template>
</xsl:stylesheet>