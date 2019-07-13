<?php
	class Student_notice extends CI_Controller {
		
	public function __construct()
	{
		parent::__construct();
		if($this->login_model->check_login())
		{
			$this->lang->load('custom',$this->session_library->get_session_data('Language'));
			$this->data['Login']['Login_as'] = $this->session_library->get_session_data('Login_as');
			$this->data['Login']['Name'] = $this->session_library->get_session_data('Name');
			$this->data['Login']['Email'] = $this->session_library->get_session_data('Email');
			$this->data['Login']['ID'] = $this->session_library->get_session_data('ID');
			$this->data['Date_format'] = $this->date_library->get_date_format();
			$this->load->model('student_notice_model');
			$this->data['my_config'] = $this->my_config = $this->config->item('skyq');
            $this->data['menu'] = $this->setting_model->get_menus($this->data,$this->my_config);
		}
		else 
		{
			redirect($this->config->item('skyq')['default_login_page']);
		}
 	}

	public function index($branch_ID = NULL)
	{
		$this->data['breadcrumb']['heading'] = 'Student Notices';  
		$this->data['breadcrumb']['route'] = array(array('title'=>'Student Notices','path'=>'Student_notice/index/'.$branch_ID),'Show');
		$this->data['branch_ID'] = $branch_ID;
		$this->load->view('includes/header',$this->data);
		$this->load->view('pages/student_notice_view',$this->data);
		$this->load->view('includes/footer',$this->data);
	}

	public function get_show_data($branch_ID = NULL)
	{
		if($this->data['Login']['Login_as'] == 'DSSK10000011')
		{
			$res = $this->student_notice_model->get_show_data(array('SN'=>array('branch_ID'=>$branch_ID,'student_ID LIKE'=>'%'.$this->data['Login']['ID'].'%')),array('title','batch_ID','date','description','ID'));
		}
		else
		{
			$res = $this->student_notice_model->get_show_data(array('SN'=>array('branch_ID'=>$branch_ID)),array('title','batch_ID','date','description','ID'));
			foreach ($res['data'] as $key => $value) {
				if (strpos($value[3], '/add') !== false) {
					$res['data'][$key][3] = str_replace('/add', '/add/'.$branch_ID, $value[3]);
				}
			}
		}
		foreach ($res['data'] as $key => $value) {
			if ($value[1] != 'all') {
				if(strpos($value[1], ',') !== FALSE)
				{
					$batch = explode(',', $value[1]);
					$batches = '';
					foreach ($batch as $keyb => $valueb) {
						$batches .= $this->str_function_library->call('fr>BT>name:ID=`'.$valueb.'`').',';
					}
					$res['data'][$key][1] = rtrim($batches,',');
				}
				else
				{
					$res['data'][$key][1] = $this->str_function_library->call('fr>BT>name:ID=`'.$value[1].'`');
				}
			}
			else
			{
				$res['data'][$key][1] = 'All';
			}
			$value[2] = date('d-m-Y h:i:s', strtotime($value[2]));
			$res['data'][$key][2] = str_replace('-', '|', $value[2]);
		}
		echo json_encode($res);
 	}

 	public function add($branch_ID = NULL, $id = NULL)
 	{
 		$this->data['breadcrumb']['heading'] = 'Add Student Notice';
		$this->data['breadcrumb']['route'] = array(array('title'=>'Student Notices','path'=>'student_notice/index/'.$branch_ID),'Add');
		$check = $this->student_notice_model->check($id,$this->data['Login']['Login_as']);
		define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        if(IS_AJAX)
		{
			$this->student_notice_model->add_or_edit();
		}
		else
		{
			if($check)
			{
				if(!is_null($id))
				{
					$this->data['breadcrumb']['heading'] = 'Edit Student Notice';  
					$this->data['breadcrumb']['route'] = array(array('title'=>'Student Notices','path'=>'student_notice/index/'.$branch_ID),'Edit');  
					$this->data['What'] = 'Edit';
					$item = $this->fetch_model->show(array('SN'=>array('ID'=>$id)));
					// $item[0]['student_ID'] = explode(',', $item[0]['student_ID']);
					$this->data['View'] = $item[0];
				}
				$this->data['subjects'] = $this->fetch_model->show('SB');
				$this->load->view('includes/header',$this->data);
				$this->load->view('pages/student_notice_add_edit_view',$this->data);
				$this->load->view('includes/footer',$this->data);
			}
			else
			{
	 			return FALSE;
			}
		}
	}

 	public function delete($item_id = NULL)
 	{
 		$delete_data = $this->student_notice_model->delete($item_id);
 		define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
		if($delete_data)
 		{
		    if(IS_AJAX)
			{
				echo json_encode($delete_data);	
			}
			else
			{
	 			redirect('student_notice');
	 		}
		}
 	}

 	public function get_students()
 	{
 		$res = $this->student_notice_model->get_students();
 		echo json_encode($res);
 	}

 	public function student_attendace($id = NULL)
 	{
 		$res = $this->student_notice_model->student_attendace($id);
 		echo json_encode($res);
 	}

 	public function save_attendance()
 	{
 		$res = $this->student_notice_model->save_attendance();
 		echo json_encode($res);
 	}

 	public function view($id = NULL)
 	{
 		$res = $this->student_notice_model->view($id);
 		echo json_encode($res);	
 	}
}
?>