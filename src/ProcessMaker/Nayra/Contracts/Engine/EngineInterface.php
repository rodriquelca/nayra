<?php

namespace ProcessMaker\Nayra\Contracts\Engine;

use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ObservableInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;

/**
 * Engine interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Engine
 */
interface EngineInterface
{
    /**
     * Factory used to create the concrete bpmn classes for the engine.
     *
     * @param RepositoryFactoryInterface $factory
     *
     * @return $this
     */
    public function setRepositoryFactory(RepositoryFactoryInterface $factory);

    /**
     * @return RepositoryFactoryInterface
     */
    public function getRepositoryFactory();

    /**
     * Dispatcher of events used by the engine.
     *
     * @param \Illuminate\Contracts\Events\Dispatcher $dispatcher
     *
     * @return $this
     */
    public function setDispatcher(\Illuminate\Contracts\Events\Dispatcher $dispatcher);

    /**
     * @return \Illuminate\Contracts\Events\Dispatcher
     */
    public function getDispatcher();

    /**
     * Run to the next state.
     *
     * @param int $maxIterations
     *
     * @return bool
     */
    public function runToNextState($maxIterations = 0);

    /**
     * Execute all the active transitions.
     *
     * @return bool
     */
    public function step();

    /**
     * Create an execution instance of a process.
     *
     * @param ProcessInterface $process
     * @param DataStoreInterface $data
     *
     * @return int
     */
    public function createExecutionInstance(ProcessInterface $process, DataStoreInterface $data);

    /**
     * Close all the execution instances.
     *
     * @return bool
     */
    public function closeExecutionInstances();
}