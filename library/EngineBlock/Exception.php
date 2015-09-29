<?php

class EngineBlock_Exception extends Exception
{
    /**
     * Emergency; system is unstable
     *
     * A "panic" condition usually affecting multiple apps/servers/sites.
     * At this level it would usually notify all tech staff on call.
     *
     * Examples: Can't reach database / critical third party system.
     */
    const CODE_EMERGENCY = 'emergency';

    /**
     * Alert: action must be taken immediately.
     *
     * Should be corrected immediately, therefore notify staff who can fix the problem.
     * An example would be the loss of a primary ISP connection.
     */
    const CODE_ALERT = 'alert';

    /**
     * Critical: critical conditions
     *
     * Should be corrected immediately, but indicates failure in a primary system,
     * an example is a loss of a backup ISP connection.
     *
     * Examples: can't contact external group provider
     */
    const CODE_CRITICAL = 'critical';

    /**
     * Error: error conditions
     *
     * Non-urgent failures, these should be relayed to developers or admins;
     * each item must be resolved within a given time.
     *
     * Examples: configuration failure
     */
    const CODE_ERROR = 'error';

    /**
     * Warning: warning conditions
     *
     * Warning messages, not an error, but indication that an error will occur if action is not taken,
     * e.g. file system 85% full - each item must be resolved within a given time.
     *
     * Examples: misconfiguration of entities
     */
    const CODE_WARNING = 'warning';

    /**
     * Notice: normal but significant condition
     *
     * Events that are unusual but not error conditions - might be summarized in an email to developers or admins
     * to spot potential problems - no immediate action required.
     *
     * Examples: 404s, user or IdP / SP input that is incorrect
     */
    const CODE_NOTICE = 'notice';

    protected $_severity;

    public $sessionId;
    public $userId;
    public $spEntityId;
    public $idpEntityId;
    public $description;

    public function __construct($message, $severity = self::CODE_ERROR, Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->_severity = $severity;
    }

    public function getSeverity()
    {
        return $this->_severity;
    }

    public function setSeverity($severity)
    {
        $this->_severity = $severity;
        return $this;
    }
}
