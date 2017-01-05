<?php
/**
 *
 * Created by Yuriy Bushev <bushevuv@gmail.com> on 05/01/2017.
 */

require_once __DIR__ . '/../realtypult-importer.php';

class ImporterTest extends PHPUnit_Framework_TestCase
{
    public function testCreateInstance()
    {
        $options = new \stdClass();

        $this->setExpectedException(\Exception::class);
        new \RealtyPultImporter($options);
    }

    public function testCreateInstance2()
    {
        $options = new \stdClass();
        $options->xmlFeedUrl = 'https://dev.realtypult.ru/xml/import-feed-realtypult.xml';
        $this->setExpectedException(\Exception::class);
        new \RealtyPultImporter($options);
    }

    public function testCreateInstance3()
    {
        $options = new \stdClass();
        $options->xmlFeedUrl = 'https://dev.realtypult.ru/xml/import-feed-realtypult.xml';
        $options->reportFileLocation = tempnam(sys_get_temp_dir(), 'xml-report');
        $this->setExpectedException(\Exception::class);
        new \RealtyPultImporter($options);
    }

    public function testCreateInstance4()
    {
        $options = new \stdClass();
        $options->xmlFeedUrl = 'https://dev.realtypult.ru/xml/import-feed-realtypult.xml';
        $options->reportFileLocation = tempnam(sys_get_temp_dir(), 'xml-report');
        $options->format = 'realtypult';
        $this->setExpectedException(\Exception::class);
        new \RealtyPultImporter($options);
    }

    public function testCreateInstance5()
    {
        $onItemSuccessWithViews = function ($item) {

            $result = new \stdClass();

            $result->url = 'http://your-site.ru/item-' . $item->id;
            $result->views = 15;

            return $result;
        };

        $options = new \stdClass();
        $options->xmlFeedUrl = 'https://dev.realtypult.ru/xml/import-feed-realtypult.xml';
        $options->reportFileLocation = tempnam(sys_get_temp_dir(), 'xml-report');
        $options->format = 'realtypult';
        $options->onItem = $onItemSuccessWithViews;
        $this->setExpectedException(\Exception::class);
        $importer = new \RealtyPultImporter($options);

        $importer->run();
    }

    public function testParseWithViewsYandex()
    {
        $onItemSuccessWithViews = function ($item) {

            $this->assertInternalType('string', $item->id);

            $result = new \stdClass();

            $result->url = 'http://your-site.ru/item-' . $item->id;
            $result->views = 130;

            return $result;
        };

        $onEnd = function ($report) {

            $this->assertInternalType('object', $report);
            $this->assertInternalType('object', $report->statictics);

            $this->assertInternalType('string', $report->location);

            $this->assertEquals(3, $report->statictics->total);
            $this->assertEquals(3, $report->statictics->success);
            $this->assertEquals(0, $report->statictics->rejected);
            $this->assertEquals(0, $report->statictics->errors);

            $reportObject = simplexml_load_file($report->location);

            $this->assertEquals(3, count($reportObject->object));

            $this->assertEquals(67951, (string)$reportObject->object[0]->attributes()['id'][0]);
            $this->assertEquals('http://your-site.ru/item-67951', (string)$reportObject->object[0]->url);
            $this->assertEquals(130, (string)$reportObject->object[0]->views);
            $this->assertNotNull($reportObject->object[0]->error);
            $this->assertNotNull($reportObject->object[0]->similarUrl);
            $this->assertNotNull($reportObject->object[0]->rejectReason);

            $this->assertEquals(69163, (string)$reportObject->object[1]->attributes()['id'][0]);
            $this->assertEquals('http://your-site.ru/item-69163', (string)$reportObject->object[1]->url);
            $this->assertEquals(130, (string)$reportObject->object[1]->views);
            $this->assertNotNull($reportObject->object[1]->error);
            $this->assertNotNull($reportObject->object[1]->similarUrl);
            $this->assertNotNull($reportObject->object[1]->rejectReason);

            $this->assertEquals(66615, (string)$reportObject->object[2]->attributes()['id'][0]);
            $this->assertEquals('http://your-site.ru/item-66615', (string)$reportObject->object[2]->url);
            $this->assertEquals(130, (string)$reportObject->object[2]->views);
            $this->assertNotNull($reportObject->object[2]->error);
            $this->assertNotNull($reportObject->object[2]->similarUrl);
            $this->assertNotNull($reportObject->object[2]->rejectReason);
        };

        $onError = function ($error) {

            $this->assertEquals('I\'m here!', 'Should not be here!');
        };

        $options = new \stdClass();
        $options->xmlFeedUrl = 'https://dev.realtypult.ru/xml/import-feed-yandex-2.xml';
        $options->reportFileLocation = tempnam(sys_get_temp_dir(), 'xml-report');
        $options->format = 'yandex';
        $options->onItem = $onItemSuccessWithViews;
        $options->onEnd = $onEnd;
        $options->onError = $onError;

        $importer = new \RealtyPultImporter($options);

        $importer->run();
    }

