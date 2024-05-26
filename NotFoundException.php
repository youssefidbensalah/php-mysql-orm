<?php

class NotFoundException extends Exception
{
    public function __construct($message = "Object not found", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
