<?php
	class NoticeService
	{
		public function __construct()
		{
				$serverName = "203.64.37.41"; 
				$connectionInfo = array( "Database"=>"Database", "UID"=>"UID", "PWD"=>"PWD","CharacterSet" => "UTF-8" );
				$this->conn = sqlsrv_connect( $serverName, $connectionInfo);

				$sync_db_server_name = "203.64.35.83"; 
				$sync_db_connection_info = array( "Database"=>"Database", "UID"=>"UID", "PWD"=>"PWD","CharacterSet" => "UTF-8" );
				$this->sync_conn =sqlsrv_connect($sync_db_server_name,  $sync_db_connection_info);

				
			
				//身分驗證,取得當前使用者資料
				$user_id='501';   //當前使用者id
				$user_unit='105010';  //當前使用者部門
				$user_role=''; //身分,例如主管
			
				if(!$user_id){
					//身分驗證不通過, 丟出例外
					$msg='權限不足';
					throw new Exception($msg);
				
				}
			
				$this->current_user= [
					'id' => $user_id,   
					'unit' => $user_unit,
					'role' => $user_role
				];



				
			
		}
		
		public function getCurrentUserId()
		{
			return $this->current_user['id'];
		}
		
		public function getCurrentUserUnit()
		{
			return $this->current_user['unit'];
		}
		
		public function canEdit($notice)
		{
			$user_id = $this->getCurrentUserId();
			if(!$notice['Id']) return true;
			
			//審核過資料無法修改
			if($notice['Reviewed']) return false;
			//建檔者本人
			if($notice['UpdatedBy'] == $user_id ) return true;
			
			return false;
			
		}
		
		public function canReview($notice)
		{
			$user_id = $this->getCurrentUserId();
			if(!$notice['Id']) return false;
			
			/// 如果是發送部門主管, 可以
			$createdBy = $notice['CreatedBy'];
			if($user_id=='501') return true;
			return false;
		}
		
		public function canDelete($notice)
		{
			if(!$notice['Id']) return false;
			$user_id = $this->getCurrentUserId();
			$canEdit=$this->canEdit($notice, $user_id);
			if(!$canEdit) return false;
			
			
			
			return true;
			
		}
		
		public function index($page=1)
		{
			$user_id = $this->getCurrentUserId();
			$user_unit = $this->getCurrentUserUnit();
		//依單位查找 或 依建檔人查找
		
			$conn = $this->conn;
			$sql = "SELECT * FROM Notices";   //WHERE CreatedBy=?
			$stmt = sqlsrv_query( $conn, $sql );
			
			$arrRecords=[];
			while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
				$arrRecords[]=$row;
			}
			
			return $arrRecords;
		}
		
		public function getById($id)
		{
			$conn = $this->conn;
		
			
			$tsql = "SELECT * FROM Notices WHERE Id=?" ;
			
			$params = array($id);
			$stmt = sqlsrv_query( $conn, $tsql , $params);
			
			$notice = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
			
			
			
			return $notice;
			
		}
		public function getAttachmentById($id)
		{
			$conn = $this->conn;
		
			
			$tsql = "SELECT * FROM NoticeAttachment WHERE Id=?" ;
			
			$params = array($id);
			$stmt = sqlsrv_query( $conn, $tsql , $params);
			
			$attachment = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
			
			return $attachment;
			
		}
		
		public function create()
		{
			$notice = $this->initNotice();
			$attachment=$this->initAttachment();
			
			return array($notice, $attachment);
			
		}
		
		public function edit($id)
		{
			$conn = $this->conn;
			$tsql = "SELECT * FROM Notices WHERE id=?" ;
					
			$params = array($id);
			$stmt = sqlsrv_query( $conn, $tsql , $params);
			
			$notice = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
			
			
			if(!$notice)  throw new Exception('查無資料');
			
			$attachment=$this->findAttachment($id);
			
			return array($notice, $attachment);
			
		}
		
		// HttpPost
		public function insert() 
		{
			$user_id = $this->getCurrentUserId();
			$user_unit = $this->getCurrentUserUnit();
			
			$conn = $this->conn;
			
			$createdBy=$user_unit;  //使用者部門
			$updatedBy=$user_id;  //使用者id
			
			$now=date('Y-m-d H:i:s');
			
			
			$values=$this->getPostedValues();
			
			
			$query = "INSERT INTO Notices (Content, Staff, Teacher , Student , Units , Classes , Levels , PS , Reviewed , CreatedBy , CreatedAt , UpdatedBy , UpdatedAt) "; 
			$query .= "VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?); SELECT SCOPE_IDENTITY()"; 
			
			$arrParams[]=$values['Content']; 
			$arrParams[]=$values['Staff'];  
			$arrParams[]=$values['Teacher']; 
			$arrParams[]=$values['Student']; 
			$arrParams[]=$values['Units']; 
			$arrParams[]=$values['Classes']; 
			$arrParams[]=$values['Levels']; 
			$arrParams[]=$values['PS']; 
			$arrParams[]=$values['Reviewed']; 
			
			$arrParams[]=$createdBy; 
			$arrParams[]=$now; 
			$arrParams[]=$updatedBy; 
			$arrParams[]=$now; 
			
			
			$resource=sqlsrv_query($conn, $query, $arrParams); 
			sqlsrv_next_result($resource); 
			sqlsrv_fetch($resource); 
				
			$notice_id= sqlsrv_get_field($resource, 0); 
			
			$has_file= false;
			if(isset($_FILES['Attachment'])){
				if (is_uploaded_file($_FILES['Attachment']['tmp_name'])) $has_file=true;
			}
			
			if($has_file){
					$this->saveAttachment($notice_id,$user_id , $user_unit);
			}
			
			
			
		}
		
		// HttpPost
		public function update($id) 
		{
			$notice=$this->getById($id);
			if(!$notice) throw new Exception('查無資料');
			
			$user_id = $this->getCurrentUserId();
			$user_unit = $this->getCurrentUserUnit();
			
			if(!$this->canEdit($notice))  throw new Exception('資料無法修改');
				
			$conn = $this->conn;
			
			$createdBy=$user_unit;  //使用者部門
			$updatedBy=$user_id;  //使用者id
			
			$now=date('Y-m-d H:i:s');
			
			
			$values=$this->getPostedValues();
			
			$query = "UPDATE Notices SET Content=(?), Staff=(?), Teacher=(?) , Student=(?) , Units=(?) , Classes=(?) , Levels=(?) , ";
			$query .= "PS=(?) , CreatedBy=(?) , CreatedAt=(?) , UpdatedBy=(?) , UpdatedAt=(?) "; 
			$query .= "WHERE Id=(?)";
			
			
			
			$arrParams[]=$values['Content'];  
			$arrParams[]=$values['Staff'];  
			$arrParams[]=$values['Teacher']; 
			$arrParams[]=$values['Student']; 
			$arrParams[]=$values['Units']; 
			$arrParams[]=$values['Classes']; 
			$arrParams[]=$values['Levels']; 
			$arrParams[]=$values['PS']; 
			
			
			$arrParams[]=$createdBy; 
			$arrParams[]=$now; 
			$arrParams[]=$updatedBy; 
			$arrParams[]=$now; 
			
			$arrParams[]=$id; 
			
			
			sqlsrv_query($conn, $query, $arrParams); 
			
			
			$has_file= false;
			if(isset($_FILES['Attachment'])){
				if (is_uploaded_file($_FILES['Attachment']['tmp_name'])) $has_file=true;
			}
			
			if($has_file){
					$this->saveAttachment($id,$user_id , $user_unit);
			}else{
				$file_title ='';
				if (isset($_POST['Attachment_Title'])){
					$file_title =$_POST['Attachment_Title'];
				} 
				
				
				
				if($file_title) $this->updateAttachmentTitle($file_title,$id,$user_id );
			}
			
		}
		
		// HttpPost
		public function approve($id) 
		{
			$notice=$this->getById($id);
			if(!$notice) throw new Exception('查無資料');
			
			if(!$this->canReview($notice))  throw new Exception('權限不足');
			
			$user_id = $this->getCurrentUserId();
			$now=date('Y-m-d H:i:s');
				
			$conn = $this->conn;
			
			$reviewed=true;
			$reviewedBy=$user_id;  //審閱者, 就是當前使用者id
			$reviewedAt=$now;
			
			$query = "UPDATE Notices SET Reviewed=(?), ReviewedBy=(?), ReviewedAt=(?)";
			$query .= "WHERE Id=(?)";
			
			$arrParams[]=$reviewed;  
			$arrParams[]=$reviewedBy;  
			$arrParams[]=$reviewedAt; 
			$arrParams[]=$id; 
			
			
			sqlsrv_query($conn, $query, $arrParams); 
			
			//審核通過,同步資料
			$this->syncNotice($id);
		}

		function getStudents($classes)
		{
			
			$classes = explode(',', $classes);
			
			$students=[];
			foreach($classes as $class){
				//AD41A
				
			}
			$students=['ss355'];
			return $students;
		}

		function getStaffs($units)
		{
			

			$units = explode(',', $units);
			
			$staffs=[];
			foreach($units as $unit){
				//102000
				
			}
			$staffs=['stephenchoe'];
			return $staffs;
		}

		function getTeachers($units)
		{
			
			$units = explode(',', $units);
		
			$teachers=[];
			foreach($units as $unit){
				//102000
				
			}

			$teachers=['stephenchoe'];
			
			return $teachers;
		}

		function getManagers($units, $levelOne, $levelTwo)
		{
			
			$units = explode(',', $units);
			
			$managers=[];
			foreach($units as $unit){
				//102000
				
			}
			$managers=['stephenchoe'];
			return $managers;
		}
		
		public function  syncNotice($notice_id)
		{
			$notice=$this->getById($notice_id);
			if(!$notice) throw new Exception('查無資料');
			
			if(!$notice['Reviewed'])  throw new Exception('資料未審核,無法同步');
			
			$type_id=1;  // 純文字
		   
			//根據Notice資料, 取得需要通知的成員代號
			$teachers=[];
			if($notice['Teacher']) $students=$this->getTeachers($notice['Units']);

		
			//$notice['Classes'] => 需要通知的班級   格式:  GD41A,ID41A 
			$students=[];
			if($notice['Student']) $students=$this->getStudents($notice['Classes']);

			$staffs=[];
			if($notice['Staff']){
				$units=$notice['Units'];  //需要通知的單位
				$levels=$notice['Levels'];
				if(!$levels)  $staffs=$this->getStaffs($units);  
				else if($levels=='1,2') $staffs=$this->getManagers($units, true, true);   // 通知一級主管與二級主管
				else if($levels=='1') $staffs=$this->getManagers($units, true, false);   // 通知一級主管
				else if($levels=='2') $staffs=$this->getManagers($units, false, true);   // 通知一級主管

			} 
			//取得需要通知的成員帳號  格式:ss355,10545001,10622501 
			$members= array_merge($teachers,$students,$staffs);
			$members=join(',', $members);
		
			
			$created_by=$notice['CreatedBy'];  //建檔的單位代碼   例如:105010
			
			$content=$notice['Content'];
			
			$attachment = $this->findAttachment($notice_id);
			
			$attachment_id=(int)$attachment['Id'];			
			if($attachment_id){
				$type_id=2;   //有附加檔案
			}
			
			$now=date('Y-m-d H:i:s');
			
			$sync_conn = $this->sync_conn;
			
			$query = "INSERT INTO school_notice_sync ( text_content, type_id , members ,  created_at , updated_at ) "; 		  
			$query .= "VALUES (?,?,?,?,?); SELECT SCOPE_IDENTITY()"; 
			
			$arrParams[]=$content;  
			$arrParams[]=$type_id; 
			$arrParams[]=$members;
			$arrParams[]=$now; 
			$arrParams[]=$now; 
			
			
			$resource=sqlsrv_query($sync_conn, $query, $arrParams); 
			if( $resource === false ) {
				die( print_r( sqlsrv_errors(), true));
				throw new Exception('同步失敗');
			}

			
			
			sqlsrv_next_result($resource); 
			sqlsrv_fetch($resource); 
				
			$sync_notice_id= sqlsrv_get_field($resource, 0);   //取得剛才新增的 id
			if($attachment_id){  //有附加檔案
				$this->syncAttachment($attachment_id,$sync_notice_id);
			}
			
		}
		
		private function syncAttachment($attachment_id,$sync_notice_id )
		{
			$attachment=$this->getAttachmentById($attachment_id);
			if(!$attachment)  throw new Exception('找不到附件檔案');
			
			$file_type=$attachment['Type'];
			$file_data=$attachment['FileData'];
			$display_name=$attachment['Title'];

			
			if(!$display_name){
				$display_name = $this->removeExtention($attachment['Name']);
				
			}
			
			$sync_conn = $this->sync_conn;
				
			$query = "INSERT INTO school_notice_attachment ( notice_id, file_type , file_data , display_name ) "; 		  
			$query .= "VALUES (?,?,?,?)"; 
			
			$arrParams[]=$sync_notice_id;  
			$arrParams[]=$file_type; 
			$arrParams[]=$file_data;
			$arrParams[]=$display_name; 
				
			sqlsrv_query( $sync_conn, $query, $arrParams);
				
				
			
		}
		
		public function delete($id)
		{
			$notice=$this->getById($id);
			if(!$notice) throw new Exception('查無資料');
			
			if(!$this->canDelete($notice))  throw new Exception('資料無法刪除');
			
			$conn = $this->conn;
			$query = "DELETE FROM Notices WHERE Id=?";
			
			$params[]=$id;
			
			
			sqlsrv_query($conn, $query, $params); 
			
		}
		public function deleteAttachment($id)
		{
			$attachment= $this->getAttachmentById($id);
			if(!$attachment)  throw new Exception('查無資料');
			
			$notice_id=$attachment['Notice_Id'];
			$notice=$this->getById($notice_id);
			if($notice) {
					if(!$this->canEdit($notice))  throw new Exception('資料無法修改');
			}
			
			$conn = $this->conn;
			$query = "DELETE FROM NoticeAttachment WHERE Id=?";		   
			$params[]=$id;
			sqlsrv_query($conn, $query, $params); 
			
			if($notice) {
				$user_id = $this->getCurrentUserId();
				$updatedBy=$user_id;  //使用者id
				$now=date('Y-m-d H:i:s');
				
				$query = "UPDATE Notices SET UpdatedBy=(?) , UpdatedAt=(?) ";		  
				$query .= "WHERE Id=(?)";
				$arrParams[]=$updatedBy; 
				$arrParams[]=$now; 		   
				$arrParams[]=$notice_id; 
				
				
				sqlsrv_query($conn, $query, $arrParams); 
			}
			
		}
		
		private function saveAttachment($notice_id)
		{
			$user_id = $this->getCurrentUserId();
			$user_unit = $this->getCurrentUserUnit();
		
			$file_name = $_FILES['Attachment']['name'];
			$file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
			$file_title = $file_name;
			if (isset($_POST['Attachment_Title'])){
				$file_title =$_POST['Attachment_Title'];
			} 

			$file_data=base64_encode(file_get_contents($_FILES['Attachment']['tmp_name']));
			
			$now=date('Y-m-d H:i:s');
			$createdBy=$user_unit;  //使用者部門
			$updatedBy=$user_id;  //使用者id
			
			$conn = $this->conn;
			$query = "INSERT INTO NoticeAttachment (Notice_Id, Title, Name , Type , FileData, CreatedBy , CreatedAt , UpdatedBy , UpdatedAt) "; 
			$query .= "VALUES (?,?,?,?,?,?,?,?,?)"; 
			
			$arrParams[]=$notice_id;  
			$arrParams[]=$file_title;  
			$arrParams[]=$file_name; 
			$arrParams[]=$file_ext; 
			$arrParams[]=$file_data; 
			
			
			$arrParams[]=$createdBy; 
			$arrParams[]=$now; 
			$arrParams[]=$updatedBy; 
			$arrParams[]=$now; 

			
			
			sqlsrv_query( $conn, $query, $arrParams);
				
				

			
			
		}
		
		private function updateAttachmentTitle($file_title,$notice_id,$user_id )
		{
				
			$attachment = $this->findAttachment($notice_id);		
		
			$attachment_id=(int)$attachment['Id'];			
			if(!$attachment_id) return;
			
				
				
			$now=date('Y-m-d H:i:s');
			
			$conn = $this->conn;
			
			$query = "UPDATE NoticeAttachment SET Title=(?), UpdatedBy=(?) , UpdatedAt=(?) "; 
			$query .= "WHERE Id=(?)";
		
			$arrParams[]=$file_title;  
			$arrParams[]=$user_id; 
			$arrParams[]=$now;  
			$arrParams[]=$attachment_id;  
			
			sqlsrv_query( $conn, $query, $arrParams);
				
		}
		
		private function findAttachment($notice_Id)
		{
			$attachment=$this->initAttachment();
				
			if($notice_Id)  {
				
				$conn = $this->conn;
				$tsql = "SELECT TOP 1 * FROM NoticeAttachment WHERE Notice_Id=?" ;
				
				$params = array($notice_Id);
				$stmt = sqlsrv_query( $conn, $tsql , $params);
				
				$record = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
				
				if($record) $attachment=$record;
	
			
			}
			
			return $attachment;
				
				
		}
		
		private function getPostedValues()
		{
			$content = $_POST['Content'];

			$staff=false;
			if (isset($_POST['Staff'])) $staff=true;

			$teacher=false;
			if (isset($_POST['Teacher'])) $teacher=true;

			$student=false;
			if (isset($_POST['Student'])) $student=true;

			$levels=false;
			if (isset($_POST['Student'])) $student=true;

			
			$units=$_POST['Units'];
			$classes=$_POST['Classes'];
			$levels=$_POST['Levels'];
			$ps=$_POST['PS'];
			$reviewed=$_POST['Reviewed'];

			$values=[
				'Content' => $content,
				'Staff' => $staff,
				'Teacher' => $teacher,
				'Student' => $student,
				'Units' => $units,
				'Classes' => $classes,
				'Levels' => $levels,
				'PS' => $ps,
				'Reviewed' => false,			
			];
			
			return $values;
			
		}
		
		
		
				
		
		public function initNotice()
		{
			
			return  [
					'Id' => 0 , 
					'Content' => '',
					'Staff' => 0,
					'Teacher' => 0,
					'Student' => 0,
					'Reviewed' => 0,
					'Units' => '',
					'Classes' => '',
					'Levels' => '',
					
					'PS' => '',

				];
				
		}
		public function initAttachment()
		{
			return [
					'Id' => 0 , 		
					'Notice_Id' => 0,
					'Title' => '',
					'Name' => '',
					'Type' => '',
					'FileData' => '',

				];
			
		}

		function removeExtention($filename)
		{
			return preg_replace('/.[^.]*$/', '', $filename);
		}
		
		
	}