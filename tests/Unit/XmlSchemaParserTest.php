<?php

declare(strict_types=1);

use Youri\vandenBogert\Software\ParserCore\Exceptions\ParseException;
use Youri\vandenBogert\Software\ParserXmlSchema\XmlSchemaParser;

describe('XmlSchemaParser', function () {
    beforeEach(function () {
        $this->parser = new XmlSchemaParser();
    });

    it('can parse XML Schema content', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
           targetNamespace="http://www.w3.org/2001/XMLSchema">
</xs:schema>';

        expect($this->parser->canParse($content))->toBeTrue();
    });

    it('cannot parse non-XML Schema content', function () {
        $content = '<?xml version="1.0"?><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"></rdf:RDF>';
        $plainText = 'This is not XML Schema';

        expect($this->parser->canParse($content))->toBeFalse();
        expect($this->parser->canParse($plainText))->toBeFalse();
    });

    it('detects XML Schema namespace', function () {
        $xmlSchemaContent = '<?xml version="1.0"?><xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"></xs:schema>';
        $xmlSchemaTargetNamespace = '<?xml version="1.0"?><schema targetNamespace="http://www.w3.org/2001/XMLSchema"></schema>';

        expect($this->parser->canParse($xmlSchemaContent))->toBeTrue();
        expect($this->parser->canParse($xmlSchemaTargetNamespace))->toBeTrue();
    });

    it('returns correct supported formats', function () {
        $formats = $this->parser->getSupportedFormats();

        expect($formats)->toContain('xml_schema');
        expect($formats)->toContain('xsd');
    });

    it('parses basic XML Schema document', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
           targetNamespace="http://www.w3.org/2001/XMLSchema">
</xs:schema>';

        $result = $this->parser->parse($content);

        expect($result)->toBeInstanceOf(\Youri\vandenBogert\Software\ParserCore\ValueObjects\ParsedOntology::class);

        // Check metadata
        expect($result->metadata['format'])->toBe('xml_schema');
        expect($result->metadata['parser'])->toBe('xml_schema');
        expect($result->metadata['namespace'])->toBe('http://www.w3.org/2001/XMLSchema#');

        // Check prefixes
        expect($result->prefixes)->toHaveKey('xsd');
        expect($result->prefixes['xsd'])->toBe('http://www.w3.org/2001/XMLSchema#');
        expect($result->prefixes)->toHaveKey('xs');
        expect($result->prefixes['xs'])->toBe('http://www.w3.org/2001/XMLSchema#');

        // Should have all standard XSD datatypes as classes
        expect($result->classes)->not->toBeEmpty();
        expect(count($result->classes))->toBeGreaterThan(40); // There are 44 XSD datatypes defined

        // No properties in XML Schema
        expect($result->properties)->toBeEmpty();
        expect($result->shapes)->toBeEmpty();
    });

    it('includes all standard XSD datatypes as classes', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
           targetNamespace="http://www.w3.org/2001/XMLSchema">
</xs:schema>';

        $result = $this->parser->parse($content);

        $classUris = array_column($result->classes, 'uri');

        // Check primitive types
        expect($classUris)->toContain('http://www.w3.org/2001/XMLSchema#string');
        expect($classUris)->toContain('http://www.w3.org/2001/XMLSchema#boolean');
        expect($classUris)->toContain('http://www.w3.org/2001/XMLSchema#decimal');
        expect($classUris)->toContain('http://www.w3.org/2001/XMLSchema#float');
        expect($classUris)->toContain('http://www.w3.org/2001/XMLSchema#double');
        expect($classUris)->toContain('http://www.w3.org/2001/XMLSchema#dateTime');
        expect($classUris)->toContain('http://www.w3.org/2001/XMLSchema#date');
        expect($classUris)->toContain('http://www.w3.org/2001/XMLSchema#time');
        expect($classUris)->toContain('http://www.w3.org/2001/XMLSchema#anyURI');

        // Check derived types
        expect($classUris)->toContain('http://www.w3.org/2001/XMLSchema#integer');
        expect($classUris)->toContain('http://www.w3.org/2001/XMLSchema#normalizedString');
        expect($classUris)->toContain('http://www.w3.org/2001/XMLSchema#token');
        expect($classUris)->toContain('http://www.w3.org/2001/XMLSchema#int');
        expect($classUris)->toContain('http://www.w3.org/2001/XMLSchema#long');
        expect($classUris)->toContain('http://www.w3.org/2001/XMLSchema#short');
        expect($classUris)->toContain('http://www.w3.org/2001/XMLSchema#byte');
        expect($classUris)->toContain('http://www.w3.org/2001/XMLSchema#positiveInteger');
        expect($classUris)->toContain('http://www.w3.org/2001/XMLSchema#nonNegativeInteger');
    });

    it('maintains correct datatype hierarchy', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
           targetNamespace="http://www.w3.org/2001/XMLSchema">
</xs:schema>';

        $result = $this->parser->parse($content);

        // Check hierarchy: integer -> decimal (no parent)
        $integerClass = $result->classes['http://www.w3.org/2001/XMLSchema#integer'];
        expect($integerClass['parent_classes'])->toContain('http://www.w3.org/2001/XMLSchema#decimal');

        // Check hierarchy: int -> long -> integer -> decimal
        $intClass = $result->classes['http://www.w3.org/2001/XMLSchema#int'];
        expect($intClass['parent_classes'])->toContain('http://www.w3.org/2001/XMLSchema#long');

        $longClass = $result->classes['http://www.w3.org/2001/XMLSchema#long'];
        expect($longClass['parent_classes'])->toContain('http://www.w3.org/2001/XMLSchema#integer');

        // Check hierarchy: token -> normalizedString -> string (no parent)
        $tokenClass = $result->classes['http://www.w3.org/2001/XMLSchema#token'];
        expect($tokenClass['parent_classes'])->toContain('http://www.w3.org/2001/XMLSchema#normalizedString');

        $normalizedStringClass = $result->classes['http://www.w3.org/2001/XMLSchema#normalizedString'];
        expect($normalizedStringClass['parent_classes'])->toContain('http://www.w3.org/2001/XMLSchema#string');

        // Primitive types should have no parents
        $stringClass = $result->classes['http://www.w3.org/2001/XMLSchema#string'];
        expect($stringClass['parent_classes'])->toBeEmpty();

        $decimalClass = $result->classes['http://www.w3.org/2001/XMLSchema#decimal'];
        expect($decimalClass['parent_classes'])->toBeEmpty();
    });

    it('categorizes datatypes correctly', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
           targetNamespace="http://www.w3.org/2001/XMLSchema">
</xs:schema>';

        $result = $this->parser->parse($content);

        // Check string category
        $stringClass = $result->classes['http://www.w3.org/2001/XMLSchema#string'];
        expect($stringClass['metadata']['category'])->toBe('string');

        $tokenClass = $result->classes['http://www.w3.org/2001/XMLSchema#token'];
        expect($tokenClass['metadata']['category'])->toBe('string');

        // Check numeric category
        $decimalClass = $result->classes['http://www.w3.org/2001/XMLSchema#decimal'];
        expect($decimalClass['metadata']['category'])->toBe('numeric');

        $integerClass = $result->classes['http://www.w3.org/2001/XMLSchema#integer'];
        expect($integerClass['metadata']['category'])->toBe('numeric');

        // Check temporal category
        $dateTimeClass = $result->classes['http://www.w3.org/2001/XMLSchema#dateTime'];
        expect($dateTimeClass['metadata']['category'])->toBe('temporal');

        $dateClass = $result->classes['http://www.w3.org/2001/XMLSchema#date'];
        expect($dateClass['metadata']['category'])->toBe('temporal');

        // Check binary category
        $hexBinaryClass = $result->classes['http://www.w3.org/2001/XMLSchema#hexBinary'];
        expect($hexBinaryClass['metadata']['category'])->toBe('binary');

        // Check logical category
        $booleanClass = $result->classes['http://www.w3.org/2001/XMLSchema#boolean'];
        expect($booleanClass['metadata']['category'])->toBe('logical');
    });

    it('marks primitive vs derived types correctly', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
           targetNamespace="http://www.w3.org/2001/XMLSchema">
</xs:schema>';

        $result = $this->parser->parse($content);

        // Primitive types
        $stringClass = $result->classes['http://www.w3.org/2001/XMLSchema#string'];
        expect($stringClass['metadata']['is_primitive'])->toBeTrue();

        $decimalClass = $result->classes['http://www.w3.org/2001/XMLSchema#decimal'];
        expect($decimalClass['metadata']['is_primitive'])->toBeTrue();

        $booleanClass = $result->classes['http://www.w3.org/2001/XMLSchema#boolean'];
        expect($booleanClass['metadata']['is_primitive'])->toBeTrue();

        // Derived types
        $integerClass = $result->classes['http://www.w3.org/2001/XMLSchema#integer'];
        expect($integerClass['metadata']['is_primitive'])->toBeFalse();

        $tokenClass = $result->classes['http://www.w3.org/2001/XMLSchema#token'];
        expect($tokenClass['metadata']['is_primitive'])->toBeFalse();

        $intClass = $result->classes['http://www.w3.org/2001/XMLSchema#int'];
        expect($intClass['metadata']['is_primitive'])->toBeFalse();
    });

    it('parses XML Schema with custom types', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
           targetNamespace="http://www.w3.org/2001/XMLSchema">

    <xs:simpleType name="ProductCode">
        <xs:annotation>
            <xs:documentation>A unique product code</xs:documentation>
        </xs:annotation>
        <xs:restriction base="xs:string">
            <xs:pattern value="[A-Z]{3}[0-9]{4}"/>
        </xs:restriction>
    </xs:simpleType>

    <xs:complexType name="ProductInfo">
        <xs:annotation>
            <xs:documentation>Product information structure</xs:documentation>
        </xs:annotation>
        <xs:sequence>
            <xs:element name="code" type="ProductCode"/>
            <xs:element name="name" type="xs:string"/>
        </xs:sequence>
    </xs:complexType>

</xs:schema>';

        $result = $this->parser->parse($content);

        // Should include standard XSD types plus custom types
        $classUris = array_column($result->classes, 'uri');

        expect($classUris)->toContain('http://www.w3.org/2001/XMLSchema#ProductCode');
        expect($classUris)->toContain('http://www.w3.org/2001/XMLSchema#ProductInfo');

        // Check custom simple type
        $productCodeClass = $result->classes['http://www.w3.org/2001/XMLSchema#ProductCode'];
        expect($productCodeClass['label'])->toBe('ProductCode');
        expect($productCodeClass['description'])->toBe('A unique product code');
        expect($productCodeClass['metadata']['category'])->toBe('schema_defined');
        expect($productCodeClass['metadata']['type_kind'])->toBe('simpleType');

        // Check custom complex type
        $productInfoClass = $result->classes['http://www.w3.org/2001/XMLSchema#ProductInfo'];
        expect($productInfoClass['label'])->toBe('ProductInfo');
        expect($productInfoClass['description'])->toBe('Product information structure');
        expect($productInfoClass['metadata']['category'])->toBe('schema_defined');
        expect($productInfoClass['metadata']['type_kind'])->toBe('complexType');
    });

    it('handles XML Schema without custom types', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
           targetNamespace="http://www.w3.org/2001/XMLSchema">
    <!-- Just standard schema without custom types -->
</xs:schema>';

        $result = $this->parser->parse($content);

        // Should only include standard XSD datatypes
        $customTypes = array_filter($result->classes, function ($class) {
            return $class['metadata']['category'] === 'schema_defined';
        });

        expect($customTypes)->toBeEmpty();
    });

    it('tests private helper methods', function () {
        $reflection = new ReflectionClass($this->parser);

        // Test isPrimitiveType method
        $isPrimitiveMethod = $reflection->getMethod('isPrimitiveType');
        $isPrimitiveMethod->setAccessible(true);

        expect($isPrimitiveMethod->invoke($this->parser, 'string'))->toBeTrue();
        expect($isPrimitiveMethod->invoke($this->parser, 'decimal'))->toBeTrue();
        expect($isPrimitiveMethod->invoke($this->parser, 'boolean'))->toBeTrue();
        expect($isPrimitiveMethod->invoke($this->parser, 'integer'))->toBeFalse(); // derived from decimal
        expect($isPrimitiveMethod->invoke($this->parser, 'token'))->toBeFalse(); // derived from normalizedString

        // Test getDatatypeCategory method
        $getCategoryMethod = $reflection->getMethod('getDatatypeCategory');
        $getCategoryMethod->setAccessible(true);

        expect($getCategoryMethod->invoke($this->parser, 'string'))->toBe('string');
        expect($getCategoryMethod->invoke($this->parser, 'decimal'))->toBe('numeric');
        expect($getCategoryMethod->invoke($this->parser, 'dateTime'))->toBe('temporal');
        expect($getCategoryMethod->invoke($this->parser, 'hexBinary'))->toBe('binary');
        expect($getCategoryMethod->invoke($this->parser, 'boolean'))->toBe('logical');
        expect($getCategoryMethod->invoke($this->parser, 'anyURI'))->toBe('other');
    });

    it('throws exception on invalid XML content', function () {
        expect(fn () => $this->parser->parse('invalid xml content'))
            ->toThrow(ParseException::class, 'XML Schema parsing failed');
    });

    it('throws exception on malformed XML', function () {
        $malformedXml = '<?xml version="1.0"?><xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"><unclosed_tag></xs:schema>';

        expect(fn () => $this->parser->parse($malformedXml))
            ->toThrow(ParseException::class);
    });

    it('has correct metadata for all datatype classes', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
           targetNamespace="http://www.w3.org/2001/XMLSchema">
</xs:schema>';

        $result = $this->parser->parse($content);

        foreach ($result->classes as $class) {
            // All classes should have required fields
            expect($class)->toHaveKeys(['uri', 'label', 'description', 'parent_classes', 'metadata']);

            // All URIs should start with XML Schema namespace
            expect($class['uri'])->toStartWith('http://www.w3.org/2001/XMLSchema#');

            // All should have metadata source
            expect($class['metadata']['source'])->toBe('xml_schema');

            // Built-in types should have category and is_primitive fields
            expect($class['metadata'])->toHaveKey('category');
            expect($class['metadata'])->toHaveKey('is_primitive');
        }
    });

    // =========================================================================
    // Story 15.1: XPath Prefix Detection
    // =========================================================================

    it('parses XSD with xsd: prefix and extracts custom types', function () {
        $content = '<?xml version="1.0"?>
<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema"
            targetNamespace="http://www.w3.org/2001/XMLSchema">
    <xsd:simpleType name="XsdPrefixType">
        <xsd:annotation>
            <xsd:documentation>Type using xsd: prefix</xsd:documentation>
        </xsd:annotation>
        <xsd:restriction base="xsd:string"/>
    </xsd:simpleType>
</xsd:schema>';

        $result = $this->parser->parse($content);

        $class = $result->classes['http://www.w3.org/2001/XMLSchema#XsdPrefixType'] ?? null;
        expect($class)->not->toBeNull();
        expect($class['label'])->toBe('XsdPrefixType');
        expect($class['description'])->toBe('Type using xsd: prefix');
    });

    it('parses XSD with default namespace (no prefix) and extracts custom types', function () {
        $content = '<?xml version="1.0"?>
<schema xmlns="http://www.w3.org/2001/XMLSchema"
        targetNamespace="http://www.w3.org/2001/XMLSchema">
    <simpleType name="DefaultNsType">
        <annotation>
            <documentation>Type using default namespace</documentation>
        </annotation>
        <restriction base="string"/>
    </simpleType>
</schema>';

        $result = $this->parser->parse($content);

        $class = $result->classes['http://www.w3.org/2001/XMLSchema#DefaultNsType'] ?? null;
        expect($class)->not->toBeNull();
        expect($class['label'])->toBe('DefaultNsType');
        expect($class['description'])->toBe('Type using default namespace');
    });

    it('extracts documentation from xsd: prefix documents', function () {
        $content = '<?xml version="1.0"?>
<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema">
    <xsd:simpleType name="DocType">
        <xsd:annotation>
            <xsd:documentation>Documentation from xsd prefix</xsd:documentation>
        </xsd:annotation>
        <xsd:restriction base="xsd:string"/>
    </xsd:simpleType>
</xsd:schema>';

        $result = $this->parser->parse($content);

        $class = $result->classes['http://www.w3.org/2001/XMLSchema#DocType'] ?? null;
        expect($class)->not->toBeNull();
        expect($class['description'])->toBe('Documentation from xsd prefix');
    });

    // =========================================================================
    // Story 15.1: Restriction Facet Extraction
    // =========================================================================

    it('extracts minInclusive facet from restriction', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="PositiveAge">
        <xs:restriction base="xs:integer">
            <xs:minInclusive value="0"/>
        </xs:restriction>
    </xs:simpleType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#PositiveAge'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['metadata'])->toHaveKey('facets');
        expect($class['metadata']['facets']['minInclusive'])->toBe('0');
    });

    it('extracts maxLength facet from restriction', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="ShortString">
        <xs:restriction base="xs:string">
            <xs:maxLength value="50"/>
        </xs:restriction>
    </xs:simpleType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#ShortString'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['metadata']['facets']['maxLength'])->toBe('50');
    });

    it('extracts pattern facet from restriction', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="ZipCode">
        <xs:restriction base="xs:string">
            <xs:pattern value="[0-9]{5}(-[0-9]{4})?"/>
        </xs:restriction>
    </xs:simpleType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#ZipCode'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['metadata']['facets']['pattern'])->toBe('[0-9]{5}(-[0-9]{4})?');
    });

    it('extracts enumeration facet with multiple values as array', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="Color">
        <xs:restriction base="xs:string">
            <xs:enumeration value="red"/>
            <xs:enumeration value="green"/>
            <xs:enumeration value="blue"/>
        </xs:restriction>
    </xs:simpleType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#Color'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['metadata']['facets']['enumeration'])->toBe(['red', 'green', 'blue']);
    });

    it('extracts whiteSpace facet from restriction', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="CollapsedText">
        <xs:restriction base="xs:string">
            <xs:whiteSpace value="collapse"/>
        </xs:restriction>
    </xs:simpleType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#CollapsedText'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['metadata']['facets']['whiteSpace'])->toBe('collapse');
    });

    it('extracts totalDigits and fractionDigits facets', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="Price">
        <xs:restriction base="xs:decimal">
            <xs:totalDigits value="10"/>
            <xs:fractionDigits value="2"/>
        </xs:restriction>
    </xs:simpleType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#Price'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['metadata']['facets']['totalDigits'])->toBe('10');
        expect($class['metadata']['facets']['fractionDigits'])->toBe('2');
    });

    it('extracts minExclusive and maxExclusive facets', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="StrictRange">
        <xs:restriction base="xs:integer">
            <xs:minExclusive value="0"/>
            <xs:maxExclusive value="100"/>
        </xs:restriction>
    </xs:simpleType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#StrictRange'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['metadata']['facets']['minExclusive'])->toBe('0');
        expect($class['metadata']['facets']['maxExclusive'])->toBe('100');
    });

    it('extracts maxInclusive facet from restriction', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="Percentage">
        <xs:restriction base="xs:integer">
            <xs:minInclusive value="0"/>
            <xs:maxInclusive value="100"/>
        </xs:restriction>
    </xs:simpleType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#Percentage'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['metadata']['facets']['maxInclusive'])->toBe('100');
        expect($class['metadata']['facets']['minInclusive'])->toBe('0');
    });

    it('extracts length and minLength facets', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="FixedCode">
        <xs:restriction base="xs:string">
            <xs:length value="5"/>
        </xs:restriction>
    </xs:simpleType>
    <xs:simpleType name="MinString">
        <xs:restriction base="xs:string">
            <xs:minLength value="3"/>
        </xs:restriction>
    </xs:simpleType>
</xs:schema>';

        $result = $this->parser->parse($content);

        $fixedCode = $result->classes['http://www.w3.org/2001/XMLSchema#FixedCode'] ?? null;
        expect($fixedCode)->not->toBeNull();
        expect($fixedCode['metadata']['facets']['length'])->toBe('5');

        $minString = $result->classes['http://www.w3.org/2001/XMLSchema#MinString'] ?? null;
        expect($minString)->not->toBeNull();
        expect($minString['metadata']['facets']['minLength'])->toBe('3');
    });

    it('extracts combined facets on same type', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="BoundedString">
        <xs:restriction base="xs:string">
            <xs:minLength value="1"/>
            <xs:maxLength value="100"/>
            <xs:pattern value="[A-Za-z0-9]+"/>
            <xs:whiteSpace value="collapse"/>
        </xs:restriction>
    </xs:simpleType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#BoundedString'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['metadata']['facets'])->toHaveKey('minLength');
        expect($class['metadata']['facets'])->toHaveKey('maxLength');
        expect($class['metadata']['facets'])->toHaveKey('pattern');
        expect($class['metadata']['facets'])->toHaveKey('whiteSpace');
        expect($class['metadata']['facets']['minLength'])->toBe('1');
        expect($class['metadata']['facets']['maxLength'])->toBe('100');
        expect($class['metadata']['facets']['pattern'])->toBe('[A-Za-z0-9]+');
        expect($class['metadata']['facets']['whiteSpace'])->toBe('collapse');
    });

    it('extracts facets from xsd: prefix documents', function () {
        $content = '<?xml version="1.0"?>
<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema">
    <xsd:simpleType name="XsdFacets">
        <xsd:restriction base="xsd:integer">
            <xsd:minInclusive value="1"/>
            <xsd:maxInclusive value="999"/>
        </xsd:restriction>
    </xsd:simpleType>
</xsd:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#XsdFacets'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['metadata']['facets']['minInclusive'])->toBe('1');
        expect($class['metadata']['facets']['maxInclusive'])->toBe('999');
    });

    // =========================================================================
    // Story 15.2: Simple Type Derivation Methods
    // =========================================================================

    it('resolves restriction base type as parent class URI', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="ShortString">
        <xs:restriction base="xs:string">
            <xs:maxLength value="50"/>
        </xs:restriction>
    </xs:simpleType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#ShortString'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#string']);
        expect($class['metadata']['derivation_method'])->toBe('restriction');
    });

    it('resolves restriction base type to full XSD URI', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="BoundedInt">
        <xs:restriction base="xs:integer">
            <xs:minInclusive value="0"/>
        </xs:restriction>
    </xs:simpleType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#BoundedInt'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['parent_classes'])->toContain('http://www.w3.org/2001/XMLSchema#integer');
    });

    it('records derivation method as restriction for restricted types', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="RestrictedDecimal">
        <xs:restriction base="xs:decimal">
            <xs:totalDigits value="5"/>
        </xs:restriction>
    </xs:simpleType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#RestrictedDecimal'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['metadata']['derivation_method'])->toBe('restriction');
        expect($class['metadata']['base_type'])->toBe('http://www.w3.org/2001/XMLSchema#decimal');
    });

    it('resolves custom type as restriction base', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
           xmlns:tns="http://example.org/types"
           targetNamespace="http://example.org/types">
    <xs:simpleType name="BaseType">
        <xs:restriction base="xs:string"/>
    </xs:simpleType>
    <xs:simpleType name="DerivedType">
        <xs:restriction base="tns:BaseType">
            <xs:maxLength value="10"/>
        </xs:restriction>
    </xs:simpleType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $derived = $result->classes['http://www.w3.org/2001/XMLSchema#DerivedType'] ?? null;

        expect($derived)->not->toBeNull();
        expect($derived['metadata']['derivation_method'])->toBe('restriction');
        expect($derived['metadata']['base_type'])->toBe('http://example.org/types#BaseType');
    });

    it('extracts list derivation with itemType', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="IntegerList">
        <xs:list itemType="xs:integer"/>
    </xs:simpleType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#IntegerList'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['metadata']['derivation_method'])->toBe('list');
        expect($class['metadata']['list_item_type'])->toBe('http://www.w3.org/2001/XMLSchema#integer');
    });

    it('extracts list itemType resolving to full URI', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="StringList">
        <xs:list itemType="xs:string"/>
    </xs:simpleType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#StringList'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['metadata']['list_item_type'])->toBe('http://www.w3.org/2001/XMLSchema#string');
    });

    it('extracts union derivation with memberTypes', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="StringOrInt">
        <xs:union memberTypes="xs:string xs:integer"/>
    </xs:simpleType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#StringOrInt'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['metadata']['derivation_method'])->toBe('union');
        expect($class['metadata']['union_member_types'])->toBe([
            'http://www.w3.org/2001/XMLSchema#string',
            'http://www.w3.org/2001/XMLSchema#integer',
        ]);
    });

    it('extracts union with multiple member types all resolved to full URIs', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="MultiUnion">
        <xs:union memberTypes="xs:string xs:integer xs:boolean xs:date"/>
    </xs:simpleType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#MultiUnion'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['metadata']['union_member_types'])->toBe([
            'http://www.w3.org/2001/XMLSchema#string',
            'http://www.w3.org/2001/XMLSchema#integer',
            'http://www.w3.org/2001/XMLSchema#boolean',
            'http://www.w3.org/2001/XMLSchema#date',
        ]);
    });

    it('handles chained restriction of a list type', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="IntList">
        <xs:list itemType="xs:integer"/>
    </xs:simpleType>
    <xs:simpleType name="ShortIntList">
        <xs:restriction base="xs:NMTOKENS">
            <xs:maxLength value="5"/>
        </xs:restriction>
    </xs:simpleType>
</xs:schema>';

        $result = $this->parser->parse($content);

        $intList = $result->classes['http://www.w3.org/2001/XMLSchema#IntList'] ?? null;
        expect($intList)->not->toBeNull();
        expect($intList['metadata']['derivation_method'])->toBe('list');

        $shortIntList = $result->classes['http://www.w3.org/2001/XMLSchema#ShortIntList'] ?? null;
        expect($shortIntList)->not->toBeNull();
        expect($shortIntList['metadata']['derivation_method'])->toBe('restriction');
    });

    it('resolves base type from xsd: prefix documents', function () {
        $content = '<?xml version="1.0"?>
<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema">
    <xsd:simpleType name="XsdDerived">
        <xsd:restriction base="xsd:string">
            <xsd:maxLength value="20"/>
        </xsd:restriction>
    </xsd:simpleType>
</xsd:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#XsdDerived'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#string']);
        expect($class['metadata']['derivation_method'])->toBe('restriction');
        expect($class['metadata']['base_type'])->toBe('http://www.w3.org/2001/XMLSchema#string');
    });

    // =========================================================================
    // Story 15.3: Complex Type Structure Extraction
    // =========================================================================

    it('extracts xs:sequence compositor with child elements', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:complexType name="PersonType">
        <xs:sequence>
            <xs:element name="firstName" type="xs:string"/>
            <xs:element name="lastName" type="xs:string"/>
            <xs:element name="age" type="xs:integer"/>
        </xs:sequence>
    </xs:complexType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#PersonType'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['metadata'])->toHaveKey('compositor');
        expect($class['metadata']['compositor']['type'])->toBe('sequence');
        expect($class['metadata']['compositor']['elements'])->toHaveCount(3);

        $elements = $class['metadata']['compositor']['elements'];
        expect($elements[0]['name'])->toBe('firstName');
        expect($elements[0]['type'])->toBe('http://www.w3.org/2001/XMLSchema#string');
        expect($elements[1]['name'])->toBe('lastName');
        expect($elements[2]['name'])->toBe('age');
        expect($elements[2]['type'])->toBe('http://www.w3.org/2001/XMLSchema#integer');
    });

    it('extracts xs:choice compositor with alternative elements', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:complexType name="ContactType">
        <xs:choice>
            <xs:element name="email" type="xs:string"/>
            <xs:element name="phone" type="xs:string"/>
        </xs:choice>
    </xs:complexType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#ContactType'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['metadata']['compositor']['type'])->toBe('choice');
        expect($class['metadata']['compositor']['elements'])->toHaveCount(2);
        expect($class['metadata']['compositor']['elements'][0]['name'])->toBe('email');
        expect($class['metadata']['compositor']['elements'][1]['name'])->toBe('phone');
    });

    it('extracts xs:all compositor with unordered elements', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:complexType name="ConfigType">
        <xs:all>
            <xs:element name="width" type="xs:integer"/>
            <xs:element name="height" type="xs:integer"/>
        </xs:all>
    </xs:complexType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#ConfigType'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['metadata']['compositor']['type'])->toBe('all');
        expect($class['metadata']['compositor']['elements'])->toHaveCount(2);
    });

    it('extracts minOccurs and maxOccurs from child elements', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:complexType name="ListType">
        <xs:sequence>
            <xs:element name="required" type="xs:string"/>
            <xs:element name="optional" type="xs:string" minOccurs="0"/>
            <xs:element name="many" type="xs:string" minOccurs="0" maxOccurs="unbounded"/>
            <xs:element name="exactly3" type="xs:string" minOccurs="3" maxOccurs="3"/>
        </xs:sequence>
    </xs:complexType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#ListType'] ?? null;

        expect($class)->not->toBeNull();
        $elements = $class['metadata']['compositor']['elements'];

        expect($elements[0]['minOccurs'])->toBe(1);
        expect($elements[0]['maxOccurs'])->toBe(1);
        expect($elements[1]['minOccurs'])->toBe(0);
        expect($elements[1]['maxOccurs'])->toBe(1);
        expect($elements[2]['minOccurs'])->toBe(0);
        expect($elements[2]['maxOccurs'])->toBe('unbounded');
        expect($elements[3]['minOccurs'])->toBe(3);
        expect($elements[3]['maxOccurs'])->toBe(3);
    });

    it('extracts complexContent with extension base type', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
           xmlns:tns="http://example.org/types"
           targetNamespace="http://example.org/types">
    <xs:complexType name="BaseType">
        <xs:sequence>
            <xs:element name="id" type="xs:integer"/>
        </xs:sequence>
    </xs:complexType>
    <xs:complexType name="ExtendedType">
        <xs:complexContent>
            <xs:extension base="tns:BaseType">
                <xs:sequence>
                    <xs:element name="name" type="xs:string"/>
                </xs:sequence>
            </xs:extension>
        </xs:complexContent>
    </xs:complexType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#ExtendedType'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['parent_classes'])->toBe(['http://example.org/types#BaseType']);
        expect($class['metadata']['derivation_method'])->toBe('extension');
        expect($class['metadata']['base_type'])->toBe('http://example.org/types#BaseType');
    });

    it('extracts complexContent with restriction base type', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
           xmlns:tns="http://example.org/types"
           targetNamespace="http://example.org/types">
    <xs:complexType name="ParentType">
        <xs:sequence>
            <xs:element name="value" type="xs:string"/>
        </xs:sequence>
    </xs:complexType>
    <xs:complexType name="RestrictedType">
        <xs:complexContent>
            <xs:restriction base="tns:ParentType">
                <xs:sequence>
                    <xs:element name="value" type="xs:string"/>
                </xs:sequence>
            </xs:restriction>
        </xs:complexContent>
    </xs:complexType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#RestrictedType'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['parent_classes'])->toBe(['http://example.org/types#ParentType']);
        expect($class['metadata']['derivation_method'])->toBe('restriction');
        expect($class['metadata']['base_type'])->toBe('http://example.org/types#ParentType');
    });

    it('extracts nested compositors', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:complexType name="NestedType">
        <xs:sequence>
            <xs:element name="name" type="xs:string"/>
            <xs:choice>
                <xs:element name="email" type="xs:string"/>
                <xs:element name="phone" type="xs:string"/>
            </xs:choice>
        </xs:sequence>
    </xs:complexType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#NestedType'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['metadata']['compositor']['type'])->toBe('sequence');
        // Should have both direct elements and nested compositor elements
        expect($class['metadata']['compositor']['elements'])->not->toBeEmpty();
    });

    it('extracts xs:attribute within complex types', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:complexType name="AttributeType">
        <xs:sequence>
            <xs:element name="value" type="xs:string"/>
        </xs:sequence>
        <xs:attribute name="id" type="xs:integer" use="required"/>
        <xs:attribute name="lang" type="xs:language"/>
    </xs:complexType>
</xs:schema>';

        $result = $this->parser->parse($content);
        $class = $result->classes['http://www.w3.org/2001/XMLSchema#AttributeType'] ?? null;

        expect($class)->not->toBeNull();
        expect($class['metadata'])->toHaveKey('attributes');
        expect($class['metadata']['attributes'])->toHaveCount(2);

        $attrs = $class['metadata']['attributes'];
        expect($attrs[0]['name'])->toBe('id');
        expect($attrs[0]['type'])->toBe('http://www.w3.org/2001/XMLSchema#integer');
        expect($attrs[0]['use'])->toBe('required');
        expect($attrs[1]['name'])->toBe('lang');
        expect($attrs[1]['type'])->toBe('http://www.w3.org/2001/XMLSchema#language');
        expect($attrs[1]['use'])->toBe('optional');
    });

    // =========================================================================
    // Story 15.4: Element and Attribute Declarations
    // =========================================================================

    it('extracts top-level element with name and type', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:element name="firstName" type="xs:string"/>
    <xs:element name="age" type="xs:integer"/>
</xs:schema>';

        $result = $this->parser->parse($content);

        expect($result->properties)->not->toBeEmpty();
        expect($result->properties)->toHaveCount(2);

        $firstName = $result->properties['http://www.w3.org/2001/XMLSchema#firstName'] ?? null;
        expect($firstName)->not->toBeNull();
        expect($firstName['name'])->toBe('firstName');
        expect($firstName['type'])->toBe('http://www.w3.org/2001/XMLSchema#string');
        expect($firstName['metadata']['kind'])->toBe('element');

        $age = $result->properties['http://www.w3.org/2001/XMLSchema#age'] ?? null;
        expect($age)->not->toBeNull();
        expect($age['name'])->toBe('age');
        expect($age['type'])->toBe('http://www.w3.org/2001/XMLSchema#integer');
    });

    it('extracts element with substitutionGroup resolved to full URI', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
           xmlns:tns="http://example.org/types"
           targetNamespace="http://example.org/types">
    <xs:element name="baseElement" type="xs:string"/>
    <xs:element name="derivedElement" type="xs:string" substitutionGroup="tns:baseElement"/>
</xs:schema>';

        $result = $this->parser->parse($content);

        $derived = $result->properties['http://www.w3.org/2001/XMLSchema#derivedElement'] ?? null;
        expect($derived)->not->toBeNull();
        expect($derived['substitution_group'])->toBe('http://example.org/types#baseElement');
    });

    it('extracts abstract element flag as native bool', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:element name="abstractEl" type="xs:string" abstract="true"/>
    <xs:element name="concreteEl" type="xs:string"/>
</xs:schema>';

        $result = $this->parser->parse($content);

        $abstract = $result->properties['http://www.w3.org/2001/XMLSchema#abstractEl'] ?? null;
        expect($abstract)->not->toBeNull();
        expect($abstract['abstract'])->toBeTrue();
        expect($abstract['abstract'])->toBeBool();

        $concrete = $result->properties['http://www.w3.org/2001/XMLSchema#concreteEl'] ?? null;
        expect($concrete)->not->toBeNull();
        expect($concrete['abstract'])->toBeFalse();
    });

    it('extracts nillable element flag as native bool', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:element name="nillableEl" type="xs:string" nillable="true"/>
    <xs:element name="nonNillable" type="xs:string"/>
</xs:schema>';

        $result = $this->parser->parse($content);

        $nillable = $result->properties['http://www.w3.org/2001/XMLSchema#nillableEl'] ?? null;
        expect($nillable)->not->toBeNull();
        expect($nillable['nillable'])->toBeTrue();
        expect($nillable['nillable'])->toBeBool();

        $nonNillable = $result->properties['http://www.w3.org/2001/XMLSchema#nonNillable'] ?? null;
        expect($nonNillable)->not->toBeNull();
        expect($nonNillable['nillable'])->toBeFalse();
    });

    it('extracts top-level attribute with name and type', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:attribute name="lang" type="xs:language"/>
    <xs:attribute name="id" type="xs:integer"/>
</xs:schema>';

        $result = $this->parser->parse($content);

        $lang = $result->properties['http://www.w3.org/2001/XMLSchema#lang'] ?? null;
        expect($lang)->not->toBeNull();
        expect($lang['name'])->toBe('lang');
        expect($lang['type'])->toBe('http://www.w3.org/2001/XMLSchema#language');
        expect($lang['metadata']['kind'])->toBe('attribute');

        $id = $result->properties['http://www.w3.org/2001/XMLSchema#id'] ?? null;
        expect($id)->not->toBeNull();
        expect($id['name'])->toBe('id');
        expect($id['type'])->toBe('http://www.w3.org/2001/XMLSchema#integer');
    });

    it('extracts attribute with default value', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:attribute name="lang" type="xs:language" default="en"/>
</xs:schema>';

        $result = $this->parser->parse($content);

        $lang = $result->properties['http://www.w3.org/2001/XMLSchema#lang'] ?? null;
        expect($lang)->not->toBeNull();
        expect($lang['default'])->toBe('en');
    });

    it('extracts attribute with fixed value', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:attribute name="version" type="xs:string" fixed="1.0"/>
</xs:schema>';

        $result = $this->parser->parse($content);

        $version = $result->properties['http://www.w3.org/2001/XMLSchema#version'] ?? null;
        expect($version)->not->toBeNull();
        expect($version['fixed'])->toBe('1.0');
    });

    it('preserves metadata distinction between elements and attributes', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:element name="myElement" type="xs:string"/>
    <xs:attribute name="myAttribute" type="xs:string"/>
</xs:schema>';

        $result = $this->parser->parse($content);

        $element = $result->properties['http://www.w3.org/2001/XMLSchema#myElement'] ?? null;
        $attribute = $result->properties['http://www.w3.org/2001/XMLSchema#myAttribute'] ?? null;

        expect($element)->not->toBeNull();
        expect($attribute)->not->toBeNull();
        expect($element['metadata']['kind'])->toBe('element');
        expect($attribute['metadata']['kind'])->toBe('attribute');
    });

    it('defaults optional element fields correctly', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:element name="simpleEl" type="xs:string"/>
</xs:schema>';

        $result = $this->parser->parse($content);

        $el = $result->properties['http://www.w3.org/2001/XMLSchema#simpleEl'] ?? null;
        expect($el)->not->toBeNull();
        expect($el['substitution_group'])->toBeNull();
        expect($el['abstract'])->toBeFalse();
        expect($el['nillable'])->toBeFalse();
    });

    it('defaults optional attribute fields correctly', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:attribute name="simpleAttr" type="xs:string"/>
</xs:schema>';

        $result = $this->parser->parse($content);

        $attr = $result->properties['http://www.w3.org/2001/XMLSchema#simpleAttr'] ?? null;
        expect($attr)->not->toBeNull();
        expect($attr['default'])->toBeNull();
        expect($attr['fixed'])->toBeNull();
    });
});
