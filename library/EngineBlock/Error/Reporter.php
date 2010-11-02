<?php
 
class EngineBlock_Error_Reporter 
{
    protected $_reports;

    /**
     * Create a new reporter
     *
     * @param Zend_Config $reports
     * @return void
     */
    public function __construct(Zend_Config $reports)
    {
        $this->_reports = $reports;
    }

    public function report(Exception $exception)
    {
        foreach ($this->_reports as $reportType => $reportConfiguration) {
            $className = 'EngineBlock_Error_Report_' . ucfirst($reportType);
            $reporter = new $className($reportConfiguration);
            $reporter->report($exception);
        }
    }
}
