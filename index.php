<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

	<title>Calculadora VLSM</title>
</head>
<body>
	<center><h4>David Marquez, Jorge Medina, Omar Jara  Redes UNEFA - TACHIRA</h4></center>
	<center><h1 class="justify-text-center">Calculadora IP en VLSM</h1></center>
	<div class="row d-flex justify-content-center mt-4">
		<div class="col-md-6">
			<div class="card border-secondary mb-3">
			<div class="card-body text-secondary">
			<form method='get'>
				<label for="exampleFormControlInput1" class="form-label">Dirección de red</label>
				<input type='text' name='direccion' class="form-control" style='width:100%' <?php if(isset($_GET['direccion'])) echo "value='".$_GET['direccion']."'"; ?> />

				<br/>

				<label for="exampleFormControlInput1" class="form-label">Mascaras de red <br>(Separadas por coma. 20,21,24,29,30,30)</label>
				<textarea name='mascara' class="form-control" style='width:100%'><?php if(isset($_GET['mascara'])) echo $_GET['mascara']; ?></textarea>

				<br/>
				<div class="d-flex justify-content-center">
					<button type="submit" class="btn btn-dark btn-outline-light">Calcular</button>
				</div>
				
			</form>
			</div>
			</div>
		</div>
	</div>
	
	<br><br><br>
	<div class="row d-flex justify-content-center">
		<div class="col-md-9">
	<table class="table table-dark">
	  <thead>
	    <tr class="table-light">
	      <th scope="col">Dirección</th>
	      <th scope="col">Mascara</th>
	      <th scope="col">Broadcast</th>
	      <th scope="col">Host Min - Host Max</th>
	    </tr>
	  </thead>
	  <tbody>
	    <tr> <?php
		if(!isset($_GET['direccion']) || !isset($_GET['mascara'])){
			die();
		}

		define('primer_oct', 0);
		define('segundo_oct', 1);
		define('tercer_oct', 2);
		define('cuarto_oct', 3);

		$r_addr = $_GET['direccion'];
		$r_mask = $_GET['mascara'];

		$results = array();

		$addr_parts = explode('.', $r_addr);
		$masks = explode(',', $r_mask);

		if(sizeof($addr_parts) < 4){
			die('Direccion de red incorrecta');
		}

		foreach($addr_parts as $part){
			if(!is_numeric($part) || $part < 0 || $part > 255){
				die('Direccion de red incorrecta');
			}
		}

		asort($masks);
		$next_addr = $addr_parts;

		foreach($masks as $mascara){
			if(!is_numeric($mascara) || $mascara < 0 || $mascara > 32){
				die("Mascara de red incorrecta: $mascara");
			}

			$subnet = array(
				'direccion' 		=> '',
				'mascara' 		=> '',
				'bit_mask'	=> '',
				'host_min'	=> '',
				'host_max'	=> '',
				'broadcast'	=> '',
				'wildcard'	=> ''
			);

			$subnet['direccion'] = implode('.', $next_addr);
			$current_addr = $next_addr;

			$mask_octets = array();
			$wilcard_octets = array();

			$binary_mask = chunk_split(str_pad(str_pad('', $mascara, '1', STR_PAD_LEFT), 32, '0', STR_PAD_RIGHT), 8, '.');
			$subnet['bit_mask'] = $binary_mask;

			for($i = 1; $i <= 4; $i++){
				if($mascara >= $i * 8){
					$mask_octets[$i - 1] = 255;
					$wilcard_octets[$i - 1] = 0;
				}else{
					$bits_on = substr_count($binary_mask, '1') - (($i - 1) * 8);
					$bits_on = $bits_on < 0 ? 0 : $bits_on;
					$mask_octets[$i - 1] = bindec(str_pad(str_pad('', $bits_on, '1', STR_PAD_LEFT), 8, '0', STR_PAD_RIGHT));
					$wilcard_octets[$i - 1] = 255 - $mask_octets[$i - 1];

					$bits_off = 8 - $bits_on;
					$max_octet_value = bindec(str_pad('', $bits_off, '1', STR_PAD_LEFT));
					$next_addr[$i - 1] += $max_octet_value;
				}
			}

			$current_addr[cuarto_oct] += 1;

			$subnet['mascara'] = implode('.', $mask_octets);
			$subnet['wildcard'] = implode('.', $wilcard_octets);
			$subnet['broadcast'] = implode('.', $next_addr);
			$subnet['host_min'] = implode('.', $current_addr);

			$current_addr = $next_addr;
			$current_addr[cuarto_oct] -= 1;

			$subnet['host_max'] = implode('.', $current_addr);

			for($i = 3; $i > 0; $i--){
				if($next_addr[$i] < 255){
					$next_addr[$i]++;
					break;
				}else{
					$next_addr[$i] = 0;
				}
			}
			?>
      		<td><?php echo $subnet['direccion'].'/'.$mascara; ?></td>
      		<td><?php echo $subnet['mascara']; ?></td>
      		<td><?php echo $subnet['broadcast']; ?></td>
      		<td><?php echo $subnet['host_min'].' - '.$subnet['host_max']; ?></td>
		</tr>
	</tbody>
			
	<?php
		}

		function printLine($name, $value){
			echo "$name: $value<br/>";
		}
	?>

	</table>
	</div>
	</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
</body>
</html>