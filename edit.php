<?php

   require_once("helper.php");
   require_once("service.php");
   
   
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	   
	   try {
			$service=new NoticeService();
			
			$id=0;
		    if (isset($_POST['Id'])) {
				$id=(int)$_POST['Id'];
		    }
			
			if($id){
				$service->update($id);
				
				redirectToIndex();
			  
		   }else{
			   $service->insert();
			   
			   redirectToIndex();
			 
		   }
			
			
			
		} catch (Exception $e) {
			
			die('錯誤: ' . $e->getMessage());
		}
       
   }
   else
   {
	    
	    try {
			$service=new NoticeService();
			
			$id=0;
			if (isset($_GET['id'])) {
			   $id=(int)$_GET['id'];
		    }
			
			$data;
			if($id){
				$data=$service->edit($id);	
                	
			}else{
				$data=$service->create();
							
			}
			
			$notice=$data[0];
			$attachment=$data[1];
			
			$canEdit=$service->canEdit($notice);
			$canReview=$service->canReview($notice);
			$canDelete=$service->canDelete($notice);
			
			
		} catch (Exception $e) {
			
			die('錯誤: ' . $e->getMessage());
		}
		
		
		
		
		
   }


    
  
?>


<link rel=stylesheet type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<link rel=stylesheet type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

<link rel=stylesheet type="text/css" href="./css/edit.css">

<div class="container">

    <h1>發送校務系統通知</h1>
    
