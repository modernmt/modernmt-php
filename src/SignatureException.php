<?php

namespace ModernMT;

class SignatureException extends ModernMTException {
    public function __construct($message) {
        parent::__construct(0, "SignatureException", $message);
    }

}
