# W3C XML Schema Test Fixtures

XML Schema Definition (XSD) fixture files for conformance testing of parser-xmlschema.

## Source

Fixtures are based on constructs defined in:
- [W3C XML Schema Part 2: Datatypes Second Edition](https://www.w3.org/TR/xmlschema-2/) (Sections 3.2-3.4)
- [W3C XML Schema Part 1: Structures Second Edition](https://www.w3.org/TR/xmlschema-1/)

All fixtures are valid XML Schema documents with proper namespace declarations.

## Fixture Files

| File | Description | W3C Spec Reference |
|------|-------------|-------------------|
| string-types.xsd | Custom string restrictions (minLength, maxLength, pattern) | Part 2, S3.2.1 |
| numeric-types.xsd | Custom numeric restrictions (minInclusive, maxInclusive) | Part 2, S3.2.2-3.2.4 |
| temporal-types.xsd | Custom date/time restrictions | Part 2, S3.2.7-3.2.14 |
| boolean-type.xsd | Boolean type usage in complex types | Part 2, S3.2.2 |
| enumeration-types.xsd | Enumeration restriction facets | Part 2, S4.3.5 |
| complex-types.xsd | Complex type definitions (sequence, choice, attributes) | Part 1, S3.4 |
| restriction-types.xsd | Various facet restrictions (length, whiteSpace, totalDigits) | Part 2, S4.3 |
| documentation-types.xsd | Types with and without xs:documentation | Part 1, S3.13 |
| namespace-variants.xsd | XSD using xsd: prefix instead of xs: | Part 1, S2.6 |
| empty-schema.xsd | Minimal empty schema with no custom types | Part 1, S3.1 |
| mixed-types.xsd | Multiple simpleType and complexType together | Part 1, S3.3-3.4 |
| anonymous-types.xsd | Types without name attribute (anonymous) | Part 1, S3.3.1 |

## Date Created

2026-02-19

## Attribution

Test fixtures created for the Composer parser-xmlschema package.
Based on W3C XML Schema specifications. W3C specifications are available at https://www.w3.org/.
