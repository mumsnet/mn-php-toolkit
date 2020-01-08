<?php

declare(strict_types=1);

namespace MnToolkit;

use Aws\Sqs\SqsClient;
use Exception;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class SendTransactionalEmail
{
    private $logger;

    public function __construct()
    {
        $logger = GlobalLogger::getInstance()->getLogger();

        if (is_null($logger)) {
            $logger = new Logger(get_class($this));
            $logger->pushHandler(new ErrorLogHandler());
        }

        $this->logger = $logger;
    }

    /**
     * Send Transactional Email
     *
     * @param $message_type
     * @param $to_address
     * @param $subject
     * @param $fallback_text
     * @param $template_fields
     * @param $cc_addresses
     * @param $request_id
     * @return
     * @throws Exception
     */
    public function sendTransactionalEmail(
        $message_type,
        $to_address,
        $subject,
        $fallback_text,
        $request_id,
        $template_fields = [],
        $cc_addresses = ''
    ) {
        //validations
        if (empty($message_type)) {
            throw new Exception('message_type cannot be blank');
        }
        if (empty($to_address)) {
            throw new Exception('to_address cannot be blank');
        }
        if (empty($subject)) {
            throw new Exception('subject cannot be blank');
        }
        if (empty($fallback_text)) {
            throw new Exception('fallback_text cannot be blank');
        }
        if (filter_var($to_address, FILTER_VALIDATE_EMAIL) == false) {
            throw new Exception('to_address: $to_address is not a valid email address');
        }


        //Set up Message body
        $message_body = [
            'message_schema_version' => '1',
            'message_type' => $message_type,
            'template_fields' => json_encode($template_fields),
            'to_address' => $to_address,
            'cc_addresses' => $cc_addresses,
            'subject' => $subject,
            'fallback_text' => $fallback_text,
            'request_id' => $request_id
        ];

        if (!getenv('SQS_MAIL2_QUEUE_URL')) {
            $this->logger->error("SQS Mail Queue Url not present: " . json_encode($message_body));
            throw new Exception('SQS Mail Queue Url not present');
        }

        $client = new SqsClient([
            'region' => getenv('AWS_DEFAULT_REGION'),
            'version' => '2012-11-05'
        ]);

        $params = [
            'MessageBody' => json_encode($message_body),
            'QueueUrl' => getenv('SQS_MAIL2_QUEUE_URL')
        ];

        $sent = $client->sendMessage($params);

        return $sent;
    }

}
