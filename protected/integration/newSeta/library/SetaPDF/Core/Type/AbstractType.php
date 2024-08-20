<?php 
/**
 * This file is part of the SetaPDF-Core Component
 * 
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Type
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: AbstractType.php 1735 2022-06-02 11:47:45Z jan.slabon $
 */

/**
 * Abstract class for all PDF types 
 * 
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Type
 * @license    https://www.setasign.com/ Commercial
 */
abstract class SetaPDF_Core_Type_AbstractType implements SplSubject
{
    /**
     * The Objects to notify on any change
     *
     * This will be the PDF document or another value holding
     * this one. Initially this will be an array.
     * 
     * @var array
     */
    protected $_observers = [];
    
    /**
     * Defines if this object is under observation
     * 
     * @var bool
     */
    protected $_observed = false;

    /**
     * Parses a php value to a pdf string and writes it into a writer.
     *
     * PHP data type     -> PDF data type
     *
     * Null              -> {@link SetaPDF_Core_Type_Null}
     *
     * Boolean           -> {@link SetaPDF_Core_Type_Boolean}
     *
     * Integer/Double    -> {@link SetaPDF_Core_Type_Numeric}
     *
     * String            -> {@link SetaPDF_Core_Type_String} or {@link SetaPDF_Core_Type_Name}(if the string starts with "/")
     *
     * Indexed array     -> {@link SetaPDF_Core_Type_Array}
     *
     * Associative array -> {@link SetaPDF_Core_Type_Dictionary}
     *
     * @param SetaPDF_Core_WriteInterface $writer
     * @param mixed $value
     * @throws InvalidArgumentException
     */
    public static function writePdfString(SetaPDF_Core_WriteInterface $writer, $value)
    {
        switch (strtolower(gettype($value))) {
            case 'integer':
            case 'double':
                SetaPDF_Core_Type_Numeric::writePdfString($writer, $value);
                return;

            case 'string':
                SetaPDF_Core_Type_String::writePdfString($writer, $value);
                return;

            case 'array':
                if (array_values($value) === $value) {
                    SetaPDF_Core_Type_Array::writePdfString($writer, $value);
                    return;
                }
                SetaPDF_Core_Type_Dictionary::writePdfString($writer, $value);
                return;

            case 'boolean':
                SetaPDF_Core_Type_Boolean::writePdfString($writer, $value);
                return;

            case 'null':
                SetaPDF_Core_Type_Null::writePdfString($writer, null);
                return;

            default:
                throw new InvalidArgumentException('Unsupported var type: ' . gettype($value));
        }
    }

    /**
     * Ensure that a value is an instance of a specific PDF type.
     *
     * @param string $type
     * @param mixed $value
     * @param string $errorMessage
     * @return mixed
     * @throws SetaPDF_Core_Type_Exception
     */
    protected static function _ensureType($type, $value, $errorMessage, $ensure = true)
    {
        if ($ensure && $value instanceof self) {
            try {
                $value = $value->ensure(true);
            } catch (Exception $e) {
                throw new SetaPDF_Core_Type_Exception(
                    $errorMessage,
                    SetaPDF_Core_Type_Exception::INVALID_DATA_TYPE,
                    $e
                );
            }
        }

        if (!($value instanceof $type)) {
            throw new SetaPDF_Core_Type_Exception(
                $errorMessage,
                SetaPDF_Core_Type_Exception::INVALID_DATA_TYPE
            );
        }

        return $value;
    }

    /**
     * Ensures that the passed value is a SetaPDF_Core_Type_AbstractType instance.
     *
     * @param mixed $value
     * @return self
     * @throws SetaPDF_Core_Type_Exception
     */
    public static function ensureType($value)
    {
        return self::_ensureType(self::class, $value, 'AbsractType value expected.');
    }

    /**
     * Ensures that the passed value is an instance of the passed type.
     *
     * @param string $type
     * @param mixed $value
     * @return SetaPDF_Core_Type_AbstractType
     * @throws SetaPDF_Core_Type_Exception
     */
    public static function ensureWithType($type, $value)
    {
        return self::_ensureType($type, $value, $type . ' value expected.');
    }

    /**
     * Checks if the type of a value is the expected one.
     *
     * Indirect references will be evaluated if `$ensure` is set to `true`.
     *
     * @param string $type
     * @param mixed $value
     * @param bool $ensure
     * @return bool
     */
    public static function is($type, $value, $ensure = true)
    {
        if ($ensure && $value instanceof self) {
            $value = $value->ensure(true);
        }

        return ($value instanceof $type);
    }

