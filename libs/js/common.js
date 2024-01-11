

$(document).ready(function(){
	//set base url and querys for later use
	$baseUrl = window.location.href.split('?')[0];
	var $queries = [], hash;
   	var q = document.URL.split('?')[1];
   	if(q != undefined){
       	q = q.split('&');
       	for(var i = 0; i < q.length; i++){
       		hash = q[i].split('=');
       		$queries[hash[0]] = hash[1];
       	}
	}
	$urlQueries = $queries;
});




function scrollToTop(){
		$([document.documentElement, document.body]).animate({
        			scrollTop: $("#overlay").offset().top
    	}, 'slow');
}

function navigateTo($url){
	window.location.href = $url;
}

function isset($value){
	if(typeof $value == "undefined"){
		return false;
	}
	if( $value.length < 1){
		return false;
	}
	return true;
}

$.fn.maxHeight = function() {
	var $maxHeight = 0;
	$(this).each(function(){
			$currentElementHeight = parseInt($(this).height());
			if($currentElementHeight > $maxHeight){$maxHeight =$currentElementHeight;}
	});	
	return  $maxHeight;
}; 