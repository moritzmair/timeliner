<?php

  include_once "SPDOStatement.php";
  include_once "SPDO.php";

  if(isset($_POST['gps1']) and isset($_POST['gps2']) and isset($_POST['pixel1']) and isset($_POST['pixel2']) and isset($_POST['id'])){
    SPDO::prepare("UPDATE  map_parts SET gps_1 = ? , gps_2 = ? , pixel_1 = ? , pixel_2 = ?, gps_set = 1 WHERE id = ?")->execute(array($_POST['gps1'],$_POST['gps2'],$_POST['pixel1'],$_POST['pixel2'],$_POST['id']));
    echo "success";
  }else{
    echo "data missing";
  }

?>