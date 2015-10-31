<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Comanda extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->helper(array('url','form','array','app'));
		$this->load->library(array('form_validation','session'));
		$this->load->database();
		$this->load->model('Login_model','login');
		$this->login->authorize();
    }
	 
	public function index(){
		
		$sql = "SELECT 
					re.id_reserva ,
					qt.numero ,
					pf.preco_base AS valor_perfil ,
					(SELECT SUM(it.preco) FROM perfil_item pit 
						LEFT JOIN item it 
							ON pit.id_item = it.id_item 
						WHERE pf.id_perfil = pit.id_perfil AND pf.id_perfil = qt.id_perfil) 
						AS valor_itens,		
					(SELECT SUM(pt.preco) FROM reserva_produto rpt 
						LEFT JOIN produto pt 
							ON rpt.id_produto = pt.id_produto 
						WHERE re.id_reserva = rpt.id_reserva) 
						AS valor_produtos

				FROM reserva re

				LEFT JOIN quarto qt 
					ON re.id_quarto = qt.id_quarto
				LEFT JOIN perfil pf 
					ON qt.id_perfil = pf.id_perfil";
		
		$this->load->view('index', array(
					'page'=>'comanda'
					,'title'=> 'Comandas'
					,'part' => 'searching'
					,'tabledata'=>$this->db->query($sql)->result()
				));
	}
	
	public function searching(){
		$this->db->like('nome', $this->input->get('nome'));
		
		$this->load->view('index',array(
					'page'=>'comanda'
					,'title'=> 'Comandas'
					,'part' => 'searching'
					,'tabledata'=>$this->db->get('comanda')->result()
				));
	}
	
	public function detail(){
		$id = $this->uri->segment(3);
		
		$sql = "SELECT 
					re.id_reserva ,
					re.entrada ,
					re.saida ,
					qt.numero ,
					pf.descricao AS perfil ,
					pf.preco_base AS valor_perfil ,
					(SELECT SUM(it.preco) FROM perfil_item pit 
						LEFT JOIN item it 
							ON pit.id_item = it.id_item 
						WHERE pf.id_perfil = pit.id_perfil AND pf.id_perfil = qt.id_perfil) 
						AS valor_itens,		
					(SELECT SUM(pt.preco) FROM reserva_produto rpt 
						LEFT JOIN produto pt 
							ON rpt.id_produto = pt.id_produto 
						WHERE re.id_reserva = rpt.id_reserva) 
						AS valor_produtos

				FROM reserva re

				LEFT JOIN cliente cl 
					ON re.id_cliente = cl.id_cliente
				LEFT JOIN quarto qt 
					ON re.id_quarto = qt.id_quarto
				LEFT JOIN perfil pf 
					ON qt.id_perfil = pf.id_perfil

				WHERE re.id_reserva = ".$id;
		$result = $this->db->query($sql)->row();
		
		$quarto = $result->numero;
		$perfil = $result->perfil;
		$entrada = $result->entrada;
		$saida = $result->saida;
		$precoQuarto = $result->valor_perfil+$result->valor_itens;
		$valorProdutos = $result->valor_produtos;
		$total = $precoQuarto+$result->valor_produtos;
		
		//fazer lista de produtos para a view
		$s = "SELECT produto, preco FROM produto pt LEFT JOIN reserva_produto rpt ON rpt.id_produto = pt.id_produto WHERE rpt.id_reserva = ".$id;
		$res = $this->db->query($s)->result();
		foreach($res as $r){
			$produtos[] = array( 
							'produto' => $r->produto,
							'preco' => $r->preco
						);
		}
		
		echo json_encode( array("id"=>$id,"numero"=> $quarto,"perfil"=>$perfil,"entrada"=>$entrada,"saida"=>$saida,"produtos"=>$produtos,"precoQuarto"=>$precoQuarto,"valorProdutos"=>$valorProdutos,"total"=>$total) );
	}
	
	public function nada(){}
}
