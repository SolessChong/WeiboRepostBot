<?php
session_start();

include_once( 'config.php' );
include_once( 'saetv2.ex.class.php' );

//$c = new SaeTClientV2( WB_AKEY , WB_SKEY , $_SESSION['token']['access_token'] );
$c = new SaeTClientV2( WB_AKEY , WB_SKEY , '2.00utce3CYwqzlC6ddc67d7c4o4sEnB' );

$mem = memcache_init();
if ($mem==false)
	echo "mc init failed\n";
$total_post_cnt = memcache_get($mem, 'total_post_cnt');	// Total post processed
$hit_post_cnt = memcache_get($mem, 'hit_post_cnt'); // Hit post count
	
$ms  = $c->public_timeline(); // done
$uid_get = $c->get_uid();
$uid = $uid_get['uid'];
$user_message = $c->show_user_by_id( $uid);//根据ID获取用户等基本信息

// Extract weibo list
$ret = $c->public_timeline();
$msg = $ret['statuses'];

var_dump(count($msg));
if ($msg === false || $msg === null){
	echo "Filed to get public_timeline\n";
	return false;
} 
	// list interesting words
$interest_str = array("的","技术","科技","IT","计算机","科学","高新产业");
foreach ($msg as $entry){
	$hitcnt = 0;
	foreach($interest_str as $str){
		$hitcnt = $hitcnt + (substr_count($entry['text'], $str) > 0);
	}
	echo $entry['id'];
	var_dump($hitcnt);
	echo "\n";
	if ($hitcnt > 0){
		$cur_repost_id = intval(memcache_get($mem, 'cur_repost_id'));
		if ($cur_repost_id < intval($entry['id'])){
			$c->repost($entry['id']);
			memcache_set($mem, 'cur_repost_id', $entry['id']);
			$hit_post_cnt = $hit_post_cnt + 1;			
			echo "Repost: ".$entry['id']."\n";
			var_dump($entry['id']);
		}
	}
}

// Update log and stat.
$total_post_cnt = $total_post_cnt + intval($ret['total_number']);
memcache_set($mem, 'total_post_cnt', $total_post_cnt);
memcache_set($mem, 'hit_post_cnt', $hit_post_cnt);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>新浪微博V2接口演示程序-Powered by Sina App Engine</title>
</head>

<body>
	<?=$user_message['screen_name']?>,您好！ 
	<h2 align="left">发送新微博</h2>
	<form action="" >
		<input type="text" name="text" style="width:300px" />
		<input type="submit" />
	</form>
<?php
if( isset($_REQUEST['text']) ) {
	$ret = $c->update( $_REQUEST['text'] );	//发送微博
	if ( isset($ret['error_code']) && $ret['error_code'] > 0 ) {
		echo "<p>发送失败，错误：{$ret['error_code']}:{$ret['error']}</p>";
	} else {
		echo "<p>发送成功</p>";
	}
}
?>

<?php if( is_array( $ms['statuses'] ) ): ?>
<?php foreach( $ms['statuses'] as $item ): ?>
<div style="padding:10px;margin:5px;border:1px solid #ccc">
	<?=$item['text'];?>
	<?=$item['id'];?>
</div>
<?php endforeach; ?>
<?php endif; ?>

</body>
</html>
