<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping;

use Zend\Form\Annotation as Form,
    Doctrine\Common\NotifyPropertyChanged,
    Doctrine\Common\PropertyChangedListener;

/**
 * @author Dmitry Popov <d.popov@altgraphic.com>
 */
trait NotifyPropertyChangedTrait
{
    /**
     * @var array
     *
     * @Form\Exclude()
     */
    private $listeners = [];

    /**
     * @param string $propName
     * @param mixed $oldValue
     * @param mixed $newValue
     * @return self
     */
    protected function onPropertyChanged($propName, $oldValue, $newValue)
    {
        foreach ($this->listeners as $listener) {
            $listener->propertyChanged($this, $propName, $oldValue, $newValue);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addPropertyChangedListener(PropertyChangedListener $listener)
    {
        $this->listeners[] = $listener;
        return $this;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        // Exclude listeners property
        return array_diff(array_keys(get_object_vars($this)), ['listeners']);
    }
}
