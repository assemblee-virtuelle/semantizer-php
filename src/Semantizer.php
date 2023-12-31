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
    private IFactory $factory;
    private \Closure $fetchFunction;

    public function __construct() {
        $this->store = new Store();
        $this->setFetchFunction(); // init the default fetch function
    }

    public function setFactory(IFactory $factory) {
        $this->factory = $factory;
    }

    public function getFactory(): IFactory {
        if (!isset($this->factory))
            throw new \Error("Uninitialized factory.");

        return $this->factory;
    }

    public function setFetchFunction(\Closure $fetchFunction = null) {
        $this->fetchFunction = $fetchFunction? $fetchFunction: \Closure::fromCallable([$this, 'getDefaultFetchFunction']);
    }

    public function getFetchFunction(): \Closure {
        return $this->fetchFunction;
    }

    public function getStore(): IStore {
        return $this->store;
    }

    public function export(Array $semanticObjects, Array $context = null): string {
        $graph = $this->merge($semanticObjects);
        return $graph->serialise('jsonld', ["compact" => true, "context" => $context]);
    }

    public function import(string $data, string $baseUri = null, Array &$resourceUriThatCantBeImported = array()): Array {
        $result = array();
        $parser = new \EasyRdf\Parser\JsonLd();
        $graph = new \EasyRdf\Graph();
        $parser->parse($graph, $data, 'jsonld', $baseUri);

        foreach ($graph->resources() as $resource) {
            try {
                $semanticable = $this->getFactory()->makeFromResource($resource);
                array_push($result, $semanticable);
                $this->getStore()->set($resource->getUri(), $semanticable);
            }
            catch(\TypeError $e) {
                array_push($resourceUriThatCantBeImported, $resource->getUri());
            }
        }
        
        return $result;
    }

    public function getDefaultfetchFunction(string $semanticObjectId): string {
        $opts = array('http' => array('method' => "GET", 'header' => "Accept: application/ld+json"));
        $context = stream_context_create($opts);
        return file_get_contents($semanticObjectId, false, $context);
    }

    public function fetch(string $semanticObjectId): Semanticable {
        if (!$this->getStore()->has($semanticObjectId)) {
            $jsonld = $this->getFetchFunction()->call($this, $semanticObjectId);
            $this->import($jsonld);
        }
        return $this->getStore()->get($semanticObjectId);
    }

    public function shorten(string $uri): string {
        $shorten = \EasyRdf\RdfNamespace::shorten($uri);
        return $shorten? $shorten: $uri;
    }

    public function expand(string $uri): string {
        return \EasyRdf\RdfNamespace::expand($uri);
    }

    public function getPrefix(string $uri): string {
        return \EasyRdf\RdfNamespace::prefixOfUri($uri);
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
