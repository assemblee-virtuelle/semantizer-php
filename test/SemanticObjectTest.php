<?php declare(strict_types=1);

namespace VirtualAssembly\Semantizer;
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . "/../vendor/autoload.php");
require_once(__DIR__ . "/Factory.php");

final class SemanticObjectTest extends TestCase
{
    public function testGetSemanticIdAndType(): void {
        $semantizer = new Semantizer();
        $so = new SemanticObject($semantizer, "http://example.org/joe", "foaf:Person");
        $this->assertSame("http://example.org/joe", $so->getSemanticId());
        $this->assertSame("foaf:Person", $so->getSemanticType());
    }
    
    public function testSemanticPropertyEmpty(): void {
        $semantizer = new Semantizer();
        $so = new SemanticObject($semantizer, "http://example.org/joe", "foaf:Person");
        $this->assertSame(null, $so->getSemanticProperty("foaf:name"));
    }

    public function testSemanticPropertyLiteralString(): void {
        $semantizer = new Semantizer();
        $so = new SemanticObject($semantizer, "http://example.org/joe", "foaf:Person");

        // Test adder and getter
        $so->addSemanticPropertyLiteral("foaf:name", "Joe");
        $this->assertSame("Joe", $so->getSemanticProperty("foaf:name"));
        $this->assertSame("Joe", $so->getSemanticProperty("foaf:name"));

        // Test setter
        $so->setSemanticProperty("foaf:name", "Joe2");
        $this->assertSame("Joe2", $so->getSemanticProperty("foaf:name"));

        // Test remover
        $so->removeSemanticProperty("foaf:name", "Joe2");
        $this->assertSame(null, $so->getSemanticProperty("foaf:name"));

        // Test collection
        $so->addSemanticPropertyLiteral("foaf:name", "Joe");
        $so->addSemanticPropertyLiteral("foaf:name", "Jack");
        $this->assertSame(["Joe", "Jack"], $so->getSemanticPropertyAll("foaf:name"));
    }

    public function testSemanticPropertyLiteralNumber(): void {
        $semantizer = new Semantizer();
        $so = new SemanticObject($semantizer, "http://example.org/joe", "foaf:Person");

        // Test adder and getter
        $so->addSemanticPropertyLiteral("foaf:number", 123);
        $this->assertSame(123, $so->getSemanticProperty("foaf:number"));
        $this->assertSame(123, $so->getSemanticProperty("foaf:number"));

        // Test setter
        $so->setSemanticProperty("foaf:number", 1234);
        $this->assertSame(1234, $so->getSemanticProperty("foaf:number"));

        // Test remover
        $so->removeSemanticProperty("foaf:number", 1234);
        $this->assertSame(null, $so->getSemanticProperty("foaf:number"));

        // Test collection
        $so->addSemanticPropertyLiteral("foaf:number", 123);
        $so->addSemanticPropertyLiteral("foaf:number", 1234);
        $this->assertSame([123, 1234], $so->getSemanticPropertyAll("foaf:number"));
    }

    public function testSemanticPropertyLiteralBooleanTrue(): void {
        $semantizer = new Semantizer();
        $so = new SemanticObject($semantizer, "http://example.org/joe", "foaf:Person");

        // Test adder and getter
        $so->addSemanticPropertyLiteral("foaf:bool", true);
        $this->assertSame(true, $so->getSemanticProperty("foaf:bool"));
        $this->assertSame(true, $so->getSemanticProperty("foaf:bool"));
        
        // Test setter
        $so->setSemanticProperty("foaf:bool", false);
        $this->assertSame(false, $so->getSemanticProperty("foaf:bool"));

        // Test remover
        $so->removeSemanticProperty("foaf:bool", false);
        $this->assertSame(null, $so->getSemanticProperty("foaf:bool"));

        // Test collection
        $so->addSemanticPropertyLiteral("foaf:bool", true);
        $so->addSemanticPropertyLiteral("foaf:bool", false);
        $this->assertSame([true, false], $so->getSemanticPropertyAll("foaf:bool"));
    }
    
    public function testSemanticPropertyLiteralBooleanFalse(): void {
        $semantizer = new Semantizer();
        $so = new SemanticObject($semantizer, "http://example.org/joe", "foaf:Person");

        // Test adder and getter
        $so->addSemanticPropertyLiteral("foaf:bool", false);
        $this->assertSame(false, $so->getSemanticProperty("foaf:bool"));
        $this->assertSame(false, $so->getSemanticProperty("foaf:bool"));

        // Test setter
        $so->setSemanticProperty("foaf:bool", true);
        $this->assertSame(true, $so->getSemanticProperty("foaf:bool"));

        // Test remover
        $so->removeSemanticProperty("foaf:bool", true);
        $this->assertSame(null, $so->getSemanticProperty("foaf:bool"));
    }

    public function testSemanticPropertyReference(): void {
        $semantizer = new Semantizer();
        $so = new SemanticObject($semantizer, "http://example.org/joe", "foaf:Person");
        $so2 = new SemanticObject($semantizer, "http://example.org/tom", "foaf:Person");
        $so3 = new SemanticObject($semantizer, "http://example.org/mel", "foaf:Person");

        // Test adder and getter
        $so->addSemanticPropertyReference("foaf:ref", $so2);
        $this->assertSame($so2, $so->getSemanticProperty("foaf:ref"));
        $this->assertSame($so2, $so->getSemanticProperty("foaf:ref"));

        // Test setter
        $so->setSemanticProperty("foaf:ref", $so3);

        // Test remover
        $so->removeSemanticProperty("foaf:ref", $so3);
        $this->assertSame(null, $so->getSemanticProperty("foaf:ref"));
        
        // Test collection
        $so->addSemanticPropertyReference("foaf:ref", $so2);
        $so->addSemanticPropertyReference("foaf:ref", $so3);
        $this->assertSame([$so2, $so3], $so->getSemanticPropertyAll("foaf:ref"));
    }

