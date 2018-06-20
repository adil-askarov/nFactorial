<?
require("../header_pre.php");

$a = 1;
$url_count = 0;
$limit = 40000;
$xml_pre = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns:xsi="https://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://www.sitemaps.org/schemas/sitemap/0.9 https://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">';
 $xml.='
	<url>
		<loc>https://www.wikicity.kz/</loc>
		<lastmod>'.date("Y-m-d").'</lastmod>
		<changefreq>daily</changefreq>
		<priority>1</priority>
	</url>
	<url>
		<loc>https://www.wikicity.kz/invite_friends/</loc>
		<lastmod>'.date("Y-m-d").'</lastmod>
		<changefreq>daily</changefreq>
		<priority>0.7</priority>
	</url>
	<url>
		<loc>https://www.wikicity.kz/about/</loc>
		<lastmod>'.date("Y-m-d").'</lastmod>
		<changefreq>daily</changefreq>
		<priority>0.7</priority>
	</url>
	<url>
		<loc>https://www.wikicity.kz/contact/</loc>
		<lastmod>'.date("Y-m-d").'</lastmod>
		<changefreq>daily</changefreq>
		<priority>0.7</priority>
	</url>
	<url>
		<loc>https://www.wikicity.kz/guideline/</loc>
		<lastmod>'.date("Y-m-d").'</lastmod>
		<changefreq>daily</changefreq>
		<priority>0.7</priority>
	</url>
	<url>
		<loc>https://www.wikicity.kz/advertise/</loc>
		<lastmod>'.date("Y-m-d").'</lastmod>
		<changefreq>daily</changefreq>
		<priority>0.7</priority>
	</url>';
$url_count = $url_count + 6;

/// добавление статичных страниц
$res = GetSelectData("select id, str_id from city where active=1 order by order_id;");
while($city_d = mysql_fetch_array($res)) { 
	$xml.='
	<url>
		<loc>https://www.wikicity.kz/'.$city_d["str_id"].'</loc>
		<lastmod>'.date("Y-m-d").'</lastmod>
		<changefreq>daily</changefreq>
		<priority>1</priority>
	</url>
	<url>
		<loc>https://www.wikicity.kz/browse/review/'.$city_d["str_id"].'/recent</loc>
		<lastmod>'.date("Y-m-d").'</lastmod>
		<changefreq>daily</changefreq>
		<priority>0.7</priority>
	</url>';
	if($city_d["str_id"]=="almaty") {
		$xml.='<url>
			<loc>https://www.wikicity.kz/events/'.$city_d["str_id"].'</loc>
			<lastmod>'.date("Y-m-d").'</lastmod>
			<changefreq>daily</changefreq>
			<priority>0.9</priority>
		</url>
		<url>
			<loc>https://www.wikicity.kz/events/'.$city_d["str_id"].'/browse/</loc>
			<lastmod>'.date("Y-m-d").'</lastmod>
			<changefreq>daily</changefreq>
			<priority>0.8</priority>
		</url>';
		$url_count = $url_count + 2;
	}
	$url_count = $url_count + 2;
	if($url_count > $limit) {
		$xml_r[$a] = $xml;
		$url_count = 0;
		$a++;
		$xml = "";
	}
}
//добавление компаний
$res = GetSelectData("select Reg_code,str_id,city_id,main_img_id from company where filtered=0 order by id;");
while($biz_d = mysql_fetch_array($res)) { 
	$xml.='
	<url>
		<loc>https://www.wikicity.kz/biz/'.$biz_d["str_id"].'</loc>
		<lastmod>'.date("Y-m-d").'</lastmod>
		<changefreq>daily</changefreq>
		<priority>1</priority>
	</url>';
	$url_count = $url_count + 1;
	if($biz_d["main_img_id"]>0) {
		$xml.='
		<url>
			<loc>https://www.wikicity.kz/biz_fotos/'.$biz_d["str_id"].'</loc>
			<lastmod>'.date("Y-m-d").'</lastmod>
			<changefreq>daily</changefreq>
			<priority>0.8</priority>
		</url>';
		$url_count = $url_count + 1;
	}
	if($url_count > $limit) {
		$xml_r[$a] = $xml;
		$url_count = 0;
		$a++;
		$xml = "";
	}
}

