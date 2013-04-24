<?php
/**
 * Improve support for priorities
 */
class EngineBlock_Job_Worker
{
    /**
     * Run 1 hour
     *
     * @var int
     * @todo make this configurable?
     */
    private $workerTimeout = 3600;

    /**
     * @var int
     * @todo make this configurable?
     */
    private $iterationTimeout = 10;

    /**
     * @var int
     */
    private $endTime;

    /**
     * @var Redis
     */
    private $redisClient;

    /**
     * @var array
     */
    private $queues;

    public function __construct(Redis $redisClient)
    {
        $this->endTime = time() + $this->workerTimeout;
        $this->redisClient = $redisClient;
    }

    /**
     * Registers a jobqueue which should be executeed, note that queues are executeed in order of registration.
     * This means the first registered queue will be executeed until it's empty, then the second one etc.
     *
     * @param EngineBlock_Job_Queue_QueueAbstract $queue
     */
    public function registerQueue(EngineBlock_Job_Queue_QueueAbstract $queue)
    {
        $this->queues[$queue->getName()] = array(
            'priority' => count($this->queues) + 1,
            'queue' => $queue
        );
    }

    /**
     * Executes jobs from queues, if all are empty, wait until timeout
     */
    public function run()
    {
        while(!$this->isItTimeToQuit()) {
            if ($this->executeJobs()) {
                continue;
            }
            sleep($this->iterationTimeout);
        }
    }

    /**
     * Checks if there are any items in the queues in order of priority and tries to execute them
     *
     * @return bool
     */
    private function executeJobs()
    {
        /** @var $queue EngineBlock_Job_Queue_QueueAbstract */
        foreach($this->queues as $queue) {
            $job = $queue['queue']->getNextJob();

            if ($job) {
                $this->executeJob($queue['queue'], $job);
                return true;
            }
        }
    }

    /**
     * @param EngineBlock_Job_Queue_QueueAbstract $queue
     * @param string $job
     */
    private function executeJob(EngineBlock_Job_Queue_QueueAbstract $queue, $job)
    {
        $jobDetails = $job;
        $queueName = $queue->getName();
        $priority = $this->queues[$queueName]['priority'];

        echo "Executeing job with priority {$priority} from queue: {$queueName}" . PHP_EOL;
        if ($queue->executeJob($jobDetails)) {
            $queue->finishJob($jobDetails);
        }
    }

    /**
     * Returns false if the script has been running for more than allowed time.
     *
     * @return bool
     */
    private function isItTimeToQuit()
    {
        return time() > $this->endTime;
    }
}