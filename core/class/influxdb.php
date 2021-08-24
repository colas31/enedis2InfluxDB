<?php

namespace Enedis2InfluxDB;

use Error;
use InfluxDB2\Client;
use InfluxDB2\Model\WritePrecision;
use InfluxDB2\Point;

class influxdb {

    private $client;
    private $token = INFLUX_TOKEN;
    private $url = INFLUX_URL;
    private $org = INFLUX_ORG;
    private $bucket = INFLUX_BUCKET;
    private $writeApi;


    public function __construct()
    {
        if (!$this->token || !$this->url || !$this->org || !$this->bucket) {
            throw new Error("All variables are not defined");
            
        }
        $this->client = new Client([
            "url" => $this->url,
            "token" => $this->token,
        ]);
        $this->writeApi = $this->client->createWriteApi();
        return $this;
    }

    public function writeInfluxDBLineProtocol() {

        $data = "mem,host=host1 used_percent=23.43234543";

        $this->writeData($data);
    }

    public function writeDataPoint() {

        $point = Point::measurement('mem')
            ->addTag('host', 'host1')
            ->addField('used_percent', 23.43234543)
            ->time(microtime(true));

        $this->writeData($point);

    }

    public function writeDataArray() {

        $dataArray = ['name' => 'cpu',
        'tags' => ['host' => 'server_nl', 'region' => 'us'],
        'fields' => ['internal' => 5, 'external' => 6],
        'time' => microtime(true)];

        $this->writeData($dataArray);
    }

    public function writeData($data, $precision = WritePrecision::S) { 

        $this->writeApi->write($data, $precision, $this->bucket, $this->org);
    }


}


