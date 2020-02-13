# mn-toolkit
This repo houses the source for our composer package `mn-toolkit`.  This package contains the following features:
* Feature toggles - Used to toggle features on and off
* Local file cache - return the cached value associated with $key if available,
  or load it using $loadFunction and add it to the cache.
* Ganesha File Store Adapter -   PHP implementation of Circuit Breaker pattern
* GlobalLogger - Logger class for this package 
* Globals frontend - Loads in header and footer for the globals
* JWT - Used to add and check JWT tokens for securing cross microservice requests
* Transactional emails - Sends transactional Emails
* Source IP detection - Might not need this one
* Graylog (site actions) - Logger for behavior logging
* Correlation ID (set origin request id) - Might not need this one
* User sessions (SSO) for lambda - sets user session for log in through lambda
* User sessions (SSO) for laravel - sets user session for log in through laravel
* Slack Messenger - Accepts a channel and a message and sends the message to the channel
## Install
Via Composer
``` bash
$ composer require mumsnet/mn-toolkit
```