    public function testParseWithViewsRealtyPult()
    {
        $onItemSuccessWithViews = function ($item) {

            $this->assertInternalType('string', $item->id);

            $result = new \stdClass();

            $result->url = 'http://your-site.ru/item-' . $item->id;
            $result->views = 15;

            return $result;
        };

        $onEnd = function ($report) {

            $this->assertInternalType('object', $report);
            $this->assertInternalType('object', $report->statictics);

            $this->assertInternalType('string', $report->location);

            $this->assertEquals(2, $report->statictics->total);
            $this->assertEquals(2, $report->statictics->success);
            $this->assertEquals(0, $report->statictics->rejected);
            $this->assertEquals(0, $report->statictics->errors);

            $reportObject = simplexml_load_file($report->location);

            $this->assertEquals(2, count($reportObject->object));

            $this->assertEquals(679511, (string)$reportObject->object[0]->attributes()['id'][0]);
            $this->assertEquals('http://your-site.ru/item-679511', (string)$reportObject->object[0]->url);
            $this->assertEquals(15, (string)$reportObject->object[0]->views);
            $this->assertNotNull($reportObject->object[0]->error);
            $this->assertNotNull($reportObject->object[0]->similarUrl);
            $this->assertNotNull($reportObject->object[0]->rejectReason);

            $this->assertEquals(679512, (string)$reportObject->object[1]->attributes()['id'][0]);
            $this->assertEquals('http://your-site.ru/item-679512', (string)$reportObject->object[1]->url);
            $this->assertEquals(15, (string)$reportObject->object[1]->views);
            $this->assertNotNull($reportObject->object[1]->error);
            $this->assertNotNull($reportObject->object[1]->similarUrl);
            $this->assertNotNull($reportObject->object[1]->rejectReason);
        };

        $onError = function ($error) {

            $this->assertEquals('I\'m here!', 'Should not be here!');
        };

        $options = new \stdClass();
        $options->xmlFeedUrl = 'https://dev.realtypult.ru/xml/import-feed-realtypult.xml';
        $options->reportFileLocation = tempnam(sys_get_temp_dir(), 'xml-report');
        $options->format = 'realtypult';
        $options->onItem = $onItemSuccessWithViews;
        $options->onEnd = $onEnd;
        $options->onError = $onError;

        $importer = new \RealtyPultImporter($options);

        $importer->run();
    }

    public function testParseWithProblems()
    {
        $onItemSuccessWithErrors = function ($item) {

            $this->assertInternalType('string', $item->id);

            $result = new \stdClass();

            if ($item->id == 69163) {

                $result->error = 'Ошибка при подключении к БД';

            } else if ($item->id == 67951) {

                $result->similarUrl = 'http://your-site.ru/similar-object-123';

            } else {

                $result->rejectReason = 'Номер телефона заблокирован';
            }

            return $result;
        };

        $onEnd = function ($report) {

            $this->assertInternalType('object', $report);
            $this->assertInternalType('object', $report->statictics);

            $this->assertInternalType('string', $report->location);

            $this->assertEquals(3, $report->statictics->total);
            $this->assertEquals(0, $report->statictics->success);
            $this->assertEquals(2, $report->statictics->rejected);
            $this->assertEquals(1, $report->statictics->errors);

            $reportObject = simplexml_load_file($report->location);

            $this->assertEquals(3, count($reportObject->object));

            $this->assertEquals(67951, (string)$reportObject->object[0]->attributes()['id'][0]);
            $this->assertEquals('http://your-site.ru/similar-object-123', (string)$reportObject->object[0]->similarUrl);
            $this->assertNotNull($reportObject->object[0]->views);
            $this->assertNotNull($reportObject->object[0]->error);
            $this->assertNotNull($reportObject->object[0]->url);
            $this->assertNotNull($reportObject->object[0]->rejectReason);

            $this->assertEquals(69163, (string)$reportObject->object[1]->attributes()['id'][0]);
            $this->assertEquals('Ошибка при подключении к БД', (string)$reportObject->object[1]->error);
            $this->assertNotNull($reportObject->object[1]->url);
            $this->assertNotNull($reportObject->object[1]->views);
            $this->assertNotNull($reportObject->object[1]->rejectReason);
            $this->assertNotNull($reportObject->object[1]->similarUrl);

            $this->assertEquals(66615, (string)$reportObject->object[2]->attributes()['id'][0]);
            $this->assertEquals('Номер телефона заблокирован', (string)$reportObject->object[2]->rejectReason);
            $this->assertNotNull($reportObject->object[1]->views);
            $this->assertNotNull($reportObject->object[2]->error);
            $this->assertNotNull($reportObject->object[2]->similarUrl);
            $this->assertNotNull($reportObject->object[1]->url);
        };

        $onError = function ($error) {

            $this->assertEquals('I\'m here!', 'Should not be here!');
        };

        $options = new \stdClass();
        $options->xmlFeedUrl = 'https://dev.realtypult.ru/xml/import-feed-yandex-2.xml';
        $options->reportFileLocation = tempnam(sys_get_temp_dir(), 'xml-report');
        $options->format = 'yandex';
        $options->onItem = $onItemSuccessWithErrors;
        $options->onEnd = $onEnd;
        $options->onError = $onError;

        $importer = new \RealtyPultImporter($options);

        $importer->run();
    }
}
