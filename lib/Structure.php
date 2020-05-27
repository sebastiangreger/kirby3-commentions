<?php

namespace sgkirby\Commentions;

use Kirby\Cms\Structure as BaseStructure;
use Kirby\Exception\InvalidArgumentException;

/**
 * The Structure hold a collection of Commention objects,
 * providing accesss to their data in a Kirby-style manner.
 */
class Structure extends BaseStructure
{
    /**
     * Creates a new Collection with the given objects
     *
     * @param array $objects
     * @param object $parent
     */
    public function __construct($objects = [], $parent = null)
    {
        $this->parent = $parent;
        $this->set($objects);
    }

    /**
     * The internal setter for collection items.
     * This makes sure that nothing unexpected ends
     * up in the collection. You can pass arrays or
     * StructureObjects
     *
     * @param string $id
     * @param array|Commention $props
     */
    public function __set(string $id, $props)
    {
        if (is_a($props, 'sgkirby\Commentions\Commention') === true) {
            $object = $props;
        } else {
            if (is_array($props) === false) {
                throw new InvalidArgumentException('Invalid commentions structure data');
            }

            if (!array_key_exists('uid', $props)) {
                throw new InvalidArgumentException('Commentions require a "uid" property.');
            };

            $object = new Commention([
                'content'    => $props,
                'id'         => $props['uid'] ?? $id,
                'parent'     => $this->parent,
                'structure'  => $this
            ]);
        }

        return parent::__set($object->id(), $object);
    }
}