    /**
     * The constructor.
     */
    public function __construct()
    {
        unset($this->_observed);
    }
    
    /**
     * Implementation of __clone().
     */
    public function __clone()
    {
        $this->_observers = [];
        unset($this->_observed);
    }

    /**
     * Clone the object recursively in the context of a document.
     *
     * @param SetaPDF_Core_Document $document
     * @return SetaPDF_Core_Type_AbstractType
     */
    public function deepClone(SetaPDF_Core_Document $document)
    {
        return clone $this;
    }
    
    /**
     * Implementation of __sleep.
     * 
     * We remove the observers from all elements because they will get read if they are
     * waked up in an observed object.
     * 
     * @return array
     */
    public function __sleep()
    {
        $props = get_object_vars($this);
        unset($props['_observers']);
        return array_keys($props);
    }
    
    /**
     * Implementation of __wakeup.
     * 
     * Unset the observed flag.
     */
    public function __wakeup()
    {
        unset($this->_observed);
    }
    
    /**
     * Add an observer to the object.
     * 
     * Implementation of the Observer Pattern.
     * 
     * @param SplObserver $observer
     */
    #[\ReturnTypeWillChange]
    public function attach(SplObserver $observer)
    {
        if 
        (
            // Check if at least one observer is added
            !isset($this->_observers[0]) ||
            // this will be reached only if a value is 
            // copied between documents or objects.
            !in_array($observer, $this->_observers, true)
        ) 
        {
            $this->_observers[] = $observer;
            $this->_observed = true;
        }
    }
    
    /**
     * Checks if this object is observed.
     * 
     * @return bool
     */
    public function isObserved()
    {
        return isset($this->_observed);
    }
    
    /**
     * Detach an observer from the object.
     * 
     * Implementation of the Observer Pattern.
     * 
     * @param SplObserver $observer
     */
    #[\ReturnTypeWillChange]
    public function detach(SplObserver $observer)
    {
        if (isset($this->_observed)) {
            if (false !== ($key = array_search($observer, $this->_observers, true))) {
                unset($this->_observers[$key]);
            }
            
            $this->_observers = array_values($this->_observers);
            if (count($this->_observers) == 0) {
                unset($this->_observed);
            }
        }
    }

    /**
     * Detach all observers from this object.
     *
     * Be careful with this method!!!
     *
     * @ignore
     */
    public function detachAll()
    {
        if (isset($this->_observed)) {
            $this->_observers = [];
            unset($this->_observed);
        }
    }

    /**
     * Notifies all attached observers.
     * 
     * Implementation of the Observer Pattern.
     *
     * Has to be called by any method that changes a value.
     */
    #[\ReturnTypeWillChange]
    public function notify()
    {
        // without this a zend_guard 5.2 encrypted package will delete the $this->_observers array after running through
        $observers = $this->_observers;

        foreach ($observers AS $observer) {
            $observer->update($this);
        }
    }

    /**
     * Returns the main value.
     * 
     * This method is used for automatically resolving of
     * indirect references.
     *
     * @param bool|null $forceObservation
     * @return SetaPDF_Core_Type_AbstractType
     */
    public function ensure($forceObservation = null)
    {
        return $this;
    }
    
    /**
     * Sets the value of the PDF type.
     *
     * @param mixed $value
     */
    abstract public function setValue($value);
    
    /**
     * Gets the PDF value.
     *
     * @return mixed
     */
    abstract public function getValue();
    
    /**
     * Returns the type as a formatted PDF string.
     *
     * @param SetaPDF_Core_Document $pdfDocument
     * @return string
     */
    abstract public function toPdfString(SetaPDF_Core_Document $pdfDocument = null);

    /**
     * Writes the type as a formatted PDF string to the document.
     *
     * @param SetaPDF_Core_Document $pdfDocument
     */
    abstract public function writeTo(SetaPDF_Core_Document $pdfDocument);

    /**
     * Converts the PDF data type to a PHP data type and returns it.
     * 
     * @return mixed
     */
    abstract public function toPhp();
    
    /**
     * This method is used to clean up an object by releasing memory and references.
     * 
     * The observers have to be removed with the "detach()"-method. Only if there is no observer left
     * this method should really release resources.
     * 
     * The method has to be implemented by each object type
     */
    public function cleanUp()
    {
        // empty method
    }
}
