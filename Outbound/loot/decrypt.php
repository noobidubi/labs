// Slightly modified version of the official roundcube sourcecode
// Links: https://github.com/roundcube/roundcubemail/blob/master/program/lib/Roundcube/rcube.php#L943-L978, https://www.roundcubeforum.net/index.php?topic=23399.0

<?php

class Decryptor {
    private $config;

    public function __construct() {
        // Config from loot/config.inc.php
        $this->config = new class {
            public function get_crypto_key($key) {
                return 'rcmail-!24ByteDESkey*Str'; // Decryption Key
            }
            public function get_crypto_method() {
                return 'DES-EDE3-CBC'; // or whatever method Roundcube uses
            }
        };
    }

    public function decrypt($cipher, $key = 'des_key', $base64 = true) {
        if (!is_string($cipher) || !strlen($cipher)) {
            return false;
        }

        if ($base64) {
            $cipher = base64_decode($cipher, true);
            if ($cipher === false) {
                return false;
            }
        }

        $ckey = $this->config->get_crypto_key($key);
        $method = $this->config->get_crypto_method();
        $iv_size = openssl_cipher_iv_length($method);
        $tag = null;

        if (preg_match('/^##(.{16})##/s', $cipher, $matches)) {
            $tag = $matches[1];
            $cipher = substr($cipher, strlen($matches[0]));
        }

        $iv = substr($cipher, 0, $iv_size);
        if (strlen($iv) < $iv_size) {
            return false;
        }

        $cipher = substr($cipher, $iv_size);
        $clear = openssl_decrypt($cipher, $method, $ckey, OPENSSL_RAW_DATA, $iv, $tag);

        return $clear;
    }
}

// 🔧 Replace with your actual encrypted input
$encrypted = "L7Rv00A8TuwJAr67kITxxcSgnIk25Am/";

$d = new Decryptor();
$decrypted = $d->decrypt($encrypted);
echo "Decrypted output: " . $decrypted . PHP_EOL;
