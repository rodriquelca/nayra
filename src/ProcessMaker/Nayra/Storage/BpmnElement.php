<?php

namespace ProcessMaker\Nayra\Storage;

use DOMAttr;
use DOMElement;
use ProcessMaker\Nayra\Contracts\Bpmn\CallableElementInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface;
use ProcessMaker\Nayra\Contracts\Storage\BpmnElementInterface;
use ProcessMaker\Nayra\Exceptions\NotImplementedException;

/**
 * Description of BpmnFileElement
 *
 * @property XmlLoader $ownerDocument
 *
 * @package \ProcessMaker\Nayra\Storage
 */
class BpmnElement extends DOMElement implements BpmnElementInterface
{

    /**
     * Get instance of the BPMN element.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface
     */
    public function getBpmnElementInstance()
    {
        $id = $this->getAttribute('id');
        if ($id && $this->ownerDocument->hasBpmnInstance($id)) {
            return $this->ownerDocument->getElementInstanceById($id);
        }
        $map = $this->ownerDocument->getBpmnElementsMapping();
        if (!array_key_exists($this->namespaceURI, $map)) {
            throw new NotImplementedException("Not found " . $this->namespaceURI);
        }
        if (!array_key_exists($this->localName, $map[$this->namespaceURI])) {
            throw new NotImplementedException("Not found " . $this->localName);
        }
        if ($map[$this->namespaceURI][$this->localName]===self::SKIP_ELEMENT) {
            return null;
        }
        list($classInterface, $mapProperties) = $map[$this->namespaceURI][$this->localName];
        if ($classInterface === self::IS_PROPERTY) {
            $bpmnElement = $this->ownerDocument->getElementInstanceById($this->nodeValue);
            $this->bpmn = $bpmnElement;
        } else {
            $bpmnElement = $this->ownerDocument->getFactory()->createInstanceOf($classInterface);
            $bpmnElement->setFactory($this->ownerDocument->getFactory());
            if ($bpmnElement instanceof CallableElementInterface) {
                $bpmnElement->setEngine($this->ownerDocument->getEngine());
            }
            $id ? $this->ownerDocument->indexBpmnElement($id, $bpmnElement) : null;
            foreach ($this->attributes as $attribute) {
                $this->setBpmnPropertyRef($attribute, $mapProperties, $bpmnElement);
            }
            $this->loadBodyContent($mapProperties, $bpmnElement);
            $this->loadChildElements($bpmnElement, $mapProperties);
        }
        return $bpmnElement;
    }

    /**
     * Load child elements.
     *
     * @param EntityInterface $owner
     * @param array $mapProperties
     */
    private function loadChildElements(EntityInterface $owner, array $mapProperties)
    {
        foreach ($this->childNodes as $node) {
            if (!($node instanceof BpmnElement)) {
                continue;
            }
            $bpmn = $node->getBpmnElementInstance($owner);
            if ($bpmn && is_object($bpmn)) {
                $this->setBpmnPropertyTo($bpmn, $node, $mapProperties, $owner);
            }
        }
    }

    /**
     * Set value of BPMN property.
     *
     * @param mixed $value
     * @param \ProcessMaker\Nayra\Storage\BpmnElement $node
     * @param array $mapProperties
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface $to
     */
    private function setBpmnPropertyTo($value, BpmnElement $node, array $mapProperties, EntityInterface $to)
    {
        foreach ($mapProperties as $name => $property) {
            list($multiplicity, $type) = $property;
            $isThisProperty = (is_string($type) && is_subclass_of($value, $type))
                || (is_array($type) && $node->namespaceURI === $type[0] && $node->localName===$type[1]);
            if ($isThisProperty && $multiplicity === 'n') {
                $to->addProperty($name, $value);
            } else if ($isThisProperty && $multiplicity == '1') {
                $to->setProperty($name, $value);
            }
        }
    }

    /**
     * Set BPMN property reference.
     *
     * @param DOMAttr $node
     * @param array $mapProperties
     * @param EntityInterface $bpmnElement
     * @return void
     */
    private function setBpmnPropertyRef(DOMAttr $node, array $mapProperties, EntityInterface $bpmnElement)
    {
        foreach ($mapProperties as $name => $property) {
            list($multiplicity, $type) = $property;
            $isThisProperty = (is_array($type) && ($node->namespaceURI === $type[0] || $node->namespaceURI===null)
                && $node->localName===$type[1]);
            if ($isThisProperty && $multiplicity === 'n') {
                $ref = $this->ownerDocument->getElementInstanceById($node->value);
                $bpmnElement->addProperty($name, $ref);
                return;
            } else if ($isThisProperty && $multiplicity == '1') {
                $id = $node->value;
                $ref = $this->ownerDocument->getElementInstanceById($id);
                $setter = 'set' . $name;
                method_exists($bpmnElement, $setter) ? $bpmnElement->$setter($ref)
                    : $bpmnElement->setProperty($name, $ref);
                return;
            }
        }
        $setter = 'set' . $node->name;
        method_exists($bpmnElement, $setter) ? $bpmnElement->$setter($node->value)
            : $bpmnElement->setProperty($node->name, $node->value);
    }

    /**
     * Load body content.
     *
     * @param array $mapProperties
     * @param EntityInterface $bpmnElement
     */
    private function loadBodyContent(array $mapProperties, EntityInterface $bpmnElement)
    {
        foreach ($mapProperties as $name => $property) {
            list($multiplicity, $type) = $property;
            $isThisProperty = $type === self::DOM_ELEMENT_BODY;
            if ($isThisProperty && $multiplicity === 'n') {
                $bpmnElement->addProperty($name, $this->textContent);
            } else if ($isThisProperty && $multiplicity == '1') {
                $bpmnElement->setProperty($name, $this->textContent);
            }
        }
    }
}