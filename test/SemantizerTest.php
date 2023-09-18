<?php declare(strict_types=1);

namespace VirtualAssembly\Semantizer;
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . "/../vendor/autoload.php");

final class SemantizerTest extends TestCase
{
    public function testExport(): void {
        $semantizer = new Semantizer();
        $semantizer->setPrefix("dfc-b", "https://github.com/datafoodconsortium/ontology/releases/latest/download/DFC_BusinessOntology.owl#");

        $context = ["https://www.datafoodconsortium.org"];

        $so = new SemanticObject(
            semantizer: $semantizer, 
            semanticId: "http://example.org/joe", 
            semanticType: "foaf:Person"
        );
        $so->addSemanticPropertyLiteral("foaf:name", "Joe");
        $so->addSemanticPropertyLiteral("dfc-b:description", "Description");

        $so2 = new SemanticObject(
            semantizer: $semantizer, 
            semanticId: "http://example.org/tom", 
            semanticType: "foaf:Person"
        );
        $so2->addSemanticPropertyLiteral("foaf:name", "Tom");

        $expected = '{"@context":["https://www.datafoodconsortium.org"],"@graph":[{"@id":"http://example.org/joe","@type":"http://xmlns.com/foaf/0.1/Person","http://xmlns.com/foaf/0.1/name":"Joe","dfc-b:description":"Description"},{"@id":"http://example.org/tom","@type":"http://xmlns.com/foaf/0.1/Person","http://xmlns.com/foaf/0.1/name":"Tom"}]}';
        $this->assertSame($expected, $semantizer->export([$so, $so2], $context));
    }
}