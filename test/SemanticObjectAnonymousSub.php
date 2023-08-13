<?php

namespace VirtualAssembly\Semantizer;

class SemanticObjectAnonymousSub extends SemanticObjectAnonymous {

    public function __construct(Semantizer $semantizer) {
        parent::__construct(semantizer: $semantizer, semanticType: "foaf:testType");
    }

}