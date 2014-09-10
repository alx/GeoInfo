CumulusClips Plugin : GeoInfo
=============================

Add geographic information (latitude/longitude) for each video.

# Install

1. Go to your CumulusClips plugin folder
2. ```git clone https://github.com/alx/GeoInfo.git```
3. In CumulusClips admin interface, enable GeoInfo plugin from plugins panel

# Usage

1. In CumulusClips admin interface, go to plugins panel and click on GeoInfo settings
2. You'll find a list of your video, for each video you can set a latitude and longitude
3. When a latitude/longitude is set for a video, a preview map of the location will be displayed

You can access to location information from the template using the following method :

```
$video = new Video ($video_id);
$geoinfo = GeoInfo::FromVideo($video->id);
echo 'latitude : ' . $geoinfo->lat . ' - longitude : ' . $geoinfo->long;
```

You can display a preview map (as in plugin settings) using the following method : 

```
$video = new Video ($video_id);
$geoinfo = GeoInfo::FromVideo($video->id);
echo '<img src=' . GeoInfo::MapUrl($geoinfo->geoinfo_id) . '/>';
```

# Screenshot

![Screenshot](/docs/screenshot.png?raw=true)
