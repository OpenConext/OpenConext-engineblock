<?php
abstract class EngineBlock_Job_Queue_QueueAbstract
{
    /**
     * @var Redis
     */
    protected $redisClient;

    /**
     * @param Redis $redisClient
     */
    public function __construct(Redis $redisClient)
    {
        $this->redisClient = $redisClient;
    }

    /**
     * @param array $job
     * @return mixed
     */
    public function push(array $job)
    {
        $queueName = $this->getName();
        $this->redisClient->lpush($queueName, $this->encodeJob($job));
    }

    /**
     * Returns next working job either one that is in working queue from earlier failed attempt to process it or a new
     * one from the pending queue
     */
    public function getNextJob()
    {
        // Check if there is still an unprocessed job in the working queue
        $job = $this->redisClient->lrange($this->getWorkingName(),  -1, 1);
        if ($job) {
            return $job[0];
        }

        return $this->moveJobToWorkingQueue();
    }

    /**
     * @param $job
     */
    public function finishJob($job)
    {
        $this->redisClient->rpop($this->getWorkingName());
    }

    /**
     * @return mixed
     */
    public function moveJobToWorkingQueue()
    {
        return $this->redisClient->rpoplpush($this->getName(), $this->getWorkingName());
    }

    protected function encodeJob($job) {
        return serialize($job);
    }

    /**
     * @param string $encodedJob
     * @return mixed
     */
    protected  function decodeJob($encodedJob) {
        return unserialize($encodedJob);
    }

    /**
     * @param string $serializedJob
     * @return bool
     */
    abstract public function executeJob($serializedJob);

    /**
     * @return
     */
    abstract public function getName();

    public function getWorkingName() {
        return $this->getName() . '-working';

    }
}