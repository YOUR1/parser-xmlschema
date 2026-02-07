<?php

namespace App\Services\Ontology\Parsers;

use App\Services\Ontology\Exceptions\OntologyImportException;

class XmlSchemaParser implements OntologyParserInterface
{
    /**
     * XML Schema datatypes that should be treated as RDF classes
     */
    private const XSD_DATATYPES = [
        // Primitive datatypes
        'string' => 'The string datatype represents character strings in XML',
        'boolean' => 'Boolean represents the values of two-valued logic: true, false',
        'decimal' => 'Decimal represents a subset of the real numbers',
        'float' => 'Single-precision 32-bit floating point type',
        'double' => 'Double-precision 64-bit floating point type',
        'duration' => 'Duration represents a duration of time',
        'dateTime' => 'DateTime represents instants of time',
        'time' => 'Time represents instants of time that recur at the same point in each calendar day',
        'date' => 'Date represents top-open intervals of exactly one day in length',
        'gYearMonth' => 'gYearMonth represents specific gregorian months in specific gregorian years',
        'gYear' => 'gYear represents gregorian years',
        'gMonthDay' => 'gMonthDay represents gregorian dates that recur',
        'gDay' => 'gDay represents whole days within an arbitrary month',
        'gMonth' => 'gMonth represents whole gregorian months within an arbitrary year',
        'hexBinary' => 'hexBinary represents arbitrary hex-encoded binary data',
        'base64Binary' => 'base64Binary represents Base64-encoded arbitrary binary data',
        'anyURI' => 'anyURI represents a Uniform Resource Identifier Reference (URI)',
        'QName' => 'QName represents XML qualified names',
        'NOTATION' => 'NOTATION represents the NOTATION attribute type from [XML]',

        // Derived datatypes
        'normalizedString' => 'normalizedString represents white space normalized strings',
        'token' => 'Token represents tokenized strings',
        'language' => 'Language represents natural language identifiers',
        'IDREFS' => 'IDREFS represents the IDREFS attribute type from [XML]',
        'ENTITIES' => 'ENTITIES represents the ENTITIES attribute type from [XML]',
        'NMTOKEN' => 'NMTOKEN represents the NMTOKEN attribute type from [XML]',
        'NMTOKENS' => 'NMTOKENS represents the NMTOKENS attribute type from [XML]',
        'Name' => 'Name represents XML Names',
        'NCName' => 'NCName represents XML "non-colonized" Names',
        'ID' => 'ID represents the ID attribute type from [XML]',
        'IDREF' => 'IDREF represents the IDREF attribute type from [XML]',
        'ENTITY' => 'ENTITY represents the ENTITY attribute type from [XML]',
        'integer' => 'Integer is derived from decimal by fixing the value of fractionDigits to be 0',
        'nonPositiveInteger' => 'nonPositiveInteger includes all integers less than or equal to zero',
        'negativeInteger' => 'negativeInteger includes all integers less than zero',
        'long' => 'Long is derived from integer by setting maxInclusive to 9223372036854775807',
        'int' => 'Int is derived from long by setting maxInclusive to 2147483647',
        'short' => 'Short is derived from int by setting maxInclusive to 32767',
        'byte' => 'Byte is derived from short by setting maxInclusive to 127',
        'nonNegativeInteger' => 'nonNegativeInteger includes all integers greater than or equal to zero',
        'unsignedLong' => 'UnsignedLong is derived from nonNegativeInteger by setting maxInclusive',
        'unsignedInt' => 'UnsignedInt is derived from unsignedLong by setting maxInclusive to 4294967295',
        'unsignedShort' => 'UnsignedShort is derived from unsignedInt by setting maxInclusive to 65535',
        'unsignedByte' => 'UnsignedByte is derived from unsignedShort by setting maxInclusive to 255',
        'positiveInteger' => 'positiveInteger includes all integers greater than zero',
    ];

    /**
     * Hierarchy of XML Schema datatypes
     */
    private const XSD_HIERARCHY = [
        'normalizedString' => 'string',
        'token' => 'normalizedString',
        'language' => 'token',
        'IDREFS' => 'token',
        'ENTITIES' => 'token',
        'NMTOKEN' => 'token',
        'NMTOKENS' => 'token',
        'Name' => 'token',
        'NCName' => 'Name',
        'ID' => 'NCName',
        'IDREF' => 'NCName',
        'ENTITY' => 'NCName',
        'integer' => 'decimal',
        'nonPositiveInteger' => 'integer',
        'negativeInteger' => 'nonPositiveInteger',
        'long' => 'integer',
        'int' => 'long',
        'short' => 'int',
        'byte' => 'short',
        'nonNegativeInteger' => 'integer',
        'unsignedLong' => 'nonNegativeInteger',
        'unsignedInt' => 'unsignedLong',
        'unsignedShort' => 'unsignedInt',
        'unsignedByte' => 'unsignedShort',
        'positiveInteger' => 'nonNegativeInteger',
    ];

