<?php
defined("isInSideApplication")?null:die('no access');

function comment($string){
	
	global $config;
	$backtrace = debug_backtrace();
	
	if (!$config['show_comments']){return;}
	
	global $commentCount;
	if (!isset($commentCount)){
	echo '<style>
.comment{
		display: inline-block;
		z-index:9999;
		font-family: arial!important;
		font-size: arial!important;
		border-left: 20px solid #cafaca;
		padding:1em;
		margin-left:3em;
		margin-bottom:1em;
		background-color:#eee;
		color:black;
		position:relative;
		font-size:10px;
}
</style>';
	}
	
	echo '<div class="comment"><b>File: '.$backtrace[0]['file'].' | Line: '.$backtrace[0]['line'].'<br/></b>COMMENT: '.$string.'</div><br/>';;
	
	$commentCount++;
	
}

?>