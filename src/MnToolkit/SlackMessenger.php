<?php

declare(strict_types=1);

namespace MnToolkit;

use Closure;

class SlackMessenger
{
    /**
     *
     *
     */
    public static function sendMessage($channel, $message)
    {
        if(env('SLACK_WEBHOOK_URL')){

           $hook_array = json_decode(env('SLACK_WEBHOOK_URLS'));

           $hook = $hook_array->{$channel};

            $ch = curl_init($hook);

            $payload = json_encode(array('text' => $message));

            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);

            curl_close($ch);

            return response()->json(['success' => true]);

        }

        return response()->json(['success' => false, 'error' => 'Sending Slack Message Failed Due To Env values']);

    }

}
