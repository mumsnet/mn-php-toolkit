<?php
declare(strict_types=1);

namespace MnToolkit;

use Exception;
use Gelf\Message;
use Gelf\Publisher;
use Gelf\Transport\UdpTransport;

class SiteAction extends MnToolkitBase
{
    private static $instance = null;

    private $envVarsProvided = false;
    private $cloudwatchRootNamespace;
    private $gelfUdpHost;
    private $gelfUdpPort;
    private $siteHostname;
    private $srvCode;
    private $transport;
    private $publisher;

    /**
     * SiteAction singleton accessor.  Returns the single instance of SiteAction
     */
    public static function getInstance(): SiteAction
    {
        if (self::$instance == null) {
            self::$instance = new SiteAction();
        }

        return self::$instance;
    }

    public function log(string $message, string $siteActionGroup, string $siteAction, array $extraPayload = [])
    {
        // thoroughly check parameters
        if (empty($message)) {
            throw new Exception('message cannot be empty');
        }
        if (empty($siteActionGroup)) {
            throw new Exception('siteActionGroup cannot be empty');
        }
        if (empty($siteAction)) {
            throw new Exception('siteAction cannot be empty');
        }
        foreach ($extraPayload as $key => $value) {
            if (!is_string($key)) {
                throw new Exception('payload key must be a string');
            }
            if (empty($key)) {
                throw new Exception('payload key cannot be empty');
            }
            if ($this->startsWith($key, '_')) {
                throw new Exception("payload key {$key} cannot start with underscore _");
            }
            if (strlen($key) < 2) {
                throw new Exception("payload key {$key} must be at least 2 characters long");
            }
            if ($key == 'id') {
                throw new Exception('payload key cannot be id');
            }
            if (!is_string($value)) {
                throw new Exception("payload value for key {$key} must be a string");
            }
        }

        // send to graylog
        $fullPayload = $extraPayload;
        $fullPayload['srv_code'] = $this->srvCode;
        $fullPayload['site_hostname'] = $this->siteHostname;
        $fullPayload['site_action_group'] = $siteActionGroup;
        $fullPayload['site_action'] = $siteAction;
        if ($this->envVarsProvided) {
            $gelfMessage = new Message();
            $gelfMessage->setShortMessage($message);
            $gelfMessage->setFacility('gelf-php');
            foreach ($fullPayload as $key => $value) {
                $gelfMessage->setAdditional($key, $value);
            }
            $this->publisher->publish($gelfMessage);
        } else {
            GlobalLogger::getInstance()->getLogger()->debug("This would have been sent to Graylog: {$message}, {$fullPayload}");
        }
    }

    /**
     * Private constructor - only called by getInstance
     */
    private function __construct()
    {
        $this->grabEnvironmentVariables();
    }

    private function grabEnvironmentVariables()
    {
        if (getenv('CLOUDWATCH_ROOT_NAMESPACE') ||
            getenv('GRAYLOG_GELF_UDP_HOST') ||
            getenv('GRAYLOG_GELF_UDP_PORT')) {
            if (($this->cloudwatchRootNamespace = getenv('CLOUDWATCH_ROOT_NAMESPACE')) === false) {
                throw new Exception('Environment variable CLOUDWATCH_ROOT_NAMESPACE is required');
            }
            if (($this->gelfUdpHost = getenv('GRAYLOG_GELF_UDP_HOST')) === false) {
                throw new Exception('Environment variable GRAYLOG_GELF_UDP_HOST is required');
            }
            if (($this->gelfUdpPort = getenv('GRAYLOG_GELF_UDP_PORT')) === false) {
                throw new Exception('Environment variable GRAYLOG_GELF_UDP_PORT is required');
            }
            if (($this->siteHostname = getenv('SITE_HOSTNAME')) === false) {
                throw new Exception('Environment variable SITE_HOSTNAME is required');
            }
            if (($this->srvCode = getenv('SRV_CODE')) === false) {
                throw new Exception('Environment variable SRV_CODE is required');
            }
            $this->transport = new UdpTransport($this->gelfUdpHost, $this->gelfUdpPort);
            $this->publisher = new Publisher();
            $this->publisher->addTransport($this->transport);
            $this->envVarsProvided = true;
        }
    }
}
