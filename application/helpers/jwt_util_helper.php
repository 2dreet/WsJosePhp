<?php

include 'JWT.php';

function key_jwt() {
    return "dc5v75vc";
}

function jwt_encode($texto) {
    $jwt = new JWT();
    return $jwt->encode($texto, key_jwt());
}

function jwt_validate($valorJWT) {
    $jwt = new JWT();
    try {
        $jwt->decode($valorJWT, key_jwt(), array('HS256'));
        return true;
    } catch (UnexpectedValueException $e) {
        return false;
    }
}

function jwt_decode($valorJWT) {
    $jwt = new JWT();
    try {
        return $jwt->decode($valorJWT, key_jwt(), array('HS256'));
    } catch (UnexpectedValueException $e) {
        return null;
    }
}
function getDadosTokenJson($token) {
    return json_decode(jwt_decode($token));
}
