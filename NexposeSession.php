<?php

namespace MyNameSpace;

use SimpleXMLElement;

/**
 * Created by PhpStorm.
 * User: wildernot
 * Date: 2016-02-26
 * Time: 09:50
 */
class NexposeSession
{
    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var int
     */
    private $engineId;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string;
     */
    private $host;

    /**
     * NexposeSession constructor.
     * @param $username
     * @param $password
     * @param $host
     */
    public function __construct($username, $password, $host)
    {
        $this->password = $password;
        $this->username = $username;
        $this->host = $host;
    }

    /**
     * Login session NeXpose
     *
     */
    public function Login()
    {
        $url = "https://".$this->host.":3780/api/1.1/xml";

        $post_string = '<?xml version="1.0" encoding="UTF-8"?><LoginRequest password='.'"'.$this->password.'"'.' user-id='.'"'.$this->username.'"'.'/>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

        $data = curl_exec($ch);

        if($data){
            $xml = new SimpleXMLElement($data);
            $this->setSessionId($xml["session-id"]);
        } else {
            $this->setSessionId(null);
        }

    }

    /**
     * Logout session NeXpose
     *
     */
    public function Logout()
    {
        $post_string = '<?xml version="1.0" encoding="UTF-8"?><LogoutRequest session-id='.'"'.$this->getSessionId().'"'.'/>';

        $url = "https://".$this->host.":3780/api/1.1/xml";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

        $data = curl_exec($ch);

        if($data){
            $xml = new SimpleXMLElement($data);
            return $xml["success"];
        } else {
            return null;
        }
    }

    /**
     * Provide a list of all sites the user is authorized to view or manage.
     * @return array
     */
    public function SiteList()
    {
        $post_string = '<?xml version="1.0" encoding="UTF-8"?><SiteListingRequest session-id='.'"'.$this->getSessionId().'"'.'></SiteListingRequest>';

        $url = "https://".$this->host.":3780/api/1.1/xml";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

        $data = curl_exec($ch);
        $list = array();
        $xml = new SimpleXMLElement($data);

        foreach($xml->SiteSummary as $site){
            $list[] = array('id'=> $site["id"],'name'=>$site["name"], 'description'=>$site['description'],
                'riskFactor'=>$site["riskfactor"],'riskscore'=>$site["riskscore"] );
        }

        return $list;
    }

    /**
     * Provide the configuration of the site, including its associated assets.
     *
     * @param $host
     * @param $siteId
     * @return mixed
     */
    public function SiteConfig($host, $siteId)
    {
        $post_string = '<?xml version="1.0" encoding="UTF-8"?><SiteConfigRequest session-id='.'"'.$this->getSessionId().'"'.' site-id='.'"'.$siteId.'"'.' ></SiteListingRequest>';

        $data = $this->call($host, $post_string);

        return $data;
    }

    /**
     * Provides a list of available scan engines and information about them.
     *
     * @param $host
     * @return mixed
     */
    public function EngineList($host)
    {
        $post_string = '<?xml version="1.0" encoding="utf-8"?><EngineListingRequest session-id='.'"'.$this->getSessionId().'"'.'/>';

        $data = $this->call($host, $post_string);

        return $data;
    }

    /**
     * Save new site.
     *
     * @param $name
     * @param $description
     * @return mixed
     */
    public function SiteSave($name, $description)
    {
        $url = "https://".$this->host.":3780/api/1.1/xml";
        $list = '<host></host>';

        $post_string = '<?xml version="1.0" encoding="utf-8"?>
        <SiteSaveRequest session-id='.'"'.$this->sessionId.'"'.'>
            <Site id="-1" name='.'"'.$name.'" '.'description='.'"'.$description.'" '.'riskfactor="1.0" isDynamic="0">
                <Hosts>'.$list.'</Hosts>
                <Credentials></Credentials>
                <Alerting></Alerting>
                <ScanConfig configID="4" name="Full audit without Web Spider" templateID="full-audit-without-web-spider" engineID="3" configVersion="3">
                    <Schedules></Schedules>
                    <ScanTriggers></ScanTriggers>
                </ScanConfig>
            </Site>
        </SiteSaveRequest>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

        $data = curl_exec($ch);

        if($data){
            $xml = new SimpleXMLElement($data);
            if($xml["success"] == 1){
                return $xml["site-id"];
            } else{
                return 0;
            }
        } else {
            return null;
        }
    }

