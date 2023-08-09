<?php

namespace VirtualAssembly\Semantizer;

require_once(__DIR__ . "/SemanticObjectAnonymousSub.php");

class Factory implements IFactory {
    private Semantizer $semantizer;

    public function __construct(Semantizer $semantizer) {
        $this->semantizer = $semantizer;
    }

    public function make(string $type): Semanticable {
        if ($type === "foaf:testType") return new SemanticObjectAnonymousSub($this->semantizer);
        return null;
    }
}