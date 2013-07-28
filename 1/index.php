<?php
session_start();

include_once( 'config.php' );
include_once( 'saetv2.ex.class.php' );

$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );

$code_url = $o->getAuthorizeURL( WB_CALLBACK_URL );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>新浪微博PHP SDK V2版 Demo - Powered by Sina App Engine</title>
</head>

<body>
<?php if( is_array( $ms['statuses'] ) ): ?>
<?php foreach( $ms['statuses'] as $item ): ?>
<div style="padding:10px;margin:5px;border:1px solid #ccc">
	<?=$item['text'];?>
	<?=$item['id'];?>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php $mem = memcache_init(); ?>
<?php if ($mem==false): ?>
<div style="padding:10px;margin:5px;border:1px solid #ccc">
	<?="mc init failed\n"; ?>
</div>
<?php endif; ?>
<div style="padding:10px;margin:5px;border:1px solid #ccc">
	<span>Total post processed:</span>
	<?=memcache_get($mem, "total_post_cnt"); ?>
</div>
<div style="padding:10px;margin:5px;border:1px solid #ccc">
	<span>Num of posts that hit the interest list:</span>
	<?=memcache_get($mem, "hit_post_cnt"); ?>
</div>

</body>
</html>
