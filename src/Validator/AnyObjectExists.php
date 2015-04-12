<?php
/**
 * CoolMS2 Doctrine Extentions Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Validator;

use DoctrineModule\Validator\ObjectExists;

class AnyObjectExists extends ObjectExists
{
    /**
     * {@inheritDoc}
     */
    public function isValid($value)
    {
        $value = null === $value ? [null] : (array) $value;

        $valueCount  = count($value);
        $fieldsCount = count($this->fields);
        if (($valueCount === 1 && $fieldsCount === 1)
            || (($valueCount !== 1 && $fieldsCount !== 1)
                && $valueCount !== $fieldsCount)
        ) {
            return parent::isValid($value);
        }

        if ($valueCount > $fieldsCount) {
            $field = reset($this->fields);
            $match = $this->objectRepository->findOneBy(array($field => $value));
        } else {
            if ($valueCount < $fieldsCount) {
                $value = array_fill(0, $fieldsCount, array_shift($value));
            }

            $value = $this->cleanSearchValue($value);

            $qb = $this->objectRepository->createQueryBuilder('o');
            $orX = $qb->expr()->orX();

            foreach ($this->fields as $key => $field) {
                $orX->add("o.$field = ?$key");
                $qb->setParameter($key, array_shift($value));
            }

            $className = $this->objectRepository->getClassName();
            $object = new $className;
            $meta = $qb->getObjectManager()->getClassMetadata($className);
            $identifier = $meta->getSingleIdentifierFieldName();

            $match = $qb->select("count(o.$identifier)")
                        ->where($orX)
                        ->getQuery()
                        ->getSingleScalarResult();
        }

        if ($match) {
            return true;
        }

        $this->error(self::ERROR_NO_OBJECT_FOUND, $value);

        return false;
    }
}
