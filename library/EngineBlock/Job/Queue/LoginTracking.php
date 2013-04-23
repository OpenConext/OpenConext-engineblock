<?php
class EngineBlock_Job_Queue_LoginTracking
    extends EngineBlock_Job_Queue_QueueAbstract
{
    const NAME = 'logintracking';

    /**
     * @var EngineBlock_Tracker
     */
    private $tracker;

    /**
     * @param Redis $redisClient
     */
    public function __construct(Redis $redisClient)
    {
        parent::__construct($redisClient);
        $this->tracker = new EngineBlock_Tracker();
    }

    /**
     * @param string $encodedJob
     */
    public function handleJob($encodedJob)
    {
        $login = $this->decodeJob($encodedJob);
        $this->tracker->storeInDatabase($login);
    }

    public function getName()
    {
        return self::NAME;
    }
}
