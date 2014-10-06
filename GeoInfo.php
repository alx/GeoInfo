<?php

class GeoInfo {

    static function Load() {
    }

    static function Info() {
        return array (
            'name'    => 'GeoInfo',
            'author'  => 'Alexandre Girard',
            'version' => '1.0.0',
            'site'    => 'http://alexgirard.com/',
            'notes'   => 'Add geographic information (latitude/longitude) for each video'
        );
    }

    static function Install() {
      $db = Database::GetInstance();

      $create_query = 'CREATE TABLE `'.DB_PREFIX.'geoinfo` (';
      $create_query .= '  `geoinfo_id` bigint(20) NOT NULL AUTO_INCREMENT,';
      $create_query .= '  `video_id` bigint(20) NOT NULL,';
      $create_query .= '  `lat` double NOT NULL,';
      $create_query .= '  `long` double NOT NULL,';
      $create_query .= '  PRIMARY KEY (`geoinfo_id`)';
      $create_query .= ') DEFAULT CHARSET=utf8';

      $db->Query($create_query);
    }

    static function Uninstall() {
      $db = Database::GetInstance();

      $drop_query = 'DROP TABLE IF EXISTS `'.DB_PREFIX.'geoinfo`';

      $db->Query($drop_query);
    }

    static function FromVideo($video_id) {
      $db = Database::GetInstance();
      $geoinfo = null;
      $query = "SELECT `lat`, `long` FROM " . DB_PREFIX . "geoinfo WHERE `video_id` = " . $video_id;
      $geoinfo_result = $db->Query ($query);
      $geoinfo = $db->FetchObj ($geoinfo_result);
      return $geoinfo;
    }

    static function MapUrl($geoinfo_id) {
      $db = Database::GetInstance();
      $geoinfo = null;
      $query = "SELECT * FROM " . DB_PREFIX . "geoinfo WHERE geoinfo_id = " . $geoinfo_id;
      $geoinfo_result = $db->Query ($query);
      $geoinfo = $db->FetchObj ($geoinfo_result);
      if(isset($geoinfo)) {
        return "http://maps.googleapis.com/maps/api/staticmap?zoom=13&size=300x150&markers=color:red%7C". $geoinfo->lat ."," . $geoinfo->long;
      } else {
        return "";
      }
    }

    static function Settings() {
      global $config;

      App::LoadClass ('Video');
      $db = Database::GetInstance();

      if (isset ($_POST['geoinfo_action']) &&  $_POST['geoinfo_action'] == "update") {

        $query = "SELECT * FROM " . DB_PREFIX . "geoinfo WHERE video_id = " . $_POST['geoinfo_video_id'];
        $geoinfo_result = $db->Query ($query);
        $geoinfo_count = $db->Count ($geoinfo_result);

        if($geoinfo_count == 0) {
          $query = "INSERT INTO " . DB_PREFIX . "geoinfo (`video_id`, `lat`, `long`) VALUES (";
          $query .= $_POST['geoinfo_video_id'] . ', ' . $_POST['geoinfo_lat'] . ', ' . $_POST['geoinfo_long'] . ')';
          $db->Query ($query);
        } else {
          $query = "UPDATE " . DB_PREFIX . "geoinfo SET ";
          $query .= "`lat` = " . $_POST['geoinfo_lat'] . ", ";
          $query .= "`long` = " . $_POST['geoinfo_long'] . " ";
          $query .= "WHERE `video_id` = " . $_POST['geoinfo_video_id'];
          $db->Query ($query);
        }

      }

      $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved'";
      $query .= " ORDER BY video_id DESC";
      $result = $db->Query ($query);
      $total = $db->Count ($result);
?>

<h1>GeoInfo</h1>

<?php if ($total > 0): ?>
<div class="block list">
  <table>
    <thead>
        <tr>
            <td class="video-title large">Title</td>
            <td class="geoinfo large">GeoInfo</td>
            <td class="large">Map</td>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $db->FetchObj ($result)): ?>

      <?php $odd = empty ($odd) ? true : false; ?>
      <?php
      $video = new Video ($row->video_id);
      $query = "SELECT * FROM " . DB_PREFIX . "geoinfo WHERE video_id = " . $row->video_id;
      $geoinfo = null;
      $geoinfo_result = $db->Query ($query);
      $geoinfo_count = $db->Count ($geoinfo_result);

      if($geoinfo_count > 0) {
        $geoinfo = $db->FetchObj ($geoinfo_result);
        $lat = $geoinfo->lat;
        $long = $geoinfo->long;
      } else {
        $lat = 0;
        $long = 0;
      }

      ?>

      <tr class="<?=$odd ? 'odd' : ''?>">
        <td class="video-title">
            <a href="<?=ADMIN?>/videos_edit.php?id=<?=$video->video_id?>" class="large"><?=$video->title?></a><br />
            <img src="<?=$config->thumb_url?>/<?=$video->filename?>.jpg" width="200px"/>
        </td>
        <td class="video-geoinfo">
          <form method="post">
            <input type="hidden" name="geoinfo_action" value="update"/>
            <input type="hidden" name="geoinfo_video_id" value="<?= $row->video_id ?>"/>
            <p><label for="geoinfo_lat">Latitude :</label><br><input name="geoinfo_lat" value="<?= $lat ?>" type="text"/></p>
            <p><label for="geoinfo_long">Longitude :</label><br><input name="geoinfo_long" value="<?= $long ?>" type="text"/></p>
            <p><input value="Submit" type="submit"/></p>
          </form>
        </td>
        <td class="video-map">
          <?php if(isset($geoinfo)) { ?>
          <img border="0" alt="Marker of video <?= $video->title ?>" src="<?= GeoInfo::MapUrl($geoinfo->geoinfo_id) ?>"></img>
          <?php } else { ?>
          <p>Set geogephic informations to display map</p>
          <?php } ?>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="block"><strong>No videos found</strong></div>
<?php endif; ?>

<?php
    }
}
?>