<form enctype="multipart/form-data" id="form-notice" method="POST" action="">

   <div class="row">
            <div class="col-md-12">
                <label>通知內容</label>
                <textarea name="Content" class="form-control" rows="6" cols="50" ><?php echo $notice['Content']; ?></textarea>   
				
                <small id="err-Content" class="text-danger" style="display: none;">請輸入通知內容</small>
            </div>

        </div>
		<div class="row">
            <div class="col-md-4 mb-3">
                <label>附加檔案</label>
                <input id="attachment-file" name="Attachment" type="file">
				
				
                <div id="div-exist-attachment" class="form-inline" style="display: none;">
                    <input id="attachment-file_name" class="form-control" value="<?php echo $attachment['Name']; ?>" type="text" disabled> 
                    <button id="btn-del-attachment" class="btn btn-danger btn-sm">
                        <span class="glyphicon glyphicon-trash"></span>
                    </button>
                </div>
            </div>
            <div class="col-md-8 mb-3">
                <label>檔案顯示名稱</label>
                <input type="text" name="Attachment_Title" class="form-control" value="<?php echo $attachment['Title']; ?>" >
                <small id="err-filename" class="text-danger" style="display: none;">請輸入檔案顯示名稱</small>
            </div>
        </div>
        
        <div class="row" style="padding-top:10px">

            <div class="col-md-4">
                <label>通知對象身份</label>

                <div class="checkbox">
                    <label>
					
                        <input class="chk-roles" type="checkbox" name="Staff" id="chk-staff" value="<?php echo $notice['Staff']; ?>">
                        <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
                        職員 <span id="level-text"></span>
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input class="chk-roles"  type="checkbox" name="Teacher" value="<?php echo $notice['Teacher']; ?>" >
                        <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
                        教師
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input class="chk-roles"  type="checkbox" name="Student" value="<?php echo $notice['Student']; ?>">
                        <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
                        學生
                    </label>
                </div>

                <small id="err-roles" class="text-danger" style="display: none;">請選擇通知對象身份</small>

            </div>
            <div class="col-md-8">
                <div id="unit-list" style="display:none">
                    <label>通知對象部門</label>
                    <button id="btn-edit-units" class="btn btn-primary btn-xs">
                        <span class="glyphicon glyphicon-pencil"></span> 編輯
                    </button>
                    <textarea id="unit-names" class="form-control" rows="5" cols="50" disabled></textarea>
                    <input type="hidden" id="unit-codes" name="Units" value="<?php echo $notice['Units']; ?>" />
                    <small id="err-units" class="text-danger" style="display: none;">請選擇通知對象部門</small>
                </div>
                <div id="class-list" class="pad-top" style="display:none">
                    <label>通知對象班級</label>
                    <button id="btn-edit-classes" class="btn btn-primary btn-xs">
                        <span class="glyphicon glyphicon-pencil"></span> 編輯
                    </button>
                    <textarea id="class-names" class="form-control" rows="5" cols="50" disabled></textarea>
                    <input type="hidden" id="class-codes" name="Classes" value="<?php echo $notice['Classes']; ?>"  />
                    <small id="err-classes" class="text-danger" style="display: none;">請選擇通知對象班級</small>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>備註</label>
                <textarea name="PS" class="form-control" rows="3" cols="50"><?php echo $notice['PS']; ?></textarea>
				
            </div>

        </div>
        <div id="submit-buttons" class="row" style="padding-top:10px">
            <div class="col-md-6">
                <button class="btn btn-success" type="submit">
                    <span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span>
                    存檔
                </button>
				
            </div>
	        <div class="col-md-6">
                <button  id="btn-delete" class="btn btn-danger" type="button">
                    <span class="glyphicon glyphicon-trash"></span>
					刪除
                </button>
				
            </div>
        </div>
		
	    <div class="row" style="display:none" >
            <div class="col-md-12">
				Id:<input id="notice-id" type="text" name="Id" value="<?php echo $notice['Id']; ?>"  />
				Reviewed:<input type="text" name="Reviewed" value="<?php echo $notice['Reviewed']; ?>" />
				Levels:<input type="text" name="Levels" value="<?php echo $notice['Levels']; ?>" />
				Attachment_ID:<input type="text" name="Attachment_Id" value="<?php echo $attachment['Id']; ?>"  />
            </div>

        </div>

       

    </form>
	
	
	<div id="div-review" class="row" style="padding-top:10px" >
	        <div class="col-md-6">
			
			<?php if( isset($notice['ReviewedAt'])) : ?>
			     <h3><span class="label label-success">已審核 ( <?php echo date_format($notice['ReviewedAt'], 'Y-m-d H:i:s'); ?> )</span></h3>
			<?php else : ?>
			   <form id="form-approve" method="POST" action="approve.php">
			     <button id="btn-review-ok" class="btn btn-success" type="button">
				   <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                    核准
                </button>
				
				<input name="Id" value="<?php echo $notice['Id']; ?>">
			   </form>	
			<?php endif ?>
			
			
            
			   

               
            </div>
	        
    </div>
	
	
	
    <div class="row" style="display:none" >
        <div class="col-md-12">
		    can-edit:<input id="can-edit" type="text" value="<?php echo $canEdit; ?>" />
			can-review:<input id="can-review" type="text" value="<?php echo $canReview; ?>"  />
			can-delete:<input id="can-delete" type="text" value="<?php echo $canDelete; ?>"  />
            select-type:<input id="select-type" type="text" value="" />
            confirm-action:<input id="confirm-action" type="text" value="" />
        </div>

    </div>
   




    <button id="open-custom-modal" type="button" style="display:none" data-toggle="modal" data-target="#custom-modal">Open Modal</button>
    <div class="modal fade" id="custom-modal" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button id="close-custom-modal" type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" id="custom-modal-title"></h4>
                </div>
                <div class="modal-body tree-modal" id="custom-modal-content">
                    <div class="row" style="padding-bottom:10px">
                        <div class="col-md-6">
                            <div class="form-inline">
                                <button id="tree-select-all" class="btn btn-primary btn-sm">全選</button> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <button id="tree-cancel-all" class="btn btn-default btn-sm">全取消</button>
                            </div>
                        </div>
                        <div class="col-md-6" id="div-level">
                            <div class="form-inline">
                                <div class="checkbox">
                                    <label>
                                        <input id="level-one" class="chk-levels" type="checkbox" value="1">
                                        <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
                                        一級主管
                                    </label>
                                </div>
                                <div style="padding-left:15px" class="checkbox">
                                    <label>
                                        <input id="level-two" class="chk-levels" type="checkbox" value="2">
                                        <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
                                        二級主管
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>

                    <ul id="treeview-members"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" id="btn-select-done" class="btn btn-success">確定</button>

                </div>
            </div>
        </div>
    </div>


    <button id="btn-alert-modal" type="button" data-toggle="modal" data-target="#alert-modal" style="display:none" >ALERT</button>
    <div class="modal fade" id="alert-modal" role="dialog">
        <div class="modal-dialog"  role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-danger">
                    <button id="close-alert" type="button" class="close" data-dismiss="modal">
                        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                    </button>
                    <h3><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> </h3>
                </div>
                <div class="modal-body" id="alert-content">

                </div>
                <div class="modal-footer" id="alert-footer">
                    <button type="button" id="btn-confirm-ok" class="btn btn-success">確定</button>

                    &nbsp; &nbsp;
                    <button type="button" id="btn-confirm-cancel" class="btn btn-default">取消</button>
                </div>
            </div>
        </div>
    </div>



