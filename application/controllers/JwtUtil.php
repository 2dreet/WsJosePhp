<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include 'JWT.php';

class JwtUtil {

    private $key = "dc5v75vc";

    public function encode($texto) {
        $jwt = new JWT();
        return $jwt->encode($texto, $this->key);
    }

    public function validate($valorJWT) {
        $jwt = new JWT();
        try {
            $jwt->decode($valorJWT, $this->key, array('HS256'));
            return true;
        } catch (UnexpectedValueException $e) {
            return false;
        }
    }
    
    public function decode($valorJWT) {
        $jwt = new JWT();
        try {
            return $jwt->decode($valorJWT, $this->key, array('HS256'));
        } catch (UnexpectedValueException $e) {
            return null;
        }
    }
    

}
