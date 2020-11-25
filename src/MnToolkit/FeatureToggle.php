<?php
declare(strict_types=1);

namespace MnToolkit;

use Exception;

class FeatureToggle extends MnToolkitBase
{
    private static $instance = null;

    private $srvFtUrl = false;

    /**
     * FeatureToggle singleton accessor.  Returns the single instance of FeatureToggle
     */
    public static function getInstance(): FeatureToggle
    {
        if (self::$instance == null) {
            self::$instance = new FeatureToggle();
        }
        return self::$instance;
    }

    /**
     * Check if named feature toggle is on or off.
     * @param  string  $toggleName  the feature toggle name eg: GLO-01
     * @param  string  #requestUri  the part of this request's URL from the protocol name up to the query string eg: /some/path
     * @return bool true if named toggle is on, false if off
     */
    public function isOn(string $toggleName, $userId = null , string $requestUri = null): bool
    {
        $toggles = $this->getToggles();

        if (is_null($toggles)) {
            return false;
        }

        foreach ($toggles->data as $toggle) {
            if ($toggle->attributes->name == $toggleName) {

                if($toggle->attributes->status =='off'){
                    return false;
                }

                $cookie = $_COOKIE['mnft'];
                if(!isset($cookie)){
                    return false;
                }

                if($toggle->attributes->status == 'publicbeta'){
                    if(isset($_COOKIE['mnbeta'])){
                        return true;
                    }
                }

                if($toggle->attributes->status == 'privatebeta'){
                    $betaUsers = explode(',', $toggle->attributes->beta_users);
                    foreach ($betaUsers as $betaUser){
                        if(trim($betaUser)==$userId){
                            return true;
                        }
                    }
                }

                $this->updateFeatureToggles($toggles->data);

                $decodedCookie = base64_decode($cookie);
                $clientPercentage = $this->calcClientPercentage($decodedCookie,$toggleName);
                $togglePercentage = $toggle->attributes->percentage;

                return
                    $clientPercentage <= $togglePercentage &&
                    $this->uriIsWhitelisted($toggle, $requestUri);
            }
        }
        return false;
    }

    /**
     * Private constructor - only called by getInstance
     */
    private function __construct()
    {
        $this->grabEnvironmentVariables();
    }

    private function updateFeatureToggles($toggles){
        $cookie = $_COOKIE['mnft'];
        $decodedCookie = base64_decode($cookie);
        $newCookie = [];
        foreach ($toggles as $toggle){
            $clientPercentage = $this->calcClientPercentage($decodedCookie,$toggle->attributes->name);
            array_push($newCookie,$decodedCookie,$toggle->attributes->name.':'.$clientPercentage);
        }
        $comma_separated = implode(",", $newCookie);
        setcookie('mnft', $comma_separated,time()+86400*30*12,'/');
    }

    private function calcClientPercentage($decodedCookie,$toggleName){

        $clientPercentage = rand(1,100);

        if (strpos($decodedCookie, $toggleName) !== false) {
            $oldToggles = explode(',', $decodedCookie);
            foreach ($oldToggles as $oldToggle){
                if (strpos($oldToggle, $toggleName) !== false) {
                    $explodedToggle = explode(':', $oldToggle);
                    $clientPercentage = $explodedToggle[1];
                    return $clientPercentage;
                }
            }
        }

        return $clientPercentage;
    }

    private function uriIsWhitelisted($toggle, ?string $requestUri) : bool
    {
        return $this->uriIsListed($toggle->attributes->{'whitelist-urls'}, $requestUri, true);
    }

    private function uriIsListed(?array $uriList, ?string $requestUri, bool $valueIfNoList) : bool
    {
        if (is_null($requestUri) || is_null($uriList) || count($uriList) == 0) {
            return $valueIfNoList;
        }

        foreach ($uriList as $uri) {
            if ($this->endsWith($uri, '*')) {
                $uri = substr($uri, 0, -1); // remove the '*'
                if ($this->startsWith($requestUri, $uri)) {
                    return true;
                }
            } elseif ($uri == $requestUri) {
                return true;
            }
        }

        return false;
    }

    private function getToggles()
    {
        $json = $this->cachedHttpGet("{$this->srvFtUrl}/api/v1/feature-toggles", 60);
        if (is_null($json)) {
            return null;
        }
        return json_decode($json);
    }

    private function grabEnvironmentVariables(): void
    {
        $this->srvFtUrl = getenv('SRV_FT_URL');
        if ($this->srvFtUrl === false) {
            throw new Exception('SRV_FT_URL env var not set');
        }
    }


}
