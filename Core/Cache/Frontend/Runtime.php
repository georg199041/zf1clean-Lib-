<?php
//TODO check needed functional
class Core_Cache_Frontend_Runtime extends Zend_Cache_Core
{
    public function load($id, $doNotTestCacheValidity = false, $doNotUnserialize = false)
    {
        if (!$this->_options['caching']) {
            return false;
        }
        $id = $this->_id($id); // cache id may need prefix
        $this->_lastId = $id;
        self::_validateIdOrTag($id);

        $this->_log("Core_Cache_Frontend_Runtime: load item '{$id}'", 7);
        $data = $this->_backend->load($id, $doNotTestCacheValidity);
        if ($data===false) {
            // no cache available
            return false;
        }
        if ((!$doNotUnserialize) && $this->_options['automatic_serialization']) {
            // we need to unserialize before sending the result
            return unserialize($data);
        }
        return $data;
    }

    public function test($id)
    {
        if (!$this->_options['caching']) {
            return false;
        }
        $id = $this->_id($id); // cache id may need prefix
        self::_validateIdOrTag($id);
        $this->_lastId = $id;

        $this->_log("Core_Cache_Frontend_Runtime: test item '{$id}'", 7);
        return $this->_backend->test($id);
    }

    public function save($data, $id = null, $tags = array(), $specificLifetime = false, $priority = 8)
    {
        if (!$this->_options['caching']) {
            return true;
        }
        if ($id === null) {
            $id = $this->_lastId;
        } else {
            $id = $this->_id($id);
        }
        self::_validateIdOrTag($id);
        self::_validateTagsArray($tags);
        if ($this->_options['automatic_serialization']) {
            // we need to serialize datas before storing them
            $data = serialize($data);
        } else {
            //if (!is_string($data)) {
            //    Zend_Cache::throwException("Datas must be string or set automatic_serialization = true");
            //}
        }

        // automatic cleaning
        if ($this->_options['automatic_cleaning_factor'] > 0) {
            $rand = rand(1, $this->_options['automatic_cleaning_factor']);
            if ($rand==1) {
                //  new way                 || deprecated way
                if ($this->_extendedBackend || method_exists($this->_backend, 'isAutomaticCleaningAvailable')) {
                    $this->_log("Core_Cache_Frontend_Runtime::save(): automatic cleaning running", 7);
                    $this->clean(Zend_Cache::CLEANING_MODE_OLD);
                } else {
                    $this->_log("Core_Cache_Frontend_Runtime::save(): automatic cleaning is not available/necessary with current backend", 4);
                }
            }
        }

        $this->_log("Core_Cache_Frontend_Runtime: save item '{$id}'", 7);
        if ($this->_options['ignore_user_abort']) {
            $abort = ignore_user_abort(true);
        }
        if (($this->_extendedBackend) && ($this->_backendCapabilities['priority'])) {
            $result = $this->_backend->save($data, $id, $tags, $specificLifetime, $priority);
        } else {
            $result = $this->_backend->save($data, $id, $tags, $specificLifetime);
        }
        if ($this->_options['ignore_user_abort']) {
            ignore_user_abort($abort);
        }

        if (!$result) {
            // maybe the cache is corrupted, so we remove it !
            $this->_log("Core_Cache_Frontend_Runtime::save(): failed to save item '{$id}' -> removing it", 4);
            $this->_backend->remove($id);
            return false;
        }

        if ($this->_options['write_control']) {
            $data2 = $this->_backend->load($id, true);
            if ($data!=$data2) {
                $this->_log("Core_Cache_Frontend_Runtime::save(): write control of item '{$id}' failed -> removing it", 4);
                $this->_backend->remove($id);
                return false;
            }
        }

        return true;
    }
    
    public function remove($id)
    {
        if (!$this->_options['caching']) {
            return true;
        }
        $id = $this->_id($id); // cache id may need prefix
        self::_validateIdOrTag($id);

        $this->_log("Core_Cache_Frontend_Runtime: remove item '{$id}'", 7);
        return $this->_backend->remove($id);
    }
    
    public function clean($mode = 'all', $tags = array())
    {
        if (!$this->_options['caching']) {
            return true;
        }
        if (!in_array($mode, array(Zend_Cache::CLEANING_MODE_ALL,
                                   Zend_Cache::CLEANING_MODE_OLD,
                                   Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                                   Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,
                                   Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG))) {
            Zend_Cache::throwException('Invalid cleaning mode');
        }
        self::_validateTagsArray($tags);

        return $this->_backend->clean($mode, $tags);
    }
}
