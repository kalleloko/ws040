<?php
class ApiException extends \Exception
{

    private $_options;

    public function __construct($message, 
                                $code = 0, 
                                Exception $previous = null, 
                                $options = []) 
    {
        parent::__construct($message, $code, $previous);

        $this->_options = $options; 
    }

    public function setOptions( $options ) {
        $this->_options = $options; 
    }

    public function getOptions() { return $this->_options; }
}