//списки
$res = GetSelectData("select id from list where filtered=0 order by id;");
while($list_d = mysql_fetch_array($res)) { 
	$xml.='
	<url>
		<loc>https://www.wikicity.kz/list/?list_id='.$list_d["id"].'</loc>
		<lastmod>'.date("Y-m-d").'</lastmod>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>';
	$url_count = $url_count + 1;
	if($url_count > $limit) {
		$xml_r[$a] = $xml;
		$url_count = 0;
		$a++;
		$xml = "";
	}
}

$res = GetSelectData("select id, str_id from city where active=1 order by order_id;");
while($city_d = mysql_fetch_array($res)) {
	$cat_res = GetSelectData("select cat.id, cat.class_name, count(c.id) as company_count
							from category cat
							left join company_category cc on cat.id=cc.category_id
							left join company c on c.id=cc.company_id
							where c.filtered=0 and c.city_id=".$city_d["id"]."
							group by cat.id having company_count>0
							order by cat.id;");
	while($cat_d = mysql_fetch_array($cat_res)) {
		$xml.='
		<url>
			<loc>https://www.wikicity.kz/search/'.$city_d["str_id"].'/'.$cat_d["class_name"].'</loc>
			<lastmod>'.date("Y-m-d").'</lastmod>
			<changefreq>daily</changefreq>
			<priority>0.8</priority>
		</url>';
		
		$url_count = $url_count + 1;
		if($url_count > $limit) {
			$xml_r[$a] = $xml;
			$url_count = 0;
			$a++;
			$xml = "";
		}
	}
}
// добавление страниц пользователей
$res = GetSelectData("select reg_code,review_count,friend_count,
					(select count(*) from list where user_id=user.id) as list_count,
					(select count(*) from company_bookmark where user_id=user.id) as bookmark_count,
					(select count(*) from user_compliment where user_id=user.id and accepted=1) as compliment_count
					from user order by id;");
while($user_d = mysql_fetch_array($res)) { 
	$xml.='
	<url>
		<loc>https://www.wikicity.kz/user_details/?Reg_code='.$user_d["reg_code"].'</loc>
		<lastmod>'.date("Y-m-d").'</lastmod>
		<changefreq>daily</changefreq>
		<priority>0.6</priority>
	</url>';
	$url_count = $url_count + 1;
	if($user_d["list_count"]>0) {
		$xml.='
		<url>
			<loc>https://www.wikicity.kz/user_details/user_details_list.php?Reg_code='.$user_d["reg_code"].'</loc>
			<lastmod>'.date("Y-m-d").'</lastmod>
			<changefreq>daily</changefreq>
			<priority>0.6</priority>
		</url>';
		$url_count = $url_count + 1;
	}
	if($user_d["review_count"]>0) {
		$xml.='
		<url>
			<loc>https://www.wikicity.kz/user_details/review_list.php?Reg_code='.$user_d["reg_code"].'</loc>
			<lastmod>'.date("Y-m-d").'</lastmod>
			<changefreq>daily</changefreq>
			<priority>0.6</priority>
		</url>';
		$url_count = $url_count + 1;
	}
	if($user_d["compliment_count"]>0) {
		$xml.='
		<url>
			<loc>https://www.wikicity.kz/user_details/compliments.php?Reg_code='.$user_d["reg_code"].'</loc>
			<lastmod>'.date("Y-m-d").'</lastmod>
			<changefreq>daily</changefreq>
			<priority>0.5</priority>
		</url>';
		$url_count = $url_count + 1;
	}
	if($user_d["friend_count"]>0) {
		$xml.='
		<url>
			<loc>https://www.wikicity.kz/user_details/user_details_friends.php?Reg_code='.$user_d["reg_code"].'</loc>
			<lastmod>'.date("Y-m-d").'</lastmod>
			<changefreq>daily</changefreq>
			<priority>0.5</priority>
		</url>';
		$url_count = $url_count + 1;
	}
	if($user_d["bookmark_count"]>0) {
		$xml.='
		<url>
			<loc>https://www.wikicity.kz/user_details/user_details_bookmarks.php?Reg_code='.$user_d["reg_code"].'</loc>
			<lastmod>'.date("Y-m-d").'</lastmod>
			<changefreq>daily</changefreq>
			<priority>0.5</priority>
		</url>';
		$url_count = $url_count + 1;
	}
	if($url_count > $limit) {
		$xml_r[$a] = $xml;
		$url_count = 0;
		$a++;
		$xml = "";
	}
}
// добавление страниц фоток бизнеса
$res = GetSelectData("select i.id, c.id, c.str_id,i.img_id,u.reg_code 
					from image i, company c, user u
					where u.id=i.user_id  and c.id=i.rec_id and i.table_name='Company' order by i.id;");
while($biz_d = mysql_fetch_array($res)) { 
	$xml.='
	<url>
		<loc>https://www.wikicity.kz/biz_fotos/'.$biz_d["str_id"].'/'.$biz_d["img_id"].'</loc>
		<lastmod>'.date("Y-m-d").'</lastmod>
		<changefreq>daily</changefreq>
		<priority>0.8</priority>
	</url>
	<url>
		<loc>https://www.wikicity.kz/user_biz_photos/?Reg_code='.$biz_d["reg_code"].'&amp;img_id='.$biz_d["img_id"].'</loc>
		<lastmod>'.date("Y-m-d").'</lastmod>
		<changefreq>daily</changefreq>
		<priority>0.8</priority>
	</url>';
	$url_count = $url_count + 2;
	if($url_count > $limit) {
		$xml_r[$a] = $xml;
		$url_count = 0;
		$a++;
		$xml = "";
	}
}
// добавление страниц фоток своих
$res = GetSelectData("select user.reg_code,image.img_id 
					from image, user where image.table_name='User' and user.id=image.rec_id order by image.id;");
while($biz_d = mysql_fetch_array($res)) { 
	$xml.='
	<url>
		<loc>https://www.wikicity.kz/user_photos/?Reg_code='.$biz_d["reg_code"].'&amp;img_id='.$biz_d["img_id"].'</loc>
		<lastmod>'.date("Y-m-d").'</lastmod>
		<changefreq>daily</changefreq>
		<priority>0.7</priority>
	</url>';
	$url_count = $url_count + 1;
	if($url_count > $limit) {
		$xml_r[$a] = $xml;
		$url_count = 0;
		$a++;
		$xml = "";
	}
} 

// добавление страниц Событий

$res = GetSelectData("select id,str_id from event where active!=-1 and filtered!=1 and id>1542 order by id;");
while($event_d = mysql_fetch_array($res)) { 
	$xml.='
	<url>
		<loc>https://www.wikicity.kz/events/'.$event_d["str_id"].'</loc>
		<lastmod>'.date("Y-m-d").'</lastmod>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>';
	$url_count = $url_count + 1;
	if($url_count > $limit) {
		$xml_r[$a] = $xml;
		$url_count = 0;
		$a++;
		$xml = "";
	}
}

/// закрывающийся тег блока ссылок
$xml_post = '</urlset>';
if($url_count < $limit) {
	$xml_r[$a] = $xml;
}

for($i=1;$i<=$a;$i++) {
	$filename = $_SERVER['DOCUMENT_ROOT'].'/system_niro/sitemap_base/sitemap'.$i.'.xml';
	$fp = fopen($filename, 'w');
	fwrite($fp, $xml_pre.$xml_r[$i].$xml_post);
	fclose($fp);
}
?>