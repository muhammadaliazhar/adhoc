<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Pages.php 1753 2022-06-28 14:42:54Z maximilian.kresse $
 */

/**
 * Class for handling PDF pages
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Catalog_Pages
    implements Countable
{
    /**
     * The catalog instance
     *
     * @var SetaPDF_Core_Document_Catalog
     */
    protected $_catalog;

    /**
     * The pages root object
     *
     * @var SetaPDF_Core_Type_IndirectObject
     */
    protected $_pagesRootObject;

    /**
     * The page count
     *
     * @var integer
     */
    protected $_pageCount = 0;

    /**
     * The current pages object while walking through the page tree
     *
     * @var SetaPDF_Core_Type_Dictionary
     */
    protected $_currentPagesObject;

    /**
     * An array holding the native indirect objects of pages
     *
     * @var array
     */
    protected $_pageObjects = [];

    /**
     * A helper array matching objects to page numbers
     *
     * @var array
     */
    protected $_pageObjectsToPageNumbers = [];

    /**
     * An array holding page instances
     *
     * @var SetaPDF_Core_Document_Page[]
     */
    protected $_pages = [];

    /**
     * Caches annotation object identifiers to page numbers
     *
     * @var array
     */
    protected $_annotationCache = [];

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Document_Catalog $catalog
     */
    public function __construct(SetaPDF_Core_Document_Catalog $catalog)
    {
        $this->_catalog = $catalog;
    }

    /**
     * Get the document instance.
     *
     * @return SetaPDF_Core_Document
     */
    public function getDocument()
    {
        return $this->_catalog->getDocument();
    }

    /**
     * Release memory / cycled references.
     */
    public function cleanUp()
    {
        $this->_catalog = null;
        $this->_pagesRootObject = null;
        $this->_currentPagesObject = null;
        foreach ($this->_pages AS $page) {
            $page->cleanUp();
        }
        $this->_pages = [];
        $this->_pageObjects = [];
        $this->_pageObjectsToPageNumbers = [];

        $this->_annotationCache = [];
    }

    /**
     * Returns the page count of the document.
     *
     * @return int
     * @throws SetaPDF_Core_Type_Exception
     * @throws SetaPDF_Core_SecHandler_Exception
     * @see Countable::count()
     * @noinspection PhpLanguageLevelInspection
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        if ($this->_pageCount === 0) {
            $this->resolvePagesRootObject();

            if ($this->_pagesRootObject === null) {
                return 0;
            }

            /**
             * @var SetaPDF_Core_Type_Dictionary $pagesRootDictionary
             */
            $pagesRootDictionary = $this->_pagesRootObject->ensure();
            SetaPDF_Core_Type_Dictionary::ensureType($pagesRootDictionary);
            $this->_pageCount = (int)SetaPDF_Core_Type_Numeric::ensureType(
                SetaPDF_Core_Type_Dictionary_Helper::getValue($pagesRootDictionary, 'Count')
            )->getValue();
        }

        return $this->_pageCount;
    }

    /**
     * Deletes a page.
     *
     * @param integer $pageNumber
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_IndirectReference_Exception
     * @throws SetaPDF_Core_Type_Exception
     * @throws SetaPDF_Core_Exception
     */
    public function deletePage($pageNumber)
    {
        $secHandler = $this->getDocument()->getSecHandler();
        if ($secHandler && !$secHandler->getPermission(SetaPDF_Core_SecHandler::PERM_ASSEMBLE)) {
            throw new SetaPDF_Core_SecHandler_Exception(
                sprintf('Deletion of pages is not allowed with this credentials (%s).', $secHandler->getAuthMode()),
                SetaPDF_Core_SecHandler_Exception::NOT_ALLOWED
            );
        }

        /* Workflow:
         * 1. Resolve the parent /Pages object
         * 2. Remove the reference to this page
         * 3. Subtract 1 from the /Count entry
         * 4. Walk up and subtract 1 from the parent /Count values
         *    until the root node is reached.
         * 5. Delete the page object
         */
        $pageObject = $this->getPagesIndirectObject($pageNumber);
        $pageDict = SetaPDF_Core_Type_Dictionary::ensureType($pageObject->ensure());
        $parentPages = SetaPDF_Core_Type_Dictionary::ensureType(
            SetaPDF_Core_Type_Dictionary_Helper::getValue($pageDict, 'Parent')
        );
        $kids = SetaPDF_Core_Type_Array::ensureType(SetaPDF_Core_Type_Dictionary_Helper::getValue($parentPages, 'Kids'));

        foreach ($kids->getValue() AS $key => $indirectReference) {
            if ($indirectReference instanceof SetaPDF_Core_Type_IndirectReference &&
                $indirectReference->getObjectIdent() === $pageObject->getObjectIdent()) {
                break;
            }
            unset($key);
        }

        if (isset($key)) {
            $kids->offsetUnset($key);

            while ($parentPages) {
                $currentCount = SetaPDF_Core_Type_Numeric::ensureType(
                    SetaPDF_Core_Type_Dictionary_Helper::getValue($parentPages, 'Count')
                )->getValue();
                $parentPages->offsetSet('Count', new SetaPDF_Core_Type_Numeric($currentCount - 1));

                $parentPages = $parentPages->offsetExists('Parent')
                    ? SetaPDF_Core_Type_Dictionary::ensureType(
                        SetaPDF_Core_Type_Dictionary_Helper::getValue($parentPages, 'Parent')
                    )->ensure(true)
                    : false;

                // TODO: If this node has no additional kids, it should be removed, too
            }

            unset($this->_pageObjectsToPageNumbers[$pageObject->getObjectIdent()]);

            $this->getDocument()->deleteObject($pageObject);
            unset($this->_pageObjects[$pageNumber - 1]);
            $this->_pageObjects = array_values($this->_pageObjects);

            unset($this->_pages[$pageNumber]);
            for ($i = $pageNumber; $i <= $this->_pageCount; $i++) {
                if (!isset($this->_pages[$i + 1])) {
                    continue;
                }

                $this->_pages[$i] = $this->_pages[$i + 1];
                unset($this->_pages[$i + 1]);
            }

            $this->_pageCount--;
        }
    }

    /**
     * Get a pages indirect object.
     *
     * @param integer $pageNumber
     * @return SetaPDF_Core_Type_IndirectObject
     * @throws SetaPDF_Core_Type_Exception
     * @throws SetaPDF_Core_Exception
     */
    public function getPagesIndirectObject($pageNumber)
    {
        return $this->_ensurePageObject($pageNumber);
    }

    /**
     * Get a page.
     *
     * @param integer $pageNumber
     * @return SetaPDF_Core_Document_Page
     * @throws SetaPDF_Core_Type_Exception
     * @throws SetaPDF_Core_Exception
     */
    public function getPage($pageNumber)
    {
        if (!isset($this->_pages[$pageNumber])) {
            $this->_pages[$pageNumber] = new SetaPDF_Core_Document_Page(
                $this->_ensurePageObject($pageNumber)
            );
        }
        return $this->_pages[$pageNumber];
    }

    /**
     * Get the last page.
     *
     * @return SetaPDF_Core_Document_Page
     * @throws SetaPDF_Core_Type_Exception
     * @throws SetaPDF_Core_Exception
     */
    public function getLastPage()
    {
        return $this->getPage($this->count());
    }

    /**
     * Extracts a page and prepares it for the usage in another document.
     *
     * This method is needed if a page should be extracted independently. For example the original
     * document should be modified after extraction and the page itself will be edited in the new
     * document (inherited attributes get flattened).
     *
     * @param integer $pageNumber
     * @param SetaPDF_Core_Document $document
     * @param boolean $returnPageInstance
     * @return SetaPDF_Core_Document_Page|SetaPDF_Core_Type_IndirectObject
     * @throws SetaPDF_Core_Type_Exception
     * @throws SetaPDF_Core_Exception
     */
    public function extract($pageNumber, SetaPDF_Core_Document $document, $returnPageInstance = true)
    {
        $indirectObject = $document->cloneIndirectObject($this->getPagesIndirectObject($pageNumber));

        if ($returnPageInstance === true) {
            return new SetaPDF_Core_Document_Page($indirectObject);
        }

        return $indirectObject;
    }

    /**
     * Find the page of an annotation object.
     *
     * @param SetaPDF_Core_Document_Page_Annotation|SetaPDF_Core_Type_IndirectObjectInterface $annotationOrIndirectObject
     * @return boolean|SetaPDF_Core_Document_Page
     * @throws SetaPDF_Core_Type_Exception
     * @throws SetaPDF_Core_Exception
     */
    public function getPageByAnnotation($annotationOrIndirectObject)
    {
        if ($annotationOrIndirectObject instanceof SetaPDF_Core_Document_Page_Annotation) {
            $annotationOrIndirectObject = $annotationOrIndirectObject->getIndirectObject($this->getDocument());
        }

        if (!$annotationOrIndirectObject instanceof SetaPDF_Core_Type_IndirectObjectInterface) {
            throw new InvalidArgumentException(
                'Argument needs to be an instance of SetaPDF_Core_Document_Page_Annotation or ' .
                'SetaPDF_Core_Type_IndirectObjectInterface.'
            );
        }

        $searchFor = $annotationOrIndirectObject->getObjectIdent();
        if (isset($this->_annotationCache[$searchFor])) {
            return $this->getPage($this->_annotationCache[$searchFor]);
        }

        for ($pageNumber = 1, $n = $this->count(); $pageNumber <= $n; $pageNumber++) {
            $page = $this->getPage($pageNumber);
            $annotations = $page->getAnnotations()->getArray();
            if ($annotations === false) {
                continue;
            }

            foreach ($annotations->getValue() as $annotation) {
                if (!$annotation instanceof SetaPDF_Core_Type_IndirectObjectInterface) {
                    continue;
                }

                $ident = $annotation->getObjectIdent();
                if (isset($this->_annotationCache[$ident])) {
                    continue;
                }

                $this->_annotationCache[$ident] = $pageNumber;
                if ($ident === $searchFor) {
                    return $page;
                }
            }
        }

        return false;
    }

    /**
     * Get the page number by a page indirect object.
     *
     * If the object is not found in the page tree, false is returned.
     *
     * @param SetaPDF_Core_Type_IndirectObjectInterface $indirectObject
     * @return boolean|integer
     * @throws SetaPDF_Core_Type_Exception
     * @throws SetaPDF_Core_Document_ObjectNotDefinedException
     * @throws SetaPDF_Core_Document_ObjectNotFoundException
     * @throws SetaPDF_Core_Exception
     */
    public function getPageNumberByIndirectObject(
        SetaPDF_Core_Type_IndirectObjectInterface $indirectObject
    ) {
        // If the indirect object is from another origin document we need to translate the object/reference
        $document = $this->getDocument();
        if ($indirectObject->getOwnerPdfDocument() &&
            $document->getInstanceIdent() !== $indirectObject->getOwnerPdfDocument()->getInstanceIdent()
        ) {
            $identData = $document->getIdForObject($indirectObject);
            $indirectObject = $document->resolveIndirectObject($identData[0], $identData[1]);
        }

        $ident = $indirectObject->getObjectIdent();
        if (isset($this->_pageObjectsToPageNumbers[$ident])) {
            return $this->_pageObjectsToPageNumbers[$ident] + 1;
        }

        for ($pageNumber = 1, $n = $this->count(); $pageNumber <= $n; $pageNumber++) {
            if (isset($this->_pages[$pageNumber])) {
                continue;
            }

            $this->_ensurePageObject($pageNumber);
            if (isset($this->_pageObjectsToPageNumbers[$ident])) {
                return $this->_pageObjectsToPageNumbers[$ident] + 1;
            }
        }

        return false;
    }

    /**
     * Get a page by its indirect object.
     *
     * @param SetaPDF_Core_Type_IndirectObject|SetaPDF_Core_Type_IndirectReference $indirectObject
     * @return SetaPDF_Core_Document_Page|false
     * @throws SetaPDF_Core_Type_Exception
     * @throws SetaPDF_Core_Exception
     * @throws SetaPDF_Core_Document_ObjectNotDefinedException
     * @throws SetaPDF_Core_Document_ObjectNotFoundException
     */
    public function getPageByIndirectObject($indirectObject)
    {
        $pageNumber = $this->getPageNumberByIndirectObject($indirectObject);
        if ($pageNumber === false) {
            return false;
        }

        return $this->getPage($pageNumber);
    }

    /**
     * Get the page number by a page object.
     *
     * If the object is not found in the page tree, false is returned.
     *
     * @param SetaPDF_Core_Document_Page $page
     * @return boolean|integer
     * @throws SetaPDF_Core_Type_Exception
     * @throws SetaPDF_Core_Document_ObjectNotDefinedException
     * @throws SetaPDF_Core_Document_ObjectNotFoundException
     * @throws SetaPDF_Core_Exception
     */
    public function getPageNumberByPageObject(SetaPDF_Core_Document_Page $page)
    {
        return $this->getPageNumberByIndirectObject($page->getPageObject());
    }

    /**
     * This method makes sure that all pages are read.
     *
     * It walks the complete page tree to cache/get all page objects in one iteration.
     * This method should be used if all pages of a document should be handled. It is
     * much faster than using the random access.
     *
     * @throws BadMethodCallException
     * @throws SetaPDF_Core_Exception
     */
    public function ensureAllPageObjects()
    {
        if (count($this->_pageObjects) > 0) {
            throw new BadMethodCallException(
                sprintf(
                    'The method "%s" could only be called if no page object was resolved before.', __METHOD__
                )
            );
        }

        $this->_ensureAllPageObjects($this->resolvePagesRootObject());
    }

    /**
     * Method to extract page objects recursively.
     *
     * @param SetaPDF_Core_Type_IndirectObjectInterface $node
     * @throws SetaPDF_Core_Type_Exception
     */
    protected function _ensureAllPageObjects(SetaPDF_Core_Type_IndirectObjectInterface $node)
    {
        $nodeDict = SetaPDF_Core_Type_Dictionary::ensureType($node->ensure());

        $kids = SetaPDF_Core_Type_Array::ensureType(SetaPDF_Core_Type_Dictionary_Helper::getValue(
            $nodeDict, 'Kids', SetaPDF_Core_Type_Null::getInstance()
        )->ensure());

        for ($i = 0, $n = $kids->count(); $i < $n; $i++) {
            try {
                $nodeReference = SetaPDF_Core_Type_IndirectReference::ensureType($kids->offsetGet($i));
                $node = SetaPDF_Core_Type_IndirectObject::ensureType($nodeReference->getValue());
            } catch (SetaPDF_Core_Type_Exception $e) {
                $this->_pageObjects[] = null;
                continue;
            }

            $nodeDict = SetaPDF_Core_Type_Dictionary::ensureType($node->ensure());

            $type = SetaPDF_Core_Type_Dictionary_Helper::getValue(
                $nodeDict, 'Type', SetaPDF_Core_Type_Null::getInstance()
            )->getValue();

            if ($type !== 'Pages') {
                $this->_pageObjects[] = $node;
                $this->_pageObjectsToPageNumbers[$node->getObjectIdent()] = count($this->_pageObjects) - 1;
            } else {
                $this->_ensureAllPageObjects($node);
            }
        }
    }

    /**
     * Ensures that a page object is read and available in the $_pageObjects property.
     *
     * @param integer $pageNumber
     * @return SetaPDF_Core_Type_IndirectObject
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_Type_Exception
     * @throws SetaPDF_Core_Exception
     */
    protected function _ensurePageObject($pageNumber)
    {
        if (!is_numeric($pageNumber)) {
            throw new InvalidArgumentException(
                'Page number needs to be a number.'
            );
        }

        $pageNumber = (int)$pageNumber;
        if ($pageNumber < 1 || $pageNumber > $this->count()) {
            throw new InvalidArgumentException(
                sprintf(
                    'Page number "%s" out of available page range (1 - %s)',
                    $pageNumber, $this->count()
                )
            );
        }

        --$pageNumber;

        if (!isset($this->_pageObjects[$pageNumber])) {
            if ($pageNumber > ($this->count() / 2)) {
                $this->_readPageBackwards($pageNumber);
            } else {
                $this->_readPage($pageNumber);
            }

            $pageObject = $this->_pageObjects[$pageNumber];
            $this->_pageObjectsToPageNumbers[$pageObject->getObjectIdent()] = $pageNumber;
        }

        return SetaPDF_Core_Type_IndirectObject::ensureType($this->_pageObjects[$pageNumber]);
    }

    /**
     * This method checks an entry in a Kids array for valid values and repairs it (if possible).
     *
     * @param SetaPDF_Core_Type_Array $kids
     * @param integer $offset
     * @return SetaPDF_Core_Type_IndirectObject
     * @throws SetaPDF_Core_Exception
     */
    private function _ensureIndirectObjectAndDictionaryAndType(SetaPDF_Core_Type_Array $kids, $offset)
    {
        $value = $kids->offsetGet($offset);
        if ($value === null) {
            throw new SetaPDF_Core_Exception('Invalid object type in Pages array.');
        }

        if (!$value instanceof SetaPDF_Core_Type_IndirectObjectInterface) {
            if ($value instanceof SetaPDF_Core_Type_Dictionary) {
                $kids->offsetSet($offset, $this->getDocument()->createNewObject($value));
                $value = $kids->offsetGet($offset);
            } else {
                throw new SetaPDF_Core_Exception('Invalid object type in Pages array.');
            }
        }

        $nodeDict = SetaPDF_Core_Type_Dictionary::ensureType($value->ensure());

        if (!$nodeDict->offsetExists('Type')) {
            throw new SetaPDF_Core_Exception('Invalid object type in Pages array.');
        }

        return $value->getValue();
    }

    /**
     * Resolves a page object by walking forwards through the page tree.
     *
     * This method is optimized, to take the fastest way through the page tree, beginning at
     * the pages root node. The page tree will be walked forward.
     *
     * @param integer $pageNumber The original page number - 1
     * @throws SetaPDF_Core_Exception
     */
    protected function _readPage($pageNumber)
    {
        $pageNumber = (int)$pageNumber;
        if (isset($this->_pageObjects[$pageNumber])) {
            return;
        }

        $node = $this->resolvePagesRootObject();
        if ($node === null) {
            throw new SetaPDF_Core_Exception('Pages root object does not exists.');
        }

        $nodeDict = SetaPDF_Core_Type_Dictionary::ensureType($node->ensure());
        $offset = 0;

        while (1) {
            $count = SetaPDF_Core_Type_Numeric::ensureType(
                SetaPDF_Core_Type_Dictionary_Helper::getValue($nodeDict, 'Count')
            )->getValue();
            $kids = SetaPDF_Core_Type_Array::ensureType(
                SetaPDF_Core_Type_Dictionary_Helper::getValue($nodeDict, 'Kids')
            );

            $kidsCount = $kids->count();
            if ($count === $kidsCount) {
                $node = $this->_ensureIndirectObjectAndDictionaryAndType($kids, $pageNumber - $offset);
                $nodeDict = SetaPDF_Core_Type_Dictionary::ensureType($node->ensure());

                $type = SetaPDF_Core_Type_Dictionary_Helper::getValue(
                    $nodeDict, 'Type', SetaPDF_Core_Type_Null::getInstance()
                )->getValue();

                if ($type !== 'Pages') {
                    $this->_pageObjectsToPageNumbers[$node->getObjectIdent()] = $pageNumber;
                    $this->_pageObjects[$pageNumber] = $node;
                    break;
                }
            }

            for ($i = 0, $n = $kidsCount; $i < $n; $i++) {
                $node = $this->_ensureIndirectObjectAndDictionaryAndType($kids, $i);
                $nodeDict = SetaPDF_Core_Type_Dictionary::ensureType($node->ensure());

                $type = SetaPDF_Core_Type_Dictionary_Helper::getValue(
                    $nodeDict, 'Type', SetaPDF_Core_Type_Null::getInstance()
                )->getValue();

                if ($type !== 'Pages') {
                    $this->_pageObjectsToPageNumbers[$node->getObjectIdent()] = $offset;
                    $this->_pageObjects[$offset++] = $node;
                    if ($offset - 1 === $pageNumber) {
                        break 2;
                    }

                    continue;
                }

                $tmpOffset = (int)SetaPDF_Core_Type_Numeric::ensureType(
                    SetaPDF_Core_Type_Dictionary_Helper::getValue($nodeDict, 'Count')
                )->getValue();

                // Check if this Kids Array is the correct path/way.
                if ($offset + $tmpOffset > $pageNumber) {
                    break;
                }

                $offset += $tmpOffset;
            }
        }
    }

    /**
     * Resolves a page object by walking backwards through the page tree.
     *
     * This method is optimized to take the fastest way through the page tree,
     * beginning at the pages root node. The page tree will be walked forward.
     *
     * @param integer $pageNumber
     * @throws SetaPDF_Core_Exception
     */
    protected function _readPageBackwards($pageNumber)
    {
        $pageNumber = (int)$pageNumber;
        if (isset($this->_pageObjects[$pageNumber])) {
            return;
        }

        $pageCount = $this->count();

        $node = $this->resolvePagesRootObject();
        if ($node === null) {
            throw new SetaPDF_Core_Exception('Pages root object does not exists.');
        }

        $nodeDict = SetaPDF_Core_Type_Dictionary::ensureType($node->ensure());
        $offset = $pageCount - 1;

        while (1) {
            $count = SetaPDF_Core_Type_Numeric::ensureType(
                SetaPDF_Core_Type_Dictionary_Helper::getValue($nodeDict, 'Count')
            )->getValue();
            $kids = SetaPDF_Core_Type_Array::ensureType(
                SetaPDF_Core_Type_Dictionary_Helper::getValue($nodeDict, 'Kids')
            );

            $kidsCount = $kids->count();
            if ($count === $kidsCount) {
                $node = $this->_ensureIndirectObjectAndDictionaryAndType(
                    $kids,
                    $pageNumber - $offset + $kidsCount - 1
                );
                $nodeDict = SetaPDF_Core_Type_Dictionary::ensureType($node->ensure());

                $type = SetaPDF_Core_Type_Dictionary_Helper::getValue(
                    $nodeDict, 'Type', SetaPDF_Core_Type_Null::getInstance()
                )->getValue();

                if ($type !== 'Pages') {
                    $this->_pageObjectsToPageNumbers[$node->getObjectIdent()] = $pageNumber;
                    $this->_pageObjects[$pageNumber] = $node;
                    break;
                }
            }

            for ($n = $kidsCount - 1; 0 <= $n; $n--) {
                $node = $this->_ensureIndirectObjectAndDictionaryAndType($kids, $n);
                $nodeDict = SetaPDF_Core_Type_Dictionary::ensureType($node->ensure());
                $type = SetaPDF_Core_Type_Dictionary_Helper::getValue(
                    $nodeDict, 'Type', SetaPDF_Core_Type_Null::getInstance()
                )->getValue();

                if ($type !== 'Pages') {
                    $this->_pageObjectsToPageNumbers[$node->getObjectIdent()] = $offset;
                    $this->_pageObjects[$offset--] = $node;
                    if ($offset + 1 === $pageNumber) {
                        break 2;
                    }

                    continue;
                }

                $tmpOffset = (int)SetaPDF_Core_Type_Numeric::ensureType(
                    SetaPDF_Core_Type_Dictionary_Helper::getValue($nodeDict, 'Count')
                )->getValue();

                if ($offset - $tmpOffset < $pageNumber) {
                    break;
                }

                $offset -= $tmpOffset;
            }
        }
    }

    /**
     * Resolves the root page tree node.
     *
     * @param boolean $create
     * @return SetaPDF_Core_Type_IndirectObject
     * @throws SetaPDF_Core_Type_Exception
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function resolvePagesRootObject($create = false)
    {
        if ($this->_pagesRootObject === null) {
            $catalog = $this->getDocument()->getCatalog()->getDictionary($create);
            if ($catalog === null) {
                return null;
            }

            if (!$catalog->offsetExists('Pages')) {
                if ($create === false) {
                    return null;
                }

                $catalog->offsetSet('Pages', $this->getDocument()->createNewObject(
                    new SetaPDF_Core_Type_Dictionary([
                        'Type' => new SetaPDF_Core_Type_Name('Pages', true),
                        'Kids' => new SetaPDF_Core_Type_Array(),
                        'Count' => new SetaPDF_Core_Type_Numeric(0)
                    ])
                ));
            }

            $pagesReference = SetaPDF_Core_Type_IndirectReference::ensureType($catalog->getValue('Pages'));
            $pagesRootObject = SetaPDF_Core_Type_IndirectObject::ensureType($pagesReference->getValue());

            $this->_pagesRootObject = $pagesRootObject;
        }

        return $this->_pagesRootObject;
    }

    /**
     * Create a page.
     *
     * @param string|array $format The page format. See constants in {@link SetaPDF_Core_PageFormats} and the
     *                             {@link SetaPDF_Core_PageFormats::getFormat() getFormat()} method.
     * @param string $orientation The orientation. See constants in {@link SetaPDF_Core_PageFormats}.
     * @param boolean $append Whether the page should be appended to the page tree or not.
     * @return SetaPDF_Core_Document_Page
     * @throws SetaPDF_Core_Exception
     */
    public function create($format, $orientation = SetaPDF_Core_PageFormats::ORIENTATION_PORTRAIT, $append = true)
    {
        $page = SetaPDF_Core_Document_Page::create(
            $this->getDocument(),
            [
                SetaPDF_Core_PageFormats::getAsBoundary($format, $orientation, SetaPDF_Core_PageBoundaries::MEDIA_BOX)
            ]
        );

        if ($append) {
            $this->append($page);
        }

        return $page;
    }

    /**
     * Ensures that the parameter ends in an array of SetaPDF_Core_Document_Page instances.
     *
     * @param SetaPDF_Core_Document_Page|SetaPDF_Core_Document_Catalog_Pages|array $pages
     * @return SetaPDF_Core_Document_Page[]
     * @throws SetaPDF_Core_Exception
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    private function _ensurePagesArray($pages)
    {
        if ($pages instanceof self) {
            $_pages = [];
            for ($i = 1; $i <= $pages->count(); $i++) {
                $_pages[] = $pages->getPage($i);
            }
            $pages = $_pages;
            unset($_pages);
        }

        if (!is_array($pages)) {
            $pages = [$pages];
        }

        return $pages;
    }

    /**
     * Append pages to the existing pages.
     *
     * @param SetaPDF_Core_Document_Page|SetaPDF_Core_Document_Catalog_Pages|array $pages
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_Type_Exception
     * @throws SetaPDF_Core_Exception
     */
    public function append($pages /*, $pageNumber = null*/)
    {
        $secHandler = $this->getDocument()->getSecHandler();
        if ($secHandler && !$secHandler->getPermission(SetaPDF_Core_SecHandler::PERM_ASSEMBLE)) {
            throw new SetaPDF_Core_SecHandler_Exception(
                sprintf('Adding pages is not allowed with this credentials (%s).', $secHandler->getAuthMode()),
                SetaPDF_Core_SecHandler_Exception::NOT_ALLOWED
            );
        }

        $pages = $this->_ensurePagesArray($pages);

        // if (null == $pageNumber)
        $pageNumber = $this->count();

        $pageCount = $this->count();

        if ($pageCount === 0) {
            $this->resolvePagesRootObject(true);
            $parent = $this->_pagesRootObject;
        } else {
            $lastPage = SetaPDF_Core_Type_Dictionary::ensureType($this->getPagesIndirectObject($pageNumber)->ensure());
            $parent = SetaPDF_Core_Type_IndirectObject::ensureType(
                SetaPDF_Core_Type_IndirectReference::ensureType($lastPage->getValue('Parent'))->getValue()
            );
        }

        $parent->observe();

        $parentDict = SetaPDF_Core_Type_Dictionary::ensureType($parent->ensure(true));
        $kids = SetaPDF_Core_Type_Array::ensureType(
            SetaPDF_Core_Type_Dictionary_Helper::getValue($parentDict, 'Kids')
        );

        $newPagesCount = count($pages);

        /** @noinspection ForeachInvariantsInspection */
        for ($i = 0; $i < $newPagesCount; $i++) {
            if (!($pages[$i] instanceof SetaPDF_Core_Document_Page)) {
                throw new InvalidArgumentException(
                    'Parameter have to be an array of SetaPDF_Core_Document_Page instances.'
                );
            }

            $pageObject = $pages[$i]->getPageObject();
            $kids->offsetSet(null, $pageObject);
            $pageDict = SetaPDF_Core_Type_Dictionary::ensureType($pageObject->ensure());

            if (
                !$pageDict->offsetExists('Parent') ||
                SetaPDF_Core_Type_IndirectReference::ensureType($pageDict->getValue('Parent'))->getObjectIdent() !== $parent->getObjectIdent()
            ) {
                $pageDict->offsetSet(
                    'Parent', new SetaPDF_Core_Type_IndirectReference($parent)
                );
            }

            $this->_pageObjectsToPageNumbers[$pageObject->getObjectIdent()] = $this->_pageCount;
            $this->_pageObjects[$this->_pageCount++] = $pageObject;
            $this->_pages[$pageCount + $i + 1] = $pages[$i];
        }

        // Update Count values
        while ($parentDict !== null) {
            $countValue = SetaPDF_Core_Type_Numeric::ensureType(
                SetaPDF_Core_Type_Dictionary_Helper::getValue($parentDict, 'Count')
            );

            $countValue->setValue($countValue->getValue() + $newPagesCount);

            if ($parentDict->offsetExists('Parent')) {
                $parentDict = SetaPDF_Core_Type_Dictionary::ensureType(
                    SetaPDF_Core_Type_Dictionary_Helper::getValue($parentDict, 'Parent')
                );
            } else {
                $parentDict = null;
            }
        }
    }

    /**
     * Prepend pages to the existing pages.
     *
     * @param SetaPDF_Core_Document_Catalog_Pages|SetaPDF_Core_Document_Page[]|SetaPDF_Core_Document_Page $pages
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_Type_Exception
     * @throws SetaPDF_Core_Exception
     */
    public function prepend($pages /*, $pageNumber = null*/)
    {
        $secHandler = $this->getDocument()->getSecHandler();
        if ($secHandler && !$secHandler->getPermission(SetaPDF_Core_SecHandler::PERM_ASSEMBLE)) {
            throw new SetaPDF_Core_SecHandler_Exception(
                sprintf('Adding pages is not allowed with this credentials (%s).', $secHandler->getAuthMode()),
                SetaPDF_Core_SecHandler_Exception::NOT_ALLOWED
            );
        }

        $pageCount = $this->count();
        if ($pageCount === 0) {
            $this->append($pages);
            return;
        }

        $pages = $this->_ensurePagesArray($pages);

        // if (null == $pageNumber)
        $pageNumber = 1;

        /**
         * @var SetaPDF_Core_Type_Dictionary $lastPage
         */
        $lastPage = $this->getPagesIndirectObject($pageNumber)->ensure();
        $parent = SetaPDF_Core_Type_IndirectObject::ensureType(
            SetaPDF_Core_Type_IndirectReference::ensureType($lastPage->getValue('Parent'))->getValue()
        );

        $parent->observe();

        $parentDict = SetaPDF_Core_Type_Dictionary::ensureType($parent->ensure(true));
        $kids = SetaPDF_Core_Type_Array::ensureType(
            SetaPDF_Core_Type_Dictionary_Helper::getValue($parentDict, 'Kids')
        );

        $newPagesCount = count($pages);
        $offset = $newPagesCount - 1;
        $pageObjects = $this->_pageObjects;
        $this->_pageObjects = [];
        foreach ($pageObjects AS $key => $page) {
            $this->_pageObjects[$key + $newPagesCount] = $page;
            $this->_pageObjectsToPageNumbers[$page->getObjectIdent()] = $key + $newPagesCount;
        }

        for ($i = $pageCount; $i > 0; $i--) {
            if (!isset($this->_pages[$i])) {
                continue;
            }

            $this->_pages[$i + $newPagesCount] = $this->_pages[$i];
            unset($this->_pages[$i]);
        }

        for ($i = $offset; 0 <= $i; $i--) {
            if (!($pages[$i] instanceof SetaPDF_Core_Document_Page)) {
                throw new InvalidArgumentException(
                    'Parameter have to be an array of SetaPDF_Core_Document_Page instances.'
                );
            }
            $pageObject = $pages[$i]->getPageObject();
            $kids->unshift($pageObject);
            $pageDict = SetaPDF_Core_Type_Dictionary::ensureType($pageObject->ensure());
            $pageDict->offsetSet(
                'Parent', new SetaPDF_Core_Type_IndirectReference($parent)
            );

            $this->_pages[$i + 1] = $pages[$i];
            $this->_pageObjects[$i] = $pageObject;
            $this->_pageObjectsToPageNumbers[$pageObject->getObjectIdent()] = $i;
            $this->_pageCount++;
        }

        // Update Count values
        while ($parentDict !== null) {
            $countValue = SetaPDF_Core_Type_Numeric::ensureType(
                SetaPDF_Core_Type_Dictionary_Helper::getValue($parentDict, 'Count')
            );

            $countValue->setValue($countValue->getValue() + $newPagesCount);

            if ($parentDict->offsetExists('Parent')) {
                $parentDict = SetaPDF_Core_Type_Dictionary::ensureType(
                    SetaPDF_Core_Type_Dictionary_Helper::getValue($parentDict, 'Parent')
                );
            } else {
                $parentDict = null;
            }
        }
    }
}
