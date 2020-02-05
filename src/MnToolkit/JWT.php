<?php
declare(strict_types=1);

namespace MnToolkit;

use Lindelius\JWT\StandardJWT;
use Exception;

define("TOKEN_EXPIRY", (60 * 60)); // 1 hour

class JWT
{
    private static $instance = null;

    private $jwtClientId = false;
    private $jwtSecretsJson = false;

    /**
     * JWT singleton accessor.  Returns the single instance of JWT
     */
    public static function getInstance(): JWT
    {
        if (self::$instance == null) {
            self::$instance = new JWT();
        }
        return self::$instance;
    }


    /**
     * Create a JWT encoded token for this client.  Optionally pass it an extra payload
     * array which will be added as claims in the JWT token.
     *
     * @param  array  $extraPayload
     * @return string the encoded JWT token
     */
    public function tokenify(array $extraPayload = []): string
    {
        $jwt = new StandardJWT();
        $jwt->exp = time() + TOKEN_EXPIRY;
        $jwt->client_id = $this->jwtClientId;
        foreach ($extraPayload as $claim => $value) {
            $jwt->{$claim} = $value;
        }
        return $jwt->encode($this->getSecret($this->jwtClientId));
    }

    /**
     * Checks if a given JWT token is valid.  Returns true if valid, false if not.
     *
     * @param  string  $encodedToken
     * @return bool
     */
    public function isValidToken(string $encodedToken): bool
    {
        return $this->decodeToken($encodedToken) !== false;
    }

    /**
     * Decodes a given JWT token and returns the decoded token as a PHP object
     * which can be used to reference the claims within the token.
     *
     * @param  string  $encodedToken
     * @return bool|StandardJWT the decoded token with claims set as PHP member variables
     */
    public function decodeToken(string $encodedToken)
    {
        try {
            return $this->_decodeToken($encodedToken);
        } catch (Exception $e) {
            GlobalLogger::getInstance()->getLogger()->error($e);
            return false;
        }
    }

    /**
     * Private constructor - only called by getInstance
     */
    private function __construct()
    {
        $this->grabEnvironmentVariables();
    }

    private function _decodeToken(string $encodedToken)
    {
        $decodedJwt = StandardJWT::decode($encodedToken);
        $clientSecret = $this->getSecret($decodedJwt->client_id);
        $decodedJwt->verify($clientSecret);
        return $decodedJwt;
    }

    private function grabEnvironmentVariables()
    {
        $this->jwtClientId = getenv('JWT_CLIENT_ID');
        if ($this->jwtClientId === false) {
            throw new Exception('JWT_CLIENT_ID env var not set');
        }
        $this->jwtSecretsJson = getenv('JWT_SECRETS');
        if ($this->jwtSecretsJson === false) {
            throw new Exception('JWT_SECRETS env var not set');
        }
    }

    private function getSecret($clientId)
    {
        $jwtSecrets = json_decode($this->jwtSecretsJson);
        if (is_null($jwtSecrets)) {
            throw new Exception("JWT_SECRETS is invalid or empty");
        }
        foreach ($jwtSecrets as $jwtSecret) {
            if ($jwtSecret->client_id == $clientId) {
                return $jwtSecret->secret;
            }
        }
        throw new Exception("JWT_SECRETS is missing a secret for $clientId");
    }
}
