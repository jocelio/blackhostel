<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$user = $this->session->get_userdata();
$gerente = $user['user_session']['gerente'];
$admin = $user['user_session']['admin'];

?>
<script>
	$(document).ready(function(){
		$('#preco').mask('000.000.000.000.000,00', {reverse: true});
		$('#duallist').DualListBox({json:false, available:'Disponiveis', selected:'Selecionados',showing:'mostrando',filterLabel:'Filtro'});

		$(".addpersonprice").click(function(){
			$.ajax({
					url: '<?php echo site_url();?>/perfil/addpersonprice/',
					data: {'qt_pessoas':$('#qt_pessoas').val(), 'preco':$('#ppreco').val() ,'perfil': $("#id_perfil").val() },
					type: 'POST',
					success: function(data){fillData(data)}
			});
		});
		
		$(document).on("click", ".remove-preco", function(event){
			event.preventDefault();
			console.log($(this).attr('href'))
			$.ajax({
				url: $(this).attr('href'),
				type: 'POST',
				success: function(data){
					
					fillData(data)
					}
			});

		});

		function fillData(data){
			data = JSON.parse(data);
			$('#bodytable').empty();
			
			$.each(data, function(i,pp) {
				$('#bodytable').append( 
				'<tr>'+
				 	'<td>'+pp.qt_pessoas+'</td>'+
				 	 '<td> R$ '+pp.preco+'</td>'+
				 	 '<td> <a href="<?php echo site_url();?>/perfil/removepersonprice/'+pp.qt_pessoas+'" class="btn btn-danger btn-sm remove-preco">Remover <span class="glyphicon glyphicon-remove"></span></a>'+ 
				 	 '</td>'+ 
			 	 '</tr>'
				); 

			});
			
			$('#qt_pessoas').val('');
			$('#ppreco').val('');
		}
	});
</script>

