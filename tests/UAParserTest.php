<?php

/**
 * @author Maxim P. (extead@gmail.com)
 */
class UAParserTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     */
    public function testBrowserDetection()
    {
        $instance = new \Extead\UAParser\UAParser();
        $data = json_decode(file_get_contents(__DIR__ . "/data/browser-test.json"), true);

        foreach ($data as $item) {
            $instance->setUA($item['ua']);
            $result = $instance->getBrowser();
            $this->assertTrue($result['name'] == $item['expect']['name']);
            $this->assertTrue($result['version'] == $item['expect']['version']);
        }

        unset($instance);
    }

    /**
     *
     */
    public function testCpuDetection()
    {
        $instance = new \Extead\UAParser\UAParser();
        $data = json_decode(file_get_contents(__DIR__ . "/data/cpu-test.json"), true);

        foreach ($data as $item) {
            $instance->setUA($item['ua']);
            $result = $instance->getCpu();
            $this->assertTrue($result['architecture'] == $item['expect']['architecture']);
        }

        unset($instance);
    }

    /**
     *
     */
    public function testDeviceDetection()
    {
        $instance = new \Extead\UAParser\UAParser();
        $data = json_decode(file_get_contents(__DIR__ . "/data/device-test.json"), true);

        foreach ($data as $item) {
            $instance->setUA($item['ua']);
            $result = $instance->getDevice();
            $this->assertTrue($result['vendor'] == $item['expect']['vendor']);
            $this->assertTrue($result['model'] == $item['expect']['model']);
            $this->assertTrue($result['type'] == $item['expect']['type']);
        }

        unset($instance);
    }

    /**
     *
     */
    public function testEngineDetection()
    {
        $instance = new \Extead\UAParser\UAParser();
        $data = json_decode(file_get_contents(__DIR__ . "/data/engine-test.json"), true);

        foreach ($data as $item) {
            $instance->setUA($item['ua']);
            $result = $instance->getEngine();
            $this->assertTrue($result['name'] == $item['expect']['name']);
            $this->assertTrue($result['version'] == $item['expect']['version']);
        }

        unset($instance);
    }

    /**
     * 
     */
    public function testOsDetection()
    {
        $instance = new \Extead\UAParser\UAParser();
        $data = json_decode(file_get_contents(__DIR__ . "/data/os-test.json"), true);

        foreach ($data as $item) {
            $instance->setUA($item['ua']);
            $result = $instance->getOs();
            $this->assertTrue($result['name'] == $item['expect']['name']);
            $this->assertTrue($result['version'] == $item['expect']['version']);
        }

        unset($instance);
    }

}