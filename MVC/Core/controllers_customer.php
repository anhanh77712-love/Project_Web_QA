<?php
    class controllers_customer{
        public function model($model){
            include_once './MVC/Model/Customer/'.$model.'.php';
            return new $model;
        }
        public function view($view,$data=[]){
            include_once './MVC/View/'.$view.'.php';
        }
    }
?>