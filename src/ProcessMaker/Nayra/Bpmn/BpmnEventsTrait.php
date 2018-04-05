<?php

namespace ProcessMaker\Nayra\Bpmn;

/**
 * Trait to implements bpmn events handling.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait BpmnEventsTrait
{
    use ObservableTrait {
        notifyEvent as private internalNotifyEvent;
    }

    /**
     * Array map of custom event classes for the bpmn element.
     *
     * @return array
     */
    abstract protected function getBpmnEventClasses();

    /**
     * Fire a event for the bpmn element.
     *
     * @param string $event
     * @param array ...$arguments
     */
    protected function notifyEvent($event, ...$arguments)
    {
        $bpmnEvents = $this->getBpmnEventClasses();
        if (isset($bpmnEvents[$event])) {
            $payload = new $bpmnEvents[$event]($this, $arguments);
        } else {
            $payload = ["object" => $this, "arguments" => $arguments];
        }
        $this->getOwnerProcess()->getDispatcher()->dispatch($event, $payload);
        array_unshift($arguments, $event);
        call_user_func_array([$this, 'internalNotifyEvent'], $arguments);
    }
}