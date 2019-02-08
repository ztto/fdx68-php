<?php
header( 'Expires: Thu, 01 Jan 1970 00:00:00 GMT' );
header( 'Last-Modified: '.gmdate( 'D, d M Y H:i:s' ).' GMT' );

// HTTP/1.1
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', FALSE );

// HTTP/1.0
header( 'Pragma: no-cache' );

define("IMAGE_PATH", "/home/pi/fdximg/");
define("PROCESS_PATH", "/usr/local/bin/");
define("PROCESS_NAME1", "fddctl");
define("PROCESS_NAME2", "fddemu");
define("PROCESS_NAME3", "fdxconv");

// 初期設定
$fddType = array("XDF", "HDM", "2HD", "DUP", "IMG", "DIM", "D68", "D88", "D77");
$path = IMAGE_PATH;

// パラメタチェック
if(isset($_GET['reload'])){
    // 情報表示
    infoOut();
} else if(isset($_GET['shutdown'])){
    // 電源断
    exec("sudo shutdown now");
} else if(isset($_GET['reboot'])){
    // 再起動
    exec("sudo shutdown -r now");
} else if(isset($_GET['start'])){
    // 起動
    $command = "sudo ".PROCESS_PATH.PROCESS_NAME2." 2>/dev/null";
    exec($command . " > /dev/null &");
    // 1.0s 
    sleep(1);
    // 情報表示
    infoOut();
} else if(isset($_GET['stop'])){
    // 終了
    $command = "sudo pkill ".PROCESS_NAME2;
    $output = array();
    $ret = null;
    exec($command, $output, $ret);
    // 0.5s 
    usleep(500000);
    // 情報表示
    infoOut();
} else if(isset($_GET['param'])){
    $param = $_GET['param'];
    if($param[0] === 'file') {
        // ファイルinsert
        $path = $param[2];
        $command = PROCESS_PATH.PROCESS_NAME1.' -i '.$param[1].' -c insert '.$param[2].$param[3];
        $output = array();
        $ret = null;
        exec($command, $output, $ret);
        // 情報表示
        infoOut();
    } else if($param[0] === 'eject') {
        // ファイルeject
        $command = PROCESS_PATH.PROCESS_NAME1.' -i '.$param[1].' -c eject';
        $output = array();
        $ret = null;
        exec($command, $output, $ret);
        // 情報表示
        infoOut();
    } else if($param[0] === 'conv') {
        // ファイルconvert
        $path = $param[2];
        $command = PROCESS_PATH.PROCESS_NAME3.' -i '.$param[2].$param[3].' -o '.$param[2].$param[3].'.FDX';
        $output = array();
        $ret = null;
        exec($command, $output, $ret);
        // データ表示
        dataOut($path, $fddType);
    } else if($param[0] === 'dir') {
        // ディレクトリ表示
        $pos = strrpos($param[1], "..");
        if($pos !== false) {
            $pos1 = strrpos($param[1], "/", -5);
            $path = substr($param[1], 0, $pos1)."/";
        } else {
            $path = $param[1];
        }
        // データ表示
        dataOut($path, $fddType);
    }
} else {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>';
    // 情報表示
    infoOut();

    // 画面自動更新
    echo '<p><div>画面自動更新</div>';
    autoReload();

    // FDD操作
    echo '<p><div>Fddエジェクト</div>';
    fddOut($path);

    // データ表示
    echo '<p><div>Image操作</div>';
    dataOut($path, $fddType);

    // 起動/停止
    echo '<p><div>FDX68 起動/停止</div>';
    fdx68StartStop();

    // 再起動/電源断
    echo '<p><div>Raspberry Pi 再起動/電源断</div>';
    raspiRebootShut();

}

// 情報表示
function infoOut() {
    echo '<div id="info">';
    // プロセス確認
    echo '<p><div>プロセス状況:';
    $result = exec("ps -aef | grep ".PROCESS_NAME2." | grep -v grep", $output);
    if(empty($output)) {
        echo '停止中</div>';
    } else {
        echo '起動中</div>';
        $command = PROCESS_PATH.PROCESS_NAME1.' -l';
        $output = array();
        $ret = null;
        exec($command, $output, $ret);
        foreach ($output as $line){
            echo '<div>'.$line.'</div>';
        }
    }
    echo '</div>';
}

