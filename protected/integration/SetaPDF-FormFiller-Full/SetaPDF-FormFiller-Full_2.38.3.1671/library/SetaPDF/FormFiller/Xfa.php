<?php
/**
 * This file is part of the SetaPDF-FormFiller Component
 *
 * @copyright  Copyright (c) 2021 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_FormFiller
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Xfa.php 1669 2021-12-02 15:10:42Z jan.slabon $
 */

/**
 * Class offering access to XFA data of a PDF form.
 *
 * @copyright  Copyright (c) 2021 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_FormFiller
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_FormFiller_Xfa
{
    /**
     * The form filler instance
     *
     * @var SetaPDF_FormFiller
     */
    protected $_formFiller;

    /**
     * The data node.
     *
     * @var DOMNode
     */
    protected $_data;

    /**
     * The template node.
     *
     * @var DOMNode
     */
    protected $_template;

    /**
     * The form node.
     *
     * @var DOMNode
     */
    protected $_form;

    /**
     * Flag indicating if the data has to be set back to the data stream on save.
     *
     * @var bool
     */
    protected $_dataNodeUpdated = false;

    /**
     * Flag indicating that the template node was changed.
     *
     * @var bool
     */
    protected $_templateNodeUpdated = false;

    /**
     * The document instance of the full XDP packet.
     *
     * @var DOMDocument
     */
    protected $_domDocument;

    /**
     * The bridge instance.
     *
     * @var SetaPDF_FormFiller_Xfa_Bridge
     */
    protected $_bridge;

    /**
     * Flag identicating that a setValue() process is working.
     *
     * @var bool
     */
    protected $_setValueIsActive = false;

    /**
     * The constructor.
     *
     * @param SetaPDF_FormFiller $formFiller The form filler instance
     */
    public function __construct(SetaPDF_FormFiller $formFiller)
    {
        $this->_formFiller = $formFiller;
    }

    /**
     * Releases memory and resources.
     *
     * @return void
     */
    public function cleanUp()
    {
        $this->_formFiller = null;
        $this->_data = null;
        $this->_domDocument = null;

        if ($this->_bridge !== null) {
            $this->_bridge->cleanUp();
            $this->_bridge = null;
        }
    }

    /**
     * Get the form filler instance.
     *
     * @return SetaPDF_FormFiller
     */
    public function getFormFiller()
    {
        return $this->_formFiller;
    }

    /**
     * Get the XFA pdf object.
     *
     * @return false|SetaPDF_Core_Type_AbstractType
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function getObject()
    {
        $dictionary = $this->_formFiller->getDocument()->getCatalog()->getAcroForm()->getDictionary();

        if ($dictionary !== false && $dictionary->offsetExists('XFA')) {
            try {
                $xfa = $dictionary->getValue('XFA');
                return $xfa->ensure();
            } catch (SetaPDF_Core_Type_IndirectReference_Exception $e) {
                // ignore
            }
        }

        return false;
    }

    /**
     * Get the raw XFA (XML/XMP) data from the PDF document.
     *
     * @return bool|string
     */
    public function getXml()
    {
        if ($this->_domDocument !== null) {
            return $this->_domDocument->saveXML();
        }

        $object = $this->getObject();
        if ($object instanceof SetaPDF_Core_Type_Stream) {
            return $object->getStream();
        }

        if ($object instanceof SetaPDF_Core_Type_Array) {
            $xml = '';
            foreach ($object AS $packet) {
                $packet = $packet->ensure();
                if ($packet instanceof SetaPDF_Core_Type_Stream) {
                    $xml .= $packet->getStream();
                }
            }

            return $xml;
        }

        return false;
    }

    /**
     * Get the raw XFA (XML/XMP) from the PDF document.
     *
     * @return bool|string
     * @alias getXml()
     * @see getXml()
     */
    public function getXmp()
    {
        return $this->getXml();
    }

    /**
     * Set the raw XFA (XML/XMP) structure.
     *
     * @param DOMDocument|string $domDocumentOrXml
     * @throws SetaPDF_FormFiller_Exception
     */
    public function setXml($domDocumentOrXml)
    {
        if (!($domDocumentOrXml instanceof DOMDocument)) {
            $domDocument = new DOMDocument();
            $domDocument->preserveWhiteSpace = false;
            if ($domDocument->loadXML($domDocumentOrXml) !== true) {
                throw new SetaPDF_FormFiller_Exception('Can\'t load the xml/xfa data');
            }
        } else {
            $domDocument = $domDocumentOrXml;
        }

        // TODO: Create array of packets

        $object = $this->getObject();
        if ($object instanceof SetaPDF_Core_Type_Stream) {
            $xml = $domDocument->saveXML();
            $object->setStream($xml);

        } else {
            $document = $this->_formFiller->getDocument();
            $stream = new SetaPDF_Core_Type_Stream();
            $stream->setValue(new SetaPDF_Core_Type_Dictionary([
                'Filter' => new SetaPDF_Core_Type_Name('FlateDecode', true)
            ]));
            $xml = $domDocument->saveXML();
            $stream->setStream($xml);
            $xmlObject = $document->createNewObject($stream);

            $dictionary = $document->getCatalog()->getAcroForm()->getDictionary();
            $dictionary->offsetSet('XFA', $xmlObject);
        }

        // reinitiate document instance
        if ($this->_domDocument !== null) {
            $domDocument = new DOMDocument();
            $domDocument->preserveWhiteSpace = false;
            if ($domDocument->loadXML($xml) !== true) {
                throw new SetaPDF_FormFiller_Exception('Can\'t load the xml/xdp data');
            }

            $this->_domDocument = $domDocument;
        }

        $this->_data = null;
    }

    /**
     * Set the raw XFA (XML/XMP) structure.
     *
     * @param DOMDocument|string $domDocumentOrXml
     * @alias setXml()
     * @see setXml()
     */
    public function setXmp($domDocumentOrXml)
    {
        $this->setXml($domDocumentOrXml);
    }

    /**
     * Get the DOMDocument instance of the XFA structure.
     *
     * @return DOMDocument
     * @throws SetaPDF_FormFiller_Exception
     */
    public function getDomDocument()
    {
        if ($this->_domDocument === null) {
            $xml = $this->getXml();

            $domDocument = new DOMDocument();
            $domDocument->preserveWhiteSpace = false;
            if ($domDocument->loadXML($xml) !== true) {
                throw new SetaPDF_FormFiller_Exception('Can\'t load the xml/xdp data');
            }

            $this->_domDocument = $domDocument;
        }

        return $this->_domDocument;
    }

    /**
     * Gets the template node from the XFA structure.
     *
     * @return bool|DOMElement
     * @throws SetaPDF_FormFiller_Exception
     */
    public function getTemplate()
    {
        if ($this->_template === null) {
            $object = $this->getObject();
            // A single stream
            if ($object instanceof SetaPDF_Core_Type_Stream) {
                $domDocument = $this->getDomDocument();

                $root = $domDocument->firstChild;
                foreach ($root->childNodes AS $childNode) {
                    if (!$childNode instanceof DOMElement) {
                        continue;
                    }

                    if ($childNode->nodeName === 'template') {
                        $data = $childNode;
                        break;
                    }
                }

                if (!isset($data)) {
                    return false;
                }

            } elseif ($object instanceof SetaPDF_Core_Type_Array) {
                $packet = $this->_findPacketStream($object, 'template');
                $domDocument = new DOMDocument();
                $domDocument->preserveWhiteSpace = false;
                $domDocument->loadXML($packet->getStream());
                $data = $domDocument->firstChild;
            } else {
                throw new SetaPDF_FormFiller_Exception('Unsupported object type found in XFA object.');
            }

            $this->_template = $data;
        }

        return $this->_template;
    }

    /**
     * Saves the template node to the PDF structure.
     *
     * @param boolean $onlyIfUpdated Save only if the template node was updated internally.
     * @return bool
     */
    public function saveTemplate($onlyIfUpdated = false)
    {
        if ($onlyIfUpdated && !$this->_templateNodeUpdated) {
            return false;
        }

        $object = $this->getObject();
        // A single stream
        if ($object instanceof SetaPDF_Core_Type_Stream) {
            $this->setXml($this->getDomDocument());

            $this->_templateNodeUpdated = false;
            return true;
        }

        $packet = $this->_findPacketStream($object, 'template');
        if ($packet) {
            $template = $this->getTemplate();
            $packet->setStream($template->ownerDocument->saveXML($template));
            $this->_templateNodeUpdated = false;
            return true;
        }

        return false;
    }

    /**
     * Get the data node.
     *
     * @param bool $create
     * @return bool|DOMElement
     */
    public function getData($create = false)
    {
        if ($this->_data === null) {
            $domDocument = $this->getDomDocument();

            $xpath = new DOMXPath($domDocument);
            $xpath->registerNamespace('xfa', 'http://www.xfa.org/schema/xfa-data/1.0/');
            $data = $xpath->query('xfa:datasets/xfa:data');

            if ($data->length === 0 && $create === false) {
                return false;
            }

            if ($data->length === 0) {
                $datasets = $xpath->query('xfa:datasets');
                if ($datasets->length === 0) {
                    $datasets = $domDocument->createElementNS('http://www.xfa.org/schema/xfa-data/1.0/', 'xfa:datasets');
                    $domDocument->firstChild->appendChild($datasets);
                } else {
                    $datasets = $datasets->item(0);
                }

                $this->_data = $domDocument->createElementNS('http://www.xfa.org/schema/xfa-data/1.0/', 'xfa:data');
                $datasets->appendChild($this->_data);
            } else {
                $this->_data = $data->item(0);
            }
        }

        return $this->_data;
    }

    /**
     * Get the form node.
     *
     * @return DOMElement
     * @throws SetaPDF_FormFiller_Exception
     */
    public function getFormNode()
    {
        if ($this->_form === null) {
            $domDocument = $this->getDomDocument();

            $xpath = new DOMXPath($domDocument);
            $xpath->registerNamespace('frm', 'http://www.xfa.org/schema/xfa-form/2.8/');
            $form = $xpath->query('frm:form');

            if ($form->length === 0) {
                $this->_form = $domDocument->createElement('form');
                $this->_form->setAttribute('xmlns', 'http://www.xfa.org/schema/xfa-form/2.8/');
                $domDocument->firstChild->appendChild($this->_form);

            } else {
                $this->_form = $form->item(0);
            }
        }

        return $this->_form;
    }

    /**
     * Saves the form node to the XFA packages.
     *
     * @return bool
     * @throws SetaPDF_FormFiller_Exception
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function saveFormNode()
    {
        $form = $this->getFormNode();
        $form->setAttribute('checksum', $this->getFormNodeChecksum());

        $object = $this->getObject();
        // A single stream
        if ($object instanceof SetaPDF_Core_Type_Stream) {
            $this->setXml($this->getDomDocument());
            return true;
        }

        $packet = $this->_findPacketStream($object, 'form');
        if ($packet) {
            $packet->setStream($form->ownerDocument->saveXML($form));
            return true;
        }

        return false;
    }

    /**
     * Finds a packets stream by its name.
     *
     * @param SetaPDF_Core_Type_Array $array
     * @param string $name
     * @param boolean $create
     * @return bool|SetaPDF_Core_Type_Stream
     */
    private function _findPacketStream(SetaPDF_Core_Type_Array $array, $name, $create = false)
    {
        $found = false;
        $postamble = null;
        foreach ($array AS $key => $packet) {
            $packet = $packet->ensure();
            if ($packet instanceof SetaPDF_Core_Type_StringValue) {
                $currentName = $packet->getValue();

                if ($create && $currentName === 'postamble') {
                    $postamble = $key;
                }

                if ($currentName === $name) {
                    $found = true;
                    continue;
                }
            }

            if ($found && $packet instanceof SetaPDF_Core_Type_Stream) {
                return $packet;
            }
        }

        if ($create && $postamble !== null) {
            $stream = new SetaPDF_Core_Type_Stream();
            $stream->setValue(new SetaPDF_Core_Type_Dictionary([
                'Filter' => new SetaPDF_Core_Type_Name('FlateDecode', true)
            ]));

            $object = $this->getFormFiller()->getDocument()->createNewObject($stream);
            $array->insertBefore($object, $postamble);
            $array->insertBefore(new SetaPDF_Core_Type_String($name), $postamble);

            return $object->ensure();
        }

        return false;
    }

    /**
     * Set the content of the data node.
     *
     * @param null|string $xml
     * @throws SetaPDF_FormFiller_Exception
     * @throws Exception
     */
    public function setData($xml)
    {
        if (!is_string($xml)) {
            throw new Exception('XML argument has to be a string.');
        }

        $tmpDomDocument = new DOMDocument();
        $tmpDomDocument->preserveWhiteSpace = false;
        $xmlDeclaration = '<?xml version="1.0" encoding="UTF-8"?>';
        if (strpos($xml, '<?xml') === 0) {
            $end = strpos($xml, '?>');
            $xmlDeclaration = substr($xml, 0, $end + 2);
            $xml = substr($xml, $end + 2);
        }

        if ($tmpDomDocument->loadXML(
            $xmlDeclaration . '<dummy xmlns:xfa="http://www.xfa.org/schema/xfa-data/1.0/">' . $xml . '</dummy>'
        ) !== true) {
            throw new SetaPDF_FormFiller_Exception('Can\'t load the xml/xfa data.');
        }

        $dataNodes = $tmpDomDocument->firstChild->childNodes;

        if ($dataNodes->length > 0) {
            $item = $dataNodes->item(0);
            if ($item instanceof DOMElement && $item->tagName == 'xfa:data') {
                $dataNodes = $dataNodes->item(0)->childNodes;
            }
        }

        $object = $this->getObject();
        $ns = 'http://www.xfa.org/schema/xfa-data/1.0/';
        // A single stream
        if ($object instanceof SetaPDF_Core_Type_Stream) {
            $domDocument = $this->getDomDocument();

            // If no root node is found the whole structure is faulty.
            $xpath = new DOMXPath($domDocument);
            $xpath->registerNamespace('xfa', $ns);
            $xpath->registerNamespace('xdp', 'http://ns.adobe.com/xdp/');

            $datasets = $xpath->query('/xdp:xdp/xfa:datasets')->item(0);
            if ($datasets === null) {
                $datasets = $domDocument->createElementNS($ns, 'xfa:datasets');
                $xdp = $xpath->query('/xdp:xdp')->item(0);
                if ($xdp === null) {
                    throw new SetaPDF_FormFiller_Exception('No XDP element found in XML structure.');
                }
                $xdp->appendChild($datasets);
            }

            $data = $xpath->query('/xdp:xdp/xfa:datasets/xfa:data')->item(0);
            /**
             * @var DOMElement $data
             */
            if ($data === null) {
                // create
                $data = $domDocument->createElementNS($ns, 'xfa:data');
                $datasets->appendChild($data);
            }

            // clean data node
            while ($data->childNodes->length > 0) {
                $data->removeChild($data->lastChild);
            }

            foreach ($dataNodes AS $node) {
                $node = $domDocument->importNode($node, true);
                $data->appendChild($node);
            }

            $this->setXml($domDocument);
        // single packets
        } else {
            $packet = $this->_findPacketStream($object, 'datasets', true);
            if ($packet) {
                $datasetDom = new DOMDocument();
                $datasetDom->preserveWhiteSpace = false;
                $xml = $packet->getStream();
                if ($xml) {
                    $datasetDom->loadXML($xml);
                }

                // If no root node is found the whole structure is faulty.
                $xpath = new DOMXPath($datasetDom);
                $xpath->registerNamespace('xfa', $ns);

                $datasets = $xpath->query('/xfa:datasets')->item(0);
                if ($datasets === null) {
                    $datasets = $datasetDom->createElementNS($ns, 'xfa:datasets');
                    $datasetDom->appendChild($datasets);
                }

                $result = $xpath->query('/xfa:datasets/xfa:data');

                /**
                 * @var DOMElement $data
                 */
                if ($result->length === 0) {
                    // create
                    $data = $datasetDom->createElementNS($ns, 'xfa:data');
                    $datasets = $datasetDom->firstChild;
                    $datasets->appendChild($data);
                } else {
                    $data = $result->item(0);
                }

                // clean data node
                while ($data->childNodes->length > 0) {
                    $data->removeChild($data->lastChild);
                }

                foreach ($dataNodes AS $node) {
                    $node = $datasetDom->importNode($node, true);
                    $data->appendChild($node);
                }

                $packet->setStream($datasetDom->saveXML($data->parentNode));
            }
        }

        $this->_data = null;
        $this->_bridge = null;
    }

    /**
     * Get the XFA bridge helper object.
     *
     * @return SetaPDF_FormFiller_Xfa_Bridge
     * @throws SetaPDF_FormFiller_Exception
     */
    public function getBridge()
    {
        if ($this->_bridge === null) {
            $template = $this->getTemplate();
            if ($template === false) {
                throw new SetaPDF_FormFiller_Exception('No template found in XFA packet.');
            }
            $data = $this->getData(true);
            $form = $this->getFormNode();

            $this->_bridge = new SetaPDF_FormFiller_Xfa_Bridge($template, $data, $form);
        }

        return $this->_bridge;
    }

    /**
     * Checks if the XFA form is a dynamic or static form.
     *
     * @return bool
     */
    public function isDynamic()
    {
        $object = $this->getObject();
        if ($object === false) {
            return false;
        }

        $fields = $this->_formFiller->getFields();

        return count($fields) === 0;
    }

    /**
     * Evaluates a native value to a data value.
     *
     * @param $value
     * @param DOMElement $fieldNode
     * @return DOMNode|string
     */
    private function _evaluateValue($value, DOMElement $fieldNode)
    {
        // Handling of items
        if (is_bool($value) || is_int($value) || $value === null) {
            $xpath = new DOMXPath($fieldNode->ownerDocument);
            $xpath->registerNamespace('tpl', $fieldNode->namespaceURI);
            $items = $xpath->query('tpl:items[@save="1"]/*', $fieldNode);
            if ($items->length === 0) {
                $items = $xpath->query('tpl:items/*', $fieldNode);
            }

            $index = $value;
            if (!is_int($value)) {
                if ($value === true) {
                    $index = 0;
                } elseif ($value === false) {
                    $index = 1;
                } elseif ($items->length > 2) {
                    $index = 2;
                }
            }

            $_value = $items->item((int)$index);
            if ($_value) {
                $value = $_value->nodeValue;
            }
        }

        return $value;
    }

    /**
     * Sets a value by a field name.
     *
     * This method is called internally if a fields value is changed to keep the
     * XML data in sync. It should be used manually with care.
     *
     * @param string $fieldName
     * @param mixed $value
     * @param string $encoding
     * @return bool
     */
    public function setValue($fieldName, $value, $encoding = 'UTF-8')
    {
        if ($this->_setValueIsActive) {
            return false;
        }

        if ($encoding !== 'UTF-8') {
            if (is_array($value)) {
                // TODO: $encoding is not used here?!
                $value = array_map(['SetaPDF_Core_Encoding', 'convert'], $value);
            } elseif (is_string($value)) {
                $value = SetaPDF_Core_Encoding::convert($value, $encoding, 'UTF-8');
            }
        }

        $bridge = $this->getBridge();
        $dataNode = $bridge->getDataNodeByFieldName($fieldName);

        if ($dataNode === false) {
            return $this->_setValueInForm($fieldName, $value);
        }

        $this->_setValueIsActive = true;

        if ($this->_dataNodeUpdated === false) {
            $this->_dataNodeUpdated = $dataNode->nodeValue != $value;
        }

        $fieldNode = $bridge->getTemplateNode($fieldName);
        $document = $dataNode->ownerDocument;
        if (is_array($value)) {
            // empty data node
            while ($dataNode->childNodes->length > 0) {
                $dataNode->removeChild($dataNode->lastChild);
            }

            foreach ($value AS $key => $_value) {
                $value[$key] = $this->_evaluateValue($_value, $fieldNode);
            }

            foreach (array_unique($value) AS $_value) {
                $valueNode = $document->createElement('value');
                $valueNode->appendChild($document->createTextNode($_value));
                $dataNode->appendChild($valueNode);
            }

        } else {
            $nodeValue = $this->_evaluateValue($value, $fieldNode);
            $dataNode->nodeValue = '';
            if ($nodeValue !== '') {
                $dataNode->appendChild($document->createTextNode((string)$nodeValue));
            }
        }

        // if the field has a global binding we need to sync the AcroForm representations
        if (!$this->isDynamic()) {
            $fields = $this->_formFiller->getFields();

            $fieldNames = $bridge->getSameBoundFields($fieldName, true);
            foreach ($fieldNames AS $_fieldName) {
                $fields->get($_fieldName)->setValue($value);
            }

            if ($fields->isForwardValueActive() === false) {
                $fields->get($fieldName)->setValue($value, $encoding);
            }
        }

        $this->_setValueIsActive = false;

        return true;
    }

    /**
     * Creates the data node and sets the value in the form package.
     *
     * @param string $fieldName
     * @param mixed $value
     * @return bool
     * @throws SetaPDF_FormFiller_Exception
     */
    protected function _setValueInForm($fieldName, $value)
    {
        $bridge = $this->getBridge();
        $fieldNamesToTemplateNodes = $bridge->getAcroFormFieldNamesToTemplateNodes();

        $fieldNode = $fieldNamesToTemplateNodes[$fieldName];

        if (!$bridge->getNoneBindingNodes()->contains($fieldNode)) {
            return false;
        }

        $form = $this->_form;
        $domDocument = $form->ownerDocument;

        $xpath = new DOMXPath($domDocument);
        $ns = 'http://www.xfa.org/schema/xfa-form/2.8/';
        $xpath->registerNamespace('frm', $ns);

        $xpathsToFields = $bridge->getXPathsToFields();
        $parts = array_filter(explode('/', $xpathsToFields[$fieldNode]));

        $currentNode = $form;
        foreach ($parts AS $part) {
            $start = strpos($part, '[');
            $tagName = substr($part, 0, $start);
            $namePos = strpos($part, '[@name=');
            if ($namePos !== false) {
                $name = substr($part, $namePos + 7);
                $name = trim(substr($name, 0, strpos($name, ']')), '"\'');
            } else {
                $name = null;
            }

            while (($xpath->query('frm:' . $part, $currentNode)->length) === 0) {
                $_node = $domDocument->createElementNS($ns, $tagName);
                if ($name !== null) {
                    $_node->setAttribute('name', $name);
                }
                $currentNode->appendChild($_node);
            }

            $currentNode = $xpath->query('frm:' . $part, $currentNode)->item(0);
        }

        // empty data node (if it was already available)
        while ($currentNode->hasChildNodes()) {
            $child = $currentNode->lastChild;
            $currentNode->removeChild($child);
        }

        $tplXpath = new DOMXPath($fieldNode->ownerDocument);
        $tplXpath->registerNamespace('tpl', $fieldNode->namespaceURI);

        // radio button group
        if ($fieldNode->tagName === 'exclGroup') {
            $fields = $tplXpath->query('tpl:field', $fieldNode);
            foreach ($fields AS $field) {
                $_node = $domDocument->createElementNS($ns, 'field');
                if ($field->hasAttribute('name')) {
                    $_node->setAttribute('name', $field->getAttribute('name'));
                }

                $currentNode->appendChild($_node);

                $valueNode = $domDocument->createElementNS($ns, 'value');
                $valueNode->setAttribute('override', '1');
                $_node->appendChild($valueNode);

                $items = $tplXpath->query('tpl:items', $field);
                foreach ($items AS $item) {
                    $nodeValue = trim($item->nodeValue);
                    if ($nodeValue == $value) {
                        $contentTypeNode = $item->firstChild->tagName;

                        $contentNode = $domDocument->createElementNS($ns, $contentTypeNode);
                        $contentNode->appendChild($domDocument->createTextNode($value));

                        $valueNode->appendChild($contentNode);
                    }
                }
            }

            return true;
        }

        $valueNode = $domDocument->createElementNS($ns, 'value');
        $valueNode->setAttribute('override', '1');
        $currentNode->appendChild($valueNode);

        $uiType = $tplXpath->query('tpl:ui/tpl:*[1]', $fieldNode)->item(0);
        $valueType = 'text';

        if ($uiType !== null) {
            switch ($uiType->tagName) {
                case 'numericEdit':
                    $valueType = 'float';
                    break;
                case 'dateTimeEdit':
                    $valueType = 'dateTime';
                    break;
            }
        }

        $valueTypeNode = $tplXpath->query('tpl:value/tpl:*[1]', $fieldNode)->item(0);
        if ($valueTypeNode !== null) {
            $valueType = $valueTypeNode->tagName;
        }

        $typeNode = $domDocument->createElementNS($ns, $valueType);
        $typeNode->appendChild($domDocument->createTextNode($this->_evaluateValue($value, $fieldNode)));
        $valueNode->appendChild($typeNode);

        return true;
    }

    /**
     * Set the default value for a specific field.
     *
     * @param string $fieldName
     * @param mixed $value
     * @param string $encoding
     * @return boolean
     */
    public function setDefaultValue($fieldName, $value, $encoding = 'UTF-8')
    {
        if ($this->_setValueIsActive) {
            return false;
        }

        $bridge = $this->getBridge();
        $fieldNode = $bridge->getTemplateNode($fieldName);

        if (false === $fieldNode) {
            return false;
        }

        $document = $fieldNode->ownerDocument;
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('tpl', $fieldNode->namespaceURI);

        /**
         * possible types:
         * - text,
         * - date, time, and dateTime
         * - boolean, decimal, float, and integer: numeric
         * - image
         * - exData
         */

        // radio buttons groups needs to be handled especially
        if ($fieldNode->tagName === 'exclGroup') {
            /* Iterate over all fields, then check their items value and compare it with the given value.
             * If it matched, clone the whole contentType node and add it in a value node it to the field node.
             */
            $fields = $xpath->query('tpl:field', $fieldNode);
            foreach ($fields AS $field) {
                $items = $xpath->query('tpl:items', $field);
                foreach ($items AS $item) {
                    $nodeValue = $item->nodeValue;
                    if ($nodeValue == $value) {
                        $fieldNode = $field;
                        $contentTypeNode = $item->firstChild->cloneNode(true);
                    }
                }

                $valueNodeList = $xpath->query('tpl:value', $field);
                if ($valueNodeList->length > 0) {
                    $valueNode = $valueNodeList->item(0);
                    $valueNode->parentNode->removeChild($valueNode);
                }
            }
        }

        $valueNodeList = $xpath->query('tpl:value', $fieldNode);
        if ($valueNodeList->length === 0) {
            $valueNode = $document->createElementNS($fieldNode->namespaceURI, 'value');
            $fieldNode->appendChild($valueNode);
        } else {
            $valueNode = $valueNodeList->item(0);
        }

        if (isset($contentTypeNode)) {
            $valueNode->appendChild($contentTypeNode);

        } else {
            $contentTypeList = $xpath->query('tpl:*', $valueNode);
            if ($contentTypeList->length === 0) {
                $contentTypeNode = $document->createElementNS($fieldNode->namespaceURI, 'text');
                $valueNode->appendChild($contentTypeNode);
            } else {
                $contentTypeNode = $contentTypeList->item(0);
            }
        }

        $contentTypeNode->removeAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'nil');

        while ($contentTypeNode->childNodes->length > 0) {
            $contentTypeNode->removeChild($contentTypeNode->lastChild);
        }

        if ($value === null) {
            return true;
        }

        if (is_array($value)) {
            // It seems like XFA only supports a single value for default values.
            $value = current($value);
        }

        if ($encoding !== 'UTF-8') {
            $value = SetaPDF_Core_Encoding::convert($value, $encoding, 'UTF-8');
        }

        switch ($contentTypeNode->tagName) {
            case 'text':
                //
                $value = $this->_evaluateValue($value, $fieldNode);

                // Check for multiline:
                $isMulitline = $xpath->query('tpl:ui/tpl:textEdit[@multiLine=1]', $fieldNode)->length > 0;
                if (!$isMulitline) {
                    $value = str_replace(["\r", "\n"], ' ', $value);
                }
                $contentTypeNode->appendChild($document->createTextNode($value));
                break;
            case 'decimal':
                $value = rtrim(rtrim(sprintf('%.8F', $value), '0'), '.');
                $contentTypeNode->appendChild($document->createTextNode($value));
                break;
            case 'float':
                $contentTypeNode->appendChild($document->createTextNode((float)$value));
                break;
            case 'integer':
                $contentTypeNode->appendChild($document->createTextNode((int)$value));
                break;
            case 'boolean':
                $contentTypeNode->appendChild($document->createTextNode($value ? '1' : '0'));
                break;

            case 'date':
            case 'time':
            case 'dateTime':
            case 'image':
            case 'exData':
            default:
                // TODO
                $contentTypeNode->appendChild($document->createTextNode($value));
                break;
        }

        $this->_templateNodeUpdated = true;

        return true;
    }

    /**
     * Set a field to read-only or to "open".
     *
     * This method is called internally by the {@link SetaPDF_FormFiller_Field_AbstractField::setReadOnly()} method.
     *
     * @param $fieldName
     * @param $readOnly
     * @return bool
     */
    public function setReadOnly($fieldName, $readOnly)
    {
        if ($this->_setValueIsActive) {
            return false;
        }

        $bridge = $this->getBridge();
        $fieldNode = $bridge->getTemplateNode($fieldName);
        if ($fieldNode === false) {
            return false;
        }

        $current = '';
        $readOnly = $readOnly ? 'readOnly' : 'open';
        if ($fieldNode->hasAttribute('access')) {
            $current = (bool)$fieldNode->getAttribute('access');
        }

        if ($current !== $readOnly) {
            $fieldNode->setAttribute('access', $readOnly);
            $this->_templateNodeUpdated = true;
        }

        return true;
    }

    /**
     * Writes the data node back to the PDF document.
     *
     * @return bool
     */
    public function syncDataNode()
    {
        if ($this->_dataNodeUpdated === false) {
            return false;
        }

        $data = $this->getData(true);
        $xml = '';
        foreach ($data->childNodes AS $childNode) {
            if ($childNode instanceof DOMElement) {
                $xml .= $data->ownerDocument->saveXML($childNode);
            }
        }

        $this->setData($xml);

        return true;
    }

    /**
     * Syncornizes the XFA form field values with the corresponding AcroForm fields.
     *
     * If XML data were passed via the {@link SetaPDF_FormFiller_Xfa::setData()} method this method will ensure that
     * the data in the corresponding AcroForm fields representations will get updated.
     */
    public function syncAcroFormFields()
    {
        // We do not need to sync anything if it is a dynamic form
        if ($this->isDynamic()) {
            return;
        }

        $bridge = $this->getBridge();
        $fieldNodes = $bridge->getAcroFormFieldNamesToTemplateNodes();
        $fields = $this->_formFiller->getFields();

        foreach ($fieldNodes AS $name => $fieldNode) {
            if ($fieldNode->hasAttribute('presence')) {
                $presence = $fieldNode->getAttribute('presence');
                if ($presence === 'invisible' || $presence === 'hidden') {
                    continue;
                }
            }

            $dataNode = $bridge->getDataNodeByFieldName($name, false);
            // try to get default value
            $xpath = new DOMXPath($fieldNode->ownerDocument);
            $xpath->registerNamespace('tpl', $fieldNode->namespaceURI);
            $defaultDataNode = null;
            $defaultDataNodesList = $xpath->query('tpl:value/tpl:*', $fieldNode);
            if ($defaultDataNodesList->length > 0) {
                $defaultDataNode = $defaultDataNodesList->item(0);
            }

            if (!$dataNode && !$defaultDataNode) {
                continue;
            }

            if (!$dataNode) {
                $dataNode = $defaultDataNode;
            }

            try {
                $field = $fields->get($name);
            } catch (SetaPDF_FormFiller_Exception $e) {
                continue;
            }

            if ($field instanceof SetaPDF_FormFiller_Field_Signature) {
                continue;
            } elseif ($field instanceof SetaPDF_FormFiller_Field_List) {
                if ($field->isMultiSelect()) {
                    $xpath = new DOMXPath($dataNode->ownerDocument);
                    $childNodes = $xpath->query('*', $dataNode);
                    $value = $nodeValues = [];
                    foreach ($childNodes as $childNode) {
                        $nodeValues[] = $childNode->nodeValue;
                        if ($childNode->nodeValue !== '') {
                            $value[] = $childNode->nodeValue;
                        }
                    }

                    if (count($nodeValues) === 0 && $defaultDataNode) {
                        $value = $defaultDataNode->nodeValue;
                    }

                } else {
                    $value = $dataNode->nodeValue !== '' ? $dataNode->nodeValue : null;
                }
            } elseif (
                $field instanceof SetaPDF_FormFiller_Field_ButtonGroup
                || $field instanceof SetaPDF_FormFiller_Field_Combo
                || $field instanceof SetaPDF_FormFiller_Field_Button
            ) {
                $value = $dataNode->nodeValue;
                if ($value === '') {
                    $value = null;
                }
            } else {
                $value = $dataNode->nodeValue;
            }

            $this->_setValueIsActive = true;

            if ($fieldNode->hasAttribute('access')) {
                $access = $fieldNode->getAttribute('access');
                $field->setReadOnly($access != 'open');
            } else {
                $field->setReadOnly(false);
            }

            try {
                $field->setValue($value);
            } catch (BadFunctionCallException $e) {
                // A field that does not support setting of a value
            } catch (InvalidArgumentException $e) {
                /* if the exception is raised because of an unknown option in a combo field,
                 * we set the value explicity. That's how Adobe Reader handles this situation.
                 */
                if ($field instanceof SetaPDF_FormFiller_Field_Combo) {
                    $editable = $field->isEditable();
                    $field->setEditable(true);
                    $field->setValue($value);
                    $field->setEditable($editable, false);
                // in that case set the value to null
                } else {
                    $field->setValue(null);
                }
            }

            $this->_setValueIsActive = false;
        }
    }

    /**
     * Calculate and get the checksum for the form node.
     *
     * @return string
     * @throws SetaPDF_FormFiller_Exception
     */
    public function getFormNodeChecksum()
    {
        $sanitaizeXml = function($xml) {
            $out = '';
            $reader = new XMLReader();
            $reader->xml($xml);
            while ($reader->read())
            {
                switch ($reader->nodeType) {
                    case XMLReader::ELEMENT:
                        $tagName = $reader->name;
                        $tagIsEmpty = $reader->isEmptyElement;
                        $out .= '<' . $tagName;

                        if ($reader->hasAttributes) {
                            for ($i = 0, $c = $reader->attributeCount; $i < $c; $i++) {
                                $reader->moveToNextAttribute();
                                $out .= ' '
                                    . htmlspecialchars($reader->name, ENT_XML1, 'UTF-8')
                                    . '="' . htmlspecialchars($reader->value, ENT_XML1, 'UTF-8')
                                    . '"';
                            }
                        }
                        $out .= '>';

                        if ($tagIsEmpty) {
                            $out .= '</' . $tagName . '>';
                        }
                        break;
                    case XMLReader::TEXT:
                        $v = htmlspecialchars($reader->value, ENT_XML1, 'UTF-8');
                        $v = strtr($v, ["\r" => '&#xD;']);
                        $out .= $v;
                        break;
                    case XMLReader::SIGNIFICANT_WHITESPACE:
                        $out .= $reader->value;
                        break;
                    case XMLReader::CDATA:
                        $out .= '<![CDATA[' . $reader->value . ']]>';
                        break;
                    case XMLReader::COMMENT:
                        $out .= '<!--' . $reader->value . '-->';
                        break;
                    case XMLReader::PI:
                        $out .= $a = '<?' . $reader->name;
                        if ($reader->value) {
                            $out .= ' ' . $reader->value;
                        }
                        $out .= '?>';
                        break;
                    case XMLReader::END_ELEMENT:
                        $out .= '</' . $reader->name . '>';
                        break;
                    default:
                        throw new SetaPDF_Exception_NotImplemented(
                            'An unknow XML element was reached which is currently not supported to calculate the checksum.'
                        );
                }
            }

            return $out;
        };

        $domDocument = $this->getDomDocument();
        $xpath = new DOMXPath($domDocument);
        $xpath->registerNamespace('xfa', 'http://www.xfa.org/schema/xfa-data/1.0/');
        $xpath->registerNamespace('pdfs', 'http://ns.adobe.com/xtd/');

        $template = $this->getTemplate();
        $dataSets = $xpath->query('xfa:datasets')->item(0);

        $ctx = hash_init('sha1');
        hash_update($ctx, $sanitaizeXml($template->ownerDocument->saveXML($template)));
        if ($dataSets) {
            hash_update($ctx, $sanitaizeXml($dataSets->ownerDocument->saveXML($dataSets)));
        }

        return base64_encode(hash_final($ctx, true));
    }
}