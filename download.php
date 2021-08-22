<?php
/*************************************************************************
 _   _       _     _   _  __     _      _____  
| \ | | __ _| |__ (_) | |/ /    / \    |__  /  
|  \| |/ _` | '_ \| | | ' /    / _ \     / /   
| |\  | (_| | |_) | | | . \ _ / ___ \ _ / /_ _ 
|_| \_|\__,_|_.__/|_| |_|\_(_)_/   \_(_)____(_)

**************************************************************************
* @Author       Nabi KaramAliZadeh
* @Website      www.Nabi.ir
* @Email        nabikaz@gmail.com
* @Package      Faradars Downloader
* @Version      1.0.0
* @Project      https://github.com/NabiKAZ/faradars-downloader
* @Copyright 2021 Nabi K.A.Z. , All rights reserved.
* @Released under the terms of the GNU General Public License v3.0
*************************************************************************/

echo "Faradars Downloader - version 1.0.0 - Copyright 2021\n";
echo "By Nabi KaramAliZadeh <www.nabi.ir> <nabikaz@gmail.com>\n";
echo "Project link: https://github.com/NabiKAZ/faradars-downloader\n";
echo "============================================================\n\n";

//خواندن فایل تنظیمات
require './config.php';

//ایجاد دایرکتوری دانلود
if (!is_dir($path)) {
	mkdir($path);
}

//دریافت لیست و آدرس دوره ها
echo 'Getting list of courses... ';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://faradars.org/ev/moharam400');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$headers = array();
$headers[] = 'Cookie: ' . $cookies;
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$result = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error: ' . curl_error($ch) . PHP_EOL;
}
curl_close($ch);
$reg = '/<div class="fd-frame">.*?<a href="(.*?)".*?src=".*?">.*?<h3 class="fd-title">(.*?)<br>/ms';
preg_match_all($reg, $result, $matches, PREG_SET_ORDER, 0);
$courses = [];
foreach($matches as $match) {
	$courses[] = [
		'title' => trim($match[2]),
		'url' => trim($match[1]),
	];
}
$total = count($courses);
echo 'Found ' . $total . ' courses. Done.' . PHP_EOL;
echo '==================================================' . PHP_EOL;

$n = 0;
foreach ($courses as $course) {
	
	//شمارشگر فهرست دوره ها
	$n++;
	echo '> Course ' . $n . '/' . $total . ' :' . PHP_EOL;
	
	//خرید یا ثبت سفارش دوره
	echo 'Buy the course (' . $course['title'] . ')... ';
	$id = pathinfo($course['url'], PATHINFO_BASENAME);
	$id = substr($id, 0, strpos($id, '-'));
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://faradars.org/courses/free');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'sku=' . $id);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$headers = array();
	$headers[] = 'Cookie: ' . $cookies;
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$result = curl_exec($ch);
	if (curl_errno($ch)) {
		echo 'Error: ' . curl_error($ch) . PHP_EOL;
	}
	curl_close($ch);
	$result = json_decode($result, true);
	if ($result['status'] === true) {
		echo 'Done.' . PHP_EOL;
	} else {
		echo 'Error!' . PHP_EOL;
	}
	
	//دریافت محتوای صفحه دوره
	echo 'Getting page of course (' . $course['url'] . ')... ';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $course['url']);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$headers = array();
	$headers[] = 'Cookie: ' . $cookies;
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$result = curl_exec($ch);
	if (curl_errno($ch)) {
		echo 'Error: ' . curl_error($ch) . PHP_EOL;
	}
	curl_close($ch);
	echo 'Done.' . PHP_EOL;
	
	//دریافت آدرس و حجم فایل دوره
	$reg = '/<div class="text-center my-4">.*?href="(.*?)".*?دانلود به صورت یک‌جا.*?حجم دانلود:(.*?)<\/span>.*?<\/div>/ms';
	preg_match($reg, $result, $matches);
	$url = trim($matches[1]);
	$size = trim($matches[2]);
	
	//ایجاد دایرکتوری فایل دوره
	if (!is_dir($path . '/' . $course['title'])) {
		mkdir($path . '/' . $course['title']);
	}
	
	//ذخیره صفحه دوره
	echo 'Downloading page of course... ';
	file_put_contents($path . '/' . $course['title'] . '/' . $course['title'] . '.htm', $result);
	echo 'Done.' . PHP_EOL;
	
	//ذخیره فایل دوره
	echo 'Downloading file of course (' . $url . ') (size: ' . $size . ')... ';
	$file_ext = pathinfo($url, PATHINFO_EXTENSION);
	$file_name = pathinfo($course['url'], PATHINFO_BASENAME);
	$fp = fopen($path . '/' . $course['title'] . '/' . $file_name . '.' . $file_ext, 'w+');
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$headers = array();
	$headers[] = 'Cookie: ' . $cookies;
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_exec($ch);
	if (curl_errno($ch)) {
		echo 'Error: ' . curl_error($ch) . PHP_EOL;
	}
	curl_close($ch);
	fclose($fp);
	echo 'Done.' . PHP_EOL;
	echo '===========================' . PHP_EOL;
}