// 画面自動更新
function autoReload() {
    // 画面自動更新
    echo '<input id="chk" type="checkbox" value="画面自動更新" onclick="';
    echo 'var self = this;';
    echo 'function reload() {';
    echo '  var req = new XMLHttpRequest();';
    echo '  req.onreadystatechange = function(){';
    echo '    if(req.readyState == 4){';
    echo '      if(req.status == 200){';
    echo '        document.getElementById(\'info\').innerHTML = req.responseText;';
    echo '        if(self.checked){';
    echo '          setTimeout(reload, 3000);';
    echo '        }';
    echo '      }';
    echo '    }';
    echo '  };';
    echo '  req.open(\'GET\',\'index.php?reload=0\',true);';
    echo '  req.send(null);';
    echo '};';
    echo 'reload();';
    echo '"/>';
}

// FDD操作
function fddOut() {
    echo '<table border="0">';
    echo '<tr>';
    // FDD?エジェクト
    echo '<td>';
    echo '<select id="ejectSelect">';
    echo   '<option value="0">FDD0</option>';
    echo   '<option value="1">FDD1</option>';
    echo '</select>';
    echo '</td>';

    // FDD?エジェクト
    echo '<td>';
    echo '<input type="button" value="排出" onclick="';
    echo 'var req = new XMLHttpRequest();';
    echo 'req.onreadystatechange = function(){';
    echo '  if(req.readyState == 4){';
    echo '    if(req.status == 200){';
    echo '      document.getElementById(\'info\').innerHTML = req.responseText;';
    echo '    }';
    echo '  }';
    echo '};';
    echo 'req.open(\'GET\',\'index.php?param[]=eject&param[]=\'+document.getElementById(\'ejectSelect\').value,true);';
    echo 'req.send(null);"/>';
    echo '</td>';

    echo '</tr>';
    echo '</table>';
}

