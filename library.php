<?php
/**
 * Created by IntelliJ IDEA.
 * User: bushev
 * Date: 03/01/2017
 * Time: 00:39
 */

namespace RealtyPultImporter;

class Exception extends \Exception
{
}

class Importer
{
    function __construct($options)
    {
        if (!is_object($options)) throw new Exception('Importer::constructor: "options" parameter is required!');
        if (!filter_var($options->xmlFeedUrl, FILTER_VALIDATE_URL)) throw new Exception('Importer::constructor: "xmlFeedUrl" should be a valid URL!');
        if (!$options->reportFileLocation) throw new Exception('Importer::constructor: "reportFileLocation" parameter is required!');
        if (!$options->format) throw new Exception('Importer::constructor: "format" parameter is required!');

        if (!$options->onItem) throw new Exception('Importer::constructor: "onItem" callback is required!');
        //if (!$options->onEnd) throw new Exception('Importer::constructor: "onItem" callback is required!');
        //if (!$options->onError) throw new Exception('Importer::constructor: "onItem" callback is required!');

        $options->onItem();
    }

    public function run()
    {

        return 0;
    }
}