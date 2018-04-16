<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\MessageEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\OperationInterface;

/**
 * MessageEventDefinition class
 *
 */
class MessageEventDefinition implements MessageEventDefinitionInterface
{
    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface $message
     */
    private $message;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\OperationInterface $operation
     */
    private $operation;

    /**
     * Get the message.
     *
     * @return MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get the Operation that is used by the Message Event.
     *
     * @return OperationInterface
     */
    public function getOperation()
    {
        return $this->operation;
    }

    public function setMessage(MessageInterface $message)
    {
        $this->message = $message;
        return $this;
    }

    public function setOperation(OperationInterface $operation)
    {
        $this->operation = $operation;
        return $this;
    }
}