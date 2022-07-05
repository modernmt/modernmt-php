<?php

namespace ModernMT;

use ModernMT\internal\HttpClient;
use ModernMT\internal\MemoryServices;

class ModernMT {

    private $http;
    public $memories;

    public function __construct($license, $platform = 'modernmt-php', $platformVersion = '1.0.1') {
        $this->http = new HttpClient('https://api.modernmt.com', [
            "MMT-ApiKey: $license",
            "MMT-Platform: $platform",
            "MMT-PlatformVersion: $platformVersion"
        ]);
        $this->memories = new MemoryServices($this->http);
    }

    /**
     * @throws ModernMTException
     */
    public function list_supported_languages() {
        return $this->http->send('get', '/translate/languages');
    }

    /**
     * @throws ModernMTException
     */
    public function translate($source, $target, $q, $hints = null, $context_vector = null, $options = null) {
        $data = [
            'source' => $source,
            'target' => $target,
            'q' => $q,
            'hints' => $hints ? implode(',', $hints) : null,
            'context_vector' => $context_vector
        ];

        if ($options) {
            if (isset($options['priority']))
                $data['priority'] = $options['priority'];
            if (isset($options['project_id']))
                $data['project_id'] = $options['project_id'];
            if (isset($options['multiline']))
                $data['multiline'] = $options['multiline'];
            if (isset($options['timeout']))
                $data['timeout'] = $options['timeout'];
            if (isset($options['format']))
                $data['format'] = $options['format'];
        }

        return $this->http->send('get', '/translate', $data);
    }

    /**
     * @throws ModernMTException
     */
    public function get_context_vector($source, $targets, $text, $hints = null, $limit = null) {
        $multiple_targets = is_array($targets);

        if ($multiple_targets)
            $targets = implode(',', $targets);

        $res = $this->http->send('get', '/context-vector', [
            'source' => $source,
            'targets' => $targets,
            'text' => $text,
            'hints' => $hints ? implode(',', $hints) : null,
            'limit' => $limit
        ]);

        if ($multiple_targets)
            return $res['vectors'];

        return isset($res['vectors'][$targets]) ? $res['vectors'][$targets] : null;
    }

    /**
     * @throws ModernMTException
     */
    public function get_context_vector_from_file($source, $targets, $file, $hints = null, $limit = null,
                                                 $compression = null) {
        $multiple_targets = is_array($targets);

        if ($multiple_targets)
            $targets = implode(',', $targets);

        $res = $this->http->send('get', '/context-vector', [
            'source' => $source,
            'targets' => $targets,
            'hints' => $hints ? implode(',', $hints) : null,
            'limit' => $limit,
            'compression' => $compression
        ], [
            'content' => $file
        ]);

        return $multiple_targets ? $res['vectors'] : $res['vectors'][$targets];
    }

}
