<?php

namespace ModernMT;

use Exception;

class ModernMTException extends Exception {

    public $type;
    private $metadata;

    public function __construct($code, $type, $message, $metadata = null) {
        parent::__construct("$type: $message", $code);
        $this->type = $type;
        $this->metadata = $metadata;
    }

    public function getMetadata() {
        return $this->metadata;
    }

}
