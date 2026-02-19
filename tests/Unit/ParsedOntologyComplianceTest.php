<?php

declare(strict_types=1);

use Youri\vandenBogert\Software\ParserCore\Contracts\OntologyParserInterface;
use Youri\vandenBogert\Software\ParserCore\Exceptions\ParseException;
use Youri\vandenBogert\Software\ParserCore\ValueObjects\ParsedOntology;
use Youri\vandenBogert\Software\ParserXmlSchema\XmlSchemaParser;

$minimalXsd = '<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
           targetNamespace="http://www.w3.org/2001/XMLSchema">
</xs:schema>';

describe('XmlSchemaParser ParsedOntology compliance', function () use ($minimalXsd) {
    beforeEach(function () {
        $this->parser = new XmlSchemaParser();
    });

    it('implements OntologyParserInterface', function () {
        expect($this->parser)->toBeInstanceOf(OntologyParserInterface::class);
    });

    it('returns ParsedOntology instance from parse()', function () use ($minimalXsd) {
        $result = $this->parser->parse($minimalXsd);
        expect($result)->toBeInstanceOf(ParsedOntology::class);
    });

    it('has classes containing XSD datatypes keyed by URI', function () use ($minimalXsd) {
        $result = $this->parser->parse($minimalXsd);

        expect($result->classes)->toBeArray();
        expect($result->classes)->not->toBeEmpty();
        expect($result->classes)->toHaveCount(44);

        // Classes are indexed by URI
        expect($result->classes)->toHaveKey('http://www.w3.org/2001/XMLSchema#string');
        expect($result->classes)->toHaveKey('http://www.w3.org/2001/XMLSchema#integer');
        expect($result->classes)->toHaveKey('http://www.w3.org/2001/XMLSchema#boolean');
    });

    it('has prefixes with xsd and xs mappings', function () use ($minimalXsd) {
        $result = $this->parser->parse($minimalXsd);

        expect($result->prefixes)->toBeArray();
        expect($result->prefixes)->toHaveCount(2);
        expect($result->prefixes['xsd'])->toBe('http://www.w3.org/2001/XMLSchema#');
        expect($result->prefixes['xs'])->toBe('http://www.w3.org/2001/XMLSchema#');
    });

    it('has empty properties array', function () use ($minimalXsd) {
        $result = $this->parser->parse($minimalXsd);

        expect($result->properties)->toBeArray();
        expect($result->properties)->toBeEmpty();
    });

    it('has empty shapes array', function () use ($minimalXsd) {
        $result = $this->parser->parse($minimalXsd);

        expect($result->shapes)->toBeArray();
        expect($result->shapes)->toBeEmpty();
    });

    it('has empty restrictions array', function () use ($minimalXsd) {
        $result = $this->parser->parse($minimalXsd);

        expect($result->restrictions)->toBeArray();
        expect($result->restrictions)->toBeEmpty();
    });

    it('preserves rawContent from input', function () use ($minimalXsd) {
        $result = $this->parser->parse($minimalXsd);

        expect($result->rawContent)->toBe($minimalXsd);
    });

    it('has metadata with required keys', function () use ($minimalXsd) {
        $result = $this->parser->parse($minimalXsd);

        expect($result->metadata)->toBeArray();
        expect($result->metadata)->toHaveKeys(['format', 'resource_count', 'parser', 'namespace']);
        expect($result->metadata['format'])->toBe('xml_schema');
        expect($result->metadata['parser'])->toBe('xml_schema');
        expect($result->metadata['namespace'])->toBe('http://www.w3.org/2001/XMLSchema#');
        expect($result->metadata['resource_count'])->toBe(44);
    });

    it('throws ParseException for invalid content', function () {
        expect(fn () => $this->parser->parse('not valid xml at all'))
            ->toThrow(ParseException::class, 'XML Schema parsing failed');
    });

    it('throws ParseException with previous exception set', function () {
        try {
            $this->parser->parse('not valid xml');
            $this->fail('Expected ParseException');
        } catch (ParseException $e) {
            expect($e->getPrevious())->not->toBeNull();
        }
    });
});
