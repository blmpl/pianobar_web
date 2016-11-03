<?php 

$cmd = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : '';
if ( $cmd and ( in_array($cmd, array('+', '-' ,'n', 'p', 't' ) ) or preg_match('/^s\d+$/',$cmd) ) ) {

    $myfile = fopen("/tmp/pianobar_ctl", "w") or die("Unable to open file!");
    echo fwrite($myfile, $cmd . "\n");
    fclose($myfile);

    if ($_POST['cmd'])
        exit;
}

//if ($_REQUEST['get']) {
    
    $data = array();

    $myfile = fopen("/tmp/pianobar_status", "r") or die("Unable to open file!");
    while(!feof($myfile)) {
        #list($key, $value) = explode("=", str_replace(PHP_EOL, '',  fgets($myfile) ), 2);
        $res = explode("=", str_replace(PHP_EOL, '',  fgets($myfile) ), 2);
        if (isset($res[1])) {
            $data[$res[0] ] = $res[1];
        }
    }
    fclose($myfile);
    
    if (isset($_POST['get'])) {
        echo json_encode($data);
        exit;
    }
//}
?>
<html>
    <head>
        <title>Pianobar</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#7070ff">
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="stylesheet" href="css/bootstrap-theme.min.css">
        <script src="js/jquery-2.2.0.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <style>
            .m-button {
                width: 60px !important;
                margin-top: 5px;
            }
       </style>
    </head>
    <body >
        <div class="container theme-showcase" role="main" style="max-width: 650px !important; margin-top:20px;">
            <div class="jumbotron">
                <div>
                  <div style="float: left; max-width:333px; text-align: left">
                        <h2 id='artist'><?= $data['artist'] ?></h2>
                        <h3 id='title'><?php echo $data['title'] . ($data['rating'] == 1 ? '(&#9733;)' : '') ?></h3>
                    </div>
                    <div style="margin-top: 20px; width:125px;float: right;">
                        <div>
                            <button type="button" class="btn btn-sm btn-primary m-button" onclick="javascript:send_key('p')">pause</button>
                            <button type="button" class="btn btn-sm btn-primary m-button" onclick="javascript:send_key('n')">next</button>
                        </div>
                        <div>
                            <button id="like" type="button" class="btn btn-sm btn-success m-button" onclick="javascript:send_key('+')">like</button> 
                            <button type="button" class="btn btn-sm btn-danger m-button" data-toggle="modal" data-target="#confirm">ban</button> 
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="confirm" role="dialog"> 
                    <div class="modal-dialog" style='text-align: center'>
                        <div class="modal-content">
                            <p>Are you sure to ban a song?</p>
                            <p>
                                <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="javascript:send_key('-')">Yes</button>
                                <button type="button" class="btn btn-warning" data-dismiss="modal" data-toggle="tooltip" data-placement="bottom" title="ban song for 1 month" onclick="javascript:send_key('t')">I'm just tired</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
                            </p>    
                        </div>
                    </div>
                </div>
                <div style="display: inline-block;  margin-top: 30px; width: 100%;">
                    <img id=cover style="width: 100%; max-height: 500px; max-width:500px" src=" <?= $data['coverArt'] ?>">
                </div>
                <!--img id=cover style="display: none; width: 500px; height: 500px;"></br-->
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" style="width: 100%">
                        <span id="station"></span> station
                        <span class="caret"></span></button>
                        <ul class="dropdown-menu" style="width: 100%; text-align:center">
                        <?php
                            if ($data['stationCount'] > 0 ) 
                                for($i=0; $i < $data['stationCount']; $i++) {
                                    printf('<li type="station" id="station%d"><a href="#" onclick="javascript:send_key(\'s%d\')">%s</a></li>' . "\n", $i, $i, $data['station' . $i]);
                                }
                        ?>    
                        </ul>
                    </div>
            </div>
        </div>

        <script type="text/javascript">
        var is_old = navigator.userAgent.indexOf("Android 1.6") > 0;

        var window_has_focus = 1;
        var timer;
        var last_title;

        if (is_old) 
            setTimeout(window.location.href='?', 10000);

        $(document).ready(function(){
                $('[data-toggle="tooltip"]').tooltip(); 
        });

        function send_key( key ) {
            if (is_old) {
                url = '?cmd=' + encodeURIComponent(key);
                //location.href = url;
                window.location.href = url;
                return false;
            }
            $('body').css('cursor', 'progress'); 
            $.post('?', {cmd : key});
            clearTimeout(timer);
            timer = setTimeout(refresh_title, 500);
            return false;
        }

        function refresh_title() {
            if (! $('#confirm').hasClass('in'))
                   $.ajax({
                        type: "POST",
                        data: 'get=1',
                        cache: false,
                        success: function(data){
                          //  setTimeout(function() {
                          //      location.reload('');
                          //  }, 1000);
                          json = JSON.parse(data);
                          if (json.artist) {

                              $('#confirm').modal('hide');
                              last_title = json.title;
                              update = 0;

                              document.title = (json.rating == 1 ? '★': '♬' ) + json.artist + ' - ' + json.title;
                              //$('#title').html(json.artist + ' - ' + json.title + ( json.rating ? ' (&hearts;)' : ''));
                              $('#title').html(json.title + ( json.rating == 1 ? ' (&#9733;)' : ''));
                              $('#artist').html(json.artist);
                              $('#cover').attr("src_tmp",json.coverArt);
                              if ( ( window_has_focus == 1 || location.search.search("forceCover=1") > 0) &&  $('#cover').attr("src") != json.coverArt) {
                                    $('#cover').attr("src",json.coverArt);
                              }
                              $('#cover').show();
                              $('#like').prop("disabled", json.rating == 1);

                              $('#station').html(json.stationName);
                              jQuery("li[type='station']").each(function() {
                                  (this.innerText == json.stationName) ? $(this).addClass("disabled") : $(this).removeClass("disabled");
                              });
                          }
                        $('body').css('cursor', 'default'); 
                        }
                    });
              timer = setTimeout(refresh_title, 2000); 
        }

        //$(document).on("keypress", function (e) {
        //        document.title = e.which
        //});
        if (! is_old ){
            $(window).blur(function(){ window_has_focus = 0  });
            $(window).focus(function(){ 
                if (window_has_focus == 0) {
                    $('#cover').attr("src", $('#cover').attr("src_tmp") )
                }; 
                window_has_focus = 1  
            });
        
            refresh_title();
        }
        </script>
    </body>
</html>
