<?php

namespace Enedis2InfluxDB;

use DateTime;
use DateInterval;
use Error;
use exception; 

class enedisfromplugin {

    private $influxdbInstance;
    private $point_id;
    private $start_hourly;
    private $start_daily;


    public function __construct() {   
        date_default_timezone_set("UTC");
        $this->point_id = ENEDIS_POINT_ID;
        $this->start_hourly = ENEDIS_START_HOURLY;
        $this->start_daily = ENEDIS_START_DAILY;
        $this->influxdbInstance = new influxdb();

        if (!$this->point_id || !$this->start_hourly  || !$this->start_daily) {
            throw new Error('All variables are not defined');
        }
         
    }

    public function import_all() {
        $this->import_all_daily(); 
        $this->import_all_hourly();
    }
    

    // TODO check if the start of contract if greater than the max allowed by the API
    private function import_all_daily() {

        // Un appel peut porter sur des données datant au maximum de 36 mois et 15 jours avant la date d’appel.
        $objDateTimeHistoryDeadline = new DateTime('NOW');
        $objDateTimeHistoryDeadline->sub(new DateInterval('P36M5D'));

        // The start date must be greater than the history deadline.
        $objDateTimeUserStart = new DateTime($this->start_daily);

        if ($objDateTimeHistoryDeadline->getTimestamp() > $objDateTimeUserStart->getTimestamp()){
            $start_date = $objDateTimeHistoryDeadline->format('Y-m-d');
        } else {
            $start_date = $objDateTimeUserStart->format('Y-m-d');
        }

        $end_date = date('Y-m-d');

        $this->getData('daily', $start_date, $end_date);
    }

    // TODO check if the start of contract if greater than the max allowed by the API
    private function import_all_hourly() {
        // Un appel peut porter au maximum sur 7 jours consécutifs. Un appel peut porter sur des données datant au maximum de 24 mois et 15 jours avant la date d’appel.
        $objDateTimeHistoryDeadline = new DateTime('NOW');
        $objDateTimeHistoryDeadline->sub(new DateInterval('P24M15D'));

        // The start date must be greater than the history deadline.
        $objDateTimeUserStart = new DateTime($this->start_hourly);

        if ($objDateTimeHistoryDeadline->getTimestamp() > $objDateTimeUserStart->getTimestamp()){
            $start_date = $objDateTimeHistoryDeadline->format('Y-m-d');
            $objDateTime = $objDateTimeHistoryDeadline;
        } else {
            $start_date = $objDateTimeUserStart->format('Y-m-d');
            $objDateTime = $objDateTimeUserStart;
        }
        

        $objDateTimeNOW = new DateTime('NOW');
        $interval = $objDateTimeNOW->diff($objDateTime);
        $days = $interval->format('%a');
        echo PHP_EOL."delta => $days".PHP_EOL;
        $delta = $days/7 + 1;
        // The requested period must be less than 7 days for an access to the load curve.

       for ($i = 1; $i <= $delta; $i++) {
            $start_date = $objDateTime->format('Y-m-d');
            $objDateTime->add(new DateInterval('P7D'));
            $end_date = $objDateTime->format('Y-m-d');
            $this->getData('hourly', $start_date, $end_date);
        }
    }

    public function dailyCron() {
        $this->getDailyData();
        $this->getHourlyData();
    }

    private function getDailyData($start_date = null, $end_date = null) {
        $this->getData('daily', $start_date, $end_date);
    }

    private function getHourlyData($start_date = null, $end_date = null) {
        $this->getData('hourly', $start_date, $end_date);
    }


    private function getData($type = 'daily', $start_date = null, $end_date = null) {
        echo PHP_EOL."- $type".PHP_EOL;
        $measures = array();
        if (!$start_date) {
            $objDateTime = new DateTime('NOW');
            if ($type === 'daily') {
                $objDateTime->sub(new DateInterval('P5D'));
            } elseif ($type === 'hourly') {
                $objDateTime->sub(new DateInterval('P5D'));
            }
            $start_date = $objDateTime->format('Y-m-d');
        }
        if (!$end_date) {
            $end_date = date('Y-m-d');
        }
        echo "--- start_date: $start_date".PHP_EOL;
        echo "--- end_date: $end_date".PHP_EOL;
        
        if ($type === 'daily') {
            $path = '/metering_data/daily_consumption?'; 
        } elseif ($type === 'hourly') {
            $path = '/metering_data/consumption_load_curve?'; 
        }
        
        $path = $path.'usage_point_id='.$this->point_id; 
        $path = $path.'&start='.$start_date; 
        $path = $path.'&end='.$end_date; 

        try {
          $url = \config::byKey('service::cloud::url').'/service/enedis?path='.urlencode($path);
          var_dump($url);
          $request_http = new \com_http($url);
          $request_http->setHeader(array('Content-Type: application/json','Autorization: '.sha512(mb_strtolower(\config::byKey('market::username')).':'.\config::byKey('market::password'))));
          $result = json_decode($request_http->exec(30,1),true);
        }
        catch (exception $e) {
          $result = array('error' => $e);
        }
        if (isset($result['error'])) {
            var_dump($result);
            throw new Error($result['error']);
        }
        var_dump($result);

        // https://datahub-enedis.fr/data-connect/documentation/metering-data-v4/

        if (isset($result['meter_reading']['interval_reading'])) {
            $measures = $result['meter_reading']['interval_reading'];
        }

        $measurement_kind = $result["meter_reading"]["reading_type"]["measurement_kind"];

        $dataInline = '';
        foreach ($measures as $metric) {
            $date = strtotime($metric['date']);
            $value = round($metric['value'], 5);

            echo "--------".$metric['date'].PHP_EOL;
            if ($value) {
                $monthH = date('M', $date);
                $month = date('m', $date);
                $year = date('Y', $date);
                $tag = ",month=$month,monthH=$monthH,year=$year";
                $dataInline .= "electricity,mode=$type$tag $measurement_kind=$value $date \n";

            }

        }
        if ($dataInline) {
            var_dump($dataInline);
            $this->influxdbInstance->writeData($dataInline);
        }
    }

} 