    /**
     * Save changes to a existing site.
     *
     * @param $siteId
     * @param $name
     * @param $description
     * @param $targets
     * @return mixed
     */
    public function SiteEditSave($siteId, $name, $description, $targets)
    {
        $url = "https://".$this->host.":3780/api/1.1/xml";

        $list = '';
        foreach($targets as $h){
            $list = $list.'<host>'.''.$h.''.'</host>';
        }

        $schedule = $this->SchedulingSite($siteId);

        if(is_array($schedule) && count($schedule) > 0){
            $s = '';
            foreach($schedule as $item){
                $s.= '<Schedule enabled="1" incremental="0" type='.'"'.$item["type"].'" '.' interval='.'"'.$item["interval"].'" '.' start='.'"'.$item["start"].'" '.' maxDuration="120" repeaterType="restart"/>';
            }
        } else {
            $s = '';
        }

        $post_string = '<?xml version="1.0" encoding="utf-8"?>
        <SiteSaveRequest session-id='.'"'.$this->sessionId.'"'.'>
            <Site id='.'"'.$siteId.'"'.' name='.'"'.$name.'" '.'description='.'"'.$description.'" '.'riskfactor="1.0" isDynamic="0">
                <Hosts>'.$list.'</Hosts>
                <Credentials></Credentials>
                <Alerting></Alerting>
                <ScanConfig configID="4" name="Full audit without Web Spider" templateID="full-audit-without-web-spider" engineID="3" configVersion="3">
                    <Schedules>'.'"'.$s.'" '.'</Schedules>
                    <ScanTriggers></ScanTriggers>
                </ScanConfig>
            </Site>
        </SiteSaveRequest>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

        $data = curl_exec($ch);

        if($data){
            $xml = new SimpleXMLElement($data);
            if($xml["success"] == 1){
                return $xml["site-id"];
            } else{
                return 0;
            }
        } else {
            return null;
        }
    }

    /**
     * Save changes to a existing site.
     *
     * @param $siteId
     * @param $name
     * @param $description
     * @param $targets array
     * @return mixed
     */
    public function SiteEditTargets($siteId, $name, $description, $targets)
    {
        $url = "https://".$this->host.":3780/api/1.1/xml";

        $list = '';
        foreach($targets as $h){
            $list = $list.'<host>'.''.$h.''.'</host>';
        }

        $schedule = $this->SchedulingSite($siteId);

        if(is_array($schedule) && count($schedule) > 0){
            $s = '';
            foreach($schedule as $item){
                $s.= '<Schedule enabled="1" incremental="0" type='.'"'.$item["type"].'" '.' interval='.'"'.$item["interval"].'" '.' start='.'"'.$item["start"].'" '.' maxDuration="120" repeaterType="restart"/>';
            }
        } else {
            $s = '';
        }

        $list = '';
        foreach($targets as $h){
            $list = $list.'<host>'.''.$h.''.'</host>';
        }

        $post_string = '<?xml version="1.0" encoding="utf-8"?>
        <SiteSaveRequest session-id='.'"'.$this->sessionId.'"'.'>
            <Site id='.'"'.$siteId.'"'.' name='.'"'.$name.'" '.'description='.'"'.$description.'" '.'riskfactor="1.0" isDynamic="0">
                <Hosts>'.$list.'</Hosts>
                <Credentials></Credentials>
                <Alerting></Alerting>
                <ScanConfig configID="4" name="Full audit without Web Spider" templateID="full-audit-without-web-spider" engineID="3" configVersion="3">
                    <Schedules>'.'"'.$s.'" '.'</Schedules>
                    <ScanTriggers></ScanTriggers>
                </ScanConfig>
            </Site>
        </SiteSaveRequest>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

        $data = curl_exec($ch);

        if($data){
            $xml = new SimpleXMLElement($data);
            if($xml["success"] == 1){
                return $xml["site-id"];
            } else{
                return 0;
            }
        } else {
            return null;
        }
    }

