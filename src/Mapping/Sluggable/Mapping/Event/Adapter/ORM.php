<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Sluggable\Mapping\Event\Adapter;

use Doctrine\ORM\Query,
    Gedmo\Sluggable\Mapping\Event\Adapter\ORM as BaseAdapterORM,
    Gedmo\Tool\Wrapper\AbstractWrapper;

/**
 * Doctrine event adapter for ORM adapted
 * for sluggable behavior
 *
 * @author Dmitry Poppov <d.popov@altgraphic.com>
 */
class ORM extends BaseAdapterORM
{
    /**
     * {@inheritDoc}
     */
    public function getSimilarSlugs($object, $meta, array $config, $slug)
    {
        $em = $this->getObjectManager();
        $wrapped = AbstractWrapper::wrap($object, $em);
        $qb = $em->createQueryBuilder();
        $qb->select('rec.' . $config['slug'])
            ->from($config['useObjectClass'], 'rec')
            ->where($qb->expr()->like('rec.' . $config['slug'], ':slug'));

        $qb->setParameter('slug', $slug . '%');

        // use the unique_base to restrict the uniqueness check
        if ($config['unique'] && isset($config['unique_base'])) {
            if (($ubase = $wrapped->getPropertyValue($config['unique_base'])) &&
                !array_key_exists($config['unique_base'], $wrapped->getMetadata()->getAssociationMappings())
            ) {
                $qb->andWhere('rec.' . $config['unique_base'] . ' = :unique_base');
                $qb->setParameter(':unique_base', $ubase);
            } elseif ($wrapped->getMetadata()->hasAssociation($config['unique_base'])) {
                $value = $wrapped->getPropertyValue($config['unique_base']);
                if ($value && $em->getClassMetadata(get_class($value))->getIdentifierValues($value)) {
                    $qb->join('rec.' . $config['unique_base'], 'unique_' . $config['unique_base']);
                    $qb->andWhere('unique_' . $config['unique_base'] . ' = :unique_base');
                    $qb->setParameter(':unique_base', $ubase);
                } else {
                    return [];
                }
            } else {
                $qb->andWhere($qb->expr()->isNull('rec.' . $config['unique_base']));
            }
        }

        // include identifiers
        foreach ((array) $wrapped->getIdentifier(false) as $id => $value) {
            if (!$meta->isIdentifier($config['slug'])) {
                $qb->andWhere($qb->expr()->neq('rec.' . $id, ':' . $id));
                $qb->setParameter($id, $value);
            }
        }

        $q = $qb->getQuery();
        $q->setHydrationMode(Query::HYDRATE_ARRAY);

        return $q->execute();
    }
}
