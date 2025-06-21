<?php
    require_once(__DIR__ . '/../Base/BaseModel.php'); 
    
    class HomeModel extends BaseModel {
        // Since home page is static, these can return empty or basic data
        public function getAll() {
            return array('code' => 0, 'data' => []);
        }

        public function getById($id) {
            return array('code' => 0, 'data' => []);
        }
    }
?>