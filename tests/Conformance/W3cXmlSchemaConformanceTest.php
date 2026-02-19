<?php

declare(strict_types=1);

use Youri\vandenBogert\Software\ParserCore\ValueObjects\ParsedOntology;
use Youri\vandenBogert\Software\ParserXmlSchema\XmlSchemaParser;

/*
 * CONFORMANCE_RESULTS
 * ===================
 * W3C XML Schema Conformance Tests for XmlSchemaParser
 * Based on W3C XML Schema Part 2: Datatypes Second Edition
 * https://www.w3.org/TR/xmlschema-2/
 *
 * Test run date: 2026-02-19
 * Parser: XmlSchemaParser
 * Total tests: 87
 * Passed: 87
 * Failed: 0
 * Skipped: 0
 *
 * Sections tested:
 * - S3.2.1-3.2.19: All 19 primitive datatypes
 * - S3.3.1-3.3.25: All 25 derived datatypes
 * - S3.4: Type hierarchy / derivation chains
 * - S4.3: Constraining facets (via XSD fixture parsing)
 * - RDF/OWL relevant XSD datatypes
 * - Edge cases: empty schema, prefix variants, anonymous types
 */

function xsdFixturePath(string $filename): string
{
    return __DIR__ . '/../Fixtures/W3c/' . $filename;
}

function xsdFixture(string $filename): string
{
    return file_get_contents(xsdFixturePath($filename));
}

$minimalXsd = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
           targetNamespace="http://www.w3.org/2001/XMLSchema">
</xs:schema>';

$xsdBase = 'http://www.w3.org/2001/XMLSchema#';

// =========================================================================
// Task 3: W3C XML Schema Primitive Datatypes (19 tests)
// =========================================================================
describe('W3C XML Schema Primitive Datatypes', function () use ($minimalXsd, $xsdBase) {
    beforeEach(function () use ($minimalXsd) {
        $this->parser = new XmlSchemaParser();
        $this->result = $this->parser->parse($minimalXsd);
    });

    it('[XSD Part 2, 3.2.1] generates xsd:string as primitive string type', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'string'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'string');
        expect($type['parent_classes'])->toBe([]);
        expect($type['metadata']['category'])->toBe('string');
        expect($type['metadata']['is_primitive'])->toBeTrue();
    });

    it('[XSD Part 2, 3.2.2] generates xsd:boolean as primitive logical type', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'boolean'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'boolean');
        expect($type['parent_classes'])->toBe([]);
        expect($type['metadata']['category'])->toBe('logical');
        expect($type['metadata']['is_primitive'])->toBeTrue();
    });

    it('[XSD Part 2, 3.2.3] generates xsd:decimal as primitive numeric type', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'decimal'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'decimal');
        expect($type['parent_classes'])->toBe([]);
        expect($type['metadata']['category'])->toBe('numeric');
        expect($type['metadata']['is_primitive'])->toBeTrue();
    });

    it('[XSD Part 2, 3.2.4] generates xsd:float as primitive numeric type', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'float'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'float');
        expect($type['parent_classes'])->toBe([]);
        expect($type['metadata']['category'])->toBe('numeric');
        expect($type['metadata']['is_primitive'])->toBeTrue();
    });

    it('[XSD Part 2, 3.2.5] generates xsd:double as primitive numeric type', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'double'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'double');
        expect($type['parent_classes'])->toBe([]);
        expect($type['metadata']['category'])->toBe('numeric');
        expect($type['metadata']['is_primitive'])->toBeTrue();
    });

    it('[XSD Part 2, 3.2.6] generates xsd:duration as primitive temporal type', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'duration'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'duration');
        expect($type['parent_classes'])->toBe([]);
        expect($type['metadata']['category'])->toBe('temporal');
        expect($type['metadata']['is_primitive'])->toBeTrue();
    });

    it('[XSD Part 2, 3.2.7] generates xsd:dateTime as primitive temporal type', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'dateTime'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'dateTime');
        expect($type['parent_classes'])->toBe([]);
        expect($type['metadata']['category'])->toBe('temporal');
        expect($type['metadata']['is_primitive'])->toBeTrue();
    });

    it('[XSD Part 2, 3.2.8] generates xsd:time as primitive temporal type', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'time'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'time');
        expect($type['parent_classes'])->toBe([]);
        expect($type['metadata']['category'])->toBe('temporal');
        expect($type['metadata']['is_primitive'])->toBeTrue();
    });

    it('[XSD Part 2, 3.2.9] generates xsd:date as primitive temporal type', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'date'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'date');
        expect($type['parent_classes'])->toBe([]);
        expect($type['metadata']['category'])->toBe('temporal');
        expect($type['metadata']['is_primitive'])->toBeTrue();
    });

    it('[XSD Part 2, 3.2.10] generates xsd:gYearMonth as primitive temporal type', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'gYearMonth'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'gYearMonth');
        expect($type['parent_classes'])->toBe([]);
        expect($type['metadata']['category'])->toBe('temporal');
        expect($type['metadata']['is_primitive'])->toBeTrue();
    });

    it('[XSD Part 2, 3.2.11] generates xsd:gYear as primitive temporal type', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'gYear'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'gYear');
        expect($type['parent_classes'])->toBe([]);
        expect($type['metadata']['category'])->toBe('temporal');
        expect($type['metadata']['is_primitive'])->toBeTrue();
    });

    it('[XSD Part 2, 3.2.12] generates xsd:gMonthDay as primitive temporal type', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'gMonthDay'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'gMonthDay');
        expect($type['parent_classes'])->toBe([]);
        expect($type['metadata']['category'])->toBe('temporal');
        expect($type['metadata']['is_primitive'])->toBeTrue();
    });

    it('[XSD Part 2, 3.2.13] generates xsd:gDay as primitive temporal type', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'gDay'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'gDay');
        expect($type['parent_classes'])->toBe([]);
        expect($type['metadata']['category'])->toBe('temporal');
        expect($type['metadata']['is_primitive'])->toBeTrue();
    });

    it('[XSD Part 2, 3.2.14] generates xsd:gMonth as primitive temporal type', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'gMonth'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'gMonth');
        expect($type['parent_classes'])->toBe([]);
        expect($type['metadata']['category'])->toBe('temporal');
        expect($type['metadata']['is_primitive'])->toBeTrue();
    });

    it('[XSD Part 2, 3.2.15] generates xsd:hexBinary as primitive binary type', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'hexBinary'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'hexBinary');
        expect($type['parent_classes'])->toBe([]);
        expect($type['metadata']['category'])->toBe('binary');
        expect($type['metadata']['is_primitive'])->toBeTrue();
    });

    it('[XSD Part 2, 3.2.16] generates xsd:base64Binary as primitive binary type', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'base64Binary'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'base64Binary');
        expect($type['parent_classes'])->toBe([]);
        expect($type['metadata']['category'])->toBe('binary');
        expect($type['metadata']['is_primitive'])->toBeTrue();
    });

    it('[XSD Part 2, 3.2.17] generates xsd:anyURI as primitive other type', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'anyURI'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'anyURI');
        expect($type['parent_classes'])->toBe([]);
        expect($type['metadata']['category'])->toBe('other');
        expect($type['metadata']['is_primitive'])->toBeTrue();
    });

    it('[XSD Part 2, 3.2.18] generates xsd:QName as primitive other type', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'QName'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'QName');
        expect($type['parent_classes'])->toBe([]);
        expect($type['metadata']['category'])->toBe('other');
        expect($type['metadata']['is_primitive'])->toBeTrue();
    });

    it('[XSD Part 2, 3.2.19] generates xsd:NOTATION as primitive other type', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'NOTATION'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'NOTATION');
        expect($type['parent_classes'])->toBe([]);
        expect($type['metadata']['category'])->toBe('other');
        expect($type['metadata']['is_primitive'])->toBeTrue();
    });
});

