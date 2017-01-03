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

        if (!$options->onItem || !is_callable($options->onItem)) throw new Exception('Importer::constructor: "onItem" callback is required!');
        if (!$options->onEnd || !is_callable($options->onEnd)) throw new Exception('Importer::constructor: "onEnd" callback is required!');
        if (!$options->onError || !is_callable($options->onError)) throw new Exception('Importer::constructor: "onError" callback is required!');

        $this->xmlFeedUrl = $options->xmlFeedUrl;

        $this->onItem = $options->onItem;
        $this->onEnd = $options->onEnd;
        $this->onError = $options->onError;

        $this->downloadPath = tempnam(sys_get_temp_dir(), 'realtypult-xml-feed');
    }

    public function run()
    {
        file_put_contents($this->downloadPath, fopen($this->xmlFeedUrl, 'r'));

        $reader = new \XMLReader();
        $reader->open($this->downloadPath);

        while ($reader->read()) {

            if ($reader->nodeType == \XMLReader::ELEMENT) {

                if ($reader->localName == 'object') {

                    $node = new \XMLReader();

                    $node->xml($reader->readOuterXML());

                    $data = $this->xml2assoc($node);

                    call_user_func_array($this->onItem, [(object)$data['object']]);
                }
            }
        }
    }

    /**
     * Parse one object to assoc array
     *
     * @param $xml
     * @return array|null|string
     */
    private function xml2assoc($xml)
    {
        $buffer = null;
        while ($xml->read()) {
            switch ($xml->nodeType) {
                case \XMLReader::END_ELEMENT:
                    return $buffer;
                case \XMLReader::ELEMENT:
                    if (!is_array($buffer)) {
                        $buffer = array();
                    }
                    $buffer[$xml->name] = $xml->isEmptyElement ? null : $this->xml2assoc($xml);
                    if ($xml->hasAttributes) {
                        while ($xml->moveToNextAttribute()) {
                            $buffer[$xml->name] = $xml->value;
                        }
                    }
                    break;
                case \XMLReader::TEXT:
                case \XMLReader::CDATA:
                    if (!is_string($buffer)) {
                        $buffer = '';
                    }
                    $buffer .= $xml->value;
            }
        }
        return $buffer;
    }
}