<?php
declare(strict_types=1);

namespace MnToolkit;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class GlobalLogger
{
    private static $instance = null;

    private $logger = null;

    /**
     * GlobalLogger singleton accessor.  Returns the single instance of GlobalLogger
     *
     * @param  LoggerInterface|null  $logger  a Psr compliant logger object if available.  Otherwise it will log to stdout
     */
    public static function getInstance(LoggerInterface $logger = null): GlobalLogger
    {
        if (self::$instance == null) {
            self::$instance = new GlobalLogger($logger);
        }

        return self::$instance;
    }

    /**
     * @return LoggerInterface the Psr compliant logger that can be used by other modules of this package
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Private contructor - only called by getInstance
     *
     * @param  LoggerInterface|null  $logger
     */
    private function __construct(LoggerInterface $logger = null)
    {
        if (is_null($logger)) {
            $logger = new Logger(get_class($this));
            $logger->pushHandler(new ErrorLogHandler());
        }
        $this->logger = $logger;
    }
}
