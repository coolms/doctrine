<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Relation\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * This annotation is used to override association mapping of property for an entity relationship.
 *
 * @author Dmitry Popov <d.popov@altgraphic.com>
 *
 * @Annotation
 * @Target("ANNOTATION")
 */
class RelationOverride extends Annotation
{
    /**
     * @var string
     */
    public $targetEntity;

    /**
     * The name of the relationship property whose mapping is being overridden.
     *
     * @var string
     */
    public $name;

    /**
     * @var string|int
     */
    public $type;

    /**
     * @var array
     */
    public $cascade;

    /**
     * @var bool
     */
    public $orphanRemoval;

    /**
     * @var string
     */
    public $fetch;

    /**
     * @var string
     */
    public $inversedBy;

    /**
     * @var string
     */
    public $mappedBy;

    /**
     * The join column that is being mapped to the persistent attribute.
     *
     * @var array<\Doctrine\ORM\Mapping\JoinColumn>
     */
    public $joinColumns;

    /**
     * The join table that maps the relationship.
     *
     * @var \Doctrine\ORM\Mapping\JoinTable
     */
    public $joinTable;
}
