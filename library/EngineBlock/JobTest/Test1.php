<?php
class EngineBlock_Job_Queue_Test1
    extends EngineBlock_Job_Queue_QueueAbstract
{
    /**
     * @param string $encodedJob
     */
    public function executeJob($encodedJob)
    {
        return true;
    }

    public function getName()
    {
        return 'test 1';
    }
}
