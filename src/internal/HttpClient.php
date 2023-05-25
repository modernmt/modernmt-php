<?php

namespace ModernMT\internal;

use CURLFile;
use ModernMT\ModernMTException;

class HttpClient {

    private $baseUrl;
    private $headers;

    public function __construct($baseUrl, $headers = null) {
        $this->baseUrl = $baseUrl;
        $this->headers = $headers;
    }

    /**
     * @throws ModernMTException
     */
    public function send($method, $path, $data = null, $files = null, $additional_headers = null) {
        $url = $this->baseUrl . $path;

        $headers = [];
        if ($this->headers)
            $headers = $this->headers;

        $headers[] = "X-HTTP-Method-Override: $method";

        if ($additional_headers) {
            foreach ($additional_headers as $name => $value)
                $headers[] = "$name: $value";
        }

        if ($data)
            $data = array_filter($data, function($el) {
                return isset($el);
            });
        else
            $data = [];

        if ($files) {
            $headers[] = 'Content-Type: multipart/form-data';

            foreach (array_filter($files) as $key => $value) {
                if (!file_exists($value))
                    throw new ModernMTException(400, 'FileNotFound', "File $value not found");

                $data[$key] = new CURLFile($value);
            }
        } else {
            // do not send empty body
            if (!empty($data)) {
                $headers[] = 'Content-Type: application/json';
                $data = json_encode($data);
            }
        }

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $result = curl_exec($curl);
        curl_close($curl);

        if ($result === false) {
            $curl_errno = curl_errno($curl);
            $timeout = $curl_errno == 28;
            $type = $timeout ? 'TimeoutException' : 'ConnectionException';
            $message = $timeout ? "Connection timed out ($curl_errno)" : "Unable to contact server ($curl_errno)";

            throw new ModernMTException(500, $type, $message);
        }

        $json = json_decode($result, true);

        $status = $json['status'];
        if ($status >= 300 || $status < 200)
            throw new ModernMTException($status, $json['error']['type'], $json['error']['message']);

        return $json['data'];
    }

}
