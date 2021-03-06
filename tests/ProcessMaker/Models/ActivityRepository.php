<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\RepositoryTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Repositories\ActivityRepositoryInterface;

/**
 * ActivityRepository
 *
 * @package ProcessMaker\Models
 */
class ActivityRepository implements ActivityRepositoryInterface
{
    use RepositoryTrait;

    /**
     * Create an activity instance.
     *
     * @param ProcessInterface|null $process
     *
     * @return Activity
     */
    public function createActivityInstance(ProcessInterface $process=null)
    {
        $activity = new Activity();
        $activity->setFactory($this->getFactory());
        return $activity;
    }

    /**
     * Creates a ScriptTask
     *
     * @param ProcessInterface|null $process
     *
     * @return ScriptTask
     */
    public function createScriptTaskInstance(ProcessInterface $process=null)
    {
        $activity = new ScriptTask();
        $activity->setFactory($this->getFactory());
        return $activity;
    }

    /**
     * Create an activity instance.
     *
     * @param ProcessInterface|null $process
     *
     * @return Activity
     */
    public function createActivityWithExceptionInstance(ProcessInterface $process=null)
    {
        $activity = new ActivityWithException();
        $activity->setFactory($this->getFactory());
        return $activity;
    }

    /**
     * Create a call activity instance.
     *
     * @return CallActivity
     */
    public function createCallActivityInstance()
    {
        $activity = new CallActivity();
        $activity->setFactory($this->getFactory());
        return $activity;
    }

    /**
     * Load and activity from a storage.
     *
     * @param string $uid
     *
     * @return Activity
     */
    public function loadActivityByUid($uid)
    {

    }

    /**
     * Store the activity.
     *
     * @param ActivityInterface $activity
     * @param bool $saveChildElements
     *
     * @return $this
     */
    public function store(ActivityInterface $activity, $saveChildElements = false)
    {

    }

    /**
     * Load a activity from a persistent storage.
     *
     * @param ProcessInterface|null $process
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface
     */
    public function create(ProcessInterface $process = null)
    {
        $activity = new Activity();
        $activity->setFactory($this->getFactory());
        $activity->setOwnerProcess($process);
        return $activity;
    }
}
