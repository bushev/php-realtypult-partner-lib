<?php

/**
 * Created by IntelliJ IDEA.
 * User: bushev
 * Date: 03/01/2017
 * Time: 00:39
 */
class RealtyPultImporter
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

        if ($options->format === 'realtypult') {

            $this->itemTag = 'object';

            $this->getItemId = function ($data) {

                return $data['id'];
            };

        } else if ($options->format === 'yandex') {

            $this->itemTag = 'offer';

            $this->getItemId = function ($data) {

                return $data['internal-id'];
            };

        } else {

            throw new Exception('Importer::constructor: "format" is unexpected!');
        }

        $this->format = $options->format;
        $this->reportFileLocation = $options->reportFileLocation;
        $this->xmlFeedUrl = $options->xmlFeedUrl;

        $this->onItem = $options->onItem;
        $this->onEnd = $options->onEnd;
        $this->onError = $options->onError;

        $this->downloadPath = tempnam(sys_get_temp_dir(), 'realtypult-xml-feed');

        $this->report = new \stdClass();
        $this->report->location = $options->reportFileLocation;

        $this->report->statictics = new \stdClass();
        $this->report->statictics->total = 0;
        $this->report->statictics->success = 0;
        $this->report->statictics->rejected = 0;
        $this->report->statictics->errors = 0;

        $this->reportFileTmpLocation = tempnam(sys_get_temp_dir(), 'realtypult-tmp-report-xml');

        $this->xmlReport = new \XMLWriter();
        $this->xmlReport->openURI($this->reportFileTmpLocation);
        $this->xmlReport->setIndent(true);
        $this->xmlReport->startDocument('1.0', 'UTF-8');
        $this->xmlReport->startElement('objects');
    }

    public function run()
    {
        if (!file_put_contents($this->downloadPath, fopen($this->xmlFeedUrl, 'r'))) {

            call_user_func_array($this->onError, ['Unable to download XML feed']);

            return;
        }

        $reader = new \XMLReader();
        $reader->open($this->downloadPath);

        while ($reader->read()) {

            if ($reader->nodeType == \XMLReader::ELEMENT) {

                if ($reader->localName == $this->itemTag) {

                    $node = new \XMLReader();

                    $node->xml($reader->readOuterXML());

                    $data = $this->xml2assoc($node);

                    $data[$this->itemTag]['id'] = call_user_func_array($this->getItemId, [$data]);

                    $item = (object)$data[$this->itemTag];

                    $result = call_user_func_array($this->onItem, [$item]);

                    if (!$result || !is_object($result)) {

                        throw new Exception('Importer::onItem: data must be an object');
                    }

                    $this->report->statictics->total++;

                    $this->xmlReport->startElement('object');
                    $this->xmlReport->writeAttribute('id', $item->id);

                    if ($result->url) {

                        $this->report->statictics->success++;

                        $this->xmlReport->writeElement('url', $result->url);

                        if ($result->views) {

                            $this->xmlReport->writeElement('views', $result->views);
                        }

                    } else if ($result->error) {

                        $this->report->statictics->errors++;

                        $this->xmlReport->writeElement('error', $result->error);

                    } else if ($result->similarUrl) {

                        $this->report->statictics->rejected++;

                        $this->xmlReport->writeElement('similarUrl', $result->similarUrl);

                    } else if ($result->rejectReason) {

                        $this->report->statictics->rejected++;

                        $this->xmlReport->writeElement('rejectReason', $result->rejectReason);

                    } else {

                        throw new Exception('Importer::onItem: unexpected data');
                    }

                    $this->xmlReport->endElement(); // object
                }
            }
        }

        $this->xmlReport->endElement(); // objects
        $this->xmlReport->endDocument();

        rename($this->reportFileTmpLocation, $this->reportFileLocation);
        unlink($this->downloadPath);

        call_user_func_array($this->onEnd, [$this->report]);
    }

    /**
     * Parse one object to assoc array
     *
     * @param $xml
     * @return array|null|string
     */
    private function xml2assoc($xml)
    {
        $assoc = null;
        while ($xml->read()) {
            switch ($xml->nodeType) {
                case \XMLReader::END_ELEMENT:
                    return $assoc;
                case \XMLReader::ELEMENT:

                    if ($xml->name === 'image') {

                        if ($this->format === 'realtypult') {

                            $assoc[] = $xml->isEmptyElement ? null : $this->xml2assoc($xml);

                        } else {

                            $assoc['images'][] = $xml->isEmptyElement ? null : $this->xml2assoc($xml);
                        }

                    } else {

                        $assoc[$xml->name] = $xml->isEmptyElement ? null : $this->xml2assoc($xml);
                    }

                    if ($xml->hasAttributes) {
                        while ($xml->moveToNextAttribute()) {
                            $assoc[$xml->name] = $xml->value;
                        }
                    }
                    break;
                case \XMLReader::TEXT:
                case \XMLReader::CDATA:
                    if (!is_string($assoc)) {
                        $assoc = '';
                    }
                    $assoc .= $xml->value;
            }
        }
        return $assoc;
    }
}