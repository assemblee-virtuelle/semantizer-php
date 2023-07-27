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

class SemanticObjectAnonymous extends SemanticObject {

    public function __construct(Semantizer $semantizer, string $semanticType) {
        parent::__construct($semantizer, "", $semanticType, true);
    }

    protected function createResource(\EasyRdf\Graph $graph, string $semanticId): \EasyRdf\Resource {
        return $graph->newBNode();
    }

    public function isBlankNode(): bool {
        return true;
    }

    public static function makeFromResource(Semantizer $semantizer, \EasyRdf\Resource $resource): SemanticObjectAnonymous {
        $type = $resource->type();

        if (!$type || !$resource->isBNode())
            return null;

        $result = new SemanticObjectAnonymous($semantizer, $type);

        foreach ($resource->propertyUris() as $prop) {
            foreach ($resource->all(\EasyRdf\RdfNamespace::shorten($prop)) as $value) {
                if ($value instanceof \EasyRdf\Resource) {
                    $result->getResource()->addResource($prop, $value);
                    // TODO: here we have to know if the resource must be fetched from
                    // the store to be instanciated as a SemanticObject. We could test 
                    // if its URI includes the DFC prefix.
                    //$semanticObject = $semantizer->fetch($value->getUri());
                    //$result->addSemanticPropertyReference($prop, $semanticObject);
                }
                else $result->addSemanticPropertyLiteral($prop, $value);
            }
        }

        return $result;
    }
    
}