    public function testSemanticPropertyReferenceAnonymous(): void {
        $semantizer = new Semantizer();
        $so = new SemanticObject($semantizer, "http://example.org/joe", "foaf:Person");
        $so2 = new SemanticObjectAnonymous($semantizer, "foaf:Person");
        $so2->addSemanticPropertyLiteral("foaf:desc", "desc");
        $so3 = new SemanticObjectAnonymous($semantizer, "foaf:Person");

        // Test adder and getter
        $so->addSemanticPropertyReference("foaf:ref", $so2);
        $this->assertSame(true, $so2->equals($so->getSemanticProperty("foaf:ref")));
        $this->assertSame(true, $so2->equals($so->getSemanticProperty("foaf:ref")));

        // Test setter
        $so->setSemanticProperty("foaf:ref", $so3);

        // Test remover
        $so->removeSemanticProperty("foaf:ref", $so3);
        $this->assertSame(null, $so->getSemanticProperty("foaf:ref"));

        // Test collection
        $so->addSemanticPropertyReference("foaf:ref", $so2);
        $so->addSemanticPropertyReference("foaf:ref", $so3);

        $properties = $so->getSemanticPropertyAll("foaf:ref");
        $this->assertSame(2, count($properties));
        $this->assertSame(true, $so2->equals($properties[0]));
        $this->assertSame(true, $so3->equals($properties[1]));
    }

    public function testSemanticPropertyReferenceSub(): void {
        $semantizer = new Semantizer();
        $factory = new Factory($semantizer);
        $semantizer->setFactory($factory);

        $so = new SemanticObject($semantizer, "http://example.org/ex", "foaf:Person");
        $soA = new SemanticObjectAnonymousSub($semantizer);
        $so->setSemanticProperty("foaf:test", $soA);

        $this->assertSame(true, $so->getSemanticProperty("foaf:test")->equals($soA));
    }

    public function testEquals(): void {
        $semantizer = new Semantizer();

        $so = new SemanticObject($semantizer, "http://example.org/joe", "foaf:Person");
        $so->addSemanticPropertyLiteral("foaf:name", "Joe");

        $so2 = new SemanticObject($semantizer, "http://example.org/joe", "foaf:Person");
        $so2->addSemanticPropertyLiteral("foaf:name", "Joe");

        $soIdDiff = new SemanticObject($semantizer, "http://example.org/joeDiff", "foaf:Person");
        $soIdDiff->addSemanticPropertyLiteral("foaf:name", "Joe");

        $soPropDiff = new SemanticObject($semantizer, "http://example.org/joe", "foaf:PersonDiff");
        $soPropDiff->addSemanticPropertyLiteral("foaf:name", "Joe");

        $soValueDiff = new SemanticObject($semantizer, "http://example.org/joe", "foaf:Person");
        $soValueDiff->addSemanticPropertyLiteral("foaf:name", "JoeDiff");

        $this->assertSame(true, $so->equals($so));
        $this->assertSame(true, $so->equals($so2));

        $this->assertSame(true, $so2->equals($so2));
        $this->assertSame(true, $so2->equals($so));
        
        $this->assertSame(false, $so->equals($soIdDiff));
        $this->assertSame(false, $so->equals($soPropDiff));
        $this->assertSame(false, $so->equals($soValueDiff));
    }

    public function testEqualsAnonymous(): void {
        $semantizer = new Semantizer();

        $so = new SemanticObjectAnonymous($semantizer, "foaf:Person");
        $so->addSemanticPropertyLiteral("foaf:name", "Joe");

        $so2 = new SemanticObjectAnonymous($semantizer, "foaf:Person");
        $so2->addSemanticPropertyLiteral("foaf:name", "Joe");

        $soPropDiff = new SemanticObjectAnonymous($semantizer, "foaf:PersonDiff");
        $soPropDiff->addSemanticPropertyLiteral("foaf:name", "Joe");

        $soValueDiff = new SemanticObjectAnonymous($semantizer, "foaf:Person");
        $soValueDiff->addSemanticPropertyLiteral("foaf:name", "JoeDiff");

        $this->assertSame(true, $so->equals($so));
        $this->assertSame(true, $so->equals($so2));

        $this->assertSame(true, $so2->equals($so2));
        $this->assertSame(true, $so2->equals($so));
        
        $this->assertSame(false, $so->equals($soPropDiff));
        $this->assertSame(false, $so->equals($soValueDiff));
    }

    public function testEqualsBoth(): void {
        $semantizer = new Semantizer();

        $so = new SemanticObject($semantizer, "http://example.org/joe", "foaf:Person");
        $so->addSemanticPropertyLiteral("foaf:name", "Joe");

        $soa = new SemanticObjectAnonymous($semantizer, "foaf:Person");
        $soa->addSemanticPropertyLiteral("foaf:name", "Joe");
        
        $this->assertSame(false, $so->equals($soa));
        $this->assertSame(false, $soa->equals($so));
    }

    public function testExportEmpty(): void {
        $semantizer = new Semantizer();
        $so = new SemanticObject($semantizer, "http://example.org/joe", "foaf:Person");
        $expected = '{"@id":"http://example.org/joe","@type":"http://xmlns.com/foaf/0.1/Person"}';
        $this->assertSame($expected, $so->toJsonLd());
    }
}