<?php
   require_once("helper.php");
   require_once("service.php");
	
	
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	   $id=0;
	   if (isset($_POST['Id'])) {
			$id=(int)$_POST['Id'];
	   }
	   
	   $attachment_id=0;
	   if (isset($_POST['Attachment_Id'])) {
			$attachment_id=(int)$_POST['Attachment_Id'];
	   }
	   
	   
	   if(!$id && !$attachment_id) return notFound();
	   
	   try {
			
			$service=new NoticeService();
			if($id){
				$service->delete($id);
			}else if($attachment_id){
				$service->deleteAttachment($attachment_id);
			}
			
			print json_encode(['success' => true ]);
	        exit;
						
			
			
	   } catch (Exception $e) {
			die('錯誤: ' . $e->getMessage());
	   }
		
	    
   }else{
	    return notFound();
	   
   }
	  
	   
	   
	   
	   
	   
	  
  


    
  
?>