    /**
     * @param $siteId
     * @param $name
     * @param $description
     * @param $targets
     * @param $scheduling
     * @return int|string|null
     */
    public function SiteScheduleScan($siteId, $name, $description, $targets, $scheduling)
    {
        $url = "https://".$this->host.":3780/api/1.1/xml";

        $start = $scheduling["start"];
        $type = $scheduling["type"];
        $interval = $scheduling["interval"];

        $schedule = $this->SchedulingSite($siteId);

        if(is_array($schedule) && count($schedule) > 0){
            $s = '';
            foreach($schedule as $item){
            $s.= '<Schedule enabled="1" incremental="0" type='.'"'.$item["type"].'" '.' interval='.'"'.$item["interval"].'" '.' start='.'"'.$item["start"].'" '.' maxDuration="120" repeaterType="restart"/>';
            }
        } else {
            $s = '';
        }

        $list = '';
        foreach($targets as $h){
            $list = $list.'<host>'.''.$h.''.'</host>';
        }

        $post_string = '<?xml version="1.0" encoding="utf-8"?>
                <SiteSaveRequest session-id='.'"'.$this->sessionId.'"'.'>
                    <Site id='.'"'.$siteId.'"'.' name='.'"'.$name.'" '.'description='.'"'.$description.'" '.'riskfactor="1.0" isDynamic="0">
                        <Hosts>'.$list.'</Hosts>
                        <Credentials></Credentials>
                        <Alerting></Alerting>
                        <ScanConfig configID="4" name="Full audit without Web Spider" templateID="full-audit-without-web-spider" engineID="3" configVersion="3">
                            <Schedules>'.'"'.$s.'" '.'
                                <Schedule enabled="1" incremental="0" type='.'"'.$type.'" '.' interval='.'"'.$interval.'" '.' start='.'"'.$start.'" '.' maxDuration="120" repeaterType="restart"/>
                            </Schedules>
                            <ScanTriggers></ScanTriggers>
                        </ScanConfig>
                    </Site>
                </SiteSaveRequest>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

        $data = curl_exec($ch);

        if($data){
            $xml = new SimpleXMLElement($data);
            if($xml["success"] == 1){
                return $xml["site-id"];
            } else{
                return $xml->Failure->Exception->message;
            }
        } else {
            return null;
        }
    }

    /**
     * Provide a list of current scheduled activities by site
     *
     * @param $siteId
     * @return array|null
     */
    private function SchedulingSite($siteId)
    {
        $url = "https://".$this->host.":3780/api/1.1/xml";
        $post_string = '<?xml version="1.0" encoding="UTF-8"?><SiteConfigRequest session-id='.'"'.$this->sessionId.'"'.' site-id='.'"'.$siteId.'"'.'></SiteConfigRequest>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

        $data = curl_exec($ch);

        if(curl_errno($ch))
            print curl_error($ch);
        else
            curl_close($ch);

        print $data;

        $xml = new SimpleXMLElement($data);
        if($xml["success"] == 1) {
            $listSchedule = array();
            foreach ($xml->Site->ScanConfig->Schedules->Schedule as $schedule) {
                $listSchedule[] = array(
                    'type' => $schedule["type"],
                    'interval' => $schedule["interval"],
                    'start' => $schedule["start"],
                    'enabled' => $schedule["enabled"]
                );
            }
            return $listSchedule;
        } else {
          return null;
        }
    }

