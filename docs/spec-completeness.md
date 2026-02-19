# Spec Completeness

> Assessment of parser-xmlschema implementation coverage against W3C XML Schema 1.1 specifications.
> Last updated: 2026-02-19

## Scope

This library focuses exclusively on **XML Schema (XSD)** datatype parsing for RDF/OWL consumers.
It recognizes built-in XSD primitive and derived datatypes (Part 2) and extracts user-defined
`xs:simpleType` and `xs:complexType` declarations (Part 1). It does **not** perform schema
validation, constraint checking, or instance document validation.

Reference specifications:
- [XML Schema Part 1: Structures](https://www.w3.org/TR/xmlschema11-1/)
- [XML Schema Part 2: Datatypes](https://www.w3.org/TR/xmlschema11-2/)

## Summary

| Spec Area | Implemented | Total | Coverage |
|---|---|---|---|
| XSD Primitive Datatypes (Part 2, S3.2) | 19 | 19 | 100% |
| XSD Derived Datatypes (Part 2, S3.3) | 25 | 25 | 100% |
| Datatype Hierarchy (Part 2, S3.4) | 25 | 25 | 100% |
| Datatype Categorization | 6 | 6 | 100% |
| Simple Type Extraction (Part 1, S3.16) | 2 | 5 | 40% |
| Complex Type Extraction (Part 1, S3.4) | 2 | 8 | 25% |
| Restriction Facets (Part 2, S4.3) | 0 | 12 | 0% |
| Element / Attribute Handling (Part 1, S3.3/S3.13) | 0 | 6 | 0% |
| Format Detection | 3 | 3 | 100% |
| Test Coverage | 241 | 241 | 100% |
| **Overall (weighted)** | | | **~55%** |

---

## XSD Primitive Datatypes

Reference: [XSD Part 2, Section 3.2](https://www.w3.org/TR/xmlschema11-2/#built-in-primitive-datatypes)

All 19 W3C primitive datatypes are recognized and emitted as classes with URI, label, description,
empty `parent_classes`, and metadata (`source`, `category`, `is_primitive`).

| Feature | Status | Location | Tests |
|---|---|---|---|
| `xsd:string` | implemented | `XmlSchemaParser:18` | `W3cConformance:57`, `Characterization:5.2`, `Unit:86` |
| `xsd:boolean` | implemented | `XmlSchemaParser:19` | `W3cConformance:66`, `Characterization:7.5`, `Unit:87` |
| `xsd:decimal` | implemented | `XmlSchemaParser:20` | `W3cConformance:75`, `Characterization:7.2`, `Unit:88` |
| `xsd:float` | implemented | `XmlSchemaParser:21` | `W3cConformance:84`, `Characterization:7.2` |
| `xsd:double` | implemented | `XmlSchemaParser:22` | `W3cConformance:93`, `Characterization:7.2` |
| `xsd:duration` | implemented | `XmlSchemaParser:23` | `W3cConformance:102`, `Characterization:7.3` |
| `xsd:dateTime` | implemented | `XmlSchemaParser:24` | `W3cConformance:111`, `Unit:91` |
| `xsd:time` | implemented | `XmlSchemaParser:25` | `W3cConformance:120`, `Characterization:7.3` |
| `xsd:date` | implemented | `XmlSchemaParser:26` | `W3cConformance:129`, `Unit:92` |
| `xsd:gYearMonth` | implemented | `XmlSchemaParser:27` | `W3cConformance:138`, `Characterization:7.3` |
| `xsd:gYear` | implemented | `XmlSchemaParser:28` | `W3cConformance:147`, `Characterization:7.3` |
| `xsd:gMonthDay` | implemented | `XmlSchemaParser:29` | `W3cConformance:156`, `Characterization:7.3` |
| `xsd:gDay` | implemented | `XmlSchemaParser:30` | `W3cConformance:165`, `Characterization:7.3` |
| `xsd:gMonth` | implemented | `XmlSchemaParser:31` | `W3cConformance:174`, `Characterization:7.3` |
| `xsd:hexBinary` | implemented | `XmlSchemaParser:32` | `W3cConformance:183`, `Characterization:7.4`, `Unit:172` |
| `xsd:base64Binary` | implemented | `XmlSchemaParser:33` | `W3cConformance:192`, `Characterization:7.4` |
| `xsd:anyURI` | implemented | `XmlSchemaParser:34` | `W3cConformance:201`, `Unit:94` |
| `xsd:QName` | implemented | `XmlSchemaParser:35` | `W3cConformance:210`, `Characterization:7.6` |
| `xsd:NOTATION` | implemented | `XmlSchemaParser:36` | `W3cConformance:219`, `Characterization:7.6` |

---

## XSD Derived Datatypes

Reference: [XSD Part 2, Section 3.3](https://www.w3.org/TR/xmlschema11-2/#built-in-derived)

All 25 W3C derived datatypes are recognized, each with the correct single parent in `parent_classes`
and `is_primitive` set to `false`.

| Feature | Status | Location | Tests |
|---|---|---|---|
| `xsd:normalizedString` (from `string`) | implemented | `XmlSchemaParser:38`, hierarchy `:69` | `W3cConformance:238`, `Characterization:6.3`, `Unit:98` |
| `xsd:token` (from `normalizedString`) | implemented | `XmlSchemaParser:39`, hierarchy `:70` | `W3cConformance:247`, `Characterization:6.4`, `Unit:99` |
| `xsd:language` (from `token`) | implemented | `XmlSchemaParser:40`, hierarchy `:71` | `W3cConformance:256`, `Characterization:6.17` |
| `xsd:NMTOKEN` (from `token`) | implemented | `XmlSchemaParser:43`, hierarchy `:74` | `W3cConformance:265`, `Characterization:6.25` |
| `xsd:NMTOKENS` (from `token`) | implemented | `XmlSchemaParser:44`, hierarchy `:75` | `W3cConformance:274`, `Characterization:6.26` |
| `xsd:Name` (from `token`) | implemented | `XmlSchemaParser:45`, hierarchy `:76` | `W3cConformance:283`, `Characterization:6.18` |
| `xsd:NCName` (from `Name`) | implemented | `XmlSchemaParser:46`, hierarchy `:77` | `W3cConformance:292`, `Characterization:6.19` |
| `xsd:ID` (from `NCName`) | implemented | `XmlSchemaParser:47`, hierarchy `:78` | `W3cConformance:301`, `Characterization:6.20` |
| `xsd:IDREF` (from `NCName`) | implemented | `XmlSchemaParser:48`, hierarchy `:79` | `W3cConformance:310`, `Characterization:6.21` |
| `xsd:IDREFS` (from `token`) | implemented | `XmlSchemaParser:41`, hierarchy `:72` | `W3cConformance:319`, `Characterization:6.23` |
| `xsd:ENTITY` (from `NCName`) | implemented | `XmlSchemaParser:49`, hierarchy `:80` | `W3cConformance:328`, `Characterization:6.22` |
| `xsd:ENTITIES` (from `token`) | implemented | `XmlSchemaParser:42`, hierarchy `:73` | `W3cConformance:337`, `Characterization:6.24` |
| `xsd:integer` (from `decimal`) | implemented | `XmlSchemaParser:50`, hierarchy `:81` | `W3cConformance:346`, `Characterization:6.2`, `Unit:97` |
| `xsd:nonPositiveInteger` (from `integer`) | implemented | `XmlSchemaParser:51`, hierarchy `:82` | `W3cConformance:355`, `Characterization:6.11` |
| `xsd:negativeInteger` (from `nonPositiveInteger`) | implemented | `XmlSchemaParser:52`, hierarchy `:83` | `W3cConformance:364`, `Characterization:6.12` |
| `xsd:long` (from `integer`) | implemented | `XmlSchemaParser:53`, hierarchy `:84` | `W3cConformance:373`, `Characterization:6.6`, `Unit:101` |
| `xsd:int` (from `long`) | implemented | `XmlSchemaParser:54`, hierarchy `:85` | `W3cConformance:382`, `Characterization:6.5`, `Unit:100` |
| `xsd:short` (from `int`) | implemented | `XmlSchemaParser:55`, hierarchy `:86` | `W3cConformance:391`, `Characterization:6.7`, `Unit:102` |
| `xsd:byte` (from `short`) | implemented | `XmlSchemaParser:56`, hierarchy `:87` | `W3cConformance:400`, `Characterization:6.8`, `Unit:103` |
| `xsd:nonNegativeInteger` (from `integer`) | implemented | `XmlSchemaParser:57`, hierarchy `:88` | `W3cConformance:409`, `Characterization:6.9`, `Unit:105` |
| `xsd:unsignedLong` (from `nonNegativeInteger`) | implemented | `XmlSchemaParser:58`, hierarchy `:89` | `W3cConformance:418`, `Characterization:6.13` |
| `xsd:unsignedInt` (from `unsignedLong`) | implemented | `XmlSchemaParser:59`, hierarchy `:90` | `W3cConformance:427`, `Characterization:6.14` |
| `xsd:unsignedShort` (from `unsignedInt`) | implemented | `XmlSchemaParser:60`, hierarchy `:91` | `W3cConformance:436`, `Characterization:6.15` |
| `xsd:unsignedByte` (from `unsignedShort`) | implemented | `XmlSchemaParser:61`, hierarchy `:92` | `W3cConformance:445`, `Characterization:6.16` |
| `xsd:positiveInteger` (from `nonNegativeInteger`) | implemented | `XmlSchemaParser:62`, hierarchy `:93` | `W3cConformance:454`, `Characterization:6.10`, `Unit:104` |

---

## Datatype Hierarchy

Reference: [XSD Part 2, Section 3.4 / Type derivation](https://www.w3.org/TR/xmlschema11-2/#built-in-datatypes)

The full parent-child derivation tree is encoded in the `XSD_HIERARCHY` constant (lines 68-94) and
validated by dedicated hierarchy chain tests.

| Feature | Status | Location | Tests |
|---|---|---|---|
| `decimal` -> `integer` -> `long` -> `int` -> `short` -> `byte` chain | implemented | `XmlSchemaParser:81-87` | `W3cConformance:473`, `Characterization:6.2-6.8`, `Unit:108-140` |
| `string` -> `normalizedString` -> `token` -> `language` chain | implemented | `XmlSchemaParser:69-71` | `W3cConformance:485`, `Characterization:6.3-6.4,6.17` |
| `nonNegativeInteger` -> `unsignedLong` -> ... -> `unsignedByte` chain | implemented | `XmlSchemaParser:89-92` | `W3cConformance:497`, `Characterization:6.13-6.16` |
| `integer` -> `nonPositiveInteger` -> `negativeInteger` chain | implemented | `XmlSchemaParser:82-83` | `W3cConformance:506`, `Characterization:6.11-6.12` |
| `integer` -> `nonNegativeInteger` -> `positiveInteger` chain | implemented | `XmlSchemaParser:88,93` | `W3cConformance:514`, `Characterization:6.9-6.10` |
| `token` -> `Name` -> `NCName` chain | implemented | `XmlSchemaParser:76-77` | `W3cConformance:522`, `Characterization:6.18-6.19` |
| `NCName` -> `ID` / `IDREF` / `ENTITY` subtypes | implemented | `XmlSchemaParser:78-80` | `W3cConformance:530`, `Characterization:6.20-6.22` |
| `token` -> `IDREFS` / `ENTITIES` / `NMTOKEN` / `NMTOKENS` | implemented | `XmlSchemaParser:72-75` | `W3cConformance:541`, `Characterization:6.23-6.26` |
| All 19 primitives have empty `parent_classes` | implemented | `XmlSchemaParser:165-167` | `W3cConformance:550`, `Characterization:6.1`, `Unit:134-139` |

---

## Datatype Categorization

The parser assigns a `category` metadata field to each built-in datatype via `getDatatypeCategory()`
(lines 231-254). This is a parser-specific enrichment, not a W3C spec requirement.

| Category | Types | Status | Location | Tests |
|---|---|---|---|---|
| `string` | `string`, `normalizedString`, `token`, `language`, `Name`, `NCName`, `ID`, `IDREF`, `ENTITY` | implemented | `XmlSchemaParser:233` | `Characterization:7.1`, `Unit:142-155` |
| `numeric` | `decimal`, `integer`, `float`, `double`, `long`, `int`, `short`, `byte`, `nonNegativeInteger`, `positiveInteger`, `nonPositiveInteger`, `negativeInteger`, `unsignedLong`, `unsignedInt`, `unsignedShort`, `unsignedByte` | implemented | `XmlSchemaParser:237` | `Characterization:7.2`, `Unit:157-162` |
| `temporal` | `dateTime`, `date`, `time`, `duration`, `gYear`, `gYearMonth`, `gMonth`, `gMonthDay`, `gDay` | implemented | `XmlSchemaParser:241` | `Characterization:7.3`, `Unit:164-169` |
| `binary` | `hexBinary`, `base64Binary` | implemented | `XmlSchemaParser:245` | `Characterization:7.4`, `Unit:171-173` |
| `logical` | `boolean` | implemented | `XmlSchemaParser:249` | `Characterization:7.5`, `Unit:175-177` |
| `other` | `anyURI`, `QName`, `NOTATION`, `IDREFS`, `ENTITIES`, `NMTOKEN`, `NMTOKENS` | implemented | `XmlSchemaParser:253` | `Characterization:7.6-7.10`, `Unit:297` |

---

## Simple Type Definitions

Reference: [XSD Part 1, Section 3.16](https://www.w3.org/TR/xmlschema11-1/#Simple_Type_Definitions)

| Feature | Status | Location | Tests |
|---|---|---|---|
| Named `xs:simpleType` extraction via XPath | implemented | `XmlSchemaParser:192` | `Characterization:9.1,9.3`, `W3cConformance:609`, `Unit:209-256` |
| `xs:documentation` extraction for simple types | implemented | `XmlSchemaParser:204-208` | `Characterization:9.8,9.16,9.19`, `W3cConformance:691` |
| Fallback description when no documentation | implemented | `XmlSchemaParser:213` | `Characterization:9.9`, `W3cConformance:700` |
| Anonymous simple types (no `@name`) skipped | implemented | `XmlSchemaParser:197-199` | `Characterization:9.12-9.13`, `W3cConformance:903` |
| `xs:restriction` base type extraction | not implemented | -- | -- |
| `xs:list` item type extraction | not implemented | -- | -- |
| `xs:union` member types extraction | not implemented | -- | -- |

---

## Complex Type Definitions

Reference: [XSD Part 1, Section 3.4](https://www.w3.org/TR/xmlschema11-1/#Complex_Type_Definitions)

| Feature | Status | Location | Tests |
|---|---|---|---|
| Named `xs:complexType` extraction via XPath | implemented | `XmlSchemaParser:193` | `Characterization:9.2,9.4`, `W3cConformance:657`, `Unit:209-256` |
| `xs:documentation` extraction for complex types | implemented | `XmlSchemaParser:204-208` | `W3cConformance:691` |
| `xs:sequence` child elements | not implemented | -- | -- |
| `xs:choice` compositor | not implemented | -- | -- |
| `xs:all` compositor | not implemented | -- | -- |
| `xs:attribute` declarations | not implemented | -- | -- |
| `xs:complexContent` (extension/restriction) | not implemented | -- | -- |
| `xs:simpleContent` (extension/restriction) | not implemented | -- | -- |

---

## Restriction Facets

Reference: [XSD Part 2, Section 4.3](https://www.w3.org/TR/xmlschema11-2/#rf-facets)

The parser does **not** extract or represent constraining facets from `xs:restriction` children.
Facets appear in test fixture files (e.g., `restriction-types.xsd`, `string-types.xsd`,
`numeric-types.xsd`) but are not parsed into the output data model. The type name and documentation
are extracted; the facet values are silently ignored.

| Feature | Status | Location | Tests |
|---|---|---|---|
| `xs:minInclusive` | not implemented | -- | fixture: `numeric-types.xsd:11` |
| `xs:maxInclusive` | not implemented | -- | fixture: `numeric-types.xsd:12` |
| `xs:minExclusive` | not implemented | -- | fixture: `numeric-types.xsd:34` |
| `xs:maxExclusive` | not implemented | -- | fixture: `numeric-types.xsd:35` |
| `xs:minLength` | not implemented | -- | fixture: `string-types.xsd:11` |
| `xs:maxLength` | not implemented | -- | fixture: `string-types.xsd:12` |
| `xs:length` | not implemented | -- | fixture: `restriction-types.xsd:11` |
| `xs:pattern` | not implemented | -- | fixture: `string-types.xsd:22` |
| `xs:enumeration` | not implemented | -- | fixture: `enumeration-types.xsd:11-13` |
| `xs:whiteSpace` | not implemented | -- | fixture: `restriction-types.xsd:21` |
| `xs:totalDigits` | not implemented | -- | fixture: `numeric-types.xsd:22` |
| `xs:fractionDigits` | not implemented | -- | fixture: `numeric-types.xsd:23` |

---

## Element and Attribute Handling

Reference: [XSD Part 1, Sections 3.3 and 3.13](https://www.w3.org/TR/xmlschema11-1/)

The parser does not extract top-level or local element/attribute declarations into the output model.
Elements and attributes appear in fixture XSD files but are only used structurally for type
extraction.

| Feature | Status | Location | Tests |
|---|---|---|---|
| Top-level `xs:element` extraction | not implemented | -- | -- |
| Local `xs:element` within complex types | not implemented | -- | -- |
| `xs:attribute` declarations | not implemented | -- | -- |
| `minOccurs` / `maxOccurs` cardinality | not implemented | -- | -- |
| `xs:group` references | not implemented | -- | -- |
| `xs:attributeGroup` references | not implemented | -- | -- |

---

## Format Detection

Reference: Parser-specific (content sniffing for XSD documents).

Implemented in `canParse()` (lines 136-143): requires `<?xml` declaration **and** the presence of
the `http://www.w3.org/2001/XMLSchema` namespace string.

| Feature | Status | Location | Tests |
|---|---|---|---|
| `<?xml` + `xmlns:xs` namespace detection | implemented | `XmlSchemaParser:140-141` | `Characterization:2.1`, `Unit:13-19` |
| `<?xml` + `targetNamespace` detection | implemented | `XmlSchemaParser:142` | `Characterization:2.2`, `Unit:30-36` |
| `getSupportedFormats()` returns `['xml_schema', 'xsd']` | implemented | `XmlSchemaParser:148-151` | `Characterization:3.1-3.3`, `Unit:38-43` |
| Leading whitespace trimmed before detection | implemented | `XmlSchemaParser:138` | `Characterization:2.3` |
| Rejects non-XSD XML (RDF/XML, Turtle, JSON-LD, HTML, plain text) | implemented | `XmlSchemaParser:140-142` | `Characterization:2.4-2.11`, `W3cConformance:598` |
| Known false-positive: RDF/XML referencing XSD namespace | documented | `XmlSchemaParser:141` | `Characterization:2.12` |
| `parse()` does not re-validate namespace (only `canParse()` does) | documented | -- | `Characterization:10.8` |

---

## Output Structure (ParsedOntology)

The parser returns a `ParsedOntology` value object with the following fields.

| Field | Content | Location | Tests |
|---|---|---|---|
| `classes` | Associative array keyed by URI; 44 built-in + custom types | `XmlSchemaParser:111` | `Compliance:29-39`, `Characterization:4.1-4.2,5.1` |
| `properties` | Always empty `[]` | `XmlSchemaParser:112` | `Compliance:51-56`, `Characterization:4.5` |
| `prefixes` | `['xsd' => '...#', 'xs' => '...#']` | `XmlSchemaParser:113-116` | `Compliance:42-48`, `Characterization:4.4` |
| `shapes` | Always empty `[]` | `XmlSchemaParser:117` | `Compliance:58-63`, `Characterization:4.6` |
| `restrictions` | Always empty `[]` | `XmlSchemaParser:118` | `Compliance:65-69` |
| `metadata` | `format`, `resource_count`, `parser`, `namespace` | `XmlSchemaParser:119-124` | `Compliance:78-87`, `Characterization:4.3` |
| `rawContent` | Preserves original input string | `XmlSchemaParser:125` | `Compliance:72-75`, `Characterization:4.7` |

---

## Error Handling

| Feature | Status | Location | Tests |
|---|---|---|---|
| Invalid XML throws `ParseException` | implemented | `XmlSchemaParser:100-101` | `Characterization:10.1-10.3`, `Unit:300-309`, `Compliance:89-101` |
| `simplexml_load_string` returning `false` triggers exception | implemented | `XmlSchemaParser:100-101` | `Characterization:10.6-10.7` |
| All exceptions wrapped with `"XML Schema parsing failed: "` prefix | implemented | `XmlSchemaParser:128-133` | `Characterization:10.2,10.10` |
| Previous exception chain preserved | implemented | `XmlSchemaParser:131` | `Characterization:10.3`, `Compliance:94-101` |
| Malformed XML (unclosed tags) throws exception | implemented | `XmlSchemaParser:99-101` | `Characterization:10.5`, `Unit:305-309` |
| Exception code always `0` | implemented | `XmlSchemaParser:131` | `Characterization:10.4` |

---

## Namespace Handling

| Feature | Status | Location | Tests |
|---|---|---|---|
| XPath namespace `xs` registered for queries | implemented | `XmlSchemaParser:104` | `Characterization:12.1-12.2` |
| `xsd:` prefix variant schemas supported | implemented | `XmlSchemaParser:104` | `Characterization:12.3`, `W3cConformance:883` |
| Default namespace (no prefix) schemas supported | implemented | `XmlSchemaParser:104` | `Characterization:12.4` |
| Documentation sub-XPath fails for `xsd:` prefix (known limitation) | documented | `XmlSchemaParser:204` | `Characterization:12.3b` |

---

## Backward Compatibility (Alias Bridge)

| Feature | Status | Location | Tests |
|---|---|---|---|
| `App\...\XmlSchemaParser` alias resolves | implemented | `aliases.php:13-29` | `AliasesTest:10-12` |
| `instanceof` compatibility across namespaces | implemented | `aliases.php:28` | `AliasesTest:16-25` |
| `E_USER_DEPRECATED` triggered on old namespace use | implemented | `aliases.php:20-27` | `AliasesTest:63-77` |
| No deprecation at autoload time | implemented | `aliases.php:19` | `AliasesTest:79-104` |
| No aliases for parser-core classes | implemented | `aliases.php` (absent) | `AliasesTest:107-123` |

---

## Standalone Architecture

| Feature | Status | Location | Tests |
|---|---|---|---|
| Implements `OntologyParserInterface` | implemented | `XmlSchemaParser:11` | `Characterization:11.1`, `Compliance:20-21` |
| No parent class (standalone) | implemented | `XmlSchemaParser:11` | `Characterization:11.2` |
| No EasyRdf dependency | implemented | `XmlSchemaParser` (entire file) | `Characterization:11.3` |
| No `ParsedRdf` usage (returns `ParsedOntology`) | implemented | `XmlSchemaParser:110` | `Characterization:11.4` |
| Only 3 public methods: `parse`, `canParse`, `getSupportedFormats` | implemented | `XmlSchemaParser:96,136,148` | `Characterization:11.5` |
| No global state side effects | implemented | `XmlSchemaParser` (entire file) | `Characterization:11.6` |

---

## Out of Scope

The following are intentionally **not** covered by this library:

| Area | Reason |
|---|---|
| Schema validation / instance checking | Parser only; no validation engine |
| XML Schema 1.1 assertions (`xs:assert`) | Validation feature, not a parsing concern |
| `xs:import` / `xs:include` / `xs:redefine` | No multi-document resolution |
| Identity constraints (`xs:key`, `xs:keyref`, `xs:unique`) | Validation feature |
| Substitution groups | Structural feature not modeled |
| Wildcard content (`xs:any`, `xs:anyAttribute`) | Not extracted |
| OWL / SHACL parsing | Handled by `parser-owl` and `parser-shacl` |

---

## Test Coverage

241 test cases across 5 test files exercising the XmlSchemaParser via the Pest framework.

| File | Test Count | Focus |
|---|---|---|
| `tests/Unit/XmlSchemaParserTest.php` | 15 | Core parse, canParse, datatypes, hierarchy, categories, custom types, error handling |
| `tests/Unit/AliasesTest.php` | 10 | Backward-compatibility alias bridge, deprecation warnings |
| `tests/Unit/ParsedOntologyComplianceTest.php` | 11 | ParsedOntology contract compliance (classes, prefixes, metadata, rawContent) |
| `tests/Characterization/XmlSchemaParserTest.php` | 117 | Exhaustive behavioral characterization: canParse (14), getSupportedFormats (3), output structure (9), built-in datatypes (10), hierarchy (28), categorization (10), primitive/derived marking (3), additional type extraction (19), error handling (10), architecture (6), XPath namespace (5) |
| `tests/Conformance/W3cXmlSchemaConformanceTest.php` | 88 | W3C spec section-tagged conformance: 19 primitive datatypes, 25 derived datatypes, 9 hierarchy chains, 12 content parsing with fixtures, 12 RDF/OWL relevant datatypes, 10 edge cases |

### Test Fixtures

12 XSD fixture files in `tests/Fixtures/W3c/`:

| Fixture | Purpose |
|---|---|
| `empty-schema.xsd` | Minimal valid schema, no custom types |
| `string-types.xsd` | `ShortName`, `ProductCode`, `CountryCode` -- string restrictions |
| `numeric-types.xsd` | `Percentage`, `Price`, `PositiveScore` -- numeric restrictions |
| `temporal-types.xsd` | `FutureDateTime`, `ModernDate`, `WorkDuration` -- temporal restrictions |
| `boolean-type.xsd` | `FeatureFlags` -- complex type with boolean elements |
| `enumeration-types.xsd` | `Color`, `Priority`, `DayOfWeek` -- enumeration facets |
| `complex-types.xsd` | `Person`, `Address`, `ContactMethod` -- sequence, attribute, choice |
| `restriction-types.xsd` | `FixedLengthCode`, `CollapsedString`, `SmallDecimal`, `StrictCode` -- varied facets |
| `documentation-types.xsd` | `DocumentedType`, `UndocumentedType`, `EmptyDocType`, `MultiDocType` -- annotation handling |
| `mixed-types.xsd` | `Email`, `PhoneNumber`, `ZipCode`, `ContactInfo`, `MailingAddress` -- mixed simple + complex |
| `anonymous-types.xsd` | Anonymous types without `@name` + one `NamedType` |
| `namespace-variants.xsd` | `xsd:` prefix variant instead of `xs:` |

---

## Remaining Gaps

The primary gaps are in **structural XSD parsing** -- the parser focuses on datatype recognition
rather than full schema structure extraction:

1. **Restriction facets** (0%) -- `xs:minInclusive`, `xs:maxLength`, `xs:pattern`, `xs:enumeration`,
   etc. are present in fixture files but their values are not extracted into the output model.
2. **Simple type derivation methods** (partial) -- `xs:restriction` base type is not resolved into
   `parent_classes` for user-defined types. `xs:list` and `xs:union` are not recognized.
3. **Complex type structure** (partial) -- `xs:sequence`, `xs:choice`, `xs:all` compositors and their
   child elements/attributes are not modeled in the output.
4. **Element/attribute declarations** (0%) -- Neither top-level nor local `xs:element` and
   `xs:attribute` declarations are extracted as properties.
5. **Documentation XPath limitation** -- When the source document uses the `xsd:` prefix instead of
   `xs:`, the sub-XPath query `.//xs:documentation` fails silently, falling back to the default
   description string (see `Characterization:12.3b`).

All **built-in datatype** spec areas are at **100% coverage** (44/44 types, full hierarchy, categorization, primitive/derived marking).

---

## Architecture Notes

The implementation is a **single-class parser** (`XmlSchemaParser`, 270 lines) with no external
RDF/graph library dependency:

1. **SimpleXML-based** -- uses PHP's built-in `simplexml_load_string()` and XPath for type extraction.
2. **Hard-coded built-in types** -- the 44 XSD datatypes and their hierarchy are encoded as PHP
   constants (`XSD_DATATYPES`, `XSD_HIERARCHY`) rather than parsed from any schema document.
3. **Additive extraction** -- built-in types are always generated first, then any user-defined
   `xs:simpleType[@name]` and `xs:complexType[@name]` found via XPath are appended.
4. **No graph model** -- unlike `parser-owl` which builds an RDF graph via EasyRdf, this parser
   works directly with XML and returns flat associative arrays.
5. **Stateless** -- no mutable state between `parse()` calls; each call produces an independent result.
