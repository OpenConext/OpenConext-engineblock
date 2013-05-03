<?php
class EngineBlock_Job_LoginTracking
{
    /**
     * @var EngineBlock_Tracker
     */
    private $tracker;

    public function setUp()
    {
        $this->tracker = new EngineBlock_Tracker();
    }

    public function perform()
    {
        $login = $this->args['login'];
        try {
            $this->tracker->storeInDatabase($login);
        } catch (Exception $e) {
            throw new Exception('Could not store login in database', $e->getCode(), $e);
        }
    }
}