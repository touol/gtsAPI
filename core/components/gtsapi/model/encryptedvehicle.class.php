<?php

class encryptedVehicle extends xPDOObjectVehicle
{
    public $class = 'encryptedVehicle';
    const version = '2.0.0';
    const cipher = 'AES-256-CBC';


    /**
     * @param $gtsapi xPDOgtsAPI
     * @param $object
     * @param array $attributes
     */
    public function put(&$gtsapi, &$object, $attributes = array())
    {
        parent::put($gtsapi, $object, $attributes);

        if (defined('PKG_ENCODE_KEY')) {
            $this->payload['object_encrypted'] = $this->encode($this->payload['object'], PKG_ENCODE_KEY);
            unset($this->payload['object']);

            if (isset($this->payload['related_objects'])) {
                $this->payload['related_objects_encrypted'] = $this->encode($this->payload['related_objects'], PKG_ENCODE_KEY);
                unset($this->payload['related_objects']);
            }

            $gtsapi->xpdo->log(xPDO::LOG_LEVEL_INFO, 'Package encrypted!');
        }
    }


    /**
     * @param $gtsapi xPDOgtsAPI
     * @param $options
     *
     * @return bool
     */
    public function install(&$gtsapi, $options)
    {
        if (!$this->decodePayloads($gtsapi, 'install')) {
            return false;
        } else {
            $gtsapi->xpdo->log(xPDO::LOG_LEVEL_INFO, 'Package decrypted!');
        }

        return parent::install($gtsapi, $options);
    }


    /**
     * @param $gtsapi xPDOgtsAPI
     * @param $options
     *
     * @return bool
     */
    public function uninstall(&$gtsapi, $options)
    {
        if (!$this->decodePayloads($gtsapi, 'uninstall')) {
            return false;
        } else {
            $gtsapi->xpdo->log(xPDO::LOG_LEVEL_INFO, 'Package decrypted!');
        }

        return parent::uninstall($gtsapi, $options);
    }


    /**
     * @param array $data
     * @param string $key
     *
     * @return string
     */
    protected function encode($data, $key)
    {
        $ivLen = openssl_cipher_iv_length($this::cipher);
        $iv = openssl_random_pseudo_bytes($ivLen);
        $cipher_raw = openssl_encrypt(serialize($data), $this::cipher, $key, OPENSSL_RAW_DATA, $iv);

        return base64_encode($iv . $cipher_raw);
    }


    /**
     * @param string $string
     * @param string $key
     *
     * @return string
     */
    protected function decode($string, $key)
    {
        $ivLen = openssl_cipher_iv_length($this::cipher);
        $encoded = base64_decode($string);
        if (ini_get('mbstring.func_overload')) {
            $strLen = mb_strlen($encoded, '8bit');
            $iv = mb_substr($encoded, 0, $ivLen, '8bit');
            $cipher_raw = mb_substr($encoded, $ivLen, $strLen, '8bit');
        } else {
            $iv = substr($encoded, 0, $ivLen);
            $cipher_raw = substr($encoded, $ivLen);
        }
        return unserialize(openssl_decrypt($cipher_raw, $this::cipher, $key, OPENSSL_RAW_DATA, $iv));
    }


    /**
     * @param $gtsapi xPDOgtsAPI
     * @param string $action
     *
     * @return bool
     */
    protected function decodePayloads(&$gtsapi, $action = 'install')
    {
        if (isset($this->payload['object_encrypted']) || isset($this->payload['related_objects_encrypted'])) {
            if (!$key = $this->getDecodeKey($gtsapi, $action)) {
                return false;
            }
            if (isset($this->payload['object_encrypted'])) {
                $this->payload['object'] = $this->decode($this->payload['object_encrypted'], $key);
                unset($this->payload['object_encrypted']);
            }
            if (isset($this->payload['related_objects_encrypted'])) {
                $this->payload['related_objects'] = $this->decode($this->payload['related_objects_encrypted'], $key);
                unset($this->payload['related_objects_encrypted']);
            }
        }

        return true;
    }


    /**
     * @param $gtsapi xPDOgtsAPI
     * @param $action
     *
     * @return bool|string
     */
    protected function getDecodeKey(&$gtsapi, $action)
    {
        $key = false;
        $endpoint = 'package/decode/' . $action;

        /** @var modgtsAPIPackage $package */
        $package = $gtsapi->xpdo->getObject('gtsapi.modgtsAPIPackage', array(
            'signature' => $gtsapi->signature,
        ));
        if ($package instanceof modgtsAPIPackage) {
            /** @var modgtsAPIProvider $provider */
            if ($provider = $package->getOne('Provider')) {
                $provider->xpdo->setOption('contentType', 'default');
                $params = array(
                    'package' => $package->package_name,
                    'version' => $gtsapi->version,
                    'username' => $provider->username,
                    'api_key' => $provider->api_key,
                    'vehicle_version' => self::version,
                );

                $response = $provider->request($endpoint, 'POST', $params);
                if ($response->isError()) {
                    $msg = $response->getError();
                    $gtsapi->xpdo->log(xPDO::LOG_LEVEL_ERROR, $msg);
                } else {
                    $data = $response->toXml();
                    if (!empty($data->key)) {
                        $key = $data->key;
                    } elseif (!empty($data->message)) {
                        $gtsapi->xpdo->log(xPDO::LOG_LEVEL_ERROR, $data->message);
                    }
                }
            }
        }

        return $key;
    }

}