<?php

namespace ModernMT\internal;

use ModernMT\ModernMTException;

class MemoryServices {

    private $http;

    public function __construct(HttpClient $http) {
        $this->http = $http;
    }

    /**
     * @throws ModernMTException
     * @deprecated use getAll() instead
     */
    public function get_all() {
        trigger_error('Method ' . __METHOD__ . ' is deprecated', E_USER_DEPRECATED);
        return $this->getAll();
    }

    /**
     * @throws ModernMTException
     */
    public function getAll() {
        return $this->http->send('get', '/memories');
    }

    /**
     * @throws ModernMTException
     */
    public function get($id) {
        return $this->http->send('get', "/memories/$id");
    }

    /**
     * @throws ModernMTException
     */
    public function create($name, $description = null, $external_id = null) {
        return $this->http->send('post', '/memories', [
            'name' => $name,
            'description' => $description,
            'external_id' => $external_id
        ]);
    }

    /**
     * @throws ModernMTException
     */
    public function edit($id, $name = null, $description = null) {
        return $this->http->send('put', "/memories/$id", [
            'name' => $name,
            'description' => $description
        ]);
    }

    /**
     * @throws ModernMTException
     */
    public function delete($id) {
        return $this->http->send('delete', "/memories/$id");
    }

    /**
     * @throws ModernMTException
     */
    public function add($memory, $source, $target, $sentence, $translation, $tuid = null) {
        return $this->http->send('post', "/memories/$memory/content", [
            'source' => $source,
            'target' => $target,
            'sentence' => $sentence,
            'translation' => $translation,
            'tuid' => $tuid
        ]);
    }

    /**
     * @throws ModernMTException
     */
    public function replace($memory, $tuid, $source, $target, $sentence, $translation) {
        return $this->http->send('put', "/memories/$memory/content", [
            'tuid' => $tuid,
            'source' => $source,
            'target' => $target,
            'sentence' => $sentence,
            'translation' => $translation
        ]);
    }

    /**
     * @throws ModernMTException
     */
    public function import($id, $tmx, $compression = null) {
        return $this->http->send('post', "/memories/$id/content", [
            'compression' => $compression
        ], [
            'tmx' => $tmx,
        ]);
    }

    /**
     * @throws ModernMTException
     * @deprecated use getImportStatus() instead
     */
    public function get_import_status($uuid) {
        trigger_error('Method ' . __METHOD__ . ' is deprecated', E_USER_DEPRECATED);
        return $this->getImportStatus($uuid);
    }

    /**
     * @throws ModernMTException
     */
    public function getImportStatus($uuid) {
        return $this->http->send('get', "/import-jobs/$uuid");
    }
}
