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

    public function __construct(Semantizer $semantizer, \EasyRdf\Resource $resource = null, string $semanticType = null) {
        parent::__construct(semantizer: $semantizer, resource: $resource, semanticId: "", semanticType: $semanticType, doNotStore: true);

        if ($resource && !$resource->isBNode())
            throw new \TypeError("To make a SemanticObjectAnonymous from a resource, it must be a blank node.", 404);
    }

    protected function createResource(\EasyRdf\Graph $graph, string $semanticId): \EasyRdf\Resource {
        return $graph->newBNode();
    }

    public function isBlankNode(): bool {
        return true;
    }
   
}