    /**
     *
     * @param $siteId int
     * @param $name string
     * @param $description string
     * @param $targets array
     * @param $scheduling array
     * @return null|string|int
     */
    public function EditScheduleScan($siteId, $name, $description, $targets, $scheduling)
    {
        $url = "https://".$this->host.":3780/api/1.1/xml";

        if(is_array($scheduling) && count($scheduling) > 0){
            $s = '';
            foreach($scheduling as $item){
                $s.= '<Schedule enabled="1" incremental="0" type='.'"'.$item["type"].'" '.' interval='.'"'.$item["interval"].'" '.' start='.'"'.$item["start"].'" '.' maxDuration="120" repeaterType="restart"/>';
            }
        } else {
            $s = '';
        }

        $list = '';
        foreach($targets as $h){
            $list = $list.'<host>'.''.$h.''.'</host>';
        }

        $post_string = '<?xml version="1.0" encoding="utf-8"?>
                <SiteSaveRequest session-id='.'"'.$this->sessionId.'"'.'>
                    <Site id='.'"'.$siteId.'"'.' name='.'"'.$name.'" '.'description='.'"'.$description.'" '.'riskfactor="1.0" isDynamic="0">
                        <Hosts>'.$list.'</Hosts>
                        <Credentials></Credentials>
                        <Alerting></Alerting>
                        <ScanConfig configID="4" name="Full audit without Web Spider" templateID="full-audit-without-web-spider" engineID="3" configVersion="3">
                            <Schedules>'.'"'.$s.'" '.'
                            </Schedules>
                            <ScanTriggers></ScanTriggers>
                        </ScanConfig>
                    </Site>
                </SiteSaveRequest>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

        $data = curl_exec($ch);

        if($data){
            $xml = new SimpleXMLElement($data);
            if($xml["success"] == 1){
                return $xml["site-id"];
            } else{
                return $xml->Failure->Exception->message;
            }
        } else {
            return null;
        }
    }

