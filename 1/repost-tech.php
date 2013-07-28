<?php
session_start();

include_once( 'config.php' );
include_once( 'saetv2.ex.class.php' );

//$c = new SaeTClientV2( WB_AKEY , WB_SKEY , $_SESSION['token']['access_token'] );
$c = new SaeTClientV2( WB_AKEY , WB_SKEY , ACCESS_TOKEN );

$mem = memcache_init();
if ($mem==false)
	echo "mc init failed\n";
$total_post_cnt = memcache_get($mem, 'total_post_cnt');	// Total post processed
$hit_post_cnt = memcache_get($mem, 'hit_post_cnt'); // Hit post count
	
$uid_get = $c->get_uid();
$uid = $uid_get['uid'];
$user_message = $c->show_user_by_id( $uid);//根据ID获取用户等基本信息

// Extract weibo list
$ret = $c->home_timeline();
$msg = $ret['statuses'];

var_dump(count($msg));
if ($msg === false || $msg === null){
	echo "Filed to get public_timeline\n";
	return false;
} 
$max_post_id = intval(memcache_get($mem, 'cur_repost_id'));
	// list interesting words
$interest_str = array("技术","科技","IT","计算机","科学","高新产业","互联网","网络");
foreach ($msg as $entry){
	$hitcnt = 0;
	foreach($interest_str as $str){
		$hitcnt = $hitcnt + (substr_count($entry['text'], $str) > 0);
	}
	echo $entry['id'];
	var_dump($hitcnt);
	echo "\n";
	if ($hitcnt > 1){
		$cur_repost_id = intval(memcache_get($mem, 'cur_repost_id'));
		if ($cur_repost_id < intval($entry['id'])){
			$c->repost($entry['id']);
			if ($entry['id'] > $max_post_id)
				$max_post_id = $entry['id'];
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
memcache_set($mem, 'cur_repost_id', $max_post_id);

?>