<?php

class PlayListElement {

    public $name, $description, $duration, $sources, $thumbnail, $poster, $videoStartSeconds, $created, $likes, $views, $videos_id, $className, $tracks;

    function __construct($name, $description, $duration, $playListSource, $playListThumbnail, $poster, $videoStartSeconds, $created, $likes, $views, $videos_id, $className='', $tracks=array()) {
        $this->name = $name;
        $this->description = $description;
        $this->setDuration($duration);
        $this->sources = $playListSource;
        $this->thumbnail = $playListThumbnail;
        $this->poster = $poster;
        $this->videoStartSeconds = intval($videoStartSeconds);
        $this->created = empty($created)?0:strtotime($created);
        $this->likes = $likes;
        $this->views = $views;
        $this->videos_id = $videos_id;
        $this->className = $className;
        $this->tracks = $tracks;
    }

    
    function getName() {
        return $this->name;
    }

    function getDescription() {
        return $this->description;
    }

    function getDuration() {
        return $this->duration;
    }

    function getPlayListSource() {
        return $this->sources;
    }

    function getPlayListThumbnail() {
        return $this->thumbnail;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setDescription($description) {
        $this->description = $description;
    }

    function setDuration($duration) {
        if (!is_int($duration)) {
            $duration = parseDurationToSeconds($duration);
        }
        $this->duration = $duration;
    }

    function setPlayListSource($playListSource) {
        $this->sources = $playListSource;
    }

    function setPlayListThumbnail($playListThumbnail) {
        $this->thumbnail = $playListThumbnail;
    }
    
    function getSources() {
        return $this->sources;
    }

    function getThumbnail() {
        return $this->thumbnail;
    }

    function getPoster() {
        return $this->poster;
    }

    function getVideoStartSeconds() {
        return $this->videoStartSeconds;
    }

    function getCreated() {
        return $this->created;
    }

    function getLikes() {
        return $this->likes;
    }

    function getViews() {
        return $this->views;
    }

    function getVideos_id() {
        return $this->videos_id;
    }

    function setSources($sources) {
        $this->sources = $sources;
    }

    function setThumbnail($thumbnail) {
        $this->thumbnail = $thumbnail;
    }

    function setPoster($poster) {
        $this->poster = $poster;
    }

    function setVideoStartSeconds($videoStartSeconds) {
        $this->videoStartSeconds = $videoStartSeconds;
    }

    function setCreated($created) {
        $this->created = $created;
    }

    function setLikes($likes) {
        $this->likes = $likes;
    }

    function setViews($views) {
        $this->views = $views;
    }

    function setVideos_id($videos_id) {
        $this->videos_id = $videos_id;
    }


}

class playListSource {

    public $src, $type, $label, $videos_id;
    static $videoHLSObj;
    
    function __construct($src, $videos_id, $youtube = false) {
        $TimeLogLimit = 0.1;
        $timelogname = __FILE__.'::playListSource::__construct';
        TimeLogStart($timelogname);
        $this->src = $src;
        $this->videos_id = intval($videos_id);
        
        $this->label = getResolutionFromFilename($src, false);
        TimeLogEnd($timelogname, __LINE__, $TimeLogLimit);
        if(empty($this->label)){
            $this->label = 'Auto';
        }else{
            $this->label .= 'p';
        }
        if($youtube){
            $this->type = "video/youtube";
        }else{
            $this->type = mime_content_type_per_filename($src);
        }
        TimeLogEnd($timelogname, __LINE__, $TimeLogLimit);
        if($this->type=="application/x-mpegURL"){
            TimeLogEnd($timelogname, __LINE__, $TimeLogLimit);
            if(!isset(playListSource::$videoHLSObj)){
                playListSource::$videoHLSObj = AVideoPlugin::getDataObject('VideoHLS');
            }
            TimeLogEnd($timelogname, __LINE__, $TimeLogLimit);
            if(!empty(playListSource::$videoHLSObj->downloadProtection)){
                if(!preg_match('/token=/', $this->src)){
                    $this->src = addQueryStringParameter($this->src, 'token', VideoHLS::getToken($this->videos_id));
                }
            }
        }
        TimeLogEnd($timelogname, __LINE__, $TimeLogLimit);
    }


}

class playListThumbnail {

    public $srcset, $type, $media = '(min-width: 400px;)';


}