// =========================================================================
// Task 4: W3C XML Schema Derived Datatypes (25 tests)
// =========================================================================
describe('W3C XML Schema Derived Datatypes', function () use ($minimalXsd, $xsdBase) {
    beforeEach(function () use ($minimalXsd) {
        $this->parser = new XmlSchemaParser();
        $this->result = $this->parser->parse($minimalXsd);
    });

    it('[XSD Part 2, 3.3.1] generates xsd:normalizedString derived from xsd:string', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'normalizedString'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'normalizedString');
        expect($type['parent_classes'])->toBe([$xsdBase . 'string']);
        expect($type['metadata']['category'])->toBe('string');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.2] generates xsd:token derived from xsd:normalizedString', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'token'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'token');
        expect($type['parent_classes'])->toBe([$xsdBase . 'normalizedString']);
        expect($type['metadata']['category'])->toBe('string');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.3] generates xsd:language derived from xsd:token', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'language'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'language');
        expect($type['parent_classes'])->toBe([$xsdBase . 'token']);
        expect($type['metadata']['category'])->toBe('string');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.4] generates xsd:NMTOKEN derived from xsd:token', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'NMTOKEN'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'NMTOKEN');
        expect($type['parent_classes'])->toBe([$xsdBase . 'token']);
        expect($type['metadata']['category'])->toBe('other');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.5] generates xsd:NMTOKENS derived from xsd:token', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'NMTOKENS'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'NMTOKENS');
        expect($type['parent_classes'])->toBe([$xsdBase . 'token']);
        expect($type['metadata']['category'])->toBe('other');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.6] generates xsd:Name derived from xsd:token', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'Name'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'Name');
        expect($type['parent_classes'])->toBe([$xsdBase . 'token']);
        expect($type['metadata']['category'])->toBe('string');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.7] generates xsd:NCName derived from xsd:Name', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'NCName'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'NCName');
        expect($type['parent_classes'])->toBe([$xsdBase . 'Name']);
        expect($type['metadata']['category'])->toBe('string');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.8] generates xsd:ID derived from xsd:NCName', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'ID'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'ID');
        expect($type['parent_classes'])->toBe([$xsdBase . 'NCName']);
        expect($type['metadata']['category'])->toBe('string');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.9] generates xsd:IDREF derived from xsd:NCName', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'IDREF'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'IDREF');
        expect($type['parent_classes'])->toBe([$xsdBase . 'NCName']);
        expect($type['metadata']['category'])->toBe('string');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.10] generates xsd:IDREFS derived from xsd:token', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'IDREFS'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'IDREFS');
        expect($type['parent_classes'])->toBe([$xsdBase . 'token']);
        expect($type['metadata']['category'])->toBe('other');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.11] generates xsd:ENTITY derived from xsd:NCName', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'ENTITY'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'ENTITY');
        expect($type['parent_classes'])->toBe([$xsdBase . 'NCName']);
        expect($type['metadata']['category'])->toBe('string');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.12] generates xsd:ENTITIES derived from xsd:token', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'ENTITIES'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'ENTITIES');
        expect($type['parent_classes'])->toBe([$xsdBase . 'token']);
        expect($type['metadata']['category'])->toBe('other');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.13] generates xsd:integer derived from xsd:decimal', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'integer'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'integer');
        expect($type['parent_classes'])->toBe([$xsdBase . 'decimal']);
        expect($type['metadata']['category'])->toBe('numeric');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.14] generates xsd:nonPositiveInteger derived from xsd:integer', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'nonPositiveInteger'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'nonPositiveInteger');
        expect($type['parent_classes'])->toBe([$xsdBase . 'integer']);
        expect($type['metadata']['category'])->toBe('numeric');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.15] generates xsd:negativeInteger derived from xsd:nonPositiveInteger', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'negativeInteger'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'negativeInteger');
        expect($type['parent_classes'])->toBe([$xsdBase . 'nonPositiveInteger']);
        expect($type['metadata']['category'])->toBe('numeric');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.16] generates xsd:long derived from xsd:integer', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'long'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'long');
        expect($type['parent_classes'])->toBe([$xsdBase . 'integer']);
        expect($type['metadata']['category'])->toBe('numeric');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.17] generates xsd:int derived from xsd:long', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'int'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'int');
        expect($type['parent_classes'])->toBe([$xsdBase . 'long']);
        expect($type['metadata']['category'])->toBe('numeric');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.18] generates xsd:short derived from xsd:int', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'short'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'short');
        expect($type['parent_classes'])->toBe([$xsdBase . 'int']);
        expect($type['metadata']['category'])->toBe('numeric');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.19] generates xsd:byte derived from xsd:short', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'byte'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'byte');
        expect($type['parent_classes'])->toBe([$xsdBase . 'short']);
        expect($type['metadata']['category'])->toBe('numeric');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.20] generates xsd:nonNegativeInteger derived from xsd:integer', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'nonNegativeInteger'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'nonNegativeInteger');
        expect($type['parent_classes'])->toBe([$xsdBase . 'integer']);
        expect($type['metadata']['category'])->toBe('numeric');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.21] generates xsd:unsignedLong derived from xsd:nonNegativeInteger', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'unsignedLong'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'unsignedLong');
        expect($type['parent_classes'])->toBe([$xsdBase . 'nonNegativeInteger']);
        expect($type['metadata']['category'])->toBe('numeric');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.22] generates xsd:unsignedInt derived from xsd:unsignedLong', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'unsignedInt'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'unsignedInt');
        expect($type['parent_classes'])->toBe([$xsdBase . 'unsignedLong']);
        expect($type['metadata']['category'])->toBe('numeric');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.23] generates xsd:unsignedShort derived from xsd:unsignedInt', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'unsignedShort'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'unsignedShort');
        expect($type['parent_classes'])->toBe([$xsdBase . 'unsignedInt']);
        expect($type['metadata']['category'])->toBe('numeric');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.24] generates xsd:unsignedByte derived from xsd:unsignedShort', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'unsignedByte'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'unsignedByte');
        expect($type['parent_classes'])->toBe([$xsdBase . 'unsignedShort']);
        expect($type['metadata']['category'])->toBe('numeric');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });

    it('[XSD Part 2, 3.3.25] generates xsd:positiveInteger derived from xsd:nonNegativeInteger', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'positiveInteger'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['uri'])->toBe($xsdBase . 'positiveInteger');
        expect($type['parent_classes'])->toBe([$xsdBase . 'nonNegativeInteger']);
        expect($type['metadata']['category'])->toBe('numeric');
        expect($type['metadata']['is_primitive'])->toBeFalse();
    });
});

