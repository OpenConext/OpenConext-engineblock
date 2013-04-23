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
    private $redisTimeout = 10;

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
        $this->endTime = time()  + $this->workerTimeout;
        $this->redisClient = $redisClient;
    }

    /**
     * Registers a jobqueue which should be processed, note that queues are processed in order of registration.
     * This means the first registered queue will be processed until it's empty, then the second one etc.
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
     * Checks if there are any items in the queues in order of priority. If all are empty, wait until timeout for
     * something to be added to the queue.
     */
    public function run()
    {
        $function = array(
            $this->redisClient,
            'brpop'
        );
        $params = array_keys($this->queues);
        $params[] = $this->redisTimeout;

        while(!$this->isItTimeToQuit()) {
            $job = call_user_func_array($function, $params);

            if ($job) {
                $queueName = $job[0];
                $priority = $this->queues[$queueName]['priority'];
                $queue = $this->queues[$queueName]['queue'];

                echo "Processing job with priority {$priority} from queue: {$queueName}" . PHP_EOL;
                if ($queue->handleJob($job[1])) {
                    // @todo find out what to do in case job is not handled correctly
                }
            }
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
