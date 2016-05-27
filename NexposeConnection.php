<?php
/**
 * Created by PhpStorm.
 * User: wildernot
 * Date: 2015-10-29
 * Time: 09:37
 */

namespace MyNameSpace;

use Entity\Policy;

/**
 * Class NexposeConnection
 */
class NexposeConnection
{
    /**
     * @var NexposeSession;
     */
    private $connection;

    /**
     * @var string
     *
     */
    private $username;

    /**
     * @var string
     *
     */
    private $password;

    /**
     * @var string
     *
     */
    private $ipAddress;

    /**
     * @var string
     *
     */
    private $port;

    /**
     * @var string
     */
    private $sessionId;

    /**
     * NexposeConnection constructor.
     * @param $police Policy
     */
    public function __construct($police)
    {
        $this->username = $police->getUsername();
        $this->password = $police->getPassword();
        $this->ipAddress = $police->getAddressIp();
        $this->port = $police->getPort();

        $this->connection = new NexposeSession($police->getUsername(), $police->getPassword(), $police->getAddressIp());
    }

    public function addPolicy($policyName, $description)
    {
        $this->connection->Login();

        $siteId = $this->connection->SiteSave($this->removeAccents($policyName), $this->removeAccents($description));

        $this->connection->Logout();

        return $siteId;
    }

    public function editScanTargets($siteId, $siteName, $description, $targets )
    {
        $this->connection->Login();

        $this->connection->SiteEditTargets($siteId, $siteName, $description, $targets);

        $this->connection->Logout();

        return $siteId;
    }

    public function getScans($siteId)
    {
        $this->connection->Login();

        $data = $this->connection->SiteScanHistory($siteId);

        $this->connection->Logout();

        return $data;
    }

    public function getTargets($siteId)
    {
        $this->connection->Login();

        $data = $this->connection->SiteDeviceListing($siteId);

        $this->connection->Logout();

        return $data;
    }

    public function ScanStatistics($scanId)
    {
        $this->connection->Login();

        $data = $this->connection->ScanStatistics($scanId);

        $this->connection->Logout();

        return $data;
    }

    public function scanSite($siteId)
    {
        $this->connection->Login();

        $data = $this->connection->scanSite($siteId);

        $this->connection->Logout();

        return $data;

    }

    public function scanAsset($assetId, $siteId)
    {
        $this->connection->Login();

        $data = $this->connection->ScanAsset($assetId, $siteId);

        $this->connection->Logout();

        return $data;

    }

    public function scheduleScan($siteId, $siteName, $description, $targets, $scheduling)
    {
        $this->connection->Login();

        $data = $this->connection->SiteScheduleScan($siteId,$siteName,$description,$targets, $scheduling);

        $this->connection->Logout();

        return $data;
    }

    public function editScheduleScan($siteId, $siteName, $description, $targets,$scheduling)
    {
        $this->connection->Login();

        $data = $this->connection->EditScheduleScan($siteId,$siteName,$description,$targets,$scheduling);

        $this->connection->Logout();

        return $data;

    }

    public function getVulnerabilitiesAsset($assetId)
    {
        $this->connection->Login();

        $data = $this->connection->VulnerabilityAsset($assetId);

        $this->connection->Logout();

        return $data;
    }

    public function getVulnerabilityDetails($vulnerabilityId)
    {
        $this->connection->Login();

        $data = $this->connection->VulnerabilityDetails($vulnerabilityId);

        $this->connection->Logout();

        return $data;
    }

    public function getScanVulnDetails($vulnerabilityId, $scanId)
    {
        $this->connection->Login();

        $data = $this->connection->VulnerabilityDetails($vulnerabilityId);

        $this->connection->Logout();

        return $data;
    }

    public function getScanDetails($scanId)
    {
        $this->connection->Login();

        $data = $this->connection->ScanStatistics($scanId);

        $this->connection->Logout();

        return $data;
    }

    public function getTargetVulnDetails($vulnerabilityId)
    {
        $this->connection->Login();

        $data = $this->connection->VulnerabilityDetails($vulnerabilityId);

        $this->connection->Logout();

        return $data;
    }

    public function getRunningScans()
    {
        $this->connection->Login();

        $data = $this->connection->ScanActivity();

        $this->connection->Logout();

        return $data;
    }

    public function getRunningScansForZone($siteId)
    {
        $this->connection->Login();

        $data = $this->connection->ScanActivitySite($siteId);

        $this->connection->Logout();

        return $data;
    }

    private static function removeAccents($string){
        $string = htmlentities($string, ENT_NOQUOTES, 'utf-8');
        $string = preg_replace('#&([A-za-z])(?:uml|circ|tilde|acute|grave|cedil|ring);#', '\1', $string);
        $string = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $string);
        $string = preg_replace('#&[^;]+;#', '', $string);
        return $string;
    }

    public function ScanHistory($siteId)
    {
        $this->connection->Login();

        $data = $this->connection->SiteScanHistory($siteId);

        $this->connection->Logout();

        return $data;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        $this->connection->Login();

        $this->setSessionId($this->connection->getSessionId());

        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

}