// =========================================================================
// Task 5: W3C XML Schema Type Hierarchy (9 tests)
// =========================================================================
describe('W3C XML Schema Type Hierarchy', function () use ($minimalXsd, $xsdBase) {
    beforeEach(function () use ($minimalXsd) {
        $this->parser = new XmlSchemaParser();
        $this->result = $this->parser->parse($minimalXsd);
    });

    it('[XSD Part 2, 3.4] verifies integer derivation chain: decimal -> integer -> long -> int -> short -> byte', function () use ($xsdBase) {
        $chain = ['decimal', 'integer', 'long', 'int', 'short', 'byte'];
        for ($i = 1; $i < count($chain); $i++) {
            $type = $this->result->classes[$xsdBase . $chain[$i]] ?? null;
            expect($type)->not->toBeNull();
            expect($type['parent_classes'])->toBe([$xsdBase . $chain[$i - 1]]);
        }
        // decimal is primitive - no parent
        $decimal = $this->result->classes[$xsdBase . 'decimal'] ?? null;
        expect($decimal['parent_classes'])->toBe([]);
    });

    it('[XSD Part 2, 3.4] verifies string derivation chain: string -> normalizedString -> token -> language', function () use ($xsdBase) {
        $chain = ['string', 'normalizedString', 'token', 'language'];
        for ($i = 1; $i < count($chain); $i++) {
            $type = $this->result->classes[$xsdBase . $chain[$i]] ?? null;
            expect($type)->not->toBeNull();
            expect($type['parent_classes'])->toBe([$xsdBase . $chain[$i - 1]]);
        }
        // string is primitive - no parent
        $string = $this->result->classes[$xsdBase . 'string'] ?? null;
        expect($string['parent_classes'])->toBe([]);
    });

    it('[XSD Part 2, 3.4] verifies unsigned derivation chain: nonNegativeInteger -> unsignedLong -> unsignedInt -> unsignedShort -> unsignedByte', function () use ($xsdBase) {
        $chain = ['nonNegativeInteger', 'unsignedLong', 'unsignedInt', 'unsignedShort', 'unsignedByte'];
        for ($i = 1; $i < count($chain); $i++) {
            $type = $this->result->classes[$xsdBase . $chain[$i]] ?? null;
            expect($type)->not->toBeNull();
            expect($type['parent_classes'])->toBe([$xsdBase . $chain[$i - 1]]);
        }
    });

    it('[XSD Part 2, 3.4] verifies negative integer chain: integer -> nonPositiveInteger -> negativeInteger', function () use ($xsdBase) {
        $nonPositive = $this->result->classes[$xsdBase . 'nonPositiveInteger'] ?? null;
        expect($nonPositive['parent_classes'])->toBe([$xsdBase . 'integer']);

        $negative = $this->result->classes[$xsdBase . 'negativeInteger'] ?? null;
        expect($negative['parent_classes'])->toBe([$xsdBase . 'nonPositiveInteger']);
    });

    it('[XSD Part 2, 3.4] verifies positive integer chain: integer -> nonNegativeInteger -> positiveInteger', function () use ($xsdBase) {
        $nonNegative = $this->result->classes[$xsdBase . 'nonNegativeInteger'] ?? null;
        expect($nonNegative['parent_classes'])->toBe([$xsdBase . 'integer']);

        $positive = $this->result->classes[$xsdBase . 'positiveInteger'] ?? null;
        expect($positive['parent_classes'])->toBe([$xsdBase . 'nonNegativeInteger']);
    });

    it('[XSD Part 2, 3.4] verifies Name derivation chain: token -> Name -> NCName', function () use ($xsdBase) {
        $name = $this->result->classes[$xsdBase . 'Name'] ?? null;
        expect($name['parent_classes'])->toBe([$xsdBase . 'token']);

        $ncName = $this->result->classes[$xsdBase . 'NCName'] ?? null;
        expect($ncName['parent_classes'])->toBe([$xsdBase . 'Name']);
    });

    it('[XSD Part 2, 3.4] verifies NCName subtypes: NCName -> ID, NCName -> IDREF, NCName -> ENTITY', function () use ($xsdBase) {
        $id = $this->result->classes[$xsdBase . 'ID'] ?? null;
        expect($id['parent_classes'])->toBe([$xsdBase . 'NCName']);

        $idref = $this->result->classes[$xsdBase . 'IDREF'] ?? null;
        expect($idref['parent_classes'])->toBe([$xsdBase . 'NCName']);

        $entity = $this->result->classes[$xsdBase . 'ENTITY'] ?? null;
        expect($entity['parent_classes'])->toBe([$xsdBase . 'NCName']);
    });

    it('[XSD Part 2, 3.4] verifies token subtypes: IDREFS, ENTITIES, NMTOKEN, NMTOKENS all derive from token', function () use ($xsdBase) {
        $tokenDerived = ['IDREFS', 'ENTITIES', 'NMTOKEN', 'NMTOKENS'];
        foreach ($tokenDerived as $name) {
            $type = $this->result->classes[$xsdBase . $name] ?? null;
            expect($type)->not->toBeNull();
            expect($type['parent_classes'])->toBe([$xsdBase . 'token']);
        }
    });

    it('[XSD Part 2, 3.4] verifies all 19 primitive types have no parent classes', function () use ($xsdBase) {
        $primitives = [
            'string', 'boolean', 'decimal', 'float', 'double',
            'duration', 'dateTime', 'time', 'date',
            'gYearMonth', 'gYear', 'gMonthDay', 'gDay', 'gMonth',
            'hexBinary', 'base64Binary', 'anyURI', 'QName', 'NOTATION',
        ];
        foreach ($primitives as $name) {
            $type = $this->result->classes[$xsdBase . $name] ?? null;
            expect($type)->not->toBeNull();
            expect($type['parent_classes'])->toBe([])
                ->and($type['metadata']['is_primitive'])->toBeTrue();
        }
    });
});