</div>   <!--End Container-->

<script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<script src="./js/treeview.js"></script>
<script src="./js/edit.js"></script>


<script type="text/javascript">
   
   function test() {
       
    }

    
    $(document).ready(function () {
		
		
        iniEdit();

        $("input[type='checkbox'][name='Staff']").change(function () {
			
            var checked = $(this).prop("checked");
            $(this).val(checked);
            onStaffCheckChanged(checked);
        });
       
        $("input[type='checkbox'][name='Teacher']").change(function () {
			
            var checked = $(this).prop("checked");
            $(this).val(checked);
            onTeacherCheckChanged(checked);
        });
        $("input[type='checkbox'][name='Student']").change(function () {
			
            var checked = $(this).prop("checked");
            $(this).val(checked);
            onStudentCheckChanged(checked);
        });


        $('#btn-select-done').click(function () {
            onSelectDone();
        });
        $('#tree-select-all').click(function () {
            $("#treeview-members").hummingbird("checkAll");
        });
        $('#tree-cancel-all').click(function () {
            $("#treeview-members").hummingbird("uncheckAll");
        });


        $('#btn-edit-units').click(function (e) {
            e.preventDefault();
            beginSelectUnits();
        });

        $('#btn-edit-classes').click(function (e) {
            e.preventDefault();
            beginSelectClasses();
        });

        

        $('#btn-del-attachment').click(function (e) {
            e.preventDefault();
            var content = '<h3>確定要刪除此附加檔案嗎?</h3>';
            var showBtn = true;
            ShowAlert(content, showBtn);

            setConfirmType('del-attachment');
           
        });

        $('#btn-confirm-ok').click(function (e) {
            e.preventDefault();
            onConfirmOK();
        });

        $('#btn-confirm-cancel').click(function (e) {
            e.preventDefault();
            CloseAlert();
        });
		
		$('#btn-delete').click(function (e) {
            e.preventDefault();
            var content = '<h3>確定要刪除嗎?</h3>';
            var showBtn = true;
            ShowAlert(content, showBtn);

            setConfirmType('delete-notice');
        });
		
		$('#btn-review-ok').click(function (e) {
            e.preventDefault();
            var content = '<h3>確定核准此通知嗎?</h3>';
            var showBtn = true;
            ShowAlert(content, showBtn);

            setConfirmType('review-ok');
        });


        $('#form-notice').keydown(function () {
            clearErrorMsg(event.target);
        });
		



        $('#form-notice').submit(function (e) {
          
             var canSubmit = true;
             var errMsgs = [];
             var inputContent = $("textarea[name='Content']");            
             if (!inputContent.val()) {
                 canSubmit = false;
                 inputContent.next().show();
                 errMsgs.push(inputContent.next().text());
             }

             if (document.getElementById('attachment-file').files.length) {
                 var inputAttachmentTitle = $("input[name='Attachment_Title']");   
                 if (!inputAttachmentTitle.val()) {
                     canSubmit = false;
                     inputAttachmentTitle.next().show();
                     errMsgs.push(inputAttachmentTitle.next().text());
                 }
             }

             var student = isTrue($("input[type='checkbox'][name='Student']").val());
             var teacher = isTrue($("input[type='checkbox'][name='Teacher']").val());
             var staff = isTrue($("input[type='checkbox'][name='Staff']").val());

             if (!student && !teacher && !staff) {
                 $('#err-roles').show();
                 errMsgs.push($('#err-roles').text());
                 canSubmit = false;
             }

             if (student) {
                 var  classes= $("input[name='Classes']").val();
                 if (!classes) {
                     canSubmit = false;
                     $('#class-list').show();
                     $("input[name='Classes']").next().show();
                     errMsgs.push($("input[name='Classes']").next().text());
                 }

             }

             if (teacher || staff) {
                 var units = $("input[name='Units']").val();
                 if (!units) {
                     canSubmit = false;
                     $('#unit-list').show();
                     $("input[name='Units']").next().show();
                     errMsgs.push($("input[name='Units']").next().text());
                 }

             }

             if (!canSubmit) {
                 showErrors(errMsgs);

                 return false;
             } 

             

             if (!student) {
                 $("input[name='Classes']").val('');
                 
             }

             if (!staff && !teacher) {
                 $("input[name='Units']").val('');
                 $("input[name='Levels']").val('');

             }


          

            

          
        });

        

       
        
    });




</script>
