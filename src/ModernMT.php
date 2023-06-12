<?php

namespace ModernMT;

use DomainException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use InvalidArgumentException;
use ModernMT\internal\HttpClient;
use ModernMT\internal\MemoryServices;
use UnexpectedValueException;

class ModernMT {

    private $http;
    public $memories;

    public function __construct($license, $platform = null, $platformVersion = null, $apiClient = null) {
        if ($platform == null) $platform = 'modernmt-php';
        if ($platformVersion == null) $platformVersion = '1.2.0';

        $headers = [
            "MMT-ApiKey: $license",
            "MMT-Platform: $platform",
            "MMT-PlatformVersion: $platformVersion"
        ];

        if ($apiClient != null)
            $headers[] = "MMT-ApiClient: $apiClient";

        $this->http = new HttpClient('https://api.modernmt.com', $headers);
        $this->memories = new MemoryServices($this->http);
    }

    /**
     * @throws ModernMTException
     * @deprecated use listSupportedLanguages() instead
     */
    public function list_supported_languages() {
        trigger_error('Method ' . __METHOD__ . ' is deprecated', E_USER_DEPRECATED);
        return $this->listSupportedLanguages();
    }

    /**
     * @throws ModernMTException
     */
    public function listSupportedLanguages() {
        return $this->http->send('get', '/translate/languages');
    }

    /**
     * @throws ModernMTException
     * @deprecated use detectLanguage() instead
     */
    public function detect_language($q, $format = null) {
        trigger_error('Method ' . __METHOD__ . ' is deprecated', E_USER_DEPRECATED);
        return $this->detectLanguage($q, $format);
    }

    /**
     * @throws ModernMTException
     */
    public function detectLanguage($q, $format = null) {
        $data = [
            'q' => $q,
            'format' => $format
        ];

        return $this->http->send('get', '/translate/detect', $data);
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
            if (isset($options['alt_translations']))
                $data['alt_translations'] = $options['alt_translations'];
        }

        return $this->http->send('get', '/translate', $data);
    }

    /**
     * @throws ModernMTException
     */
    public function batchTranslate($webhook, $source, $target, $q, $hints = null, $context_vector = null, $options = null) {
        $data = [
            'webhook' => $webhook,
            'source' => $source,
            'target' => $target,
            'q' => $q,
            'hints' => $hints ? implode(',', $hints) : null,
            'context_vector' => $context_vector
        ];

        $headers = null;

        if ($options) {
            if (isset($options['project_id']))
                $data['project_id'] = $options['project_id'];
            if (isset($options['multiline']))
                $data['multiline'] = $options['multiline'];
            if (isset($options['format']))
                $data['format'] = $options['format'];
            if (isset($options['alt_translations']))
                $data['alt_translations'] = $options['alt_translations'];
            if (isset($options['metadata']))
                $data['metadata'] = $options['metadata'];

            if (isset($options['idempotency_key']))
                $headers = ["x-idempotency-key" => $options['idempotency_key']];
        }

        $result = $this->http->send('post', '/translate/batch', $data, null, $headers);
        return $result["enqueued"];
    }

    /**
     * @throws ModernMTException
     * @throws SignatureException
     */
    public function handleCallback($body, $signature) {
        // Verify callback signature
        try {
            $key = ModernMT::getPublicKey();
            JWT::decode($signature, new Key($key, 'RS256'));
        } catch (InvalidArgumentException $e) {
            throw new SignatureException($e->getMessage());
        } catch (DomainException $e) {
            throw new SignatureException($e->getMessage());
        } catch (UnexpectedValueException $e) {
            throw new SignatureException($e->getMessage());
        }

        if (is_string($body))
            $body = json_decode($body, true);

        $result = $body["result"];
        $metadata = isset($body["metadata"]) ? $body["metadata"] : null;

        $status = $result["status"];
        if ($status >= 300 || $status < 200) {
            $error = $result["error"];
            $type = isset($error["type"]) ? $error["type"] : null;
            $message = isset($error["message"]) ? $error["message"] : null;

            throw new ModernMTException($status, $type, $message, $metadata);
        } else {
            return ["data" => $result["data"], "metadata" => $metadata];
        }
    }

    /**
     * @throws ModernMTException
     * @deprecated use getContextVector() instead
     */
    public function get_context_vector($source, $targets, $text, $hints = null, $limit = null) {
        trigger_error('Method ' . __METHOD__ . ' is deprecated', E_USER_DEPRECATED);
        return $this->getContextVector($source, $targets, $text, $hints, $limit);
    }

    /**
     * @throws ModernMTException
     */
    public function getContextVector($source, $targets, $text, $hints = null, $limit = null) {
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
     * @deprecated use getContextVectorFromFile() instead
     */
    public function get_context_vector_from_file($source, $targets, $file, $hints = null, $limit = null,
                                                 $compression = null) {
        trigger_error('Method ' . __METHOD__ . ' is deprecated', E_USER_DEPRECATED);
        return $this->getContextVectorFromFile($source, $targets, $file, $hints, $limit, $compression);
    }

    /**
     * @throws ModernMTException
     */
    public function getContextVectorFromFile($source, $targets, $file, $hints = null, $limit = null,
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

        if ($multiple_targets)
            return $res['vectors'];

        return isset($res['vectors'][$targets]) ? $res['vectors'][$targets] : null;
    }

    /**
     * @throws ModernMTException
     */
    private function retrievePublicKey() {
        $result = $this->http->send('get', '/translate/batch/key');
        return base64_decode($result["publicKey"]);
    }

    private function storePublicKey($pk, $cache_file) {
        // try to atomically write cache file
        $tmp_file = tempnam(sys_get_temp_dir(), "mmt_pkey_");
        file_put_contents($tmp_file, $pk);
        rename($tmp_file, $cache_file);
    }

    /**
     * @throws ModernMTException
     */
    private function getPublicKey() {
        $cache_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "php_mmt_batch_translate_public_key.pub";

        if (!is_file($cache_file)) {
            $pk = $this->retrievePublicKey();
            $this->storePublicKey($pk, $cache_file);
        } else if ((filectime($cache_file) + 3600) < time()) {  // file is older than 1 hour
            try {
                $pk = $this->retrievePublicKey();
                $this->storePublicKey($pk, $cache_file);
            } catch (ModernMTException $e) {
                // if file exists then retrieve last available key and ignore API exception
                $pk = file_get_contents($cache_file);
            }
        } else {
            $pk = file_get_contents($cache_file);
        }

        return $pk;
    }

    /**
     * @throws ModernMTException
     */
    public function me() {
        return $this->http->send('get', '/users/me');
    }
}
