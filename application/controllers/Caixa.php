<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class caixa extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->helper(array('url','form','array','app'));
		$this->load->library(array('form_validation','session'));
		$this->load->database();
    }
	 
	public function index(){
		$this->load->view('index', array(
					'page'=>'Caixa'
					,'title'=> 'Movimento de Caixa'
					,'part' => 'show'
					,'tabledata'=>$this->db->get_where('caixa', 'DATE(data) = DATE(CURDATE())')->result()
				));
	}
	
	public function show(){
		$this->load->view('index',array(
					'page'=>'Caixa'
					,'title'=> 'Movimento de Caixa'
					,'part' => 'show'
					,'tabledata'=>$this->db->get('caixa')->result()
				));
	}
	
	public function inserting(){
		$this->load->view('index', array(
					'page'=>'Caixa'
					,'title'=> 'Movimento de Caixa'
					,'part' => 'inserting'));
	}
	
	public function editing(){
		$id = $this->uri->segment(3) ? $this->uri->segment(3) : $this->input->post('id_caixa');
		if($id){
			$this->load->view('index', array(
						'page'=>'Caixa'
						,'title'=> 'Movimento de Caixa'
						,'part' => 'editing'
						,'caixa'=> $this->db->get_where('caixa', array('id_caixa' => $id))->row() ));
		}else{
			$this->show();
		}
	}
	public function deleting(){
		$id = $this->uri->segment(3) ? $this->uri->segment(3) : $this->input->post('id_caixa');
		if($id){
			$this->load->view('index', array(
						'page'=>'Caixa'
						,'title'=> 'Movimento de Caixa'
						,'part' => 'deleting'
						,'caixa'=> $this->db->get_where('caixa', array('id_caixa' => $id))->row() ));
		}else{
			$this->show();
		}
	}
	
	public function save(){
		if ($this->runFormValidations() == TRUE){
			
			$user = $this->session->get_userdata();
			$id_usuario = $user['user_session']['id_usuario'];
			$data = date("Y-m-d H:i:s");
			
			$dados = elements(array('operacao','valor','observacao'),$this->input->post());
			$dados['id_usuario'] = $id_usuario;
			$dados['data'] = $data;
			
			$this->db->insert('caixa', $dados); 
			
			$this->load->view('index',array(
					'page'=>'Caixa'
					,'title'=> 'Movimento de Caixa'
					,'part' => 'inserting'
			));
			
			$this->session->set_flashdata('msg', 'Usuário cadastrado com sucesso.');
			redirect(current_url());
			
		}else{
			$this->inserting();
		}
	}
	
	public function edit(){
		if ($this->runFormValidations() == TRUE){
			
			$user = $this->session->get_userdata();
			$id_usuario = $user['user_session']['id_usuario'];
			$data = date("Y-m-d H:i:s");
			
			$dados = elements(array('operacao','valor','observacao'),$this->input->post());
			$dados['id_usuario'] = $id_usuario;
			$dados['data'] = $data;
			
			$this->db->where('id_caixa', $this->input->post('id_caixa'));
			$this->db->update('caixa', $dados); 
			
			$this->session->set_flashdata('msg', 'Caixa atualizado com sucesso.');
			$this->show();
			// redirect(s());
			
		}else{
			$this->editing();
		}
	}
	
	public function delete(){
		if ($this->runFormValidations() == TRUE){
			
			
			$this->db->where('id_caixa', $this->input->post('id_caixa'));
			$this->db->delete('caixa'); 
			
			$this->session->set_flashdata('msg', 'Caixa deletado com sucesso.');
			$this->show();
			//redirect(current_url());
			
		}else{
			$this->deleting();
		}
	}
	
	private function runFormValidations(){
	
		$this->form_validation->set_message('operacao',"%s é um campo obrigatório.");
		$this->form_validation->set_message('valor',"%s é um campo obrigatório.");
		
		$this->form_validation->set_rules('operacao', 'Operacao', 'required');
		$this->form_validation->set_rules('valor', 'Valor', 'required');
		
		return $this->form_validation->run();
	
	}
}
