<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1">
{if isset($template_vars->title)}
	<title>{$template_vars->title}</title>
{else}
	<title>Framework</title>
{/if}
{*
<script>
	var $session_id = "{$template_vars->session_id}";
	var $apiKey = "{$template_vars->apiKey}"
	var $siteAddress = "{$template_vars->siteAddress}"
</script>
*}

{$template_vars->css}
{$template_vars->script}


</head>
<body>
<div id='page_container' class="{$template_vars->page_name}">
{*
<div id="overlay">
</div><!--end overlay-->
<div id="overlay_content">
	<div class="content">
		<div class="loading"><img src="assets/images/loading_spinner.png" alt="Loading..." /></div>
	</div>
	<div class="clear_fix"></div>
</div><!--end overlay_content-->
*}
{*<div id="status_alert">loaded</div>*}