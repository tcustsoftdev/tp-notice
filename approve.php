<?php

    require_once("helper.php");
    require_once("service.php");
	
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$id=0;
	    if (isset($_POST['Id'])) {
	 		$id=(int)$_POST['Id'];
	    }
	
        $service=new NoticeService();
		
		try {
			
			$service=new NoticeService();
			
			$service->approve($id);
			
			redirectToIndex();
			
	   } catch (Exception $e) {
			die('錯誤: ' . $e->getMessage());
	   }
		
		
	}else{
		die('錯誤: 頁面不存在(404)');
		
	}