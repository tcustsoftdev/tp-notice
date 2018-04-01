<?php

   require_once("helper.php");
   require_once("service.php");
   
   
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	   
	   
       
   }
   else
   {
	    $records=[];
	    try {
			$service=new NoticeService();
			
			$page=1;
			if (isset($_GET['page'])) {
			   $page=(int)$_GET['page'];
		    }
			
			$records=$service->index($page);	
			
			
		} catch (Exception $e) {
			
			die('錯誤: ' . $e->getMessage());
		}
		
   }


    
  
?>

<link rel=stylesheet type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

<div class="container">
    <div class="row">
        <div class="col-md-10">
		    <h1>校務系統通知紀錄</h1>
        </div>
		<div class="col-md-2">
		    <h1> 
				<a href="edit.php" class="btn btn-primary">
				   <span class="glyphicon glyphicon-plus"></span> 
						新增			
				</a>	
		   	</h1>
        </div>
    </div>

    
    <table class="table table-striped">
        <thead>
            <tr>
                <th style="width:15%">審核狀況</th>
                <th style="width:50%">通知內容</th>
                
               
                <th style="width:10%">建檔單位</th>
                <th>建檔時間</th>
                <th style="width:5%"></th>
            </tr>
        </thead>
        <tbody>
			<?php foreach($records as $notice): ?>
				<tr>
				    <td>
				    <?php if( isset($notice['ReviewedAt'])) : ?>
			               <h5><span class="label label-success">已審核 ( <?php echo date_format($notice['ReviewedAt'], 'Y-m-d H:i:s'); ?> )</span></h5>					
					<?php endif ?>
					</td>
					<td><?php echo $notice['Content']; ?></td>
					
					<td><?php echo $notice['CreatedBy']; ?></td>
					
					<td><?php echo date_format($notice['CreatedAt'], 'Y-m-d H:i:s'); ?> </td>
				
					<td>
						<a href="edit.php?id=<?php echo $notice['Id']; ?>" class="btn btn-primary btn-sm">
							<span class="glyphicon glyphicon-pencil"></span> 
						</a>
                    
                    </td>
				</tr>
			<?php endforeach; ?>
            
        </tbody>
    </table>
</div>


<script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>


<script type="text/javascript">
    
</script>
