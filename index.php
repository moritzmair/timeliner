<?php
  date_default_timezone_set('Europe/Berlin');
  include_once "classes/SPDOStatement.php";
  include_once "classes/SPDO.php";
  $error = array();
  $success = array();


  if($_FILES != array()){
    if(!isset($_POST['year'])){
      $error[] = "Please provide a year.";
    }
    elseif(move_uploaded_file($_FILES["file"]["tmp_name"], "map_parts/".$_FILES["file"]["name"])){
      $timestamp = strtotime('01-01-'.$_POST['year']);
      SPDO::prepare("INSERT INTO map_parts (name,gps_set,timestamp) VALUES (?,0,?)")->execute(array($_FILES["file"]["name"],$timestamp));
      $success[] = "The image was successfully uploaded";
    }else{
      $error[] = "The image file could not be uploaded";
    }
    
  }

?>

<!DOCTYPE html>
<html lang="de" prefix="og: http://ogp.me/ns#">
 <head>
  <meta charset="utf-8">
  <title>timeliner - create a map timeline from historic map pieces</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/font-awesome.min.css">
  <link rel="stylesheet" href="css/leaflet.css">
  <link rel="stylesheet" href="css/style.css">
  <script type="text/javascript">
    L_DISABLE_3D = true;
  </script>
  <script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
  <script type="text/javascript" src="js/bootstrap.min.js"></script>
  <script type="text/javascript" src="js/leaflet.js"></script>
 </head>
 <body>
  <div class="container">
    <?php
      if(isset($_GET['display'])){
        $images = SPDO::query("SELECT * FROM map_parts ORDER BY timestamp ASC")->fetchAll(PDO::FETCH_ASSOC);
        $images_count = count($images);
        ?>

          <div id="big_map"></div>
          <table style="width:100%;text-align:center;"><tr>
          <?php

          foreach ($images as $key => $image) {
            echo '<td>'.date("Y",$image['timestamp']).'</td>';
          }

          ?>
          </tr><tr><td colspan="<?=$images_count?>">
          <input id="timeline" type="range" name="points" min="1" max="<?=$images_count?>" style="width:81%;margin:auto;"><br>
          use right and left arrow keys to switch between images
          </td></tr></table>
          
          <script type="text/javascript">

            document.onkeyup = function(e) {
              e = e || window.event;
              switch(e.which || e.keyCode) {
                  case 37: // left
                  $('#timeline').val((parseInt($('#timeline').val())-1));
                  break;

                  case 39: // right
                  $('#timeline').val((parseInt($('#timeline').val())+1));
                  break;

                  default: return; // exit this handler for other keys
              }
              e.preventDefault(); // prevent the default action (scroll / move caret)
          };

            var images = [
              <?php
              foreach ($images as $key => $image) {
                $all_elems[] = '"'.$image['name'].'"';
              }

              echo implode(",",$all_elems);
              
              ?>
            ]

            function hide_pics(){
              $(images).each(function(key, value){
                $('img[src$="map_parts/'+value+'"]').css('opacity',0);
              });
              $('img[src$="map_parts/'+images[($( "#timeline" ).val()-1)]+'"]').css('opacity',1);
              setTimeout(function(){
                hide_pics();
              },100);
            }

            hide_pics();
          
            var map = L.map('big_map').setView([49.87, 8.65], 15);
            L.tileLayer('http://a.tile.openstreetmap.org/{z}/{x}/{y}.png', {
              maxZoom: 18
            }).addTo(map);

            var blueIcon = L.icon({
                iconUrl: 'img/marker_blue.png',
                iconAnchor:   [12, 41], // point of the icon which will correspond to marker's location
            });
              

          </script>
        <?php

        foreach ($images as $key => $image) {

          list($img_width, $img_height) = getimagesize("map_parts/".$image['name']);

          $gps1 = explode(",",$image['gps_1']);
          $gps2 = explode(",",$image['gps_2']);
          $pixel1 = explode(",",$image['pixel_1']);
          $pixel2 = explode(",",$image['pixel_2']);

          $gps_1_x = $gps1[1];
          $gps_1_y = $gps1[0];
          $gps_2_x = $gps2[1];
          $gps_2_y = $gps2[0];

          $pixel_1_x = $pixel1[0];
          $pixel_1_y = $pixel1[1];
          $pixel_2_x = $pixel2[0];
          $pixel_2_y = $pixel2[1];

          //calc rotation

          $gps_angle = 31; // <- dirty fix

          /*
          $opposite_leg_gps = $gps_1_x-$gps_2_x;
          $adjacent_leg_gps = $gps_2_y-$gps_1_y;

          $gps_angle = rad2deg(atan(($opposite_leg_gps/$adjacent_leg_gps)));
          */

          $opposite_leg_pixel = $pixel_1_x-$pixel_2_x;
          $adjacent_leg_pixel = $pixel_1_y-$pixel_2_y;

          $pixel_angle = rad2deg(atan(($opposite_leg_pixel/$adjacent_leg_pixel)));

          $turn_angle = $gps_angle-$pixel_angle;

          $origin_top = $pixel_1_x/$img_width;
          $origin_left = $pixel_1_y/$img_height;

          //calc position and size on map
          if($turn_angle > 70){
            $one_pixel_in_lon = ($gps_2_y - $gps_1_y)/($pixel_2_y - $pixel_1_y);
            $one_pixel_in_lat = ($gps_2_x - $gps_1_x)/($pixel_2_x - $pixel_1_x);
          }else{
            $one_pixel_in_lon = ($gps_2_x - $gps_1_x)/($pixel_2_x - $pixel_1_x);
            $one_pixel_in_lat = ($gps_2_y - $gps_1_y)/($pixel_2_y - $pixel_1_y);
          }

          $lon_left = $gps_1_x-($pixel_1_x*$one_pixel_in_lon);
          $lon_right = $gps_1_x+(($img_width-$pixel_1_x)*$one_pixel_in_lon);

          $lat_top = $gps_1_y-($pixel_1_y*$one_pixel_in_lat);
          $lat_bottom = $gps_1_y+(($img_height-$pixel_1_y)*$one_pixel_in_lat);

          

          ?>

            <style>
              <?php
                echo 'img[src$="map_parts/'.$image['name'].'"] {'."\n";
                echo 'transform-origin: '.($origin_top*100).'% '.($origin_left*100).'%;'."\n";
                echo '-webkit-transform: rotate('.round($turn_angle,2).'deg);'."\n";
                echo '}';
              ?>
            </style>

            
            <script type="text/javascript">

              $( document ).ready(function() {

                var imageUrl = 'map_parts/<?=$image['name']?>';

                imageBounds = [[<?=$lat_bottom?>, <?=$lon_left?>], [<?=$lat_top?>,<?=$lon_right?>]];

                L.imageOverlay(imageUrl, imageBounds, {opacity: 0.8}).addTo(map);
              });

            </script>
          <?php
          }

      }else{
        if($error != array()){
          echo '<div class="error_msg">';
          foreach ($error as $key => $value) {
            echo $value."<br>";
          }
          echo '</div>';
        }
        if($success != array()){
          echo '<div class="success_msg">';
          foreach ($success as $key => $value) {
            echo $value."<br>";
          }
          echo '</div>';
        }

      ?>
      Upload file:
      <form action="index.php" method="post" enctype="multipart/form-data">
        <input name="file" type="file">
        <input style="width:70px;" placeholder="Year" name="year" type="number"><br>
        <input value="Upload file" type="submit">
      </form>
      <br>
      <br>
      <?php
        if(isset($_GET['img'])){
          ?>
            <a href="index.php">back to overview</a><br><br>
            <div class="row">
              <div class="col-lg-6">
                <?php
                  $image = SPDO::query("SELECT * FROM map_parts WHERE id = '".$_GET['img']."'")->fetchAll(PDO::FETCH_ASSOC);

                  

                  $icon_half_width = 12;
                  $icon_height = 41;

                  $positions = explode(",",$image[0]['pixel_1']);
                  if($positions[0] == 0 and $positions[1] == 0){
                    $pos1_x = 150;
                    $pos1_y = 100;
                  }else{
                    $pos1_x = $positions[0]-$icon_half_width;
                    $pos1_y = $positions[1]-$icon_height;
                  }

                  
                  $positions = explode(",",$image[0]['pixel_2']);
                  if($positions[0] == 0 and $positions[1] == 0){
                    $pos2_x = 100;
                    $pos2_y = 100;
                  }else{
                    $pos2_x = $positions[0]-$icon_half_width;
                    $pos2_y = $positions[1]-$icon_height;
                  }
                ?>
                <div id="image_div" style="overflow:auto;width:100%;height:400px;">
                  <img src="map_parts/<?=$image[0]['name']?>">
                  <img draggable="false" onmousedown="dragstart(this)" class="image_marker" id="marker_1" src="img/marker_red.png" style="left:<?=$pos1_x?>px;top:<?=$pos1_y?>px;">
                  <img draggable="false" onmousedown="dragstart(this)" class="image_marker" id="marker_2" src="img/marker_blue.png" style="left:<?=$pos2_x?>px;top:<?=$pos2_y?>px;">
                </div>
              </div>
              <div class="col-lg-6">
                <div id="map"></div>
              </div>
              <button onclick="save_marker_pos(<?=$_GET['img']?>);">Save positions</button>
              <div id="success_save"></div>
            </div>
          <?php
        }else{
          $images = SPDO::query("SELECT * FROM map_parts")->fetchAll(PDO::FETCH_ASSOC);
          foreach ($images as $key => $value) {
            echo '<a href="index.php?img='.$value['id'].'">'.$value['name'].'</a><br>';
          }
        }
        ?>

        <script type="text/javascript">

    function save_marker_pos(id){
      var icon_half_width = 12;
      var icon_height = 41;

      $.post( "classes/save.php", {
        gps1: "49.87319,8.65604",
        gps2: "49.87794,8.65145",
        pixel1: ((parseInt($("#marker_1").css('left'))+icon_half_width)+","+(icon_height+parseInt($("#marker_1").css('top')))),
        pixel2: ((parseInt($("#marker_2").css('left'))+icon_half_width)+","+(icon_height+parseInt($("#marker_2").css('top')))),
        id: id
      } ).done(function( data ) {
        $("#success_save").html("Saved data successfully.")
      });
    }

    var blueIcon = L.icon({
        iconUrl: 'img/marker_blue.png',

        iconAnchor:   [12, 41], // point of the icon which will correspond to marker's location
    });

    var redIcon = L.icon({
        iconUrl: 'img/marker_red.png',

        iconAnchor:   [12, 41], // point of the icon which will correspond to marker's location
    });

    $( document ).ready(function() {
      var map = L.map('map').setView([49.87, 8.65], 14);
      L.tileLayer('http://a.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18
      }).addTo(map);

      var marker = L.marker([49.87319, 8.65604], {icon: redIcon}).addTo(map);
      var marker = L.marker([49.87794, 8.65145], {icon: blueIcon}).addTo(map);

      draginit();
    });

    //Das Objekt, das gerade bewegt wird.
    var dragobjekt = null;
    // Position, an der das Objekt angeklickt wurde.
    var dragx = 0;
    var dragy = 0;
    // Mausposition
    var posx = 0;
    var posy = 0;

    function draginit() {
     // Initialisierung der Überwachung der Events
      document.onmousemove = drag;
      document.onmouseup = dragstop;
    }

    function dragstart(element) {
       //Wird aufgerufen, wenn ein Objekt bewegt werden soll.
      dragobjekt = element;
      dragx = posx - dragobjekt.offsetLeft;
      dragy = posy - dragobjekt.offsetTop;
      console.log(dragx);
    }

    function dragstop() {
      //Wird aufgerufen, wenn ein Objekt nicht mehr bewegt werden soll.
      dragobjekt=null;
    }

    function drag(ereignis) {
      //Wird aufgerufen, wenn die Maus bewegt wird und bewegt bei Bedarf das Objekt.
      posx = document.all ? window.event.clientX : ereignis.pageX;
      posy = document.all ? window.event.clientY : ereignis.pageY;
      if(dragobjekt != null) {
        dragobjekt.style.left = (posx - dragx) + "px";
        dragobjekt.style.top = (posy - dragy) + "px";
      }
    }

  </script>

    <?php
      }
    ?>
    
  </div>
  
 </body>
</html>