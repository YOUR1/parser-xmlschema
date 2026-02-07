<?php

use App\Services\Ontology\Exceptions\OntologyImportException;
use App\Services\Ontology\Parsers\XmlSchemaParser;

describe('XmlSchemaParser', function () {
    beforeEach(function () {
        $this->parser = new XmlSchemaParser;
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

        expect($result)->toHaveKeys(['metadata', 'prefixes', 'classes', 'properties', 'shapes', 'raw_content']);

        // Check metadata
        expect($result['metadata']['format'])->toBe('xml_schema');
        expect($result['metadata']['parser'])->toBe('xml_schema');
        expect($result['metadata']['namespace'])->toBe('http://www.w3.org/2001/XMLSchema#');

        // Check prefixes
        expect($result['prefixes'])->toHaveKey('xsd');
        expect($result['prefixes']['xsd'])->toBe('http://www.w3.org/2001/XMLSchema#');
        expect($result['prefixes'])->toHaveKey('xs');
        expect($result['prefixes']['xs'])->toBe('http://www.w3.org/2001/XMLSchema#');

        // Should have all standard XSD datatypes as classes
        expect($result['classes'])->not->toBeEmpty();
        expect(count($result['classes']))->toBeGreaterThan(40); // There are 44 XSD datatypes defined

        // No properties in XML Schema
        expect($result['properties'])->toBeEmpty();
        expect($result['shapes'])->toBeEmpty();
    });

    it('includes all standard XSD datatypes as classes', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
           targetNamespace="http://www.w3.org/2001/XMLSchema">
</xs:schema>';

        $result = $this->parser->parse($content);

        $classUris = collect($result['classes'])->pluck('uri')->toArray();

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
        $integerClass = collect($result['classes'])->firstWhere('uri', 'http://www.w3.org/2001/XMLSchema#integer');
        expect($integerClass['parent_classes'])->toContain('http://www.w3.org/2001/XMLSchema#decimal');

        // Check hierarchy: int -> long -> integer -> decimal
        $intClass = collect($result['classes'])->firstWhere('uri', 'http://www.w3.org/2001/XMLSchema#int');
        expect($intClass['parent_classes'])->toContain('http://www.w3.org/2001/XMLSchema#long');

        $longClass = collect($result['classes'])->firstWhere('uri', 'http://www.w3.org/2001/XMLSchema#long');
        expect($longClass['parent_classes'])->toContain('http://www.w3.org/2001/XMLSchema#integer');

        // Check hierarchy: token -> normalizedString -> string (no parent)
        $tokenClass = collect($result['classes'])->firstWhere('uri', 'http://www.w3.org/2001/XMLSchema#token');
        expect($tokenClass['parent_classes'])->toContain('http://www.w3.org/2001/XMLSchema#normalizedString');

        $normalizedStringClass = collect($result['classes'])->firstWhere('uri', 'http://www.w3.org/2001/XMLSchema#normalizedString');
        expect($normalizedStringClass['parent_classes'])->toContain('http://www.w3.org/2001/XMLSchema#string');

        // Primitive types should have no parents
        $stringClass = collect($result['classes'])->firstWhere('uri', 'http://www.w3.org/2001/XMLSchema#string');
        expect($stringClass['parent_classes'])->toBeEmpty();

        $decimalClass = collect($result['classes'])->firstWhere('uri', 'http://www.w3.org/2001/XMLSchema#decimal');
        expect($decimalClass['parent_classes'])->toBeEmpty();
    });

    it('categorizes datatypes correctly', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
           targetNamespace="http://www.w3.org/2001/XMLSchema">
</xs:schema>';

        $result = $this->parser->parse($content);

        // Check string category
        $stringClass = collect($result['classes'])->firstWhere('uri', 'http://www.w3.org/2001/XMLSchema#string');
        expect($stringClass['metadata']['category'])->toBe('string');

        $tokenClass = collect($result['classes'])->firstWhere('uri', 'http://www.w3.org/2001/XMLSchema#token');
        expect($tokenClass['metadata']['category'])->toBe('string');

        // Check numeric category
        $decimalClass = collect($result['classes'])->firstWhere('uri', 'http://www.w3.org/2001/XMLSchema#decimal');
        expect($decimalClass['metadata']['category'])->toBe('numeric');

        $integerClass = collect($result['classes'])->firstWhere('uri', 'http://www.w3.org/2001/XMLSchema#integer');
        expect($integerClass['metadata']['category'])->toBe('numeric');

        // Check temporal category
        $dateTimeClass = collect($result['classes'])->firstWhere('uri', 'http://www.w3.org/2001/XMLSchema#dateTime');
        expect($dateTimeClass['metadata']['category'])->toBe('temporal');

        $dateClass = collect($result['classes'])->firstWhere('uri', 'http://www.w3.org/2001/XMLSchema#date');
        expect($dateClass['metadata']['category'])->toBe('temporal');

        // Check binary category
        $hexBinaryClass = collect($result['classes'])->firstWhere('uri', 'http://www.w3.org/2001/XMLSchema#hexBinary');
        expect($hexBinaryClass['metadata']['category'])->toBe('binary');

        // Check logical category
        $booleanClass = collect($result['classes'])->firstWhere('uri', 'http://www.w3.org/2001/XMLSchema#boolean');
        expect($booleanClass['metadata']['category'])->toBe('logical');
    });

    it('marks primitive vs derived types correctly', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
           targetNamespace="http://www.w3.org/2001/XMLSchema">
</xs:schema>';

        $result = $this->parser->parse($content);

        // Primitive types
        $stringClass = collect($result['classes'])->firstWhere('uri', 'http://www.w3.org/2001/XMLSchema#string');
        expect($stringClass['metadata']['is_primitive'])->toBeTrue();

        $decimalClass = collect($result['classes'])->firstWhere('uri', 'http://www.w3.org/2001/XMLSchema#decimal');
        expect($decimalClass['metadata']['is_primitive'])->toBeTrue();

        $booleanClass = collect($result['classes'])->firstWhere('uri', 'http://www.w3.org/2001/XMLSchema#boolean');
        expect($booleanClass['metadata']['is_primitive'])->toBeTrue();

        // Derived types
        $integerClass = collect($result['classes'])->firstWhere('uri', 'http://www.w3.org/2001/XMLSchema#integer');
        expect($integerClass['metadata']['is_primitive'])->toBeFalse();

        $tokenClass = collect($result['classes'])->firstWhere('uri', 'http://www.w3.org/2001/XMLSchema#token');
        expect($tokenClass['metadata']['is_primitive'])->toBeFalse();

        $intClass = collect($result['classes'])->firstWhere('uri', 'http://www.w3.org/2001/XMLSchema#int');
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
        $classUris = collect($result['classes'])->pluck('uri')->toArray();

        expect($classUris)->toContain('http://www.w3.org/2001/XMLSchema#ProductCode');
        expect($classUris)->toContain('http://www.w3.org/2001/XMLSchema#ProductInfo');

        // Check custom simple type
        $productCodeClass = collect($result['classes'])->firstWhere('uri', 'http://www.w3.org/2001/XMLSchema#ProductCode');
        expect($productCodeClass['label'])->toBe('ProductCode');
        expect($productCodeClass['description'])->toBe('A unique product code');
        expect($productCodeClass['metadata']['category'])->toBe('schema_defined');
        expect($productCodeClass['metadata']['type_kind'])->toBe('simpleType');

        // Check custom complex type
        $productInfoClass = collect($result['classes'])->firstWhere('uri', 'http://www.w3.org/2001/XMLSchema#ProductInfo');
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
        $customTypes = collect($result['classes'])->filter(function ($class) {
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
            ->toThrow(OntologyImportException::class, 'XML Schema parsing failed');
    });

    it('throws exception on malformed XML', function () {
        $malformedXml = '<?xml version="1.0"?><xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"><unclosed_tag></xs:schema>';

        expect(fn () => $this->parser->parse($malformedXml))
            ->toThrow(OntologyImportException::class);
    });

    it('has correct metadata for all datatype classes', function () {
        $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
           targetNamespace="http://www.w3.org/2001/XMLSchema">
</xs:schema>';

        $result = $this->parser->parse($content);

        foreach ($result['classes'] as $class) {
            // All classes should have required fields
            expect($class)->toHaveKeys(['uri', 'label', 'description', 'parent_classes', 'metadata']);

            // All URIs should start with XML Schema namespace
            expect($class['uri'])->toStartWith('http://www.w3.org/2001/XMLSchema#');

            // All should have metadata source
            expect($class['metadata']['source'])->toBe('xml_schema');

            // All should have category and is_primitive fields
            expect($class['metadata'])->toHaveKey('category');
            expect($class['metadata'])->toHaveKey('is_primitive');
        }
    });
});