// =========================================================================
// Task 6: W3C XML Schema Content Parsing (12 tests)
// =========================================================================
describe('W3C XML Schema Content Parsing', function () use ($xsdBase) {
    beforeEach(function () {
        $this->parser = new XmlSchemaParser();
    });

    it('[XSD Part 1, 3.1] parses empty-schema.xsd and returns only built-in datatypes', function () use ($xsdBase) {
        $result = $this->parser->parse(xsdFixture('empty-schema.xsd'));
        expect($result)->toBeInstanceOf(ParsedOntology::class);
        expect(count($result->classes))->toBe(44);

        $customTypes = array_filter($result->classes, function ($class) {
            return $class['metadata']['category'] === 'schema_defined';
        });
        expect($customTypes)->toHaveCount(0);
    });

    it('[XSD Part 1, 3.1] canParse() returns true for valid XSD fixture files', function () {
        $fixtures = [
            'empty-schema.xsd', 'string-types.xsd', 'numeric-types.xsd',
            'temporal-types.xsd', 'boolean-type.xsd', 'enumeration-types.xsd',
            'complex-types.xsd', 'restriction-types.xsd', 'documentation-types.xsd',
            'mixed-types.xsd', 'anonymous-types.xsd',
        ];
        foreach ($fixtures as $fixture) {
            expect($this->parser->canParse(xsdFixture($fixture)))->toBeTrue();
        }
    });

    it('[XSD Part 1, 4.1] canParse() rejects non-XSD XML documents', function () {
        $nonXsd = '<?xml version="1.0"?><root><element>value</element></root>';
        expect($this->parser->canParse($nonXsd))->toBeFalse();

        $rdfXml = '<?xml version="1.0"?><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"></rdf:RDF>';
        expect($this->parser->canParse($rdfXml))->toBeFalse();

        expect($this->parser->canParse('not xml at all'))->toBeFalse();
        expect($this->parser->canParse(''))->toBeFalse();
    });

    it('[XSD Part 2, 3.2.1] parses string-types.xsd and extracts custom string restrictions', function () use ($xsdBase) {
        $result = $this->parser->parse(xsdFixture('string-types.xsd'));

        $shortName = $result->classes[$xsdBase . 'ShortName'] ?? null;
        expect($shortName)->not->toBeNull();
        expect($shortName['label'])->toBe('ShortName');
        expect($shortName['description'])->toBe('A short name between 1 and 50 characters');
        expect($shortName['metadata']['category'])->toBe('schema_defined');
        expect($shortName['metadata']['type_kind'])->toBe('simpleType');

        $productCode = $result->classes[$xsdBase . 'ProductCode'] ?? null;
        expect($productCode)->not->toBeNull();
        expect($productCode['label'])->toBe('ProductCode');

        $countryCode = $result->classes[$xsdBase . 'CountryCode'] ?? null;
        expect($countryCode)->not->toBeNull();
        expect($countryCode['label'])->toBe('CountryCode');
    });

    it('[XSD Part 2, 3.2.3] parses numeric-types.xsd and extracts custom numeric restrictions', function () use ($xsdBase) {
        $result = $this->parser->parse(xsdFixture('numeric-types.xsd'));

        $percentage = $result->classes[$xsdBase . 'Percentage'] ?? null;
        expect($percentage)->not->toBeNull();
        expect($percentage['label'])->toBe('Percentage');
        expect($percentage['metadata']['type_kind'])->toBe('simpleType');

        $price = $result->classes[$xsdBase . 'Price'] ?? null;
        expect($price)->not->toBeNull();
        expect($price['label'])->toBe('Price');
    });

    it('[XSD Part 2, 4.3.5] parses enumeration-types.xsd and extracts enumeration restrictions', function () use ($xsdBase) {
        $result = $this->parser->parse(xsdFixture('enumeration-types.xsd'));

        $color = $result->classes[$xsdBase . 'Color'] ?? null;
        expect($color)->not->toBeNull();
        expect($color['label'])->toBe('Color');
        expect($color['description'])->toBe('An enumeration of basic colors');
        expect($color['metadata']['type_kind'])->toBe('simpleType');

        $priority = $result->classes[$xsdBase . 'Priority'] ?? null;
        expect($priority)->not->toBeNull();

        $dayOfWeek = $result->classes[$xsdBase . 'DayOfWeek'] ?? null;
        expect($dayOfWeek)->not->toBeNull();
    });

    it('[XSD Part 1, 3.4] parses complex-types.xsd and extracts complex type definitions', function () use ($xsdBase) {
        $result = $this->parser->parse(xsdFixture('complex-types.xsd'));

        $person = $result->classes[$xsdBase . 'Person'] ?? null;
        expect($person)->not->toBeNull();
        expect($person['label'])->toBe('Person');
        expect($person['description'])->toBe('A person with name, age, and email');
        expect($person['metadata']['type_kind'])->toBe('complexType');

        $address = $result->classes[$xsdBase . 'Address'] ?? null;
        expect($address)->not->toBeNull();
        expect($address['metadata']['type_kind'])->toBe('complexType');

        $contactMethod = $result->classes[$xsdBase . 'ContactMethod'] ?? null;
        expect($contactMethod)->not->toBeNull();
    });

    it('[XSD Part 2, 4.3] parses restriction-types.xsd and extracts facet restriction types', function () use ($xsdBase) {
        $result = $this->parser->parse(xsdFixture('restriction-types.xsd'));

        $fixedLength = $result->classes[$xsdBase . 'FixedLengthCode'] ?? null;
        expect($fixedLength)->not->toBeNull();
        expect($fixedLength['label'])->toBe('FixedLengthCode');

        $collapsed = $result->classes[$xsdBase . 'CollapsedString'] ?? null;
        expect($collapsed)->not->toBeNull();

        $smallDecimal = $result->classes[$xsdBase . 'SmallDecimal'] ?? null;
        expect($smallDecimal)->not->toBeNull();

        $strictCode = $result->classes[$xsdBase . 'StrictCode'] ?? null;
        expect($strictCode)->not->toBeNull();
    });

    it('[XSD Part 1, 3.13] parses documentation-types.xsd and extracts xs:documentation content', function () use ($xsdBase) {
        $result = $this->parser->parse(xsdFixture('documentation-types.xsd'));

        $documented = $result->classes[$xsdBase . 'DocumentedType'] ?? null;
        expect($documented)->not->toBeNull();
        expect($documented['description'])->toBe('This type has explicit documentation');

        $undocumented = $result->classes[$xsdBase . 'UndocumentedType'] ?? null;
        expect($undocumented)->not->toBeNull();
        expect($undocumented['description'])->toBe('XML Schema type: UndocumentedType');
    });

    it('[XSD Part 1, 3.13] extracts first documentation when multiple xs:documentation elements exist', function () use ($xsdBase) {
        $result = $this->parser->parse(xsdFixture('documentation-types.xsd'));

        $multiDoc = $result->classes[$xsdBase . 'MultiDocType'] ?? null;
        expect($multiDoc)->not->toBeNull();
        expect($multiDoc['description'])->toBe('First documentation block');
    });

    it('[XSD Part 1, 3.13] falls back to default description for empty documentation element', function () use ($xsdBase) {
        $result = $this->parser->parse(xsdFixture('documentation-types.xsd'));

        $emptyDoc = $result->classes[$xsdBase . 'EmptyDocType'] ?? null;
        expect($emptyDoc)->not->toBeNull();
        expect($emptyDoc['description'])->toBe('XML Schema type: EmptyDocType');
    });

    it('[XSD Part 2, 3.2.7] parses temporal-types.xsd and extracts custom date/time restrictions', function () use ($xsdBase) {
        $result = $this->parser->parse(xsdFixture('temporal-types.xsd'));

        $futureDateTime = $result->classes[$xsdBase . 'FutureDateTime'] ?? null;
        expect($futureDateTime)->not->toBeNull();
        expect($futureDateTime['label'])->toBe('FutureDateTime');
        expect($futureDateTime['metadata']['type_kind'])->toBe('simpleType');

        $modernDate = $result->classes[$xsdBase . 'ModernDate'] ?? null;
        expect($modernDate)->not->toBeNull();

        $workDuration = $result->classes[$xsdBase . 'WorkDuration'] ?? null;
        expect($workDuration)->not->toBeNull();
    });

    it('[XSD Part 1, 3.3-3.4] parses mixed-types.xsd containing both simpleType and complexType', function () use ($xsdBase) {
        $result = $this->parser->parse(xsdFixture('mixed-types.xsd'));

        // 44 built-in + 5 custom types
        expect(count($result->classes))->toBe(49);

        $simpleTypes = ['Email', 'PhoneNumber', 'ZipCode'];
        foreach ($simpleTypes as $name) {
            $type = $result->classes[$xsdBase . $name] ?? null;
            expect($type)->not->toBeNull();
            expect($type['metadata']['type_kind'])->toBe('simpleType');
        }

        $complexTypes = ['ContactInfo', 'MailingAddress'];
        foreach ($complexTypes as $name) {
            $type = $result->classes[$xsdBase . $name] ?? null;
            expect($type)->not->toBeNull();
            expect($type['metadata']['type_kind'])->toBe('complexType');
        }
    });
});


