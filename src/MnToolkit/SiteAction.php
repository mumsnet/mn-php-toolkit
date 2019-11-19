<?php
declare(strict_types=1);

namespace MnToolkit;

use Aws\CloudWatch;
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
    private $awsRegion;
    private $cloudwatchClient;

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

    public function log(
        string $who,
        string $message,
        string $siteActionGroup = 'test',
        string $siteAction = 'unknown',
        array $extraPayload = []
    ) {
        // thoroughly check parameters
        if (empty($who)) {
            throw new Exception('who cannot be empty');
        }
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
        if (is_numeric($who)) {
            $fullPayload['user_id'] = $who;
        } elseif (strpos($who, '@') !== false) {
            $fullPayload['email'] = $who;
        } else {
            $fullPayload['username'] = $who;
        }
        if ($this->envVarsProvided) {
            $gelfMessage = new Message();
            $gelfMessage->setShortMessage($message);
            $gelfMessage->setFacility('gelf-php');
            foreach ($fullPayload as $key => $value) {
                $gelfMessage->setAdditional($key, $value);
            }
            $this->publisher->publish($gelfMessage);
        } else {
            $dump = print_r($fullPayload, true);
            GlobalLogger::getInstance()->getLogger()->debug("This would have been sent to Graylog: {$message}, {$dump}");
        }

        // send to cloudwatch
        $rootNamespace = ($this->envVarsProvided) ? $this->cloudwatchRootNamespace : 'mn';
        $siteHostname = ($this->envVarsProvided) ? $this->siteHostname : 'localhost';
        $cloudwatchData = array(
            'Namespace' => "{$rootNamespace}/{$siteActionGroup}",
            'MetricData' => array(
                array(
                    'MetricName' => $siteAction,
                    'Dimensions' => array(
                        array(
                            'Name' => 'site_hostname',
                            'Value' => $siteHostname
                        )
                    ),
                    'Value' => 1,
                    'Unit' => 'Count'
                )
            )
        );
        if ($this->envVarsProvided) {
            try {
                $this->cloudwatchClient->putMetricData($cloudwatchData);
            } catch (Exception $e) {
                GlobalLogger::getInstance()->getLogger()->error($e->getMessage());
            }
        } else {
            $dump = print_r($cloudwatchData, true);
            GlobalLogger::getInstance()->getLogger()->debug("This would have been sent to Cloudwatch: {$dump}");
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
            if (($this->awsRegion = getenv('SRV_AWS_REGION')) === false) {
                throw new Exception('Environment variable SRV_AWS_REGION is required');
            }

            // set up gelf publisher
            $this->transport = new UdpTransport($this->gelfUdpHost, $this->gelfUdpPort);
            $this->publisher = new Publisher();
            $this->publisher->addTransport($this->transport);

            // set up aws sdk
            $this->cloudwatchClient = new CloudWatch\CloudWatchClient([
                'region' => $this->awsRegion,
                'version' => '2010-08-01'
            ]);

            // record the fact that all env vars have been provided
            // which means we should be able to send to graylog and cloudwatch for real
            $this->envVarsProvided = true;
        }
    }
}
