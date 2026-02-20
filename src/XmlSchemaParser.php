<?php

declare(strict_types=1);

namespace Youri\vandenBogert\Software\ParserXmlSchema;

use Youri\vandenBogert\Software\ParserCore\Contracts\OntologyParserInterface;
use Youri\vandenBogert\Software\ParserCore\Exceptions\ParseException;
use Youri\vandenBogert\Software\ParserCore\ValueObjects\ParsedOntology;

class XmlSchemaParser implements OntologyParserInterface
{
    /**
     * @var array<string, string>
     */
    private const XSD_DATATYPES = [
        // 19 Primitive datatypes
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
        // 25 Derived datatypes
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
     * @var array<string, string>
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

    private const XSD_NAMESPACE = 'http://www.w3.org/2001/XMLSchema';

    private const XSD_BASE_URI = 'http://www.w3.org/2001/XMLSchema#';

    /**
     * @var list<string>
     */
    private const FACET_NAMES = [
        'minInclusive',
        'maxInclusive',
        'minExclusive',
        'maxExclusive',
        'minLength',
        'maxLength',
        'length',
        'totalDigits',
        'fractionDigits',
        'pattern',
        'whiteSpace',
        'enumeration',
    ];

    public function parse(string $content, array $options = []): ParsedOntology
    {
        try {
            $xml = simplexml_load_string($content);
            if ($xml === false) {
                throw new ParseException('Invalid XML Schema content');
            }

            $this->registerXsdNamespace($xml);

            $classes = $this->generateDatatypeClasses();
            $additionalClasses = $this->extractAdditionalTypes($xml);
            $classes = array_merge($classes, $additionalClasses);

            $properties = $this->extractTopLevelDeclarations($xml);

            return new ParsedOntology(
                classes: $this->indexByUri($classes),
                properties: $properties,
                prefixes: [
                    'xsd' => self::XSD_BASE_URI,
                    'xs' => self::XSD_BASE_URI,
                ],
                shapes: [],
                restrictions: [],
                metadata: [
                    'format' => 'xml_schema',
                    'resource_count' => count($classes) + count($properties),
                    'parser' => 'xml_schema',
                    'namespace' => self::XSD_BASE_URI,
                ],
                rawContent: $content,
            );
        } catch (\Exception $e) {
            throw new ParseException(
                'XML Schema parsing failed: ' . $e->getMessage(),
                0,
                $e,
            );
        }
    }

    /**
     * Register the XSD namespace with the 'xs' prefix for XPath queries,
     * regardless of what prefix the document actually uses (xs:, xsd:, or default).
     */
    private function registerXsdNamespace(\SimpleXMLElement $xml): void
    {
        $xml->registerXPathNamespace('xs', self::XSD_NAMESPACE);
    }

    /**
     * Extract top-level element and attribute declarations as properties.
     *
     * @return array<string, array<string, mixed>>
     */
    private function extractTopLevelDeclarations(\SimpleXMLElement $xml): array
    {
        /** @var array<string, array<string, mixed>> $properties */
        $properties = [];

        $targetNamespace = $this->getTargetNamespace($xml);
        $namespacePrefixes = $this->getNamespacePrefixes($xml);

        // Extract top-level xs:element declarations (direct children of xs:schema)
        $elements = $xml->xpath('/xs:schema/xs:element[@name]');
        if (is_array($elements)) {
            foreach ($elements as $element) {
                $name = (string) ($element['name'] ?? '');
                if ($name === '') {
                    continue;
                }

                $typeRef = (string) ($element['type'] ?? '');
                $substitutionGroup = (string) ($element['substitutionGroup'] ?? '');
                $abstract = (string) ($element['abstract'] ?? 'false');
                $nillable = (string) ($element['nillable'] ?? 'false');

                $uri = self::XSD_BASE_URI . $name;
                $properties[$uri] = [
                    'uri' => $uri,
                    'name' => $name,
                    'type' => $typeRef !== '' ? $this->resolveTypeReference($typeRef, $namespacePrefixes, $targetNamespace) : '',
                    'substitution_group' => $substitutionGroup !== '' ? $this->resolveTypeReference($substitutionGroup, $namespacePrefixes, $targetNamespace) : null,
                    'abstract' => $abstract === 'true',
                    'nillable' => $nillable === 'true',
                    'metadata' => [
                        'kind' => 'element',
                        'source' => 'xml_schema',
                    ],
                ];
            }
        }

        // Extract top-level xs:attribute declarations (direct children of xs:schema)
        $attributes = $xml->xpath('/xs:schema/xs:attribute[@name]');
        if (is_array($attributes)) {
            foreach ($attributes as $attribute) {
                $name = (string) ($attribute['name'] ?? '');
                if ($name === '') {
                    continue;
                }

                $typeRef = (string) ($attribute['type'] ?? '');
                $default = isset($attribute['default']) ? (string) $attribute['default'] : null;
                $fixed = isset($attribute['fixed']) ? (string) $attribute['fixed'] : null;

                $uri = self::XSD_BASE_URI . $name;
                $properties[$uri] = [
                    'uri' => $uri,
                    'name' => $name,
                    'type' => $typeRef !== '' ? $this->resolveTypeReference($typeRef, $namespacePrefixes, $targetNamespace) : '',
                    'default' => $default,
                    'fixed' => $fixed,
                    'metadata' => [
                        'kind' => 'attribute',
                        'source' => 'xml_schema',
                    ],
                ];
            }
        }

        return $properties;
    }

    public function canParse(string $content): bool
    {
        $content = trim($content);

        return str_starts_with($content, '<?xml') &&
            (str_contains($content, 'http://www.w3.org/2001/XMLSchema') ||
             str_contains($content, 'targetNamespace="http://www.w3.org/2001/XMLSchema"'));
    }

    /**
     * @return list<string>
     */
    public function getSupportedFormats(): array
    {
        return ['xml_schema', 'xsd'];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function generateDatatypeClasses(): array
    {
        $classes = [];
        $baseUri = 'http://www.w3.org/2001/XMLSchema#';

        foreach (self::XSD_DATATYPES as $datatype => $description) {
            /** @var list<string> $parentClasses */
            $parentClasses = [];

            if (isset(self::XSD_HIERARCHY[$datatype])) {
                $parentClasses[] = $baseUri . self::XSD_HIERARCHY[$datatype];
            }

            $classes[] = [
                'uri' => $baseUri . $datatype,
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
     * @return list<array<string, mixed>>
     */
    private function extractAdditionalTypes(\SimpleXMLElement $xml): array
    {
        $classes = [];

        // Determine the target namespace for resolving local type references
        $targetNamespace = $this->getTargetNamespace($xml);
        $namespacePrefixes = $this->getNamespacePrefixes($xml);

        $simpleTypes = $xml->xpath('//xs:simpleType[@name]');
        $complexTypes = $xml->xpath('//xs:complexType[@name]');

        foreach (array_merge($simpleTypes ?: [], $complexTypes ?: []) as $type) {
            $name = (string) $type['name'];
            if ($name === '') {
                continue;
            }

            $uri = self::XSD_BASE_URI . $name;

            // Register namespace on child element for sub-XPath queries
            $type->registerXPathNamespace('xs', self::XSD_NAMESPACE);

            $documentation = $this->extractDocumentation($type);

            /** @var array<string, mixed> $metadata */
            $metadata = [
                'source' => 'xml_schema',
                'category' => 'schema_defined',
                'type_kind' => $type->getName(),
            ];

            /** @var list<string> $parentClasses */
            $parentClasses = [];

            if ($type->getName() === 'simpleType') {
                $derivation = $this->extractSimpleTypeDerivation($type, $namespacePrefixes, $targetNamespace);
                $metadata = array_merge($metadata, $derivation['metadata']);
                $parentClasses = $derivation['parent_classes'];

                $facets = $this->extractRestrictionFacets($type);
                if ($facets !== []) {
                    $metadata['facets'] = $facets;
                }
            } elseif ($type->getName() === 'complexType') {
                $complexInfo = $this->extractComplexTypeStructure($type, $namespacePrefixes, $targetNamespace);
                $metadata = array_merge($metadata, $complexInfo['metadata']);
                $parentClasses = $complexInfo['parent_classes'];
            }

            $classes[] = [
                'uri' => $uri,
                'label' => $name,
                'description' => $documentation !== '' ? $documentation : "XML Schema type: {$name}",
                'parent_classes' => $parentClasses,
                'metadata' => $metadata,
            ];
        }

        return $classes;
    }

    /**
     * Extract simple type derivation info (restriction, list, union).
     *
     * @param array<string, string> $namespacePrefixes
     * @return array{parent_classes: list<string>, metadata: array<string, mixed>}
     */
    private function extractSimpleTypeDerivation(
        \SimpleXMLElement $type,
        array $namespacePrefixes,
        string $targetNamespace,
    ): array {
        /** @var list<string> $parentClasses */
        $parentClasses = [];
        /** @var array<string, mixed> $metadata */
        $metadata = [];

        // Check for xs:restriction
        $restriction = $type->xpath('.//xs:restriction');
        if (is_array($restriction) && $restriction !== []) {
            $base = (string) ($restriction[0]['base'] ?? '');
            if ($base !== '') {
                $resolvedBase = $this->resolveTypeReference($base, $namespacePrefixes, $targetNamespace);
                $parentClasses[] = $resolvedBase;
                $metadata['derivation_method'] = 'restriction';
                $metadata['base_type'] = $resolvedBase;
            }

            return ['parent_classes' => $parentClasses, 'metadata' => $metadata];
        }

        // Check for xs:list
        $list = $type->xpath('.//xs:list');
        if (is_array($list) && $list !== []) {
            $itemType = (string) ($list[0]['itemType'] ?? '');
            if ($itemType !== '') {
                $resolvedItemType = $this->resolveTypeReference($itemType, $namespacePrefixes, $targetNamespace);
                $metadata['derivation_method'] = 'list';
                $metadata['list_item_type'] = $resolvedItemType;
            }

            return ['parent_classes' => $parentClasses, 'metadata' => $metadata];
        }

        // Check for xs:union
        $union = $type->xpath('.//xs:union');
        if (is_array($union) && $union !== []) {
            $memberTypes = (string) ($union[0]['memberTypes'] ?? '');
            if ($memberTypes !== '') {
                $members = preg_split('/\s+/', trim($memberTypes));
                /** @var list<string> $resolvedMembers */
                $resolvedMembers = [];
                if (is_array($members)) {
                    foreach ($members as $member) {
                        $resolvedMembers[] = $this->resolveTypeReference($member, $namespacePrefixes, $targetNamespace);
                    }
                }
                $metadata['derivation_method'] = 'union';
                $metadata['union_member_types'] = $resolvedMembers;
            }

            return ['parent_classes' => $parentClasses, 'metadata' => $metadata];
        }

        return ['parent_classes' => $parentClasses, 'metadata' => $metadata];
    }

    /**
     * Resolve a prefixed type reference (e.g., "xs:string") to a full URI.
     *
     * @param array<string, string> $namespacePrefixes
     */
    private function resolveTypeReference(string $reference, array $namespacePrefixes, string $targetNamespace): string
    {
        if (str_contains($reference, ':')) {
            [$prefix, $localName] = explode(':', $reference, 2);
            $namespaceUri = $namespacePrefixes[$prefix] ?? '';
            if ($namespaceUri !== '') {
                return $namespaceUri . '#' . $localName;
            }
        }

        // No prefix -- resolve against target namespace or XSD namespace
        if ($targetNamespace !== '') {
            return $targetNamespace . '#' . $reference;
        }

        return self::XSD_BASE_URI . $reference;
    }

    /**
     * Get the target namespace from the schema root element.
     */
    private function getTargetNamespace(\SimpleXMLElement $xml): string
    {
        return (string) ($xml['targetNamespace'] ?? '');
    }

    /**
     * Get all namespace prefix mappings declared in the schema document.
     *
     * Uses getDocNamespaces() which returns all declared namespace prefixes,
     * including those only used in attribute values (e.g., type references).
     *
     * @return array<string, string>
     */
    private function getNamespacePrefixes(\SimpleXMLElement $xml): array
    {
        /** @var array<string, string> $namespaces */
        $namespaces = $xml->getDocNamespaces(true);
        return $namespaces;
    }

    /**
     * Extract documentation text from a type element.
     */
    private function extractDocumentation(\SimpleXMLElement $type): string
    {
        $docElements = $type->xpath('.//xs:documentation');
        if (is_array($docElements) && $docElements !== []) {
            $firstDoc = reset($docElements);
            return (string) $firstDoc;
        }

        return '';
    }

    /**
     * Extract restriction facets from a simpleType element.
     *
     * @return array<string, string|list<string>>
     */
    private function extractRestrictionFacets(\SimpleXMLElement $type): array
    {
        $restriction = $type->xpath('.//xs:restriction');
        if (!is_array($restriction) || $restriction === []) {
            return [];
        }

        $restrictionElement = $restriction[0];
        $restrictionElement->registerXPathNamespace('xs', self::XSD_NAMESPACE);

        /** @var array<string, string|list<string>> $facets */
        $facets = [];

        foreach (self::FACET_NAMES as $facetName) {
            $facetElements = $restrictionElement->xpath('xs:' . $facetName);
            if (!is_array($facetElements) || $facetElements === []) {
                continue;
            }

            if ($facetName === 'enumeration') {
                /** @var list<string> $values */
                $values = [];
                foreach ($facetElements as $element) {
                    $values[] = (string) $element['value'];
                }
                $facets[$facetName] = $values;
            } else {
                $facets[$facetName] = (string) $facetElements[0]['value'];
            }
        }

        return $facets;
    }

    /**
     * Extract complex type structure including compositors and complexContent.
     *
     * @param array<string, string> $namespacePrefixes
     * @return array{parent_classes: list<string>, metadata: array<string, mixed>}
     */
    private function extractComplexTypeStructure(
        \SimpleXMLElement $type,
        array $namespacePrefixes,
        string $targetNamespace,
    ): array {
        /** @var list<string> $parentClasses */
        $parentClasses = [];
        /** @var array<string, mixed> $metadata */
        $metadata = [];

        // Check for complexContent (extension or restriction of another complex type)
        $complexContentResult = $this->extractComplexContent($type, $namespacePrefixes, $targetNamespace);
        if ($complexContentResult !== null) {
            return $complexContentResult;
        }

        // Extract compositor (sequence, choice, all)
        $compositor = $this->extractCompositor($type, $namespacePrefixes, $targetNamespace);
        if ($compositor !== null) {
            $metadata['compositor'] = $compositor;
        }

        // Extract attributes
        $attributes = $this->extractAttributes($type, $namespacePrefixes, $targetNamespace);
        if ($attributes !== []) {
            $metadata['attributes'] = $attributes;
        }

        return ['parent_classes' => $parentClasses, 'metadata' => $metadata];
    }

    /**
     * Extract complexContent (extension or restriction) from a complex type.
     *
     * @param array<string, string> $namespacePrefixes
     * @return array{parent_classes: list<string>, metadata: array<string, mixed>}|null
     */
    private function extractComplexContent(
        \SimpleXMLElement $type,
        array $namespacePrefixes,
        string $targetNamespace,
    ): ?array {
        $complexContent = $type->xpath('.//xs:complexContent');
        if (!is_array($complexContent) || $complexContent === []) {
            return null;
        }

        $ccElement = $complexContent[0];
        $ccElement->registerXPathNamespace('xs', self::XSD_NAMESPACE);

        // Check for extension
        $extension = $ccElement->xpath('xs:extension');
        if (is_array($extension) && $extension !== []) {
            $base = (string) ($extension[0]['base'] ?? '');
            if ($base !== '') {
                $resolvedBase = $this->resolveTypeReference($base, $namespacePrefixes, $targetNamespace);
                return [
                    'parent_classes' => [$resolvedBase],
                    'metadata' => [
                        'derivation_method' => 'extension',
                        'base_type' => $resolvedBase,
                    ],
                ];
            }
        }

        // Check for restriction
        $restriction = $ccElement->xpath('xs:restriction');
        if (is_array($restriction) && $restriction !== []) {
            $base = (string) ($restriction[0]['base'] ?? '');
            if ($base !== '') {
                $resolvedBase = $this->resolveTypeReference($base, $namespacePrefixes, $targetNamespace);
                return [
                    'parent_classes' => [$resolvedBase],
                    'metadata' => [
                        'derivation_method' => 'restriction',
                        'base_type' => $resolvedBase,
                    ],
                ];
            }
        }

        return null;
    }

    /**
     * Extract compositor (sequence, choice, all) from a complex type or extension element.
     *
     * @param array<string, string> $namespacePrefixes
     * @return array{type: string, elements: list<array<string, mixed>>}|null
     */
    private function extractCompositor(
        \SimpleXMLElement $parent,
        array $namespacePrefixes,
        string $targetNamespace,
    ): ?array {
        foreach (['sequence', 'choice', 'all'] as $compositorType) {
            $compositors = $parent->xpath('.//xs:' . $compositorType);
            if (is_array($compositors) && $compositors !== []) {
                $compositor = $compositors[0];
                $compositor->registerXPathNamespace('xs', self::XSD_NAMESPACE);

                $elements = $this->extractCompositorElements($compositor, $namespacePrefixes, $targetNamespace);

                return [
                    'type' => $compositorType,
                    'elements' => $elements,
                ];
            }
        }

        return null;
    }

    /**
     * Extract child elements from a compositor element, including nested compositors.
     *
     * @param array<string, string> $namespacePrefixes
     * @return list<array<string, mixed>>
     */
    private function extractCompositorElements(
        \SimpleXMLElement $compositor,
        array $namespacePrefixes,
        string $targetNamespace,
    ): array {
        /** @var list<array<string, mixed>> $elements */
        $elements = [];

        // Extract direct child elements
        $childElements = $compositor->xpath('xs:element');
        if (is_array($childElements)) {
            foreach ($childElements as $element) {
                $name = (string) ($element['name'] ?? '');
                $typeRef = (string) ($element['type'] ?? '');
                $minOccurs = (string) ($element['minOccurs'] ?? '1');
                $maxOccurs = (string) ($element['maxOccurs'] ?? '1');

                if ($name === '') {
                    continue;
                }

                $elements[] = [
                    'name' => $name,
                    'type' => $typeRef !== '' ? $this->resolveTypeReference($typeRef, $namespacePrefixes, $targetNamespace) : '',
                    'minOccurs' => (int) $minOccurs,
                    'maxOccurs' => $maxOccurs === 'unbounded' ? 'unbounded' : (int) $maxOccurs,
                ];
            }
        }

        // Extract elements from nested compositors
        foreach (['sequence', 'choice', 'all'] as $nestedType) {
            $nestedCompositors = $compositor->xpath('xs:' . $nestedType);
            if (is_array($nestedCompositors)) {
                foreach ($nestedCompositors as $nested) {
                    $nested->registerXPathNamespace('xs', self::XSD_NAMESPACE);
                    $nestedElements = $this->extractCompositorElements($nested, $namespacePrefixes, $targetNamespace);
                    foreach ($nestedElements as $el) {
                        $elements[] = $el;
                    }
                }
            }
        }

        return $elements;
    }

    /**
     * Extract attribute declarations from a complex type element.
     *
     * @param array<string, string> $namespacePrefixes
     * @return list<array{name: string, type: string, use: string}>
     */
    private function extractAttributes(
        \SimpleXMLElement $type,
        array $namespacePrefixes,
        string $targetNamespace,
    ): array {
        /** @var list<array{name: string, type: string, use: string}> $attributes */
        $attributes = [];

        $attrElements = $type->xpath('.//xs:attribute');
        if (!is_array($attrElements)) {
            return [];
        }

        foreach ($attrElements as $attr) {
            $name = (string) ($attr['name'] ?? '');
            $typeRef = (string) ($attr['type'] ?? '');
            $use = (string) ($attr['use'] ?? 'optional');

            if ($name === '') {
                continue;
            }

            $attributes[] = [
                'name' => $name,
                'type' => $typeRef !== '' ? $this->resolveTypeReference($typeRef, $namespacePrefixes, $targetNamespace) : '',
                'use' => $use,
            ];
        }

        return $attributes;
    }

    private function isPrimitiveType(string $datatype): bool
    {
        return !isset(self::XSD_HIERARCHY[$datatype]);
    }

    private function getDatatypeCategory(string $datatype): string
    {
        if (in_array($datatype, ['string', 'normalizedString', 'token', 'language', 'Name', 'NCName', 'ID', 'IDREF', 'ENTITY'], true)) {
            return 'string';
        }

        if (in_array($datatype, ['decimal', 'integer', 'float', 'double', 'long', 'int', 'short', 'byte', 'nonNegativeInteger', 'positiveInteger', 'nonPositiveInteger', 'negativeInteger', 'unsignedLong', 'unsignedInt', 'unsignedShort', 'unsignedByte'], true)) {
            return 'numeric';
        }

        if (in_array($datatype, ['dateTime', 'date', 'time', 'duration', 'gYear', 'gYearMonth', 'gMonth', 'gMonthDay', 'gDay'], true)) {
            return 'temporal';
        }

        if (in_array($datatype, ['hexBinary', 'base64Binary'], true)) {
            return 'binary';
        }

        if ($datatype === 'boolean') {
            return 'logical';
        }

        return 'other';
    }

    /**
     * @param list<array<string, mixed>> $classes
     * @return array<string, array<string, mixed>>
     */
    private function indexByUri(array $classes): array
    {
        $indexed = [];
        foreach ($classes as $class) {
            /** @var string $uri */
            $uri = $class['uri'];
            $indexed[$uri] = $class;
        }
        return $indexed;
    }
}
