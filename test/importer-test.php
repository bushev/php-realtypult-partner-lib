<?php

/**
 * Created by IntelliJ IDEA.
 * User: bushev
 * Date: 03/01/2017
 * Time: 00:34
 */

require_once '../library.php';

function onItemSuccessWithViews($item)
{
    $result = new \stdClass();

    $result->url = 'http://your-site.ru/item-' . $item->id;
    $result->views = 15;

    return $result;
}

class ImporterTest extends PHPUnit_Framework_TestCase
{
    public function testCreateInstance()
    {
        $options = new \stdClass();

        $this->expectException(\RealtyPultImporter\Exception::class);
        new \RealtyPultImporter\Importer($options);

//        $this->assertEquals($importer->run(), 0);
    }

    public function testCreateInstance2()
    {
        $options = new \stdClass();
        $options->xmlFeedUrl = 'https://dev.realtypult.ru/xml/import-feed-realtypult.xml';
        $this->expectException(\RealtyPultImporter\Exception::class);
        new \RealtyPultImporter\Importer($options);
    }

    public function testCreateInstance3()
    {
        $options = new \stdClass();
        $options->xmlFeedUrl = 'https://dev.realtypult.ru/xml/import-feed-realtypult.xml';
        $options->reportFileLocation = '/Users/bushev/Downloads/rm-report.xml';
        $this->expectException(\RealtyPultImporter\Exception::class);
        new \RealtyPultImporter\Importer($options);
    }

    public function testCreateInstance4()
    {
        $options = new \stdClass();
        $options->xmlFeedUrl = 'https://dev.realtypult.ru/xml/import-feed-realtypult.xml';
        $options->reportFileLocation = '/Users/bushev/Downloads/rm-report.xml';
        $options->format = 'realtypult';
        $this->expectException(\RealtyPultImporter\Exception::class);
        new \RealtyPultImporter\Importer($options);
    }

    public function testCreateInstance5()
    {

        $options = new \stdClass();
        $options->xmlFeedUrl = 'https://dev.realtypult.ru/xml/import-feed-realtypult.xml';
        $options->reportFileLocation = '/Users/bushev/Downloads/rm-report.xml';
        $options->format = 'realtypult';
        $options->onItem = 'onItemSuccessWithViews';
        $this->expectException(\RealtyPultImporter\Exception::class);
        new \RealtyPultImporter\Importer($options);
    }
}
