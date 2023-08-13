<?php

namespace VirtualAssembly\Semantizer;

require_once(__DIR__ . "/SemanticObjectAnonymousSub.php");

class Factory implements IFactory {
    private Semantizer $semantizer;

    public function __construct(Semantizer $semantizer) {
        $this->semantizer = $semantizer;
    }

    public function makeFromResource(\EasyRdf\Resource $resource): Semanticable {
        $type = $resource->type();
        if ($type === "foaf:testType") return new SemanticObjectAnonymousSub($this->semantizer);
        if ($type === "foaf:Person") return new SemanticObjectAnonymous(semantizer: $this->semantizer, resource: $resource);
        return null;
    }
}