// データ表示
function dataOut($path, $fddType) {
    $array_dir = array();
    $array_file1 = array();
    $array_file2 = array();
    // フォルダチェック
    if($dir = opendir($path)) {
        while(($file = readdir($dir)) !== FALSE) {
            $file_path = $path.$file;
            if(!is_file($file_path)) {
                if(($path === IMAGE_PATH) &&
                   ($file === '..')) {
                    continue;
                }
                //ディレクトリを表示
                if($file !== '.') {
                    $array_dir[] = $file;
                }
            } else {
                //ファイルを表示
                $path_data = pathinfo($file);
                $ext = strtoupper($path_data['extension']);
                if($ext === 'FDX') {
                    $array_file1[] = $file;
                } else if(in_array($ext, $fddType)) {
                    $array_file2[] = $file;
                }
            }
        }
        closedir($dir);

        sort($array_dir);
        sort($array_file1);
        sort($array_file2);

        echo '<table id="table" border="0">';
        foreach ($array_dir as $file) {
            //ディレクトリを表示
            echo '<tr>';
            echo '<td>';
            echo '<input type="button" value="変更" onclick="';
            echo 'var req = new XMLHttpRequest();';
            echo 'req.onreadystatechange = function(){';
            echo '  if(req.readyState == 4){';
            echo '    if(req.status == 200){';
            echo '      document.getElementById(\'table\').innerHTML = req.responseText;';
            echo '    }';
            echo '  }';
            echo '};';
            echo 'req.open(\'GET\',\'index.php?param[]=dir&param[]='.$path.$file.'/'.'\',true);';
            echo 'req.send(null);"/>';
            echo '</td>';
            echo '<td>';
            echo '</td>';
            echo '<td>';
            echo '<div>'.$file.'</div>';
            echo '</td>';
            echo '</tr>';
        }
        $cnt = 0;
        foreach ($array_file1 as $file) {
            $cnt++;
            echo '<tr>';
            echo '<td>';
            echo '<select id="insertSelect'.$cnt.'">';
            echo   '<option value="0">FDD0</option>';
            echo   '<option value="1">FDD1</option>';
            echo '</select>';
            echo '</td>';

            echo '<td>';
            echo '<input type="button" value="挿入" onclick="';
            echo 'var req = new XMLHttpRequest();';
            echo 'req.onreadystatechange = function(){';
            echo '  if(req.readyState == 4){';
            echo '    if(req.status == 200){';
            echo '      document.getElementById(\'info\').innerHTML = req.responseText;';
            echo '    }';
            echo '  }';
            echo '};';
            echo 'req.open(\'GET\',\'index.php?param[]=file&param[]=\'+document.getElementById(\'insertSelect'.$cnt.'\').value+\'&param[]='.$path.'&param[]='.$file.'\',true);';
            echo 'req.send(null);"/>';
            echo '</td>';

            echo '<td>';
            echo '<div>'.$file.'</div>';
            echo '</td>';
            echo '</tr>';
        }

        foreach ($array_file2 as $file) {
            echo '<tr>';
            echo '<td>';
            echo '<input type="button" value="変換" onclick="';
            echo 'var req = new XMLHttpRequest();';
            echo 'req.onreadystatechange = function(){';
            echo '  if(req.readyState == 4){';
            echo '    if(req.status == 200){';
            echo '      document.getElementById(\'table\').innerHTML = req.responseText;';
            echo '    }';
            echo '  }';
            echo '};';
            echo 'req.open(\'GET\',\'index.php?param[]=conv&param[]=0&param[]='.$path.'&param[]='.$file.'\',true);';
            echo 'req.send(null);"/>';
            echo '</td>';

            echo '<td>';
            echo '</td>';

            echo '<td>';
            echo '<div>'.$file.'</div>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}

// 起動/停止
function fdx68StartStop() {
    // 起動
    echo '<input type="button" value="起動" onclick="';
    echo 'var req = new XMLHttpRequest();';
    echo 'req.onreadystatechange = function(){';
    echo '  if(req.readyState == 4){';
    echo '    if(req.status == 200){';
    echo '      document.getElementById(\'info\').innerHTML = req.responseText;';
    echo '    }';
    echo '  }';
    echo '};';
    echo 'req.open(\'GET\',\'index.php?start=0\',true);';
    echo 'req.send(null);"/>';
    // 停止
    echo '<input type="button" value="停止" onclick="';
    echo 'var req = new XMLHttpRequest();';
    echo 'req.onreadystatechange = function(){';
    echo '  if(req.readyState == 4){';
    echo '    if(req.status == 200){';
    echo '      document.getElementById(\'info\').innerHTML = req.responseText;';
    echo '    }';
    echo '  }';
    echo '};';
    echo 'req.open(\'GET\',\'index.php?stop=0\',true);';
    echo 'req.send(null);"/>';
}

// 再起動/電源断
function raspiRebootShut() {
    // 再起動
    echo '<input type="button" value="再起動" onclick="';
    echo 'var req = new XMLHttpRequest();';
    echo 'req.onreadystatechange = function(){';
    echo '  if(req.readyState == 4){';
    echo '    if(req.status == 200){';
    echo '      location.reload();';
    echo '    }';
    echo '  }';
    echo '};';
    echo 'req.open(\'GET\',\'index.php?reboot=0\',true);';
    echo 'req.send(null);"/>';
    // 電源断
    echo '<input type="button" value="電源断" onclick="';
    echo 'var req = new XMLHttpRequest();';
    echo 'req.onreadystatechange = function(){';
    echo '  if(req.readyState == 4){';
    echo '    if(req.status == 200){';
    echo '      location.reload();';
    echo '    }';
    echo '  }';
    echo '};';
    echo 'req.open(\'GET\',\'index.php?shutdown=0\',true);';
    echo 'req.send(null);"/>';

    echo '</body></html>';
}
?>
