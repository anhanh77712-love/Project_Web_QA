<?php
    class controllers_customer{
       public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
        public function model($model){
            include_once './MVC/Model/Customer/'.$model.'.php';
            return new $model;
        }
        public function view($view,$data=[]){
            if (is_array($data) && count($data) > 0) {
                extract($data);
            }
            include_once './MVC/View/'.$view.'.php';
        }
    }
?>