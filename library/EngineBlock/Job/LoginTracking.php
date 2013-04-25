<?php
class EngineBlock_Job_Job_LoginTracking
    implements EngineBlock_Job_Job_JobInterface
{
    /**
     * @var EngineBlock_Tracker
     */
    private $tracker;

    public function setUp()
    {
        echo 'setup';
        $this->tracker = new EngineBlock_Tracker();
    }

    public function perform()
    {
        $login = $this->args['login'];
        if (!$this->tracker->storeInDatabase($login));
        {
            throw new Exception('Could not stored tracked login in database');
        }
    }
}