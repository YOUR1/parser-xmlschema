<?php

declare(strict_types=1);

use Youri\vandenBogert\Software\ParserCore\Exceptions\ParseException;
use Youri\vandenBogert\Software\ParserCore\Contracts\OntologyParserInterface;
use Youri\vandenBogert\Software\ParserXmlSchema\XmlSchemaParser;

$findClass = fn (array $classes, string $uri) => $classes[$uri] ?? null;

$minimalXsd = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
           targetNamespace="http://www.w3.org/2001/XMLSchema">
</xs:schema>';

describe('XmlSchemaParser', function () use ($findClass, $minimalXsd) {
    beforeEach(function () {
        $this->parser = new XmlSchemaParser();
    });

    // =========================================================================
    // Task 2: Characterize canParse() detection behavior (AC: #6)
    // =========================================================================
    describe('canParse()', function () {
        // 2.1
        it('returns true for content with <?xml declaration and xmlns:xs XSD namespace', function () {
            $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
</xs:schema>';
            expect($this->parser->canParse($content))->toBeTrue();
        });

        // 2.2
        it('returns true for content with <?xml declaration and targetNamespace XSD', function () {
            $content = '<?xml version="1.0"?>
<schema targetNamespace="http://www.w3.org/2001/XMLSchema">
</schema>';
            expect($this->parser->canParse($content))->toBeTrue();
        });

        // 2.3
        it('returns true for content with leading whitespace before <?xml', function () {
            $content = '   <?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
</xs:schema>';
            expect($this->parser->canParse($content))->toBeTrue();
        });

        // 2.4
        it('returns false for empty string', function () {
            expect($this->parser->canParse(''))->toBeFalse();
        });

        // 2.5
        it('returns false for whitespace-only content', function () {
            expect($this->parser->canParse('   '))->toBeFalse();
        });

        // 2.6
        it('returns false for plain text content', function () {
            expect($this->parser->canParse('This is not XML Schema'))->toBeFalse();
        });

        // 2.7
        it('returns false for Turtle content', function () {
            $content = '@prefix owl: <http://www.w3.org/2002/07/owl#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .';
            expect($this->parser->canParse($content))->toBeFalse();
        });

        // 2.8
        it('returns false for JSON-LD content', function () {
            $content = '{"@context": "http://schema.org/", "@type": "Person"}';
            expect($this->parser->canParse($content))->toBeFalse();
        });

        // 2.9
        it('returns false for RDF/XML content without XSD namespace', function () {
            $content = '<?xml version="1.0"?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
</rdf:RDF>';
            expect($this->parser->canParse($content))->toBeFalse();
        });

        // 2.10
        it('returns false for generic XML without XSD namespace', function () {
            $content = '<?xml version="1.0"?><root/>';
            expect($this->parser->canParse($content))->toBeFalse();
        });

        // 2.11
        it('returns false for HTML content', function () {
            $content = '<html><body><p>Hello</p></body></html>';
            expect($this->parser->canParse($content))->toBeFalse();
        });

        // 2.12
        it('returns true for XSD namespace appearing in a non-standard position (false positive)', function () {
            // str_contains matches anywhere in content, so an RDF/XML with XSD reference passes
            $content = '<?xml version="1.0"?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns:xsd="http://www.w3.org/2001/XMLSchema">
</rdf:RDF>';
            expect($this->parser->canParse($content))->toBeTrue();
        });

        // 2.13
        it('requires BOTH str_starts_with for <?xml AND str_contains for namespace', function () {
            // Has <?xml but no namespace -> false
            $xmlOnly = '<?xml version="1.0"?><root/>';
            expect($this->parser->canParse($xmlOnly))->toBeFalse();

            // Has namespace but no <?xml -> false
            $nsOnly = '<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"></xs:schema>';
            expect($this->parser->canParse($nsOnly))->toBeFalse();

            // Has both -> true
            $both = '<?xml version="1.0"?><xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"></xs:schema>';
            expect($this->parser->canParse($both))->toBeTrue();
        });

        // 2.14
        it('returns false for content that contains XSD namespace but does NOT start with <?xml', function () {
            $content = 'http://www.w3.org/2001/XMLSchema is the XSD namespace';
            expect($this->parser->canParse($content))->toBeFalse();
        });
    });

    // =========================================================================
    // Task 3: Characterize getSupportedFormats() (AC: #6)
    // =========================================================================
    describe('getSupportedFormats()', function () {
        // 3.1
        it('returns xml_schema and xsd', function () {
            expect($this->parser->getSupportedFormats())->toBe(['xml_schema', 'xsd']);
        });

        // 3.2
        it('returns an array with exactly 2 elements', function () {
            expect($this->parser->getSupportedFormats())->toHaveCount(2);
        });

        // 3.3
        it('contains xml_schema first and xsd second', function () {
            $formats = $this->parser->getSupportedFormats();
            expect($formats[0])->toBe('xml_schema');
            expect($formats[1])->toBe('xsd');
        });
    });

    // =========================================================================
    // Task 4: Characterize parse() output structure (AC: #1, #9, #10, #11, #12)
    // =========================================================================
    describe('parse() output structure', function () use ($minimalXsd) {
        // 4.1
        it('returns a ParsedOntology value object', function () use ($minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            expect($result)->toBeInstanceOf(\Youri\vandenBogert\Software\ParserCore\ValueObjects\ParsedOntology::class);
        });

        // 4.2
        it('has exactly 6 top-level keys', function () use ($minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            expect($result)->toHaveProperties(['metadata', 'prefixes', 'classes', 'properties', 'shapes', 'rawContent']);
            // ParsedOntology has 8 properties: classes, properties, prefixes, shapes, restrictions, metadata, rawContent, graphs
            expect((new \ReflectionClass($result))->getProperties())->toHaveCount(8);
        });

        // 4.3
        it('has metadata with exactly 4 keys and correct values', function () use ($minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $metadata = $result->metadata;

            expect(array_keys($metadata))->toHaveCount(4);
            expect($metadata['format'])->toBe('xml_schema');
            expect($metadata['resource_count'])->toBeInt();
            expect($metadata['parser'])->toBe('xml_schema');
            expect($metadata['namespace'])->toBe('http://www.w3.org/2001/XMLSchema#');
        });

        // 4.4
        it('has prefixes exactly as xsd and xs mapping to XSD namespace', function () use ($minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            expect($result->prefixes)->toBe([
                'xsd' => 'http://www.w3.org/2001/XMLSchema#',
                'xs' => 'http://www.w3.org/2001/XMLSchema#',
            ]);
        });

        // 4.5
        it('has properties always as empty array', function () use ($minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            expect($result->properties)->toBe([]);
        });

        // 4.6
        it('has shapes always as empty array', function () use ($minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            expect($result->shapes)->toBe([]);
        });

        // 4.7
        it('preserves the original input string exactly in rawContent', function () use ($minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            expect($result->rawContent)->toBe($minimalXsd);
        });

        // 4.8
        it('has resource_count equal to count of classes', function () use ($minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            expect($result->metadata['resource_count'])->toBe(count($result->classes));
        });

        // 4.9
        it('has prefixes with exactly 2 entries', function () use ($minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            expect($result->prefixes)->toHaveCount(2);
        });
    });

    // =========================================================================
    // Task 5: Characterize built-in XSD datatype generation (AC: #2, #3, #4)
    // =========================================================================
    describe('built-in XSD datatypes', function () use ($findClass, $minimalXsd) {
        // 5.1
        it('generates exactly 44 built-in XSD datatype classes', function () use ($minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            expect($result->classes)->toHaveCount(44);
        });

        // 5.2
        it('includes all 19 primitive datatypes with correct URIs', function () use ($minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $uris = array_column($result->classes, 'uri');

            $primitives = [
                'string', 'boolean', 'decimal', 'float', 'double',
                'duration', 'dateTime', 'time', 'date',
                'gYearMonth', 'gYear', 'gMonthDay', 'gDay', 'gMonth',
                'hexBinary', 'base64Binary', 'anyURI', 'QName', 'NOTATION',
            ];

            foreach ($primitives as $primitive) {
                expect($uris)->toContain('http://www.w3.org/2001/XMLSchema#' . $primitive);
            }
        });

        // 5.3
        it('includes all 25 derived datatypes with correct URIs', function () use ($minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $uris = array_column($result->classes, 'uri');

            $derived = [
                'normalizedString', 'token', 'language', 'IDREFS', 'ENTITIES',
                'NMTOKEN', 'NMTOKENS', 'Name', 'NCName', 'ID', 'IDREF', 'ENTITY',
                'integer', 'nonPositiveInteger', 'negativeInteger', 'long', 'int',
                'short', 'byte', 'nonNegativeInteger', 'unsignedLong', 'unsignedInt',
                'unsignedShort', 'unsignedByte', 'positiveInteger',
            ];

            foreach ($derived as $d) {
                expect($uris)->toContain('http://www.w3.org/2001/XMLSchema#' . $d);
            }
        });

        // 5.4
        it('has required keys on each class: uri, label, description, parent_classes, metadata', function () use ($minimalXsd) {
            $result = $this->parser->parse($minimalXsd);

            foreach ($result->classes as $class) {
                expect($class)->toHaveKeys(['uri', 'label', 'description', 'parent_classes', 'metadata']);
            }
        });

        // 5.5
        it('has each class uri starting with XSD namespace', function () use ($minimalXsd) {
            $result = $this->parser->parse($minimalXsd);

            foreach ($result->classes as $class) {
                expect($class['uri'])->toStartWith('http://www.w3.org/2001/XMLSchema#');
            }
        });

        // 5.6
        it('has each class label equal to the datatype name', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);

            foreach ($result->classes as $class) {
                $localName = str_replace('http://www.w3.org/2001/XMLSchema#', '', $class['uri']);
                expect($class['label'])->toBe($localName);
            }
        });

        // 5.7
        it('has each class description as a non-empty string', function () use ($minimalXsd) {
            $result = $this->parser->parse($minimalXsd);

            foreach ($result->classes as $class) {
                expect($class['description'])->toBeString();
                expect($class['description'])->not->toBeEmpty();
            }
        });

        // 5.8
        it('has metadata source as xml_schema for each class', function () use ($minimalXsd) {
            $result = $this->parser->parse($minimalXsd);

            foreach ($result->classes as $class) {
                expect($class['metadata']['source'])->toBe('xml_schema');
            }
        });

        // 5.9
        it('has metadata category set to one of the valid categories', function () use ($minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $validCategories = ['string', 'numeric', 'temporal', 'binary', 'logical', 'other'];

            foreach ($result->classes as $class) {
                expect($validCategories)->toContain($class['metadata']['category']);
            }
        });

        // 5.10
        it('has metadata is_primitive set to a boolean value', function () use ($minimalXsd) {
            $result = $this->parser->parse($minimalXsd);

            foreach ($result->classes as $class) {
                expect($class['metadata']['is_primitive'])->toBeBool();
            }
        });
    });

    // =========================================================================
    // Task 6: Characterize XSD datatype hierarchy (AC: #3)
    // =========================================================================
    describe('datatype hierarchy', function () use ($findClass, $minimalXsd) {
        // 6.1
        it('has empty parent_classes for all primitive types', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);

            $primitives = [
                'string', 'boolean', 'decimal', 'float', 'double',
                'duration', 'dateTime', 'time', 'date',
                'gYearMonth', 'gYear', 'gMonthDay', 'gDay', 'gMonth',
                'hexBinary', 'base64Binary', 'anyURI', 'QName', 'NOTATION',
            ];

            foreach ($primitives as $primitive) {
                $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#' . $primitive);
                expect($class['parent_classes'])->toBe([])
                    ->and($class)->not->toBeNull();
            }
        });

        // 6.2
        it('has integer with parent decimal', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#integer');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#decimal']);
        });

        // 6.3
        it('has normalizedString with parent string', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#normalizedString');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#string']);
        });

        // 6.4
        it('has token with parent normalizedString', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#token');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#normalizedString']);
        });

        // 6.5
        it('has int with parent long', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#int');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#long']);
        });

        // 6.6
        it('has long with parent integer', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#long');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#integer']);
        });

        // 6.7
        it('has short with parent int', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#short');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#int']);
        });

        // 6.8
        it('has byte with parent short', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#byte');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#short']);
        });

        // 6.9
        it('has nonNegativeInteger with parent integer', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#nonNegativeInteger');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#integer']);
        });

        // 6.10
        it('has positiveInteger with parent nonNegativeInteger', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#positiveInteger');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#nonNegativeInteger']);
        });

        // 6.11
        it('has nonPositiveInteger with parent integer', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#nonPositiveInteger');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#integer']);
        });

        // 6.12
        it('has negativeInteger with parent nonPositiveInteger', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#negativeInteger');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#nonPositiveInteger']);
        });

        // 6.13
        it('has unsignedLong with parent nonNegativeInteger', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#unsignedLong');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#nonNegativeInteger']);
        });

        // 6.14
        it('has unsignedInt with parent unsignedLong', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#unsignedInt');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#unsignedLong']);
        });

        // 6.15
        it('has unsignedShort with parent unsignedInt', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#unsignedShort');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#unsignedInt']);
        });

        // 6.16
        it('has unsignedByte with parent unsignedShort', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#unsignedByte');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#unsignedShort']);
        });

        // 6.17
        it('has language with parent token', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#language');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#token']);
        });

        // 6.18
        it('has Name with parent token', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#Name');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#token']);
        });

        // 6.19
        it('has NCName with parent Name', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#NCName');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#Name']);
        });

        // 6.20
        it('has ID with parent NCName', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#ID');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#NCName']);
        });

        // 6.21
        it('has IDREF with parent NCName', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#IDREF');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#NCName']);
        });

        // 6.22
        it('has ENTITY with parent NCName', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#ENTITY');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#NCName']);
        });

        // 6.23
        it('has IDREFS with parent token', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#IDREFS');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#token']);
        });

        // 6.24
        it('has ENTITIES with parent token', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#ENTITIES');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#token']);
        });

        // 6.25
        it('has NMTOKEN with parent token', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#NMTOKEN');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#token']);
        });

        // 6.26
        it('has NMTOKENS with parent token', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#NMTOKENS');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#token']);
        });

        // 6.27
        it('has exactly 1 parent class for each derived type', function () use ($minimalXsd) {
            $result = $this->parser->parse($minimalXsd);

            $derived = [
                'normalizedString', 'token', 'language', 'IDREFS', 'ENTITIES',
                'NMTOKEN', 'NMTOKENS', 'Name', 'NCName', 'ID', 'IDREF', 'ENTITY',
                'integer', 'nonPositiveInteger', 'negativeInteger', 'long', 'int',
                'short', 'byte', 'nonNegativeInteger', 'unsignedLong', 'unsignedInt',
                'unsignedShort', 'unsignedByte', 'positiveInteger',
            ];

            foreach ($result->classes as $class) {
                $localName = str_replace('http://www.w3.org/2001/XMLSchema#', '', $class['uri']);
                if (in_array($localName, $derived, true)) {
                    expect($class['parent_classes'])->toHaveCount(1);
                }
            }
        });

        // 6.28
        it('has parent_classes values as full URIs', function () use ($minimalXsd) {
            $result = $this->parser->parse($minimalXsd);

            foreach ($result->classes as $class) {
                foreach ($class['parent_classes'] as $parentUri) {
                    expect($parentUri)->toStartWith('http://www.w3.org/2001/XMLSchema#');
                }
            }
        });
    });

    // =========================================================================
    // Task 7: Characterize XSD datatype categorization (AC: #4)
    // =========================================================================
    describe('datatype categorization', function () use ($findClass, $minimalXsd) {
        // 7.1
        it('categorizes string types correctly', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $stringTypes = ['string', 'normalizedString', 'token', 'language', 'Name', 'NCName', 'ID', 'IDREF', 'ENTITY'];

            foreach ($stringTypes as $type) {
                $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#' . $type);
                expect($class['metadata']['category'])->toBe('string');
            }
        });

        // 7.2
        it('categorizes numeric types correctly', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $numericTypes = [
                'decimal', 'integer', 'float', 'double', 'long', 'int', 'short', 'byte',
                'nonNegativeInteger', 'positiveInteger', 'nonPositiveInteger', 'negativeInteger',
                'unsignedLong', 'unsignedInt', 'unsignedShort', 'unsignedByte',
            ];

            foreach ($numericTypes as $type) {
                $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#' . $type);
                expect($class['metadata']['category'])->toBe('numeric');
            }
        });

        // 7.3
        it('categorizes temporal types correctly', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $temporalTypes = ['dateTime', 'date', 'time', 'duration', 'gYear', 'gYearMonth', 'gMonth', 'gMonthDay', 'gDay'];

            foreach ($temporalTypes as $type) {
                $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#' . $type);
                expect($class['metadata']['category'])->toBe('temporal');
            }
        });

        // 7.4
        it('categorizes binary types correctly', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $binaryTypes = ['hexBinary', 'base64Binary'];

            foreach ($binaryTypes as $type) {
                $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#' . $type);
                expect($class['metadata']['category'])->toBe('binary');
            }
        });

        // 7.5
        it('categorizes boolean as logical', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#boolean');
            expect($class['metadata']['category'])->toBe('logical');
        });

        // 7.6
        it('categorizes anyURI, QName, NOTATION as other', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $otherTypes = ['anyURI', 'QName', 'NOTATION'];

            foreach ($otherTypes as $type) {
                $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#' . $type);
                expect($class['metadata']['category'])->toBe('other');
            }
        });

        // 7.7
        it('categorizes IDREFS as other despite being string-derived', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#IDREFS');
            expect($class['metadata']['category'])->toBe('other');
        });

        // 7.8
        it('categorizes ENTITIES as other', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#ENTITIES');
            expect($class['metadata']['category'])->toBe('other');
        });

        // 7.9
        it('categorizes NMTOKEN as other', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#NMTOKEN');
            expect($class['metadata']['category'])->toBe('other');
        });

        // 7.10
        it('categorizes NMTOKENS as other', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#NMTOKENS');
            expect($class['metadata']['category'])->toBe('other');
        });
    });

    // =========================================================================
    // Task 8: Characterize primitive vs derived type marking (AC: #2)
    // =========================================================================
    describe('primitive vs derived type marking', function () use ($findClass, $minimalXsd) {
        // 8.1
        it('marks all 19 primitive types with is_primitive true', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);

            $primitives = [
                'string', 'boolean', 'decimal', 'float', 'double',
                'duration', 'dateTime', 'time', 'date',
                'gYearMonth', 'gYear', 'gMonthDay', 'gDay', 'gMonth',
                'hexBinary', 'base64Binary', 'anyURI', 'QName', 'NOTATION',
            ];

            foreach ($primitives as $primitive) {
                $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#' . $primitive);
                expect($class['metadata']['is_primitive'])->toBeTrue();
            }
        });

        // 8.2
        it('marks all 25 derived types with is_primitive false', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);

            $derived = [
                'normalizedString', 'token', 'language', 'IDREFS', 'ENTITIES',
                'NMTOKEN', 'NMTOKENS', 'Name', 'NCName', 'ID', 'IDREF', 'ENTITY',
                'integer', 'nonPositiveInteger', 'negativeInteger', 'long', 'int',
                'short', 'byte', 'nonNegativeInteger', 'unsignedLong', 'unsignedInt',
                'unsignedShort', 'unsignedByte', 'positiveInteger',
            ];

            foreach ($derived as $d) {
                $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#' . $d);
                expect($class['metadata']['is_primitive'])->toBeFalse();
            }
        });

        // 8.3
        it('uses native PHP bool for is_primitive values', function () use ($minimalXsd) {
            $result = $this->parser->parse($minimalXsd);

            foreach ($result->classes as $class) {
                expect($class['metadata']['is_primitive'])->toBeBool();
                expect(is_bool($class['metadata']['is_primitive']))->toBeTrue();
            }
        });
    });

    // =========================================================================
    // Task 9: Characterize additional type extraction from XSD content (AC: #5)
    // =========================================================================
    describe('additional type extraction', function () use ($findClass, $minimalXsd) {
        // 9.1
        it('extracts xs:simpleType with correct URI', function () use ($findClass) {
            $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="MyString">
        <xs:restriction base="xs:string"/>
    </xs:simpleType>
</xs:schema>';

            $result = $this->parser->parse($content);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#MyString');
            expect($class)->not->toBeNull();
            expect($class['uri'])->toBe('http://www.w3.org/2001/XMLSchema#MyString');
        });

        // 9.2
        it('extracts xs:complexType with correct URI', function () use ($findClass) {
            $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:complexType name="PersonType">
        <xs:sequence>
            <xs:element name="name" type="xs:string"/>
        </xs:sequence>
    </xs:complexType>
</xs:schema>';

            $result = $this->parser->parse($content);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#PersonType');
            expect($class)->not->toBeNull();
            expect($class['uri'])->toBe('http://www.w3.org/2001/XMLSchema#PersonType');
        });

        // 9.3
        it('has simpleType with correct label and type_kind', function () use ($findClass) {
            $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="ProductCode">
        <xs:restriction base="xs:string"/>
    </xs:simpleType>
</xs:schema>';

            $result = $this->parser->parse($content);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#ProductCode');
            expect($class['label'])->toBe('ProductCode');
            expect($class['metadata']['type_kind'])->toBe('simpleType');
        });

        // 9.4
        it('has complexType with correct label and type_kind', function () use ($findClass) {
            $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:complexType name="AddressType">
        <xs:sequence>
            <xs:element name="street" type="xs:string"/>
        </xs:sequence>
    </xs:complexType>
</xs:schema>';

            $result = $this->parser->parse($content);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#AddressType');
            expect($class['label'])->toBe('AddressType');
            expect($class['metadata']['type_kind'])->toBe('complexType');
        });

        // 9.5
        it('has category schema_defined for extracted types', function () use ($findClass) {
            $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="MyType">
        <xs:restriction base="xs:string"/>
    </xs:simpleType>
</xs:schema>';

            $result = $this->parser->parse($content);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#MyType');
            expect($class['metadata']['category'])->toBe('schema_defined');
        });

        // 9.6
        it('has source xml_schema for extracted types', function () use ($findClass) {
            $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="MyType">
        <xs:restriction base="xs:string"/>
    </xs:simpleType>
</xs:schema>';

            $result = $this->parser->parse($content);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#MyType');
            expect($class['metadata']['source'])->toBe('xml_schema');
        });

        // 9.7 (updated by Story 15.2): parent_classes now populated from restriction base
        it('has parent_classes populated from restriction base for simple types', function () use ($findClass) {
            $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="MyType">
        <xs:restriction base="xs:string"/>
    </xs:simpleType>
</xs:schema>';

            $result = $this->parser->parse($content);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#MyType');
            expect($class['parent_classes'])->toBe(['http://www.w3.org/2001/XMLSchema#string']);
        });

        // 9.8
        it('gets description from xs:documentation element when present', function () use ($findClass) {
            $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="ProductCode">
        <xs:annotation>
            <xs:documentation>A unique product code</xs:documentation>
        </xs:annotation>
        <xs:restriction base="xs:string"/>
    </xs:simpleType>
</xs:schema>';

            $result = $this->parser->parse($content);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#ProductCode');
            expect($class['description'])->toBe('A unique product code');
        });

        // 9.9
        it('falls back to XML Schema type: {name} when no xs:documentation', function () use ($findClass) {
            $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="Undocumented">
        <xs:restriction base="xs:string"/>
    </xs:simpleType>
</xs:schema>';

            $result = $this->parser->parse($content);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#Undocumented');
            expect($class['description'])->toBe('XML Schema type: Undocumented');
        });

        // 9.10
        it('appends additional types AFTER the 44 built-in datatypes', function () use ($findClass) {
            $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="CustomType">
        <xs:restriction base="xs:string"/>
    </xs:simpleType>
</xs:schema>';

            $result = $this->parser->parse($content);
            // The 44 built-in types come first, then additional types
            expect($result->classes)->toHaveCount(45);

            // The last class should be the custom type
            $lastClass = $result->classes['http://www.w3.org/2001/XMLSchema#CustomType'];
            expect($lastClass)->not->toBeNull();
            expect($lastClass['uri'])->toBe('http://www.w3.org/2001/XMLSchema#CustomType');
        });

        // 9.11
        it('includes both built-in and additional types in resource_count', function () {
            $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="TypeA">
        <xs:restriction base="xs:string"/>
    </xs:simpleType>
    <xs:complexType name="TypeB">
        <xs:sequence>
            <xs:element name="x" type="xs:string"/>
        </xs:sequence>
    </xs:complexType>
</xs:schema>';

            $result = $this->parser->parse($content);
            expect($result->metadata['resource_count'])->toBe(46); // 44 + 2
            expect($result->classes)->toHaveCount(46);
        });

        // 9.12
        it('does not extract anonymous types without @name attribute', function () {
            $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:element name="person">
        <xs:complexType>
            <xs:sequence>
                <xs:element name="name" type="xs:string"/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
</xs:schema>';

            $result = $this->parser->parse($content);
            // Only the 44 built-in types, no anonymous types
            expect($result->classes)->toHaveCount(44);
        });

        // 9.13
        it('skips types with empty name attribute', function () {
            $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="">
        <xs:restriction base="xs:string"/>
    </xs:simpleType>
</xs:schema>';

            $result = $this->parser->parse($content);
            expect($result->classes)->toHaveCount(44);
        });

        // 9.14
        it('extracts multiple simpleType and complexType elements', function () {
            $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="TypeA">
        <xs:restriction base="xs:string"/>
    </xs:simpleType>
    <xs:simpleType name="TypeB">
        <xs:restriction base="xs:integer"/>
    </xs:simpleType>
    <xs:complexType name="TypeC">
        <xs:sequence>
            <xs:element name="x" type="xs:string"/>
        </xs:sequence>
    </xs:complexType>
    <xs:complexType name="TypeD">
        <xs:sequence>
            <xs:element name="y" type="xs:string"/>
        </xs:sequence>
    </xs:complexType>
</xs:schema>';

            $result = $this->parser->parse($content);
            expect($result->classes)->toHaveCount(48); // 44 + 4

            $uris = array_column($result->classes, 'uri');
            expect($uris)->toContain('http://www.w3.org/2001/XMLSchema#TypeA');
            expect($uris)->toContain('http://www.w3.org/2001/XMLSchema#TypeB');
            expect($uris)->toContain('http://www.w3.org/2001/XMLSchema#TypeC');
            expect($uris)->toContain('http://www.w3.org/2001/XMLSchema#TypeD');
        });

        // 9.15
        it('still includes all 44 built-in datatypes even with only custom types in content', function () use ($findClass) {
            $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="OnlyCustom">
        <xs:restriction base="xs:string"/>
    </xs:simpleType>
</xs:schema>';

            $result = $this->parser->parse($content);
            // 44 built-in + 1 custom
            expect($result->classes)->toHaveCount(45);

            // Verify a built-in type exists
            $stringClass = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#string');
            expect($stringClass)->not->toBeNull();
        });

        // 9.16
        it('extracts documentation via .//xs:documentation XPath nested within xs:annotation', function () use ($findClass) {
            $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:complexType name="Nested">
        <xs:annotation>
            <xs:documentation>Nested documentation text</xs:documentation>
        </xs:annotation>
        <xs:sequence>
            <xs:element name="x" type="xs:string"/>
        </xs:sequence>
    </xs:complexType>
</xs:schema>';

            $result = $this->parser->parse($content);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#Nested');
            expect($class['description'])->toBe('Nested documentation text');
        });

        // 9.17 (review addition): additional types do NOT have is_primitive key
        it('does not include is_primitive key in additional type metadata', function () use ($findClass) {
            $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="NoPrimitiveKey">
        <xs:restriction base="xs:string"/>
    </xs:simpleType>
</xs:schema>';

            $result = $this->parser->parse($content);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#NoPrimitiveKey');
            expect($class)->not->toBeNull();
            expect($class['metadata'])->not->toHaveKey('is_primitive');
            expect($class['metadata'])->toHaveKey('type_kind');
        });

        // 9.18 (review addition): built-in types do NOT have type_kind key
        it('does not include type_kind key in built-in type metadata', function () use ($findClass, $minimalXsd) {
            $result = $this->parser->parse($minimalXsd);
            $stringClass = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#string');
            expect($stringClass)->not->toBeNull();
            expect($stringClass['metadata'])->not->toHaveKey('type_kind');
            expect($stringClass['metadata'])->toHaveKey('is_primitive');
        });

        // 9.19
        it('uses first documentation element only when multiple exist', function () use ($findClass) {
            $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="MultiDoc">
        <xs:annotation>
            <xs:documentation>First documentation</xs:documentation>
            <xs:documentation>Second documentation</xs:documentation>
        </xs:annotation>
        <xs:restriction base="xs:string"/>
    </xs:simpleType>
</xs:schema>';

            $result = $this->parser->parse($content);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#MultiDoc');
            expect($class['description'])->toBe('First documentation');
        });
    });

    // =========================================================================
    // Task 10: Characterize error handling (AC: #7, #8)
    // =========================================================================
    describe('error handling', function () {
        // 10.1
        it('throws ParseException for non-XML content', function () {
            expect(fn () => $this->parser->parse('invalid xml content'))
                ->toThrow(ParseException::class);
        });

        // 10.2
        it('has XML Schema parsing failed prefix for wrapped exceptions', function () {
            try {
                $this->parser->parse('invalid xml content');
                $this->fail('Expected ParseException');
            } catch (ParseException $e) {
                expect($e->getMessage())->toStartWith('XML Schema parsing failed: ');
            }
        });

        // 10.3
        it('has previous exception set for wrapped exceptions', function () {
            try {
                $this->parser->parse('invalid xml content');
                $this->fail('Expected ParseException');
            } catch (ParseException $e) {
                expect($e->getPrevious())->not->toBeNull();
            }
        });

        // 10.4
        it('has exception code 0', function () {
            try {
                $this->parser->parse('invalid xml content');
                $this->fail('Expected ParseException');
            } catch (ParseException $e) {
                expect($e->getCode())->toBe(0);
            }
        });

        // 10.5
        it('throws for malformed XML with unclosed tags', function () {
            $malformed = '<?xml version="1.0"?><xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"><unclosed></xs:schema>';
            expect(fn () => $this->parser->parse($malformed))
                ->toThrow(ParseException::class);
        });

        // 10.6
        it('double-wraps the Invalid XML Schema content exception via self-wrapping', function () {
            try {
                $this->parser->parse('not xml');
                $this->fail('Expected ParseException');
            } catch (ParseException $e) {
                // The outer message wraps the inner one
                expect($e->getMessage())->toBe('XML Schema parsing failed: Invalid XML Schema content');
                // The inner exception is an ParseException too
                expect($e->getPrevious())->toBeInstanceOf(ParseException::class);
                expect($e->getPrevious()->getMessage())->toBe('Invalid XML Schema content');
            }
        });

        // 10.7
        it('throws for empty string via simplexml_load_string returning false', function () {
            try {
                $this->parser->parse('');
                $this->fail('Expected ParseException');
            } catch (ParseException $e) {
                expect($e->getMessage())->toBe('XML Schema parsing failed: Invalid XML Schema content');
                expect($e->getPrevious())->toBeInstanceOf(ParseException::class);
            }
        });

        // 10.8
        it('parses successfully with valid XML but no XSD namespace', function () {
            $content = '<?xml version="1.0"?><root><child>test</child></root>';
            $result = $this->parser->parse($content);

            // parse() does not validate XSD namespace -- only canParse() does
            expect($result)->toBeInstanceOf(\Youri\vandenBogert\Software\ParserCore\ValueObjects\ParsedOntology::class);
            expect($result)->toHaveProperties(['metadata', 'prefixes', 'classes', 'properties', 'shapes', 'rawContent']);
            // Still generates all 44 built-in types
            expect($result->classes)->toHaveCount(44);
        });

        // 10.9
        it('self-wraps: inner OIE thrown inside try is caught and re-wrapped', function () {
            try {
                $this->parser->parse('<<<invalid>>>');
                $this->fail('Expected ParseException');
            } catch (ParseException $e) {
                // Outer exception
                expect($e->getMessage())->toStartWith('XML Schema parsing failed: ');
                expect($e->getCode())->toBe(0);
                // Inner exception (previous)
                $previous = $e->getPrevious();
                expect($previous)->not->toBeNull();
                expect($previous)->toBeInstanceOf(ParseException::class);
                expect($previous->getMessage())->toBe('Invalid XML Schema content');
                // The previous of the inner exception should be null
                // (The inner OIE was created without a $previous)
                expect($previous->getPrevious())->toBeNull();
            }
        });

        // 10.10
        it('wraps all exceptions uniformly via single catch block', function () {
            // Any exception thrown inside the try block is caught by catch(\Exception $e)
            // and wrapped with the same prefix
            try {
                $this->parser->parse('');
                $this->fail('Expected ParseException');
            } catch (ParseException $e) {
                // Verify the wrapping pattern: 'XML Schema parsing failed: ' + original message
                expect($e->getMessage())->toMatch('/^XML Schema parsing failed: /');
            }
        });
    });

    // =========================================================================
    // Task 11: Characterize standalone architecture (AC: #12, #13)
    // =========================================================================
    describe('standalone architecture', function () {
        // 11.1
        it('implements OntologyParserInterface', function () {
            expect($this->parser)->toBeInstanceOf(OntologyParserInterface::class);
        });

        // 11.2
        it('does not extend any parent class', function () {
            $reflection = new ReflectionClass(XmlSchemaParser::class);
            expect($reflection->getParentClass())->toBeFalse();
        });

        // 11.3
        it('has no dependency on EasyRdf', function () {
            // Verify no EasyRdf classes are used or referenced
            $sourceFile = (new ReflectionClass(XmlSchemaParser::class))->getFileName();
            $sourceCode = file_get_contents($sourceFile);
            expect($sourceCode)->not->toContain('EasyRdf');
        });

        // 11.4
        it('has no ParsedRdf in its codebase and returns ParsedOntology', function () {
            $sourceFile = (new ReflectionClass(XmlSchemaParser::class))->getFileName();
            $sourceCode = file_get_contents($sourceFile);
            expect($sourceCode)->not->toContain('ParsedRdf');

            $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
</xs:schema>';
            $result = $this->parser->parse($content);
            expect($result)->toBeInstanceOf(\Youri\vandenBogert\Software\ParserCore\ValueObjects\ParsedOntology::class);
        });

        // 11.5
        it('has the 3 public methods matching OntologyParserInterface', function () {
            $reflection = new ReflectionClass(XmlSchemaParser::class);
            $publicMethods = array_filter(
                $reflection->getMethods(ReflectionMethod::IS_PUBLIC),
                fn ($m) => $m->getDeclaringClass()->getName() === XmlSchemaParser::class
            );
            $methodNames = array_map(fn ($m) => $m->getName(), $publicMethods);
            sort($methodNames);

            expect($methodNames)->toBe(['canParse', 'getSupportedFormats', 'parse']);
        });

        // 11.6
        it('has no global state side effects from parse()', function () {
            // Parse should not change any global state (no EasyRdf\RdfNamespace::set() calls)
            $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
</xs:schema>';

            // Call parse twice; both should return identical results
            $result1 = $this->parser->parse($content);
            $result2 = $this->parser->parse($content);

            expect($result1)->toEqual($result2);
        });
    });

    // =========================================================================
    // Task 12: Characterize XPath namespace registration behavior (AC: #1)
    // =========================================================================
    describe('XPath namespace registration', function () use ($findClass) {
        // 12.1
        it('registers xs XPath namespace to enable XPath queries', function () use ($findClass) {
            $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="XPathTest">
        <xs:restriction base="xs:string"/>
    </xs:simpleType>
</xs:schema>';

            $result = $this->parser->parse($content);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#XPathTest');
            expect($class)->not->toBeNull();
        });

        // 12.2
        it('extracts types via //xs:simpleType[@name] and //xs:complexType[@name] XPath', function () use ($findClass) {
            $content = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:simpleType name="SimpleOne">
        <xs:restriction base="xs:string"/>
    </xs:simpleType>
    <xs:complexType name="ComplexOne">
        <xs:sequence>
            <xs:element name="x" type="xs:string"/>
        </xs:sequence>
    </xs:complexType>
</xs:schema>';

            $result = $this->parser->parse($content);
            expect($findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#SimpleOne'))->not->toBeNull();
            expect($findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#ComplexOne'))->not->toBeNull();
        });

        // 12.3
        it('extracts types from schemas using xsd: prefix instead of xs:', function () use ($findClass) {
            // The parser registers 'xs' as the XPath namespace, which matches the
            // http://www.w3.org/2001/XMLSchema namespace regardless of document prefix
            $content = '<?xml version="1.0"?>
<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema">
    <xsd:simpleType name="XsdPrefixType">
        <xsd:restriction base="xsd:string"/>
    </xsd:simpleType>
</xsd:schema>';

            $result = $this->parser->parse($content);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#XsdPrefixType');
            expect($class)->not->toBeNull();
            expect($class['label'])->toBe('XsdPrefixType');
        });

        // 12.3b (updated by Story 15.1): documentation now works for xsd: prefix schemas
        it('extracts documentation from xsd: prefix schemas after namespace registration fix', function () use ($findClass) {
            // Story 15.1 fixed sub-XPath queries by registering namespace on child elements.
            // Documentation is now correctly extracted regardless of document prefix.
            $content = '<?xml version="1.0"?>
<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema">
    <xsd:simpleType name="DocTestType">
        <xsd:annotation>
            <xsd:documentation>This documentation should NOT be extracted</xsd:documentation>
        </xsd:annotation>
        <xsd:restriction base="xsd:string"/>
    </xsd:simpleType>
</xsd:schema>';

            $result = $this->parser->parse($content);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#DocTestType');
            expect($class)->not->toBeNull();
            // Documentation is now correctly extracted after Story 15.1 fix
            expect($class['description'])->toBe('This documentation should NOT be extracted');
        });

        // 12.4
        it('handles schemas with default namespace (no prefix)', function () use ($findClass) {
            $content = '<?xml version="1.0"?>
<schema xmlns="http://www.w3.org/2001/XMLSchema">
    <simpleType name="DefaultNsType">
        <restriction base="string"/>
    </simpleType>
</schema>';

            $result = $this->parser->parse($content);
            $class = $findClass($result->classes, 'http://www.w3.org/2001/XMLSchema#DefaultNsType');

            // XPath with registered 'xs' prefix matches elements in the
            // http://www.w3.org/2001/XMLSchema namespace, regardless of document prefix.
            // Default namespace (no prefix) elements ARE in this namespace,
            // so XPath //xs:simpleType[@name] SHOULD match them.
            expect($class)->not->toBeNull();
            expect($class['label'])->toBe('DefaultNsType');
        });
    });
});
