<?php

namespace Bundle\LichessBundle\Validator;

use Symfony\Components\Validator\MessageInterpolator\XliffMessageInterpolator; 

class NoValidationXliffMessageInterpolator extends XliffMessageInterpolator 
{
    /**
     * Parses the given file into a SimpleXMLElement
     * Does NOT validate the file (much faster)
     *
     * @param  string $file
     * @return SimpleXMLElement
     */
    protected function parseFile($file)
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        if (!$dom->load($file, LIBXML_COMPACT)) {
            throw new \Exception(implode("\n", $this->getXmlErrors()));
        }
        $dom->validateOnParse = true;
        $dom->normalizeDocument();
        libxml_use_internal_errors(false);

        return simplexml_import_dom($dom);
    }
}