    public function parse(string $content, array $options = []): array
    {
        try {
            // Parse XML content
            $xml = simplexml_load_string($content);
            if ($xml === false) {
                throw new OntologyImportException('Invalid XML Schema content');
            }

            // Register XML Schema namespace
            $xml->registerXPathNamespace('xs', 'http://www.w3.org/2001/XMLSchema');

            // Generate RDF classes for all XSD datatypes
            $classes = $this->generateDatatypeClasses();

            // Extract any additional types defined in the schema
            $additionalClasses = $this->extractAdditionalTypes($xml);
            $classes = array_merge($classes, $additionalClasses);

            return [
                'metadata' => [
                    'format' => 'xml_schema',
                    'resource_count' => count($classes),
                    'parser' => 'xml_schema',
                    'namespace' => 'http://www.w3.org/2001/XMLSchema#',
                ],
                'prefixes' => [
                    'xsd' => 'http://www.w3.org/2001/XMLSchema#',
                    'xs' => 'http://www.w3.org/2001/XMLSchema#',
                ],
                'classes' => $classes,
                'properties' => [], // XML Schema doesn't define RDF properties
                'shapes' => [],
                'raw_content' => $content,
            ];

        } catch (\Exception $e) {
            throw new OntologyImportException('XML Schema parsing failed: '.$e->getMessage(), 0, $e);
        }
    }

    public function canParse(string $content): bool
    {
        $content = trim($content);

        // Check if it's an XML Schema document
        return str_starts_with($content, '<?xml') &&
                (str_contains($content, 'http://www.w3.org/2001/XMLSchema') ||
                 str_contains($content, 'targetNamespace="http://www.w3.org/2001/XMLSchema"'));
    }

    public function getSupportedFormats(): array
    {
        return ['xml_schema', 'xsd'];
    }

    /**
     * Generate RDF classes for all known XML Schema datatypes
     */
    private function generateDatatypeClasses(): array
    {
        $classes = [];
        $baseUri = 'http://www.w3.org/2001/XMLSchema#';

        foreach (self::XSD_DATATYPES as $datatype => $description) {
            $parentClasses = [];

            // Add parent class if there's a hierarchy
            if (isset(self::XSD_HIERARCHY[$datatype])) {
                $parentClasses[] = $baseUri.self::XSD_HIERARCHY[$datatype];
            }

            $classes[] = [
                'uri' => $baseUri.$datatype,
                'label' => $datatype,
                'description' => $description,
                'parent_classes' => $parentClasses,
                'metadata' => [
                    'source' => 'xml_schema',
                    'category' => $this->getDatatypeCategory($datatype),
                    'is_primitive' => $this->isPrimitiveType($datatype),
                ],
            ];
        }

        return $classes;
    }

    /**
     * Extract any additional types defined in the XML Schema
     */
    private function extractAdditionalTypes(\SimpleXMLElement $xml): array
    {
        $classes = [];

        // Look for complex types and simple types defined in the schema
        $simpleTypes = $xml->xpath('//xs:simpleType[@name]');
        $complexTypes = $xml->xpath('//xs:complexType[@name]');

        foreach (array_merge($simpleTypes ?: [], $complexTypes ?: []) as $type) {
            $name = (string) $type['name'];
            if (empty($name)) {
                continue;
            }

            $uri = 'http://www.w3.org/2001/XMLSchema#'.$name;

            // Get documentation if available
            $documentation = '';
            $docElements = $type->xpath('.//xs:documentation');
            if (! empty($docElements)) {
                $documentation = (string) $docElements[0];
            }

            $classes[] = [
                'uri' => $uri,
                'label' => $name,
                'description' => $documentation ?: "XML Schema type: $name",
                'parent_classes' => [],
                'metadata' => [
                    'source' => 'xml_schema',
                    'category' => 'schema_defined',
                    'type_kind' => $type->getName(), // simpleType or complexType
                ],
            ];
        }

        return $classes;
    }

    /**
     * Determine if a datatype is primitive or derived
     */
    private function isPrimitiveType(string $datatype): bool
    {
        return ! isset(self::XSD_HIERARCHY[$datatype]);
    }

    /**
     * Categorize datatypes by their nature
     */
    private function getDatatypeCategory(string $datatype): string
    {
        if (in_array($datatype, ['string', 'normalizedString', 'token', 'language', 'Name', 'NCName', 'ID', 'IDREF', 'ENTITY'])) {
            return 'string';
        }

        if (in_array($datatype, ['decimal', 'integer', 'float', 'double', 'long', 'int', 'short', 'byte', 'nonNegativeInteger', 'positiveInteger', 'nonPositiveInteger', 'negativeInteger', 'unsignedLong', 'unsignedInt', 'unsignedShort', 'unsignedByte'])) {
            return 'numeric';
        }

        if (in_array($datatype, ['dateTime', 'date', 'time', 'duration', 'gYear', 'gYearMonth', 'gMonth', 'gMonthDay', 'gDay'])) {
            return 'temporal';
        }

        if (in_array($datatype, ['hexBinary', 'base64Binary'])) {
            return 'binary';
        }

        if ($datatype === 'boolean') {
            return 'logical';
        }

        return 'other';
    }
}
