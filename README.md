# mn-toolkit
This repo houses the source for our composer package `mn-toolkit`.  This package contains the following features:
* Feature toggles - Used to toggle features on and off
    
* Local file cache - return the cached value associated with $key if available,
  or load it using $loadFunction and add it to the cache.
  
* Ganesha File Store Adapter -   PHP implementation of Circuit Breaker pattern<br>
More info at: https://github.com/ackintosh/ganesha

* GlobalLogger - Logger class for this package <br>
    Globbal logger is used to set an instance of a global logger and then retrieve it when needed so we can log errors to it. This sends the errors to your logs on your local and to papertrail on stage and production.
    We mainly use it for system errors and you can use it like so: `log::error('Token does not exist');`

* Globals frontend - Loads in global assets - [documentation](https://github.com/mumsnet/globals_service/blob/staging/README.md)

* JWT - Used to add and check JWT tokens for securing cross microservice requests
<br> To set a JWT token, generate it on the JWT website using the ENV values. There is a JWT quide in the docs.
When the token has been generated, you add it to the Authorization header of all the requests that you want to secure.
To check if a request has a token you use:  
`$authorization = $request->header('Authorization');`<br>
`$withoutBearer = str_replace('bearer ', '', $authorization);`<br>
 `if($authorization && $withoutBearer != ''){`<br>
`$isValid = JWT::getInstance()->isValidToken($withoutBearer);`<br>
`if($isValid){`<br>
`return $next($request);`<br>
`}`<br>
`}`<br>

* Transactional emails - Sends transactional Emails<br>
To use this function you need to pass it an instance of your logger so that the package can log thngs in case they go wrong during sending. You use it like so: <br>
`$logger = Log::getLogger();`<br>
        `$mn_transctional_email = new SendTransactionalEmail($logger);`<br>
         `$mn_transctional_email->sendTransactionalEmail($template, $email, 'Mumnset notification', 'Hello panos', '127.0.0.1', ['body' => $body]);`<br>
     The sendTransactional Mail function requires the following parameters:<br>
     `        $message_type,`<br>
             ` $to_address,`<br>
              `$subject,`<br>
              `$fallback_text,`<br>
              `$request_id,`<br>
              `$template_fields = [],`<br>
              `$cc_addresses = ''` <br>   

* Source IP detection - Might not need this one

* SiteAction (Graylog) - Logger for behavior logging<br>
This logger is used less for errors and more for user behavior logging like: user tried to update his details without being logged in.<br>
In these logs you can pass quite a lot of info, like so:<br>
`$this->siteaction = SiteAction::getInstance();`<br>
`$this->siteaction->log($user_id, 'Failed to created pregnancy record', 'profile', 'profile_update', ['errors' => json_encode($e->getMessage())]);
`

* Correlation ID (set origin request id) - Might not need this one

* User sessions (SSO) for lambda - sets user session for log in through lambda

* User sessions (SSO) for laravel - sets user session for log in through laravel
<br> You can set a session like so:<br> `$session = new UserSessionsLaravel();`<br>
                                    ` $session->setUserSession(['user_id' => $userEmailCheck->id], true);`
<br>You can get a user id from the session like so:<br>
`        $session = new UserSessionsLaravel($_COOKIE);`<br>
         `$userId = $session->getUserIdFromSession();`
* Slack Messenger - Accepts a channel and a message and sends the message to the channel<br>
This is quite simple to use. Just call the function and give it a channel name and a message. Make sure you have the channels credentials in your ENV.
`SlackMessenger::sendMessage('channel','message')`
## Install
Via Composer
``` bash
$ composer require mumsnet/mn-toolkit
```
