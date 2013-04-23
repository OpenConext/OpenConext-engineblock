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
    abstract public function handleJob($serializedJob);

    /**
     * @return
     */
    abstract public function getName();
}