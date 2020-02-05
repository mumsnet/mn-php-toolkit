<?php
declare(strict_types=1);

namespace MnToolkit;

use GuzzleHttp\Exception\RequestException;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Helper\Psr16Adapter;

class MnToolkitBase
{
    static $cache = null;
    public static function checkCache()
    {
        if(isset(self::$cache))
        {
            return self::$cache;
        };
        CacheManager::setDefaultConfig(new ConfigurationOption([
            'path' => '/tmp',
        ]));
        $cache = new Psr16Adapter('Files');
        self::$cache = $cache;
        return $cache;
    }

    protected function getJwtToken($extraPayload = []): string
    {
        return JWT::getInstance()->tokenify($extraPayload);
    }

    protected function cachedHttpGet(
        string $url,
        int $secondsToExpiry,
        array $headers = [],
        bool $includeJwt = true,
        int $timeout = 2
    ): ?string {
        try {
            if ($includeJwt) {
                $headers['Authorization'] = "Bearer {$this->getJwtToken()}";
            }
            $key = md5($url);
            return FileCache::getInstance()->fetch($key, $secondsToExpiry, function () use ($url, $headers, $timeout) {
                $client = WiredHttpClient::getInstance()->newClient(['timeout' => 2]);
                $response = $client->get($url, [
                    'http_errors' => false,
                    'headers' => $headers,
                    'timeout' => $timeout
                ]);
                if ($response->getStatusCode() == 200) {
                    return $response->getBody()->getContents();
                } else {
                    if ($response->getStatusCode() != 404) { // we don't want to log 404s - too many of them
                        GlobalLogger::getInstance()->getLogger()->error("Failed to call GET {$url}.  Status code: {$response->getStatusCode()}");
                    }
                    return null;
                }
            });
        } catch (RequestException $e) {
            GlobalLogger::getInstance()->getLogger()->error("Failed to call GET {$url}");
            if ($e->hasResponse()) {
                GlobalLogger::getInstance()->getLogger()->error(Psr7\str($e->getResponse()));
            }
        }
        return null;
    }

    protected function startsWith(string $haystack, string $needle): bool
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    protected function endsWith(string $haystack, string $needle): bool
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }
}
