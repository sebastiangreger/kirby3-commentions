<?php

namespace sgkirby\Commentions\Formatter;

use HTMLPurifier_AttrValidator;
use HTMLPurifier_Injector;
use HTMLPurifier_Token_End;
use HTMLPurifier_Token_Start;

// https://stackoverflow.com/questions/9765982/configuring-htmlpurifier-to-display-external-links-as-plain-text

class RemoveEmptyLinksInjector extends HTMLPurifier_Injector {

    public $name = 'RemoveInactiveLinks';
    public $needed = ['a'];

    private $attrValidator;
    private $config;
    private $context;

    public function prepare($config, $context)
    {
        $this->attrValidator = new HTMLPurifier_AttrValidator();
        $this->config = $config;
        $this->context = $context;
        return parent::prepare($config, $context);
    }

    public function handleElement(&$token)
    {
        if ($token->name !== 'a' || !$token instanceof HTMLPurifier_Token_Start) {
            // Only apply to a telements
            return;
        }

        // We need to validate the attributes now since this doesn't normally
        // happen until after MakeWellFormed. If the link has no href
        // attribute, it needs to be removed.
        $this->attrValidator->validateToken($token, $this->config, $this->context);
        $token->armor['ValidateAttributes'] = true;

        if (!empty($token->attr['href'])) {
            // Link has attributes and the required href attribute,
            // just keep it and abort here.
            return;
        }

        $nesting = 0;
        while ($this->forwardUntilEndToken($i, $current, $nesting)) {};

        if ($current instanceof HTMLPurifier_Token_End && $current->name === 'a') {
            // Mark closing a tag for deletion
            $current->markForDeletion = true;
            // Delete a span tag
            $token = false;
        }
    }

    public function handleEnd(&$token)
    {
        if ($token->markForDeletion) {
            $token = false;
        }
    }
};
