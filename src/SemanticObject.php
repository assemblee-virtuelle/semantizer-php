<?php
/*
Copyright (c) 2023 Maxime Lecoq <maxime@lecoqlibre.fr>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

namespace VirtualAssembly\Semantizer;

class SemanticObject implements Semanticable {

    private Semantizer $semantizer;
    private \EasyRdf\Graph $graph;
    private \EasyRdf\Resource $resource;

    public function __construct(Semantizer $semantizer, \EasyRdf\Resource $resource = null, string $semanticId = null, string $semanticType = null, bool $doNotStore = false) {
        $this->semantizer = $semantizer;
        $this->graph = new \EasyRdf\Graph();

        if ($resource) {
            // We create a new Semanticable by copying the passed in resource.
            $this->resource = $this->createResource($this->graph, $resource->getUri());
            foreach ($resource->propertyUris() as $prop) {
                foreach ($resource->all(\EasyRdf\RdfNamespace::shorten($prop)) as $value) {
                    if ($value instanceof \EasyRdf\Resource) {
                        $this->getResource()->addResource($prop, $value);
                        // TODO: here we have to know if the resource must be fetched from
                        // the store to be instanciated as a SemanticObject. We could test 
                        // if its URI includes the DFC prefix.
                        //$semanticObject = $semantizer->fetch($value->getUri());
                        //$result->addSemanticPropertyReference($prop, $semanticObject);
                    }
                    else $this->addSemanticPropertyLiteral($prop, $value);
                }
            }
            $this->resource = $resource;
        }

        else {
            if ($semanticId === null)
                throw new \Error("When creating an object, one must provide its id.");
            if (!$semanticType)
                throw new \Error("When creating an object, one must provide its type.");
            $this->resource = $this->createResource($this->graph, $semanticId);
            $this->resource->setType($semanticType);
        }

        if (!$doNotStore)
            $semantizer->store($this);
    }

    protected function createResource(\EasyRdf\Graph $graph, string $semanticId): \EasyRdf\Resource {
        return $graph->resource($semanticId);
    }

    public function getGraph(): \EasyRdf\Graph {
        return $this->graph;
    }

    public function getResource(): \EasyRdf\Resource {
        return $this->resource;
    }

    public function getSemantizer(): Semantizer {
        return $this->semantizer;
    }

    public function isBlankNode(): bool {
        return false;
    }

    public function getSemanticId(): string {
        return $this->resource->getUri();
    }

    public function getSemanticType(): string {
        return $this->getResource()->type();
    }

    public function equals(SemanticObject $other): bool {
        return $this->getGraph()->toRdfPhp() == $other->getGraph()->toRdfPhp();
    }

    private function makeBlankNode(\EasyRdf\Resource $resource): \EasyRdf\Resource {
        $bn = $this->graph->newBNode();
        foreach ($resource->propertyUris() as $uri) {
            $prefixedUri = \EasyRdf\RdfNamespace::shorten($uri, true);
            $value = $resource->get($prefixedUri);
            $value = $value instanceof \EasyRdf\Resource? $resource->getResource($prefixedUri): $value;
            $bn->add($uri, $value);
        }
        return $bn;
    }

    private function addResource(string $name, \EasyRdf\Resource $resource): void {
        $this->getResource()->addResource($name, $resource);
    }
    
    public function addSemanticPropertyReference(string $name, Semanticable $semanticObject): void {
        $resource = $semanticObject->isBlankNode()? $this->makeBlankNode($semanticObject->getResource()): $semanticObject->getResource();
        $this->addResource($name, $resource);
    }

    public function addSemanticPropertyLiteral(string $name, mixed $value): void {
        $this->getResource()->addLiteral($name, $value); //$newValue);
    }

    private function makeAnonymous(\EasyRdf\Resource $resource): SemanticObjectAnonymous {
        return $this->getSemantizer()->getFactory()->makeFromResource($resource);
    }

    private function fetch(string $uri): Semanticable {
        return $this->getSemantizer()->fetch($uri);
    }

    public function getSemanticProperty(string $name): mixed {
        $semanticProperty = $this->getGraph()->get($this->getResource(), $name);
        if ($semanticProperty instanceof \EasyRdf\Resource)
            return $this->getSemanticPropertyReference($semanticProperty);
        if ($semanticProperty instanceof \EasyRdf\Literal)
            return $this->getSemanticPropertyLiteral($semanticProperty);
        return null;
    }

    private function getSemanticPropertyReference(\EasyRdf\Resource $resource): Semanticable {
        return $resource->isBNode()? $this->makeAnonymous($resource): $this->fetch($resource->getUri());
    }

    private function getSemanticPropertyLiteral(\EasyRdf\Literal $literal): mixed {
        return $literal->getValue();
    }

    private function getSemanticPropertyReducer(mixed $typeHint): callable {
        return $typeHint instanceof \EasyRdf\Resource? $this->getSemanticPropertyResourceReducer($typeHint): $this->getSemanticPropertyLiteralReducer();
    }

    private function getSemanticPropertyResourceReducer(\EasyRdf\Resource $resource): callable {
        return $resource->isBNode()? $this->getSemanticPropertyAnonymousReducer(): $this->getSemanticPropertyReferenceReducer();
    }

    private function getSemanticPropertyReferenceReducer(): callable {
        return function ($acc, $item) { 
            $semanticObject = $this->fetch($item->getUri());
            array_push($acc, $semanticObject);
            return $acc; 
        };
    }

    private function getSemanticPropertyAnonymousReducer(): callable {
        return function ($acc, $item) {
            $anonymous = $this->makeAnonymous($item);
            array_push($acc, $anonymous); 
            return $acc;
        };
    }

    private function getSemanticPropertyLiteralReducer(): callable {
        return function ($acc, $item) { array_push($acc, $item->getValue()); return $acc; };
    }

    public function getSemanticPropertyAll(string $name): Array {
        $results = $this->getGraph()->all($this->getResource(), $name);

        if (empty($results))
            return $results;
        
        $reducer = $this->getSemanticPropertyReducer($results[0]);
        return array_reduce($results, $reducer, []);
    }

    /** BUG: when a property is a blank node, we must also delete the 
     * blank node from the graph. The library does not seem to allow
     * that.
     */
    public function removeSemanticPropertyAll(string $name): void {
        $this->getGraph()->delete($this->getResource(), $name);
    }

    /** BUG: when a property is a blank node, we must also delete the 
     * blank node from the graph. The library does not seem to allow
     * that.
     */
    public function removeSemanticProperty(string $name, mixed $value): void {
        if ($value instanceof SemanticObjectAnonymous) {
            // As the passed in value is not the same object that is stored in the 
            // graph (but a copy), we have to find the corresponding object in the 
            // graph using the equals method.
            $candidates = $this->getGraph()->allOfType($value->getResource()->type());
            foreach ($candidates as $candidate) {
                if ($candidate->isBNode() && $value->equals($this->makeAnonymous($candidate))) {
                    $value = $candidate;
                    break; // there can't be multiple equal objects, right?
                }
            }

            // If we did not find the blank node we throw an error.
            if (!$value instanceof \EasyRdf\Resource)
                throw new Error("Corresponding blank node not found for property: " . $name);
        }
        
        if ($value instanceof SemanticObject) {
            $value = $value->getResource();
        }
        
        $this->graph->delete($this->getResource(), $name, $value);
    }

    public function setSemanticProperty(string $name, mixed $newValue, mixed $oldValue = null): void {
        $isBlankNode = $newValue instanceof SemanticObjectAnonymous;

        if ($newValue instanceof SemanticObject || $isBlankNode) {
            $newValue = $newValue->getResource();
        }

        if ($isBlankNode)
            $newValue = $this->makeBlankNode($newValue);

        $this->graph->set($this->getResource(), $name, $newValue);
    }

    public function toJsonLd(): string {
        return $this->graph->serialise("jsonld", ["compact" => true]);
    }
    
}