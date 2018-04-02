<?php

    function redirectToIndex()
    {
	    /* 回到列表index */
		header("Location: http://localhost/tp-notices/index.php");
		exit;
    }
	function notFound()
	{
		http_response_code(404);
		print json_encode(['error' => '找不到網頁']);
		exit;
		  
	}
	
	function jsonError($msg)
	{
		  http_response_code(500);
		  print json_encode(['error' => $msg]);
		  exit;		
	}

	
	