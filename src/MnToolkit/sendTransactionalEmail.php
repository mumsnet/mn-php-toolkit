<?php

declare(strict_types=1);

namespace MnToolkit;

use Aws\Sqs\SqsClient;

class SendTransactionalEmail
{
    private $logger;
    
    public function __construct($logger)
    {
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
     */
    public function sendTransactionalEmail(
        $message_type, 
        $to_address, 
        $subject, 
        $fallback_text, 
        $template_fields=[], 
        $cc_addresses = '' , 
        $request_id
    ){
            //validations
            if(empty($message_type)){
                throw new Exception('message_type cannot be blank');
            }
            if(empty($to_address)){
                throw new Exception('to_address cannot be blank');
            }
            if(empty($subject)){
                throw new Exception('subject cannot be blank');
            }
            if(empty($fallback_text)){
                throw new Exception('fallback_text cannot be blank');
            }
            if(filter_var($to_address, FILTER_VALIDATE_EMAIL) == false){
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

            //Put it on the SQS Queue
            if(env('SQS_MAIL2_QUEUE_URL')){

                $client = new SqsClient([
                    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
                    'version' => '2012-11-05'
                ]);

                $params = [
                    'MessageBody' => json_encode($message_body),
                    'QueueUrl' => env('SQS_MAIL2_QUEUE_URL')
                ];

                $client->sendMessage($params);

            }else{

                $this->logger->info("Payload for SQS: ". $message_body);

            }

    }

}
