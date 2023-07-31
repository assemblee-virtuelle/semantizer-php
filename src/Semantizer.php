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

class Semantizer {

    private IStore $store;

    public function __construct() {
        $this->store = new Store();
    }

    public function export(Array $semanticObjects, Array $context = null): string {
        $graph = $this->merge($semanticObjects);
        return $graph->serialise('jsonld', ["compact" => true, "context" => $context]);
    }

    public function fetch(string $semanticObjectId): Semanticable {
        return $this->store->get($semanticObjectId);
    }

    public function setPrefix(string $prefix, string $uri): void {
        \EasyRdf\RdfNamespace::set($prefix, $uri);
    }

    public function store(Semanticable $semanticable): void {
        $this->store->set($semanticable);
    }

    public function merge(Array $objects): \EasyRdf\Graph {
        $resultGraph = new \EasyRdf\Graph();
    
        // We need to browse every graph resource
        foreach ($objects as $object) {
            $blankNodes = [];
            $objectGraph = $object->getGraph();
    
            foreach ($objectGraph->resources() as $objectResource) {
                $resultGraphResource = null;
                
                if ($objectResource->isBNode())
                    $resultGraphResource = $blankNodes[$objectResource->getBNodeId()];
                else $resultGraphResource = $resultGraph->resource($objectResource->getUri());
        
                foreach ($objectResource->propertyUris() as $prop) {
                    foreach ($objectResource->all(\EasyRdf\RdfNamespace::shorten($prop)) as $value) {
                        // If the property is a blank node
                        if ($value instanceof \EasyRdf\Resource && $value->isBNode()) {
                            $graphBlankNode = $resultGraph->newBNode();
                            $blankNodes[$value->getBNodeId()] = $graphBlankNode;
                            $value = $graphBlankNode;
                        }
                        $resultGraphResource->add($prop, $value);
                    }
                }
            }
    
        }
    
        return $resultGraph;
    }
}
