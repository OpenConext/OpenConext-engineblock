<?php

interface ServiceRegistry_Cron_Job_Interface
{
    public function runForCronTag($cronTag);
}