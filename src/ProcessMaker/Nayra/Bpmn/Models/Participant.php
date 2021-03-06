<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Models\ActivityActivatedEvent;
use ProcessMaker\Nayra\Bpmn\ParticipantTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ParticipantInterface;

/**
 * Activity implementation.
 *
 * @package ProcessMaker\Models
 */
class Participant implements ParticipantInterface
{
    use ParticipantTrait;

    /**
     * Array map of custom event classes for the bpmn element.
     *
     * @return array
     */
    protected function getBpmnEventClasses()
    {
        return [
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED => ActivityActivatedEvent::class,
        ];
    }
}
