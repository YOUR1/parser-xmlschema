<?php

declare(strict_types=1);

use Youri\vandenBogert\Software\ParserXmlSchema\XmlSchemaParser;

describe('class_alias bridge', function () {

    describe('alias resolution', function () {
        it('resolves XmlSchemaParser from old namespace', function () {
            expect(class_exists('App\Services\Ontology\Parsers\XmlSchemaParser'))->toBeTrue();
        });
    });

    describe('instanceof compatibility', function () {
        it('new XmlSchemaParser is instanceof old namespace name', function () {
            $parser = new XmlSchemaParser();
            expect($parser)->toBeInstanceOf('App\Services\Ontology\Parsers\XmlSchemaParser');
        });

        it('old namespace resolves to same class as new namespace', function () {
            $oldReflection = new \ReflectionClass('App\Services\Ontology\Parsers\XmlSchemaParser');
            $newReflection = new \ReflectionClass(XmlSchemaParser::class);
            expect($oldReflection->getName())->toBe($newReflection->getName());
        });
    });

    describe('deprecation warnings', function () {
        $captureDeprecations = function (): array {
            static $cache = null;
            if ($cache !== null) {
                return $cache;
            }

            $projectRoot = dirname(__DIR__, 2);
            $script = <<<'PHP'
<?php
$deprecations = [];
set_error_handler(function (int $errno, string $errstr) use (&$deprecations) {
    if ($errno === E_USER_DEPRECATED) {
        $deprecations[] = $errstr;
    }
    return true;
});
require $argv[1] . '/vendor/autoload.php';
// Trigger alias by referencing old namespace class
class_exists('App\Services\Ontology\Parsers\XmlSchemaParser');
echo json_encode($deprecations);
PHP;
            $tempFile = tempnam(sys_get_temp_dir(), 'alias_test_');
            if ($tempFile === false) {
                throw new \RuntimeException('Failed to create temp file');
            }
            file_put_contents($tempFile, $script);
            $output = shell_exec('php ' . escapeshellarg($tempFile) . ' ' . escapeshellarg($projectRoot)) ?? '[]';
            unlink($tempFile);

            $cache = json_decode($output, true) ?? [];

            return $cache;
        };

        it('triggers E_USER_DEPRECATED when old XmlSchemaParser class is referenced', function () use ($captureDeprecations) {
            expect($captureDeprecations())->toBeArray()->toHaveCount(1);
        });

        it('deprecation message contains old and new FQCN', function () use ($captureDeprecations) {
            $deprecations = $captureDeprecations();
            expect($deprecations[0])
                ->toContain('App\Services\Ontology\Parsers\XmlSchemaParser')
                ->toContain('Youri\vandenBogert\Software\ParserXmlSchema\XmlSchemaParser');
        });

        it('deprecation message mentions v2.0 removal', function () use ($captureDeprecations) {
            $deprecations = $captureDeprecations();
            expect($deprecations[0])->toContain('v2.0');
        });

        it('does NOT trigger deprecation at autoload time', function () {
            $projectRoot = dirname(__DIR__, 2);
            $script = <<<'PHP'
<?php
$deprecations = [];
set_error_handler(function (int $errno, string $errstr) use (&$deprecations) {
    if ($errno === E_USER_DEPRECATED) {
        $deprecations[] = $errstr;
    }
    return true;
});
require $argv[1] . '/vendor/autoload.php';
// Do NOT reference any old namespace classes
echo json_encode($deprecations);
PHP;
            $tempFile = tempnam(sys_get_temp_dir(), 'alias_test_');
            if ($tempFile === false) {
                throw new \RuntimeException('Failed to create temp file');
            }
            file_put_contents($tempFile, $script);
            $output = shell_exec('php ' . escapeshellarg($tempFile) . ' ' . escapeshellarg($projectRoot)) ?? '[]';
            unlink($tempFile);

            $deprecations = json_decode($output, true) ?? [];
            expect($deprecations)->toBeArray()->toHaveCount(0);
        });
    });

    describe('no aliases for parser-core classes', function () {
        it('does not eagerly alias old OntologyParserInterface', function () {
            // OntologyParserInterface alias is owned by parser-core (Story 2.7), not parser-xmlschema
            // Using false = don't trigger autoloading, just check if already loaded in memory
            expect(class_exists('App\Services\Ontology\Parsers\OntologyParserInterface', false))->toBeFalse();
        });

        it('does not eagerly alias old OntologyImportException', function () {
            // OntologyImportException alias is owned by parser-core (Story 2.7), not parser-xmlschema
            expect(class_exists('App\Services\Ontology\Exceptions\OntologyImportException', false))->toBeFalse();
        });

        it('does not eagerly alias old ParsedRdf', function () {
            // ParsedRdf alias is owned by parser-core (Story 2.7), not parser-xmlschema
            expect(class_exists('App\Services\Ontology\Parsers\ValueObjects\ParsedRdf', false))->toBeFalse();
        });
    });
});