    /**
     * Scan the specified site.
     *
     * @param $siteId
     * @return null|int
     */
    public function ScanSite($siteId)
    {
        $url = "https://".$this->host.":3780/api/1.1/xml";

        $post_string = '<?xml version="1.0" encoding="utf-8"?>
        <SiteScanRequest session-id='.'"'.$this->sessionId.'"'.' site-id='.'"'.$siteId.'"'.'></SiteScanRequest>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

        $data = curl_exec($ch);

        if(curl_errno($ch))
            return curl_error($ch);
        else
            curl_close($ch);

        if($data){
            $xml = new SimpleXMLElement($data);
            if($xml["success"] == 1){
                return (int)$xml->Scan["scan-id"];
            } else{
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * Scan a specified asset of site assets.
     *
     * @param $assetId
     * @param $siteId
     * @return int|null|string
     */
    public function ScanAsset($assetId, $siteId)
    {
        $url = "https://".$this->host.":3780/api/1.1/xml";

        $post_string = '<?xml version="1.0" encoding="utf-8"?>
            <SiteDevicesScanRequest session-id='.'"'.$this->sessionId.'"'.' site-id='.'"'.$siteId.'"'.'>
                <Devices>
                    <device id='.'"'.$assetId.'"'.'/>
                </Devices>
                <Hosts></Hosts>
            </SiteDevicesScanRequest>';

        date_default_timezone_set('GMT');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

        $data = curl_exec($ch);

        if(curl_errno($ch))
            return curl_error($ch);
        else
            curl_close($ch);

        if($data){
            $xml = new SimpleXMLElement($data);
            if($xml["success"] == 1){
                return (int)$xml->Scan["scan-id"];
            } else{
                return $xml->Failure->Exception->message;
            }
        } else {
            return null;
        }
    }

    /**
     * Provide a list of current scan activities across all Scan Engines managed by the Security Console.
     *
     * @return mixed
     */
    public function ScanActivity()
    {
        $post_string = '<?xml version="1.0" encoding="utf-8"?><ScanActivityRequest session-id='.'"'.$this->getSessionId().'"'.'></ScanActivityRequest>';

        $url = "https://".$this->host.":3780/api/1.1/xml";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

        $data = curl_exec($ch);

        if($data){
            $xml = new SimpleXMLElement($data);
            if($xml["success"] == 1){
                $listScan = array();
                foreach($xml->ScanSummary as $scan){
                    $listScan[] = array('scan'=>$scan["scan-id"],'site'=>$scan["site-id"],'startTime'=>$scan["startTime"] ,'status'=>$scan["status"]);
                }
                return $listScan;
            } else{
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * Provide a list of current scan activities across all Scan Engines managed by the Security Console.
     *
     * @return mixed
     */
    public function ScanActivitySite($siteId)
    {
        $post_string = '<?xml version="1.0" encoding="utf-8"?><ScanActivityRequest session-id='.'"'.$this->getSessionId().'"'.'></ScanActivityRequest>';

        $url = "https://".$this->host.":3780/api/1.1/xml";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

        $data = curl_exec($ch);

        if($data){
            $xml = new SimpleXMLElement($data);
            if($xml["success"] == 1){
                $listScan = array();
                foreach($xml->ScanSummary as $scan){
                    if($scan["site-id"] == $siteId){
                        $listScan[] = array('scan'=>$scan["scan-id"],'site'=>$scan["site-id"],'startTime'=>$scan["startTime"] ,'status'=>$scan["status"]);
                    }
                }
                return $listScan;
            } else{
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * Get scan statistics, including node and vulnerability breakdowns.
     *
     * @param $scanId
     * @return mixed
     */
    public function ScanStatistics($scanId)
    {
        $this->setEngineId(3);

        $url = "https://".$this->host.":3780/api/1.1/xml";

        $post_string = '<?xml version="1.0" encoding="utf-8"?><ScanStatisticsRequest session-id='.'"'.$this->getSessionId().'"'.' engine-id='.'"'.$this->getEngineId().'"'.' scan-id='.'"'.$scanId.'"'.'></ScanStatisticsRequest>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

        $data = curl_exec($ch);

        if($data){
            $xml = new SimpleXMLElement($data);
            if($xml["success"] == 1){
                $scan = array(
                    'id'=>$xml->ScanSummary["scan-id"],
                    'startTime'=>$xml->ScanSummary["startTime"],
                    'endTime'=>$xml->ScanSummary["endTime"],
                    'status'=>$xml->ScanSummary["status"]);
                $vulnerabilities = array();
                foreach($xml->ScanSummary->vulnerabilities as $vulnerability){
                    $vulnerabilities[] = array(
                        'status'=>$vulnerability['status'],
                        'severity'=>$vulnerability['severity'],
                        'count'=> $vulnerability['count']);
                }
                $d = array();
                $d[] = $scan;
                $d[] = $vulnerabilities;

                return $d;
            } else{
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * Delete the specified site and all associated scan data.
     *
     * @param $host
     * @param $siteId
     * @return mixed
     */
    public function SiteDelete($host, $siteId)
    {
        $post_string = '<?xml version="1.0" encoding="utf-8"?><SiteDeleteRequest session-id='.'"'.$this->getSessionId().'"'.' site-id='.'"'.$siteId.'"'.'></SiteDeleteRequest>';

        $data = $this->call($host, $post_string);

        return $data;
    }

    /**
     * Provide a list of all of the assets in a site. If no site-id is specified, then this will return all of the
     * assets for the Scan Engine, grouped by site-id.
     *
     * @param $siteId
     * @return mixed|null
     */
    public function SiteDeviceListing($siteId)
    {
        $url = "https://".$this->host.":3780/api/1.1/xml";

        $post_string = '<?xml version="1.0" encoding="utf-8"?><SiteDeviceListingRequest session-id='.'"'.$this->getSessionId().'"'.' site-id='.'"'.$siteId.'"'.'></SiteDeviceListingRequest>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

        $data = curl_exec($ch);

        if($data){
            $xml = new SimpleXMLElement($data);
            if($xml["success"] == 1){
                $list = array();
                foreach($xml->SiteDevices->device as $device){
                    $list[] = array('id'=> $device["id"],'address'=>$device["address"], 'riskfactor'=>$device["riskfactor"]);
                }
                return $list;
            } else{
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * Provide a list of all previous scans of the site.
     *
     * @param $siteId
     * @return array|string|null
     */
    public function SiteScanHistory($siteId)
    {
        $url = "https://".$this->host.":3780/api/1.1/xml";

        $post_string = '<?xml version="1.0" encoding="utf-8"?><SiteScanHistoryRequest session-id='.'"'.$this->getSessionId().'"'.' site-id='.'"'.$siteId.'"'.'></SiteScanHistoryRequest>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

        $data = curl_exec($ch);

        if($data) {
            $xml = new SimpleXMLElement($data);
            if($xml["success"] == 1) {
                $listScan = array();
                foreach ($xml->ScanSummary as $scan) {
                    $listScan[] = array(
                        'id' => $scan["scan-id"],
                        'name' => $scan["name"],
                        'startTime' => $scan["startTime"],
                        'endTime' => $scan["endTime"],
                        'status' => $scan["status"]
                    );
                }
                return $listScan;
            } else {
                return $xml->Failure->Exception->message;
            }

        } else {
            return null;
        }
    }

    /**
     * Provides a list of vulnerabilities checked. A vulnerability is considered “credentialed”
     * when all of its checks require credentials or if the check depends on previous authentication during a scan.
     *
     * @param $host
     * @return mixed
     */
    public function VulnerabilityListing($host)
    {
        $post_string = '<?xml version="1.0" encoding="UTF-8"?><VulnerabilityListingRequest session-id='.'"'.$this->getSessionId().'"'.' sync-id="1234"/>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL,$host);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

        $data = curl_exec($ch);

        if(curl_errno($ch))
            print curl_error($ch);
        else
            curl_close($ch);

        return $data;
    }

    /**
     * Provides a list of vulnerabilities checked in asset
     *
     * @param $assetId
     * @return array|string
     */
    public function VulnerabilityAsset($assetId)
    {
        $url = "https://".$this->host.":3780/api/1.1/xml";

        $post_string = '<?xml version="1.0" encoding="utf-8"?>
				<ReportAdhocGenerateRequest session-id='.'"'.$this->getSessionId().'"'.'>
					<AdhocReportConfig template-id="audit-report" format="ns-xml">
						<Filters>
							<filter type="device" id='.'"'.$assetId.'"'.'></filter>
						</Filters>
					</AdhocReportConfig>
				</ReportAdhocGenerateRequest>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

        $data = curl_exec($ch);

        if(curl_errno($ch))
            return curl_error($ch);
        else
            curl_close($ch);

        $t = substr($data,230,-20);
        $l = base64_decode($t);
        $xml = new SimpleXMLElement($l);

        $vulnerabilities = array();

        foreach($xml->devices->device->services as $services){
            foreach($services->service as $service){
                $port = $service["port"].' / '.$service["protocol"].' / '.$service["name"];
                foreach($service->vulnerabilities->vulnerability as $vulnerability){
                    if($vulnerability->id["type"] == 'cve'){
                        $vulnerabilities[] = array($vulnerability["id"],$port,$vulnerability->id);
                    } else {
                        $vulnerabilities[] = array($vulnerability["id"],$port,null);
                    }
                }
            }
        }
        return $vulnerabilities;
    }

    /**
     * Provide the full details of a vulnerability, including its description, cross-references, and solution.
     *
     * @param $vulnerabilityId
     * @return array|string
     */
    public function VulnerabilityDetails($vulnerabilityId)
    {
        $url = "https://".$this->host.":3780/api/1.1/xml";

        $post_string = '<?xml version="1.0" encoding="utf-8"?><VulnerabilityDetailsRequest session-id='.'"'.$this->sessionId.'"'.' vuln-id='.'"'.$vulnerabilityId.'"'.'/>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

        $data = curl_exec($ch);

        if(curl_errno($ch))
            return curl_error($ch);
        else
            curl_close($ch);


        $xml = new SimpleXMLElement($data);

        $regex = '/(CVE)[-.]\d{4}[-.]?\d{4}[-.]?\b/';
        $vulnerability = array();
        $vulnerability["title"] = $xml->Vulnerability["title"];
        $vulnerability["cvssScore"] = $xml->Vulnerability["cvssScore"];
        $vulnerability["severity"] = $xml->Vulnerability["severity"];
        $vulnerability["published"] = $xml->Vulnerability["published"];
        $vulnerability["modified"] = $xml->Vulnerability["modified"];
        $vulnerability["description"] = $xml->Vulnerability->description->p;
        $solution = $xml->Vulnerability->solution->asXml();
        $vulnerability["solution"] = strip_tags($solution);
        if(preg_match($regex,$vulnerability["title"],$match)){
            $vulnerability["cve"] = $match[0];
        }else{
            $vulnerability["cve"] = $vulnerability["title"];
        }

        return $vulnerability;
    }

    /**
     * CURL request
     *
     * @param $url
     * @param $post_string
     * @return mixed
     */
    private function call($url, $post_string)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

        if(curl_errno($ch)){
            return curl_error($ch);
        } else {
            curl_close($ch);
            return curl_exec($ch);
        }
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     */
    private function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @return int
     */
    public function getEngineId()
    {
        return $this->engineId;
    }

    /**
     * @param int $engineId
     */
    public function setEngineId($engineId)
    {
        $this->engineId = $engineId;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }
}