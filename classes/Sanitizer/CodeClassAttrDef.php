<?php

namespace sgkirby\Commentions\Sanitizer;

use HTMLPurifier_AttrDef;

class CodeClassAttrDef extends HTMLPurifier_AttrDef {
    public function validate($string, $config, $context) {
        return preg_match('/^language-[a-z0-9]+$/', $string) === 1;
    }
}
