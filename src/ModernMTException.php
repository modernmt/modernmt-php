<?php

namespace ModernMT;

use Exception;

class ModernMTException extends Exception {

    public $type;

    public function __construct($code, $type, $message) {
        parent::__construct("$type: $message", $code);
        $this->type = $type;
    }

}
