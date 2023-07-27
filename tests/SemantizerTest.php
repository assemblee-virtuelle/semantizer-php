<?php declare(strict_types=1);

namespace VirtualAssembly\Semantizer;
use PHPUnit\Framework\TestCase;

final class SemantizerTest extends TestCase
{
    public function testExport(): void {
        $semantizer = new Semantizer();
        \EasyRdf\RdfNamespace::set("dfc", "https://github.com/datafoodconsortium/ontology/releases/latest/download/DFC_BusinessOntology.owl#");

        $context = ["https://www.datafoodconsortium.org"];

        $so = new SemanticObject($semantizer, "http://example.org/joe", "foaf:Person");
        $so->addSemanticPropertyLiteral("foaf:name", "Joe");
        $so->addSemanticPropertyLiteral("dfc:description", "Description");

        $so2 = new SemanticObject($semantizer, "http://example.org/tom", "foaf:Person");
        $so2->addSemanticPropertyLiteral("foaf:name", "Tom");

        $expected = '{"@context":["https://www.datafoodconsortium.org"],"@graph":[{"@id":"http://example.org/joe","@type":"http://xmlns.com/foaf/0.1/Person","http://xmlns.com/foaf/0.1/name":"Joe","dfc-b:description":"Description"},{"@id":"http://example.org/tom","@type":"http://xmlns.com/foaf/0.1/Person","http://xmlns.com/foaf/0.1/name":"Tom"}]}';
        $this->assertSame($expected, $semantizer->export([$so, $so2], $context));
    }
}