<?php 
/**
* Área da tela responsável pela pesquisa e exibição da lista de resultados
*/
	if($part =="searching"){
	

?>


	<form action="<?php echo site_url();?>/perfil/searching">
	<div class="row">
		<div class="col-md-5 form-group">
			<input type="text" placeholder="Descrição do perfil" name="descricao" class="form-control"/>
		</div>
		<div class="col-md-5 form-group">
			<input type="submit" name="submit" value="Buscar" class="btn btn-success">
		</div>
	</div>
	<div class="row">
		<div class="col-md-1 col-often-11 form-group pull-right">
			<?php if($gerente) { ?><a class="btn btn-info" href="<?php echo site_url();?>/perfil/inserting">Novo</a><?php } ?>
		</div>
	</div>
	</form>
	<div class="row">
		<div class="large-12 columns">
		<table class="table table-striped table-bordered table-responsive"> 
			<tr>
				<th>ID</th>
				<th>Descrição</th>
				<th>Reserva</th>
				<th>Preço do Perfil</th>
				<th>Preço dos Itens</th>
				<th>Total Perfil</th>
				<?php if($gerente) { ?><th>Opções</th><?php } ?>
			</tr>
			<?php foreach($tabledata as $perfil){ ?>
			<tr>
				<td><?php echo $perfil->id_perfil ?></td>
				<td><?php echo $perfil->descricao ?></td>
				<td><?php echo ($perfil->tp_modo_reserva ==1?'Diária':(($perfil->tp_modo_reserva == 2)?'Hora': 'Pernoite'));; ?></td>
				<td>R$ <?php echo ($perfil->tp_modo_reserva ==2)?monetaryOutput($perfil->preco_base):'&nbsp;&nbsp;&nbsp;&nbsp;-'; ?></td>
				<td>R$ <?php echo monetaryOutput($perfil->preco_itens) ?> </td>
				<td>R$ <?php echo ($perfil->tp_modo_reserva ==2)?monetaryOutput($perfil->preco_base + $perfil->preco_itens):'&nbsp;&nbsp;&nbsp;&nbsp;-'; ?></td>
				<?php if($gerente) { ?><td>
					<a href="<?php echo site_url();?>/perfil/editing/<?php  echo $perfil->id_perfil ?>" class="btn btn-default btn-sm">Editar 
						<span class="glyphicon glyphicon-edit"></span>
					</a>
				
					<a href="<?php echo site_url();?>/perfil/deleting/<?php  echo $perfil->id_perfil ?>" class="btn btn-default btn-sm">Deletar 
						<span class="glyphicon glyphicon-remove"></span>
					</a>
				</td><?php } ?>
			</tr>
			<?php } ?>
			
			
			
		</table> 
		</div>
	</div>
	
<?php 
/**
* Ã�rea da tela responsÃ¡vel pelo formulÃ¡rio de inserÃ§Ã£o de dados
*/
	}else if($part =="inserting"){
		
	echo form_open('perfil/save');	
?>

<div class="row">
	<div class="col-md-6 form-group">		  
	  <?php
		echo form_label('Descrição');
		echo form_input(array('name'=>'descricao','class'=>'form-control','placeholder'=>'Descrição do perfil'),set_value('descricao'),'autofocus');
	  ?>
	</div>
</div>
<div class="row">
	<div class="col-md-6 form-group">
	  <?php
		echo form_label('Preço');
		echo form_input(array('name'=>'preco_base','id'=>'preco','class'=>'form-control','placeholder'=>'Preço base do perfil'),set_value('preco'));
		
	  ?>
	</div>
</div>
<div class="row">
	<div class="col-md-6 form-group">
		<label>Modalidade de Reserva</label>
		<select name="tp_modo_reserva" class="form-control" required="true">
				<option value=""> -- Selecione --</option>
				<option value="1">Diária</option>
				<option value="2">Hora</option>
				<option value="3">Pernoite</option>
		
		</select>
	</div>
</div>
<div class="row">
	<div class="col-md-6 form-group">
		<label>Itens</label>
	</div>
</div>
<div class="row">
	<div class="col-md-8 form-group">
		<select name="itens[]" class="form-control" id="duallist" multiple="true">
			<?php foreach($itens as $item){ ?>
				<option value="<?php echo $item->id_item ?>"><?php echo $item->descricao.' - '.$item->preco ?> </option>
			<?php } ?>
		</select>
	</div>
</div>

<div class="row">
	<div class="col-md-6 form-group">
	 <?php
		echo form_submit(array('name'=>'cadastrar','class' =>'btn btn-success'),'Cadastrar')." ";
		echo form_reset(array('name'=>'limpar','class' =>'btn btn-danger'),'Limpar');
	  ?>
	</div>
	<div class="col-md-6 form-group">
		<a class="btn btn-info" href="<?php echo site_url();?>/perfil" class="button success">Voltar</a>  
	</div>
</div>

<?php 

/**
* Ã�rea da tela responsÃ¡vel pelo formulÃ¡rio de ediÃ§Ã£o de dados
*/
	}else if($part =="editing"){
		
	echo form_open('perfil/edit');
?>
<input type="hidden" name="id_perfil" value="<?php echo $perfil->id_perfil ?>" id="id_perfil">
<div class="row">
	<div class="col-md-6 form-group">		  
	  <?php
		echo form_label('Descrição');
		echo form_input(array('name'=>'descricao','class'=>'form-control','placeholder'=>'Descrição do perfil'),$perfil->descricao ,'autofocus');
	  ?>
	</div>
</div>
<div class="row">
	<div class="col-md-6 form-group">
		<label>Modalidade de Reserva</label>
		<select name="tp_modo_reserva" class="form-control" required="true">
				<option value=""> -- Selecione --</option>
				<option value="1" <?php echo tagAs('selected', $perfil->tp_modo_reserva , 1) ?> >Diária </option>
				<option value="2" <?php echo tagAs('selected', $perfil->tp_modo_reserva , 2) ?>>Hora </option>
				<option value="3" <?php echo tagAs('selected', $perfil->tp_modo_reserva , 3) ?>>Pernoite </option>
		
		</select>
	</div>
</div>
<?php if($perfil->tp_modo_reserva == 2){?>
<div class="row">
	<div class="col-md-6 form-group">
	  <?php
		echo form_label('Preço');
		echo form_input(array('name'=>'preco_base','id'=>'preco','class'=>'form-control','placeholder'=>'Preço do perfil'),$perfil->preco_base);
	  ?>
</div>
</div>
<?php }else{ ?>
<div class="row">
	<div class="col-md-6 form-group">

<div class="panel panel-primary">
  <div class="panel-heading">Hóspedes</div>
  <div class="panel-body">
	<div class="row">
	<div class="col-md-12 form-group">
	
		<div class="row">
			<div class="col-md-5 form-group">
				<label>Quantidade de Hóspedes</label>
				<input type="number" id="qt_pessoas" class="form-control" min="1">
			</div>
			<div class="col-md-4 form-group">
				<label>Preço</label>
				<input type="text" id="ppreco" class="form-control" >
			</div>
			<div class="col-md-3 form-group">
				<input type="button" value="Adicionar" class="btn btn-success addpersonprice" style="margin-top: 24px;"/>
			</div>
		</div>
			
		<div class="row">
			<div class="col-md-12 form-group">
				<table class="table">
					<thead>
						<tr>
							<th>Quantidade de Hóspedes</th>
							<th>Preço</th>
							<th>Opções</th>
						</tr>
					</thead>
					<tbody id="bodytable">
						<?php foreach ($pessoaspreco as $pp){?>
							<tr>
								<td><?php echo $pp['qt_pessoas'] ?></td>
								<td>R$ <?php echo monetaryOutput( $pp['preco'] )?></td>
								<td> <a href="<?php echo site_url().'/perfil/removepersonprice/'.$pp['qt_pessoas'] ?>" class="btn btn-danger btn-sm remove-preco">Remover <span class="glyphicon glyphicon-remove"></span></a></td>
							</tr>
						<?php }?>
					</tbody>
				</table>
			</div>
		</div>
		
	</div>
	</div>				 
				  
  </div>
</div>

	</div>
</div>
<?php }?>
<div class="row">
	<div class="col-md-8 form-group">
		<select name="itens[]" class="form-control" id="duallist" multiple="true">
			<?php foreach($itens as $item){ ?>
				<option value="<?php echo $item->id_item ?>" <?php echo (@in_array($item->id_item, $perfilItens))?'selected="true"':'' ?> ><?php echo $item->descricao.' - '.$item->preco ?> </option>
			<?php } ?>
		</select>
	</div>
</div>
<div class="row">
	<div class="col-md-6 form-group">
	 <?php
		echo form_submit(array('name'=>'editar','class' =>'btn btn-success'),'Editar')." ";
		echo form_reset(array('name'=>'limpar','class' =>'btn btn-danger'),'Limpar');
	  ?>	
	</div>
	<div class="col-md-6 form-group">
		<a class="btn btn-info" href="<?php echo site_url();?>/perfil" class="button success">Voltar</a>  
	</div>
</div>

<?php
/**
* Ã�rea da tela responsÃ¡vel pela confirmaÃ§Ã£o de deleÃ§Ã£o dos dados
*/
	}else if($part =="deleting"){
		
	echo form_open('perfil/delete');
	echo form_hidden('id_perfil', $perfil->id_perfil);
?>

<div class="row">
	<div class="col-md-6 form-group">		  
	  <?php
		echo form_label('Descrição');
		echo form_input(array('name'=>'descricao','class'=>'form-control','readonly'=>'readonly'),$perfil->descricao);
	  ?>
	</div>
</div>
<div class="row">
	<div class="col-md-6 form-group">
	  <?php
		echo form_label('Preço');
		echo form_input(array('name'=>'preco','id'=>'preco','class'=>'form-control','readonly'=>'readonly'),$perfil->preco_base);
	  ?>
	</div>
</div>
<div class="row">
	<div class="col-md-6 form-group">
	 <?php
		echo form_submit(array('name'=>'deletar','class' =>'btn btn-danger'),'Deletar')." ";
		//echo form_reset(array('name'=>'limpar','class' =>'btn btn-danger'),'Limpar');
	  ?>	
	</div>
	<div class="col-md-6 form-group">
		<a class="btn btn-info" href="<?php echo site_url();?>/perfil" class="button success">Voltar</a>  
	</div>
</div>

<?php
echo form_close();
}
?>

<div class="row">
	
	<?php 
	$a = validation_errors();
	if(!empty($a)){ ?>
	<div class="alert alert-success">
		<?php echo $a; ?>
	</div>
	<?php } 
	
	$b = $this->session->flashdata('msg');
	if(!empty($b)){ ?>
	<div class="alert alert-success">
	  <?php echo $b; ?>	
	</div>
	<?php } ?>
</div>	
