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
							ON rpt.id_produto = pt.id_produto and rpt.ativo = 1
						WHERE re.id_reserva = rpt.id_reserva) 
						AS valor_produtos

				FROM reserva re

				inner JOIN quarto qt 
					ON re.id_quarto = qt.id_quarto
				inner JOIN perfil pf 
					ON qt.id_perfil = pf.id_perfil
				where re.id_situacao not in (2,3,5,6)";
		
		$this->load->view('index', array(
					'page'=>'comanda'
					,'title'=> 'Comandas'
					,'part' => 'searching'
					,'tabledata'=>$this->db->query($sql)->result()
					,'produtos'=>$this->db->get('produto')->result()
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
		$id = (int) $this->uri->segment(3);
		
		$sql = "SELECT 
					re.id_reserva ,
					re.entrada ,
					re.saida ,
					
					TIMESTAMPDIFF
					(
					DAY, 
					re.entrada + INTERVAL TIMESTAMPDIFF(MONTH, re.entrada, re.saida) MONTH, 
					re.saida
					) AS dias ,
					
					TIMESTAMPDIFF
					(
					HOUR, 
					re.entrada + INTERVAL TIMESTAMPDIFF(DAY,  re.entrada, re.saida) DAY, 
					re.saida
					) AS hora,
					
					TIMESTAMPDIFF
					(
					MINUTE, 
					re.entrada + INTERVAL TIMESTAMPDIFF(HOUR,  re.entrada, re.saida) HOUR, 
					re.saida
					) AS minutos,
					
					qt.numero ,
					pf.descricao AS perfil ,
					pf.tp_modo_reserva AS tipo ,
					pf.preco_base AS valor_perfil ,
					(SELECT SUM(it.preco) FROM perfil_item pit 
						LEFT JOIN item it 
							ON pit.id_item = it.id_item 
						WHERE pf.id_perfil = pit.id_perfil AND pf.id_perfil = qt.id_perfil) 
						AS valor_itens,		
					(SELECT SUM(pt.preco) FROM reserva_produto rpt 
						LEFT JOIN produto pt 
							ON rpt.id_produto = pt.id_produto and rpt.ativo = 1
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
		$permanencia = $result->hora.':'.$result->minutos;
		$diarias = (!$result->dias)?1:$result->dias;
		$precoPerfil = $result->valor_perfil+$result->valor_itens;
		$valorProdutos = $result->valor_produtos;
	
		if( $result->tipo == 1 ){
			// diaria
			$precoQuarto = $precoPerfil*$diarias;
		} elseif( $result->tipo==2 ) {
			// hora
			if($result->minutos>15) // tolerancia
				$precoQuarto = $precoPerfil*($result->hora.',5');
			else
				$precoQuarto = $precoPerfil*$result->hora;
		}
		
		$total = $precoQuarto+$result->valor_produtos;
				
		//fazer lista de produtos para a view
		$s = "SELECT rpt.id_reserva_produto, produto, preco FROM produto pt inner JOIN reserva_produto rpt ON rpt.id_produto = pt.id_produto and rpt.ativo = 1 WHERE rpt.id_reserva = ".$id;
		$produtos = $this->db->query($s)->result_array();
		
		echo json_encode( 
					 array(
						"id"=>$id
						,"numero"=> $quarto
						,"perfil"=>$perfil
						,"entrada"=>dateTimeToBr($entrada)
						,"saida"=>dateTimeToBr($saida)
						,"permanencia"=>$permanencia
						,"produtos"=>$produtos
						,"precoPerfil"=> monetaryOutput($precoPerfil)
						,"precoQuarto"=> monetaryOutput($precoQuarto)
						,"valorProdutos"=> monetaryOutput($valorProdutos)
						,"total"=>monetaryOutput($total) 
						   )
						);
	}
	
	public function nada(){}
}
