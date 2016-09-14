<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Class_dao extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function metodo() {
        return "";
    }
}
