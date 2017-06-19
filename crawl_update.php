<?php
require_once ("simple_html_dom.php");//クロール用
clearstatcache();

$today = getdate();
$date = new DateTime();
$date->setDate($today['year'],$today['mon'],$today['mday']);
$date_url = $date->format('Ymd');

$json_update = false;//更新したjsonファイルを取得するかどうか

$update_time_filename = 'last_time.json';
$jsonData_time[] = ['last_time' => date("Y-m-d H:i:s")];
file_put_contents('./json/'.$update_time_filename, json_encode($jsonData_time ,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
echo "出力";

$page_num = 1;
$diary_count = 0;

do {
	$url  = '//'. $date_url .'/'.$page_num.'/';
	$html = file_get_html ($url);
	$filename = 'diary' . $date_url.'.json';
	$diary_count = 0;

	foreach($html->find('.diary_photolay_tbl') as $element){
		$img = $element->find("a", 0);
		$img_a = $img->find("img",0);

		$diary_time = $element->find(".diarytime",0)->plaintext;
		if($diary_time != null){
			$diary_count++;
		}

		if($img_a != null){
				$img_url = 'http:' . $img_a->src;
				$cast_name = $element->find("a", 1)->plaintext;
				$title = $element->find("a", 2)->plaintext;
				$diary_link = '' . $img->href;
		}else{
				$img_a = $element->find("img", 0);
				$img_url = 'http:' . $img_a->src;
				$cast_name = $element->find("a", 0)->plaintext;
				$title = $element->find("a", 1)->plaintext;
				$diary_time = $element->find(".diarytime",0)->plaintext;
				$diary_link = '' . $img->href;
		}


			if($page_num == 1)$jsonData[] = ['title' => $title, 'link' => $diary_link,'name' => $cast_name,'img' =>  basename($img_url),'time' => $diary_time ];
			else{
				$json_date = array();//配列を空にする
				$json_date[] = ['title' => $title, 'link' => $diary_link,'name' => $cast_name,'img' =>  basename($img_url),'time' => $diary_time ];
				$jsonData  = array_merge($jsonData, $json_date);
			}

	}

	$page_num++;//次のページ
} while($diary_count >= 10);

	if(file_exists('./json/'.$filename)){//もし既に今日のjsonが存在したら、評価する
		$json_check = json_decode( file_get_contents('./json/diary' . $date_url . '.json') , true );
		$json_check_count = count($json_check);
	}else{
		$json_check_count = count($jsonData);
	}

	if( count($jsonData) != $json_check_count){//更新されていたら、新規でまとめjsonの作成
		$json_update = true;
	}

	$filename = 'diary' . $date_url . '.json';
	file_put_contents('./json/'.$filename, json_encode($jsonData ,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));//日付ごとのjsonデータ作成


$jsonData = array();

if($json_update == true){//もし上記で今日のjsonが更新されたら
echo "jsonファイルを統合します<br>";

	$date_num = 30;
	$date->setDate($today['year'],$today['mon'],$today['mday']);
	$date_url = $date->format('Ymd');

	for ($i = $date_num; $i >= 1; $i--){
		$filename = 'diary' . $date_url.'.json';
		if(file_exists('./json/'.$filename)){
			$json_date = json_decode( file_get_contents('./json/'.$filename) , true );//各日付ごとのjsonファイルを読み込み
			if($json_date != null)$jsonData = array_merge($jsonData, $json_date);//マージしていく
		}
		$date->modify('-1 days');
		$date_url = $date->format('Ymd');
	}

	$filename = 'diary.json';
	if($jsonData != null){
	$jdata = 'var jsonData =' . json_encode($jsonData ,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) . ';';
	file_put_contents($filename, $jdata);//jsonデータ作成
	}

	//JSONデータを出力
	header(" Content-Type:application/json; charset=utf-8");
	echo file_get_contents($filename);

}

echo "終了しました";
?>
