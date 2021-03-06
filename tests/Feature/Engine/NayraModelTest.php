<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Models\DataStore;
use ProcessMaker\Models\Flow;
use ProcessMaker\Models\Process;
use ProcessMaker\Nayra\Bpmn\Model\Activity;
use ProcessMaker\Nayra\Bpmn\Model\EndEvent;
use ProcessMaker\Nayra\Bpmn\Model\ExclusiveGateway;
use ProcessMaker\Nayra\Bpmn\Model\InclusiveGateway;
use ProcessMaker\Nayra\Bpmn\Model\StartEvent;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ExclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\InclusiveGatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;
use ProcessMaker\Nayra\Contracts\FactoryInterface;
use ProcessMaker\Nayra\Factory;

/**
 * Tests for the Nayra/Bpmn/Model classes
 *
 * @package Tests\Feature\Engine
 */
class NayraModelTest extends EngineTestCase
{
    /**
     * Tests a process with exclusive gateways that uses the Nayra/Bpmn/Model classes
     */
    public function testProcessWithExclusiveGateway()
    {
        $config = $this->createMappingConfiguration();
        $factory = new Factory($config);

        $processData = $this->createProcessWithExclusiveGateway($factory);
        $process = $processData['process'];
        $start = $processData['start'];
        $activityA = $processData['activityA'];
        $activityB = $processData['activityB'];
        $activityC = $processData['activityC'];
        $dataStore = $processData['dataStore'];
        $dataStore->putData('A', '2');
        $dataStore->putData('B', '1');

        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Start the process
        $start->start();

        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. Two activities are activated.
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        $tokenB = $activityB->getTokens($instance)->item(0);
        $activityB->complete($tokenB);

        //the run to next state should go false when the max steps is reached.
        $this->assertFalse($this->engine->runToNextState(1));
    }

    /**
     * Tests a process with inclusive gateways that uses the Nayra/Bpmn/Model classes
     */
    public function testProcessWithInclusiveGateway()
    {
        $config = $this->createMappingConfiguration();
        $factory = new Factory($config);

        $processData = $this->createProcessWithInclusiveGateway($factory);
        $process = $processData['process'];
        $start = $processData['start'];
        $activityA = $processData['activityA'];
        $activityB = $processData['activityB'];
        $dataStore = $processData['dataStore'];
        $dataStore->putData('A', '1');
        $dataStore->putData('B', '1');

        $instance = $this->engine->createExecutionInstance($process, $dataStore);

        //Start the process
        $start->start();

        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. Two activities are activated.
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED
        ]);

        //Completes the Activity A
        $tokenA = $activityA->getTokens($instance)->item(0);
        $activityA->complete($tokenA);

        //the run to next state should go false when the max steps is reached.
        $this->assertFalse($this->engine->runToNextState(1));

        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. The activity is closed.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
        ]);

        //Completes the Activity B
        $tokenB = $activityB->getTokens($instance)->item(0);
        $activityB->complete($tokenB);
        $this->engine->runToNextState();

        //Assertion: Verify the triggered engine events. The activity is closed and process is ended.
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_ARRIVES,
            GatewayInterface::EVENT_GATEWAY_ACTIVATED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            GatewayInterface::EVENT_GATEWAY_TOKEN_PASSED,
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            EndEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EndEventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
    }

    /**
     * Creates the mappings between instances and concrete classes
     *
     * @return array
     */
    private function createMappingConfiguration()
    {
        return [
            ActivityInterface::class => Activity::class,
            StartEventInterface::class => StartEvent::class,
            EndEventInterface::class => EndEvent::class,
            ExclusiveGatewayInterface::class => ExclusiveGateway::class,
            InclusiveGatewayInterface::class => InclusiveGateway::class,
            ProcessInterface::class => Process::class,
            DataStoreInterface::class => DataStore::class,
            FlowInterface::class => Flow::class,
        ];
    }

    /**
     * Creates a process that contains an exclusive gateway, start, end events and activities
     *
     * @param FactoryInterface $factory
     *
     * @return array
     */
    private function createProcessWithExclusiveGateway(FactoryInterface $factory)
    {
        $process = $factory->createInstanceOf(ProcessInterface::class);
        $start = $factory->createInstanceOf(StartEventInterface::class);
        $gatewayA = $factory->createInstanceOf(ExclusiveGatewayInterface::class);
        $activityA = $factory->createInstanceOf(ActivityInterface::class);
        $activityB = $factory->createInstanceOf(ActivityInterface::class);
        $activityC = $factory->createInstanceOf(ActivityInterface::class);
        $end = $factory->createInstanceOf(EndEventInterface::class);
        $dataStore = $factory->createInstanceOf(DataStoreInterface::class);

        $process
            ->addActivity($activityA)
            ->addActivity($activityB)
            ->addActivity($activityC);

        $process
            ->addGateway($gatewayA);

        $process
            ->addEvent($start)
            ->addEvent($end);

        //flows
        $start->createFlowTo($gatewayA, $factory);
        $gatewayA
            ->createConditionedFlowTo($activityA, function ($data) {
                return $data['A']=='1';
            }, false, $factory)
            ->createConditionedFlowTo($activityB, function ($data) {
                return $data['B']=='1';
            }, false, $factory)
            ->createFlowTo($activityC, $factory);

        $activityA->createFlowTo($end, $factory);
        $activityB->createFlowTo($end, $factory);
        $activityC->createFlowTo($end, $factory);

        return [
            'process' => $process,
            'start' => $start,
            'gatewayA' => $gatewayA,
            'activityA' => $activityA,
            'activityB' => $activityB,
            'activityC' => $activityC,
            'end' => $end,
            'dataStore' => $dataStore,
        ];
    }

    /**
     * Creates a process that contains an inclusive gateway, start, end events and activities
     *
     * @param FactoryInterface $factory
     *
     * @return array
     */
    private function createProcessWithInclusiveGateway($factory)
    {
        $process = $factory->createInstanceOf(ProcessInterface::class);
        $start = $factory->createInstanceOf(StartEventInterface::class);
        $gatewayA = $factory->createInstanceOf(InclusiveGatewayInterface::class);
        $gatewayB = $factory->createInstanceOf(InclusiveGatewayInterface::class);
        $activityA = $factory->createInstanceOf(ActivityInterface::class);
        $activityB = $factory->createInstanceOf(ActivityInterface::class);
        $end = $factory->createInstanceOf(EndEventInterface::class);
        $dataStore = $factory->createInstanceOf(DataStoreInterface::class);

        $process
            ->addActivity($activityA)
            ->addActivity($activityB);
        $process
            ->addGateway($gatewayA)
            ->addGateway($gatewayB);
        $process
            ->addEvent($start)
            ->addEvent($end);

        //flows
        $start->createFlowTo($gatewayA, $factory);
        $gatewayA
            ->createConditionedFlowTo($activityA, function ($data) {
                return $data['A'] == '1';
            }, false, $factory)
            ->createConditionedFlowTo($activityB, function ($data) {
                return $data['B'] == '1';
            }, false, $factory);
        $activityA->createFlowTo($gatewayB, $factory);
        $activityB->createFlowTo($gatewayB, $factory);
        $gatewayB->createFlowTo($end, $factory);
        return [
            'process' => $process,
            'start' => $start,
            'gatewayA' => $gatewayA,
            'gatewayB' => $gatewayB,
            'activityA' => $activityA,
            'activityB' => $activityB,
            'end' => $end,
            'dataStore' => $dataStore,
        ];
    }
}
