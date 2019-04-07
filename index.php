<?php date_default_timezone_set('Asia/Seoul');?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="style.css">
    <script src="http://code.jquery.com/jquery-1.10.2.js"></script>
    <title>양식장관리시스템</title>
  </head>
  <body>
    <?php
    // 1000=1s
    $reload_interval_time = 5000; // 페이지 갱신 간격
    $m_result_loading_time = 3000; // 측정버튼 클릭 후 결과값 대기 시간 ( ※ 결과 대기 시간은 페이지 갱신 간격보다 작아야 함 )
    //$result_txt_file_path = "pi/";  // result.txt 파일저장경로
    //$req_txt_file_path = "pi/";     // cmd_*_req.txt 파일저장경로
    //$index_path = "/smartset/";  // index페이지 경로
    $result_txt_file_path = "../../../home/pi/";  // result.txt 파일저장경로
    $req_txt_file_path = "../../../home/pi/";     // cmd_*_req.txt 파일저장경로
    $index_path = "./";  // index페이지 경로

    $m_time = date('o M d H:i:s', filemtime($result_txt_file_path."result.txt")); // result.txt 최종수정시간 가져오기 ?>
  <div>
    <h1 class="title">양식장관리 시스템</h1>
    <h2 class="time"><span><b>현재시간</b><?=date('o M d H:i:s')?></span><span><b>측정시간</b><?=$m_time?></span></h2>
    <a href="" class="refresh">All Refresh<br><img src="img/refresh.png"></a>
    
    <?php 

    /* 측정 및 작동 요구 버튼 클릭 후 프로세스 */
    if(isset($_GET['ltime'])) {
      if($m_time<>$_GET['ltime']) { // result.txt의 최종수정시간이 바뀌었는지 비교
        echo "<script>location.replace('".$index_path."');</script>";
      } else if($_GET['fail']=="N") { // 3초 후 결과값이 들어왔는지 다시 탐색
        echo "<script>setTimeout(function() { location.replace('".$index_path."?loading=".$_GET['loading']."&ltime=".$m_time."&fail=L'); }, ".$m_result_loading_time.");</script>";
      } else if($_GET['fail']=="L") { // result.txt의 최종수정시간이 바뀌지 않았으면 응답없음으로 간주
        echo "<script>location.replace('".$index_path."?loading=".$_GET['loading']."&ltime=".$m_time."&fail=Y');</script>";
      }
    }

    /* 측정 및 작동 요구 버튼 클릭 시 txt파일 저장 프로세스 */
    if(isset($_GET['req'])) {
      $req_txt = "";
      switch($_GET['req']) {
        case "sen": $req_txt="1"; break;
        case "valm": $req_txt="2"; break;
        case "light": $req_txt="3"; break;
        case "misc1": $req_txt="4"; break;
        case "misc2": $req_txt="5"; break;
      }
      $req_txt .= "\n1\n";
      $reqfile = fopen($req_txt_file_path."cmd_".$_GET['req']."_req.txt", "w") or die("Unable to open file1!");  // txt파일 생성
      fwrite($reqfile, $req_txt);
      fclose($reqfile);
      echo "<script>location.replace('".$index_path."?loading=".$_GET['req']."&ltime=".$m_time."&fail=N');</script>";  // 측정 및 작동요구 결과 체크 프로세스로 이동
    }

    /* result.txt 데이터 로드 */
    $misc1 = "OFF";
    $light = "소등";
    $valm = "OFF";
    $misc2 = "OFF";
    $move = "미감지";
    $sen_wt = "";
    $sen_ph = "";
    $sen_sl = "";
    $is_read = false;

    $fp = fopen($result_txt_file_path."result.txt", "r"); // result.txt 읽어오기
    $fr = fread($fp, 1000);
    $data = explode("\n", $fr); // \n을 기준으로 배열 생성
    fclose($fp);

    // 값이 1이면 ON
    if($data[0]==1) $misc1 = "ON";
    if($data[1]==1) $light = "점등";
    if($data[2]==1) $valm = "ON";
    if($data[3]==1) $misc2 = "ON";
    if($data[4]==1) $move = "감지";

    if($data[5]=="999") $sen_wt="센서없음"; // 값이 999면 센서없음
    else $sen_wt = $data[5]."°C";
    
    if($data[6]=="999") $sen_ph="센서없음"; // 값이 999면 센서없음
    else $sen_ph = $data[6];

    if($data[7]=="999") $sen_sl="센서없음"; // 값이 999면 센서없음
    else $sen_sl = $data[7]."%";

    if($data[8]==0) $is_read = true;  // 이미 읽은 파일이면 읽음상태 true

    // 측정 및 작동 요구 버튼 클릭 후 상태 표시 (fail==N : 측정중  /  fail==Y : 응답없음)
    if(isset($_GET['loading'])) {
      switch($_GET['loading']) {
        case "sen": 
          if($_GET['fail']=="Y") {
            $sen_wt="응답없음";
            $sen_ph="응답없음";
            $sen_sl="응답없음";
          } else {
            $sen_wt="측정중";
            $sen_ph="측정중";
            $sen_sl="측정중";
          }
        break;
        case "valm": 
          if($_GET['fail']=="Y") {
            $valm="응답없음"; 
          } else {
            $valm="강제송출";
          }
        break;
        case "light": 
          if($_GET['fail']=="Y") {
            $light="응답없음"; 
          } else {
            $light="상태변경";
          }
        break;
        case "misc1": 
          if($_GET['fail']=="Y") {
            $misc1="응답없음"; 
          } else {
            $misc1="상태변경";
          }
        break;
        case "misc2": 
          if($_GET['fail']=="Y") {
            $misc2="응답없음"; 
          } else {
            $misc2="상태변경";
          }
        break;
      }
    }
    ?>
    
    <!-- 버튼 클릭 후 첫 결과값 읽어오기 전 까지 작동중지 ( href 제거 ) -->
    <div class="sensor">
      <h3>- 센서측정 -</h3>
      <ul><li<?php if($sen_wt=="응답없음") { ?> class="discon"<?php } else if($sen_wt=="센서없음") { ?> class="off"<?php } ?>>
          <a<?php if(!isset($_GET['fail']) || $_GET['fail']<>"N") { ?> href="./?req=sen"<?php } ?>>
            <div>
                <h4>수온</h4>
                <div class="line"></div>
                <img src="img/refresh2.png">
                <h5><?=$sen_wt?></h5>
            </div>
          </a>
        </li><li<?php if($sen_ph=="응답없음") { ?> class="discon"<?php } else if($sen_ph=="센서없음") { ?> class="off"<?php } ?>>
          <a<?php if(!isset($_GET['fail']) || $_GET['fail']<>"N") { ?> href="./?req=sen"<?php } ?>>
            <div>
                <h4>PH</h4>
                <div class="line"></div>
                <img src="img/refresh2.png">
                <h5><?=$sen_ph?></h5>
            </div>
          </a>
        </li><li<?php if($sen_sl=="응답없음") { ?> class="discon"<?php } else if($sen_sl=="센서없음") { ?> class="off"<?php } ?>>
          <a<?php if(!isset($_GET['fail']) || $_GET['fail']<>"N") { ?> href="./?req=sen"<?php } ?>>
            <div>
                <h4>염도</h4>
                <div class="line"></div>
                <img src="img/refresh2.png">
                <h5><?=$sen_sl?></h5>
            </div>
          </a>
        </li></ul>
    </div>
    <div class="sensor">
      <h3>- 상태제어 & 확인 -</h3>
      <ul><li<?php if($valm=="응답없음") { ?> class="discon"<?php } else if($valm=="OFF") { ?> class="off"<?php } ?>>
          <a<?php if(!isset($_GET['fail']) || $_GET['fail']<>"N") { ?> href="./?req=valm"<?php } ?>>
            <div>
                <h4>음성경보</h4>
                <div class="line"></div>
                <img src="img/refresh2.png">
                <h5><?=$valm?></h5>
            </div>
          </a>
        </li><li<?php if($light=="응답없음") { ?> class="discon"<?php } else if($light=="소등") { ?> class="off"<?php } ?>>
          <a<?php if(!isset($_GET['fail']) || $_GET['fail']<>"N") { ?> href="./?req=light"<?php } ?>>
            <div>
                <h4>조명제어</h4>
                <div class="line"></div>
                <img src="img/refresh2.png">
                <h5><?=$light?></h5>
            </div>
          </a>
        </li><li<?php if($move=="응답없음") { ?> class="discon"<?php } else if($move=="미감지") { ?> class="off"<?php } ?>>
          <a<?php if(false) { ?> href="./?req="<?php } ?>>
            <div>
                <h4>움직임감지</h4>
                <div class="line"></div>
                <img src="img/refresh2.png" style="visibility: hidden;">
                <h5 style="position: relative; bottom: 25px;"><?=$move?></h5>
            </div>
          </a>
        </li></ul>
        <ul><li<?php if($misc1=="응답없음") { ?> class="discon"<?php } else if($misc1=="OFF") { ?> class="off"<?php } ?>>
          <a<?php if(!isset($_GET['fail']) || $_GET['fail']<>"N") { ?> href="./?req=misc1"<?php } ?>>
            <div>
                <h4>기타1</h4>
                <div class="line"></div>
                <img src="img/refresh2.png">
                <h5><?=$misc1?></h5>
            </div>
          </a>
        </li><li<?php if($misc2=="응답없음") { ?> class="discon"<?php } else if($misc2=="OFF") { ?> class="off"<?php } ?>>
          <a<?php if(!isset($_GET['fail']) || $_GET['fail']<>"N") { ?> href="./?req=misc2"<?php } ?>>
            <div>
                <h4>카메라전원</h4>
                <div class="line"></div>
                <img src="img/refresh2.png">
                <h5><?=$misc2?></h5>
            </div>
          </a>
        </li></ul>
    </div>
  </div>

    <?php 
      if($is_read==false) { // result.txt를 아직 읽지 않은 경우 ( 마지막줄 값 1인 경우 )
        $myfile = fopen($result_txt_file_path."result.txt", "w") or die("Unable to open file2!");
        if(substr($fr,-2)=="1\n") {
          fwrite($myfile, substr($fr,0,-2)."0\n");  // 마지막줄 값 <1\n>을 지우고 <0\n> 추가
        } else if(substr($fr,-2)=="\n1") { 
          fwrite($myfile, substr($fr,0,-1)."0\n");  // 마지막줄 값 <1>을 지우고 <0\n> 추가 (마지막줄 \n이 빠져있는 경우)
        } else {
          fwrite($myfile, $fr);
        }
        fclose($myfile);
      }
    ?>
  </body>
</html>

<script>
// function downloadInnerHtml() {
//   var elHtml = "0x310x31";
//   elHtml = elHtml.replace(/\<br\>/gi,'\n');
//   var link = document.createElement('a');
//   link.setAttribute('download', "cmd_sen_req.txt");
//   link.setAttribute('href', 'data:txt;charset=utf-8,' + encodeURIComponent(elHtml));
//   link.click();
// }
//downloadInnerHtml();

setTimeout(function() {
  location.reload();
}, <?=$reload_interval_time?>); // 위에서 설정한 화면갱신 간격이 지나면 페이지 reload
</script>