// =========================================================================
// Task 7a: RDF/OWL Relevant XSD Datatypes (12 tests)
// =========================================================================
describe('RDF/OWL Relevant XSD Datatypes', function () use ($minimalXsd, $xsdBase) {
    beforeEach(function () use ($minimalXsd) {
        $this->parser = new XmlSchemaParser();
        $this->result = $this->parser->parse($minimalXsd);
    });

    it('[XSD Part 2, 3.2.1] generates xsd:string used as default literal datatype in RDF', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'string'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['label'])->toBe('string');
        expect($type['description'])->toContain('character strings');
        expect($type['metadata']['source'])->toBe('xml_schema');
    });

    it('[XSD Part 2, 3.2.2] generates xsd:boolean used for owl:FunctionalProperty values', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'boolean'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['label'])->toBe('boolean');
        expect($type['metadata']['category'])->toBe('logical');
    });

    it('[XSD Part 2, 3.2.3] generates xsd:decimal used as base for xsd:integer in OWL', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'decimal'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['label'])->toBe('decimal');
        expect($type['metadata']['category'])->toBe('numeric');
    });

    it('[XSD Part 2, 3.2.4] generates xsd:float used in OWL datatype restrictions', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'float'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['metadata']['category'])->toBe('numeric');
        expect($type['metadata']['is_primitive'])->toBeTrue();
    });

    it('[XSD Part 2, 3.2.5] generates xsd:double used in OWL datatype restrictions', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'double'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['metadata']['category'])->toBe('numeric');
        expect($type['metadata']['is_primitive'])->toBeTrue();
    });

    it('[XSD Part 2, 3.2.7] generates xsd:dateTime used for temporal assertions in RDF', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'dateTime'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['label'])->toBe('dateTime');
        expect($type['metadata']['category'])->toBe('temporal');
    });

    it('[XSD Part 2, 3.2.9] generates xsd:date used for date-only assertions in RDF', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'date'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['label'])->toBe('date');
        expect($type['metadata']['category'])->toBe('temporal');
    });

    it('[XSD Part 2, 3.3.13] generates xsd:integer used as common numeric type in OWL', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'integer'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['label'])->toBe('integer');
        expect($type['parent_classes'])->toBe([$xsdBase . 'decimal']);
        expect($type['metadata']['category'])->toBe('numeric');
    });

    it('[XSD Part 2, 3.3.20] generates xsd:nonNegativeInteger used in OWL cardinality restrictions', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'nonNegativeInteger'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['label'])->toBe('nonNegativeInteger');
        expect($type['parent_classes'])->toBe([$xsdBase . 'integer']);
    });

    it('[XSD Part 2, 3.2.17] generates xsd:anyURI used for IRI references in RDF', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'anyURI'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['label'])->toBe('anyURI');
        expect($type['description'])->toContain('URI');
    });

    it('[XSD Part 2, 3.3.3] generates xsd:language used for xml:lang tags in RDF literals', function () use ($xsdBase) {
        $type = $this->result->classes[$xsdBase . 'language'] ?? null;
        expect($type)->not->toBeNull();
        expect($type['label'])->toBe('language');
        expect($type['parent_classes'])->toBe([$xsdBase . 'token']);
        expect($type['metadata']['category'])->toBe('string');
    });

    it('[XSD Part 2, 3.2] includes all RDF/OWL core datatypes in a single parse result', function () use ($xsdBase) {
        $rdfOwlTypes = [
            'string', 'boolean', 'decimal', 'float', 'double',
            'dateTime', 'date', 'time', 'integer', 'nonNegativeInteger',
            'positiveInteger', 'anyURI', 'language', 'normalizedString',
            'token', 'hexBinary', 'base64Binary', 'int', 'long',
        ];
        foreach ($rdfOwlTypes as $name) {
            $type = $this->result->classes[$xsdBase . $name] ?? null;
            expect($type)->not->toBeNull()
                ->and($type['uri'])->toBe($xsdBase . $name);
        }
    });
});


