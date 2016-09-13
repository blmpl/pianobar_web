<?php 

$cmd = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : '';
if ( $cmd and in_array($cmd, array('+', '-' ,'n', 'p' )) ) {

    $myfile = fopen("/tmp/pianobar_ctl", "w") or die("Unable to open file!");
    echo fwrite($myfile, $cmd);
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
        if ($res[0] and $res[1]) {
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
            <div class="jumbotron" style='text-align: center'>
                <div>
                    <div style="float: left; max-width:333px; text-align: left">
                        <h2 id='artist'><?= $data['artist'] ?></h2>
                        <h3 id='title'><?php echo $data['title'] . (isset($data['rating']) ? '(&#9733;)' : '') ?></h3>
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
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <p>Are you sure to ban a song?</p>
                            <p>
                                <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="javascript:end_key('-')">Yes</button>
                                <button type="button" class="btn btn-warning" data-dismiss="modal" data-toggle="tooltip" data-placement="bottom" title="ban song for 1 month" onclick="javascript:end_key('t')">I'm just tired</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
                            </p>    
                        </div>
                    </div>
                </div>
                <div style="display: inline-block;  margin-top: 30px;">
                    <img id=cover style="width: 100%" src=" <?= $data['coverArt'] ?>">
                </div>
                <!--img id=cover style="display: none; width: 500px; height: 500px;"></br-->
            </div>
        </div>

        <script type="text/javascript">
        var is_old = navigator.userAgent.indexOf("Android 1.6") > 0;

        var window_has_focus = 1;
        var timer;
        var last_title;
        var update = 0;

        if (is_old) 
            setTimeout(window.location.href='?', 10000);

        $(document).ready(function(){
                $('[data-toggle="tooltip"]').tooltip(); 
        });

        function send_key( key ) {
            update = 1;
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
                   $.ajax({
                        type: "POST",
                        data: 'get=1',
                        cache: false,
                        success: function(data){
                          //  setTimeout(function() {
                          //      location.reload('');
                          //  }, 1000);
                          json = JSON.parse(data);
                          if (json.artist && (json.title != last_title || update) ) {

                              $('#confirm').modal('hide');
                              last_title = json.title;
                              update = 0;

                              document.title = (json.rating ? '★': '♬' ) + json.artist + ' - ' + json.title;
                              //$('#title').html(json.artist + ' - ' + json.title + ( json.rating ? ' (&hearts;)' : ''));
                              $('#title').html(json.title + ( json.rating ? ' (&#9733;)' : ''));
                              $('#artist').html(json.artist);
                              $('#cover').attr("src_tmp",json.coverArt);
                              if (window_has_focus == 1  &&  $('#cover').attr("src") != json.coverArt) {
                                    $('#cover').attr("src",json.coverArt);
                              }
                              $('#cover').show();
                              $('#like').prop("disabled", json.rating == 1);
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
