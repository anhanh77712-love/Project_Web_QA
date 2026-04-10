<?php
    class controllers{
        public function model($model){
            include_once './MVC/Model/'.$model.'.php';
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