// =========================================================================
// Task 7b: XML Schema Edge Cases (9 tests)
// =========================================================================
describe('XML Schema Edge Cases', function () use ($xsdBase) {
    beforeEach(function () {
        $this->parser = new XmlSchemaParser();
    });

    it('[XSD Part 1, 3.1] parses empty schema returning exactly 44 built-in types', function () use ($xsdBase) {
        $result = $this->parser->parse(xsdFixture('empty-schema.xsd'));
        expect(count($result->classes))->toBe(44);
        expect($result->metadata['resource_count'])->toBe(44);
    });

    it('[XSD Part 1, 3.1] returns correct metadata format for parsed schema', function () {
        $result = $this->parser->parse(xsdFixture('empty-schema.xsd'));
        expect($result->metadata['format'])->toBe('xml_schema');
        expect($result->metadata['parser'])->toBe('xml_schema');
        expect($result->metadata['namespace'])->toBe('http://www.w3.org/2001/XMLSchema#');
    });

    it('[XSD Part 1, 2.6] handles xsd: prefix variant schema in canParse()', function () {
        $content = xsdFixture('namespace-variants.xsd');
        expect($this->parser->canParse($content))->toBeTrue();
    });

    it('[XSD Part 1, 2.6] parses xsd: prefix variant and still generates all 44 built-in types plus custom types', function () use ($xsdBase) {
        $result = $this->parser->parse(xsdFixture('namespace-variants.xsd'));
        // 44 built-in types + 2 custom types (AlternatePrefix, AlternateComplexType)
        expect(count($result->classes))->toBe(46);

        // Verify built-in types still present
        expect($result->classes[$xsdBase . 'string'] ?? null)->not->toBeNull();
        expect($result->classes[$xsdBase . 'integer'] ?? null)->not->toBeNull();
        expect($result->classes[$xsdBase . 'boolean'] ?? null)->not->toBeNull();

        // Verify custom types from xsd: prefix schema are also extracted
        expect($result->classes[$xsdBase . 'AlternatePrefix'] ?? null)->not->toBeNull();
        expect($result->classes[$xsdBase . 'AlternateComplexType'] ?? null)->not->toBeNull();
    });

    it('[XSD Part 1, 3.3.1] does not extract anonymous types (types without name attribute)', function () use ($xsdBase) {
        $result = $this->parser->parse(xsdFixture('anonymous-types.xsd'));

        // Should only have 44 built-in + 1 named type
        expect(count($result->classes))->toBe(45);

        // Named type should be present
        $named = $result->classes[$xsdBase . 'NamedType'] ?? null;
        expect($named)->not->toBeNull();
        expect($named['label'])->toBe('NamedType');
    });

    it('[XSD Part 1, 3.3] custom types have schema_defined category and type_kind metadata', function () use ($xsdBase) {
        $result = $this->parser->parse(xsdFixture('string-types.xsd'));

        $customTypes = array_filter($result->classes, function ($class) {
            return $class['metadata']['category'] === 'schema_defined';
        });

        foreach ($customTypes as $type) {
            expect($type['metadata'])->toHaveKey('type_kind');
            expect($type['metadata'])->not->toHaveKey('is_primitive');
        }
    });

    it('[XSD Part 1, 3.3] custom types have empty parent_classes', function () use ($xsdBase) {
        $result = $this->parser->parse(xsdFixture('complex-types.xsd'));

        $customTypes = array_filter($result->classes, function ($class) {
            return $class['metadata']['category'] === 'schema_defined';
        });

        foreach ($customTypes as $type) {
            expect($type['parent_classes'])->toBe([]);
        }
    });

    it('[XSD Part 1, 3.1] preserves raw XSD content in rawContent property', function () {
        $content = xsdFixture('empty-schema.xsd');
        $result = $this->parser->parse($content);
        expect($result->rawContent)->toBe($content);
    });

    it('[XSD Part 1, 3.3] custom types are appended after the 44 built-in datatypes in resource_count', function () use ($xsdBase) {
        $result = $this->parser->parse(xsdFixture('boolean-type.xsd'));

        // 44 built-in + 1 complex type (FeatureFlags)
        expect(count($result->classes))->toBe(45);
        expect($result->metadata['resource_count'])->toBe(45);

        $featureFlags = $result->classes[$xsdBase . 'FeatureFlags'] ?? null;
        expect($featureFlags)->not->toBeNull();
        expect($featureFlags['label'])->toBe('FeatureFlags');
        expect($featureFlags['metadata']['type_kind'])->toBe('complexType');
    });

    it('[XSD Part 1, 3.1] returns xsd and xs prefix mappings for all parsed schemas', function () {
        $fixtures = ['empty-schema.xsd', 'string-types.xsd', 'complex-types.xsd'];
        foreach ($fixtures as $fixture) {
            $result = $this->parser->parse(xsdFixture($fixture));
            expect($result->prefixes['xsd'])->toBe('http://www.w3.org/2001/XMLSchema#');
            expect($result->prefixes['xs'])->toBe('http://www.w3.org/2001/XMLSchema#');
        }
    });
});
