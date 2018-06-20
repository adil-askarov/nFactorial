<?
$biz_id = 0;
if(isset($_POST["biz_id"])) {
	$biz_id = intval($_POST["biz_id"]);
}
$stat_period = "";
if(isset($_POST["stat_period"])) {
	$stat_period = htmlspecialchars($_POST["stat_period"]);
}
$stat_type = "";
if(isset($_POST["stat_type"])) {
	$stat_type = htmlspecialchars($_POST["stat_type"]);
}
$avg_revenue = 0;
if(isset($_POST["avg_revenue"])) {
	$avg_revenue = intval($_POST["avg_revenue"]);
}
require("../header_pre.php");

$curr_month = date("m");
$curr_year = date("Y");
$curr_date = new DateTime();
$response["column"] = array();
$action = array();
$max_count = 0;
$view_total = 0;
$action_total = 0;
$revenue_total = 0;
if(!$avg_revenue) $avg_revenue = 3000;
if($stat_type == "revenue") {
	$avg_rev = $avg_revenue;
} else {
	$avg_rev = 1;
}
if($stat_period == "12month") {
	$sql = "SELECT id, type as d_type, DATE_FORMAT(date_post, '%Y%m') as date_f, DATE_FORMAT(date_post, '%m') as x
		FROM biz_activity WHERE biz_id=".$biz_id." and (DATE_FORMAT(date_post, '%Y%m') <= '".$curr_year.$curr_month."') 
		and (DATE_FORMAT(date_post, '%Y%m') > '".(intval($curr_year)-1).$curr_month."');";
	$result = GetSelectData($sql);
	while($row = mysql_fetch_array($result)) {
		$action[$row[d_type]]++;
		if($row["d_type"] != 'biz_page') {
			$action_total++;
		} else {
			$view_total++;
		}
		if((($stat_type == "view") && ($row["d_type"] == 'biz_page')) || ((($stat_type == "action") || ($stat_type == "revenue")) && ($row["d_type"] != 'biz_page'))) {
			$period[$row["date_f"]][count] += $avg_rev;
			if(intval($max_count) < intval($period[$row["date_f"]][count])) {
				$max_count = intval($period[$row["date_f"]][count]);
			}
		}
	}
	$curr_year--;
	$curr_month++;
	for($i=0;$i<=11;$i++) {
		if($i > 0) {
			$curr_month = intval($curr_month) + 1;
		} else {
			$curr_month = intval($curr_month);
		}
		if($curr_month == "13") {
			$curr_year = $curr_year + 1;
			$curr_month = 1;
		}
		if(intval($curr_month) < 10) {$curr_month = "0".$curr_month;}
		
		$period[$curr_year.$curr_month]["name"] = $curr_month;
		if(!$period[$curr_year.$curr_month]["count"]) {
			$period[$curr_year.$curr_month]["count"] = 0;
		}
		
		$column = array();
		$column["view_count"] = intval($period[$curr_year.$curr_month]["count"]);
		$column["period_name"] = $period[$curr_year.$curr_month]["name"];
		$column["period_name_full"] = MonthName(intval($curr_month))." ".$curr_year;
		array_push($response["column"], $column);
		if($i == 0) {
			$response["begin_period"] = MonthName(intval($curr_month))." ".$curr_year;
		} else
		if($i == 11) {
			$response["end_period"] = MonthName(intval($curr_month))." ".$curr_year;
		}
	}
} else 
if($stat_period == "1month") {
	$curr_date->modify('-29 days');
	$sql = "SELECT id, type, DATE_FORMAT(date_post, '%Y%m%d') as date_f, DATE_FORMAT(date_post, '%d') as x
		FROM biz_activity WHERE biz_id=".$biz_id." 
		and (date_post >= '".$curr_date->format('Y-m-d')."');";
	$result = GetSelectData($sql);
	while($row = mysql_fetch_array($result)) {
		$action[$row["type"]]++;
		if($row["type"] != 'biz_page') {
			$action_total++;
		} else {
			$view_total++;
		}
		if((($stat_type == "view") && ($row["type"] == 'biz_page')) || ((($stat_type == "action") || ($stat_type == "revenue")) && ($row["type"] != 'biz_page'))) {
			$period[$row["date_f"]][count] += $avg_rev;
			if(intval($max_count) < $period[$row["date_f"]][count]) {
				$max_count = $period[$row["date_f"]][count];
			}
		}
	}
	for($i=0;$i<=29;$i++) {
		
		$period[$curr_date->format('Ymd')][name] = $curr_date->format('d.m.Y');
		if(!$period[$curr_date->format('Ymd')][count]) {
			$period[$curr_date->format('Ymd')][count] = 0;
		}
		
		$column = array();
		$column["view_count"] = $period[$curr_date->format('Ymd')]["count"];
		$column["period_name"] = $period[$curr_date->format('Ymd')]["name"];
		$column["period_name_full"] = intval($curr_date->format('d'))." ".MonthName(intval($curr_date->format('m')))." ".$curr_date->format('Y');
		
		array_push($response["column"], $column);
		if($i == 0) {
			$response["begin_period"] = $curr_date->format('d')." ".MonthName(intval($curr_date->format('m'))).", ".$curr_date->format('Y');
		} else
		if($i == 29) {
			$response["end_period"] = $curr_date->format('d')." ".MonthName(intval($curr_date->format('m'))).", ".$curr_date->format('Y');
		}
		$curr_date->modify('+1 days');
	}
	
}
if($stat_type == "action") {
	$response["action_phone"] = intval($action['phone']);
	$response["action_website"] = intval($action['website']);
	$response["action_photo"] = intval($action['photo']);
	$response["action_bookmark"] = intval($action['bookmark']);
	$response["action_map"] = intval($action['map']);
	$response["action_checkin"] = intval($action['checkin']);
}
//if($max_count==0) {$max_count=0;} elseif($max_count<2) {$max_count=2;} elseif($max_count<4) {$max_count=4;} elseif($max_count<8) {$max_count=8;} elseif($max_count<10) {$max_count=12;} elseif($max_count<16) {$max_count=16;} elseif($max_count<20) {$max_count=20;} elseif($max_count<28) {$max_count=28;} elseif($max_count<40) {$max_count=40;} elseif($max_count<55) {$max_count=60;} elseif($max_count<75) {$max_count=80;} elseif($max_count<95) {$max_count=100;} elseif($max_count<155) {$max_count=160;} elseif($max_count<190) {$max_count=200;} elseif($max_count<270) {$max_count=280;} elseif($max_count<390) {$max_count=400;} elseif($max_count<580) {$max_count=600;} elseif($max_count<780) {$max_count=800;} elseif($max_count<980) {$max_count=1000;} elseif($max_count<1580) {$max_count=1600;} elseif($max_count<1970) {$max_count=2000;} elseif($max_count<2770) {$max_count=2800;} elseif($max_count<3950) {$max_count=4000;} elseif($max_count<5950) {$max_count=6000;} elseif($max_count<7900) {$max_count=8000;} elseif($max_count<11000) {$max_count=12000;} elseif($max_count<14000) {$max_count=16000;} elseif($max_count<18000) {$max_count=20000;} else {$max_count=ceil($max_count/500*6)*100;}
// изменил из-за дробного числа в промежутке $max_count = round(round(($max_count+1)/3,0)*4, 1-strlen($max_count));

$max_count = round(($max_count+1)/3,0)*4;
if(strlen($max_count) > 2) {
	$max_count = round($max_count, 2-strlen($max_count));
}

$response["y_max"] = $max_count;
$response["view_total"] = number_format($view_total, 0, ',', '.');
$response["action_total"] = number_format($action_total, 0, ',', '.');
$response["revenue_total"] = number_format($action_total*$avg_revenue, 0, ',', '.');
	
function MonthName($month_number) {
	$month_number = intval($month_number);
	if($month_number == 1) {
		return "Январь";
	}
	if($month_number == 2) {
		return "Февраль";
	}
	if($month_number == 3) {
		return "Март";
	}
	if($month_number == 4) {
		return "Апрель";
	}
	if($month_number == 5) {
		return "Май";
	}
	if($month_number == 6) {
		return "Июнь";
	}
	if($month_number == 7) {
		return "Июль";
	}
	if($month_number == 8) {
		return "Август";
	}
	if($month_number == 9) {
		return "Сентябрь";
	}
	if($month_number == 10) {
		return "Октябрь";
	}
	if($month_number == 11) {
		return "Ноябрь";
	}
	if($month_number == 12) {
		return "Декабрь";
	}
}

echo json_encode($response);
?>