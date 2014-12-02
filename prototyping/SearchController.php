<?php

require_once 'Database.php';
require_once '/home/BUSaleCredentials/DatabaseCredentials.php';

class SearchController {
    private $db;

    public function __construct() {
        $credentials = DatabaseCredentials::get();
        $this->db = new Database($credentials['db_name'], $credentials['db_host_address'], 
                                 $credentials['db_username'], $credentials['db_password']);
    }

    public function getInstagramImages($search_str = null){
        $image_info = $this->db->fetchAll("Prototype1");
        if(!empty($search_str)) {
            foreach($image_info as $key=>$value) {
                if(strpos($value['caption'], $search_str)===false) {
                    unset($image_info[$key]);
                }
            }
        }
        return json_encode($image_info);
    }

    public function databaseTests() {
        $this->db->tests();
    }
}

