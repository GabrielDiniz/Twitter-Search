<?php
include_once 'mysql.php';
$con = new Mysql();
$host = "http://search.twitter.com/search.json";
$parametros = "?rpp=100&include_entities=true&result_type=recent&q=";
$todas = array();
$consultas = array();
while(1==1){
	$result = $con->query("SELECT * from queries order by frequencia");
	
	foreach ($result as $data){
		if(!in_array($data[1],$todas)){
			$consultas[$data[1]] = array(
				'id'=>$data[0],
				'pesquisa'=>urlencode($data[1]),
				'refresh'=>null,
				'cont'=>-1,
				'ultimo'=>$data[2],
				'frequencia'=>$data[3],
				'n'=>0,
				'ultima_execucao' => $data[4],
			);
			$todas[] = $data[1];
		}
	}
	$data = $con->query("SELECT * from config");
	$wait   = $data[0]*1;
	$fator  = $data[1]*1;
	$maximo = $data[2]*1;
	$minimo = $data[3]*1;
	$frequencia_minima 	= $data[4]*1;
	$retorno_frequencia	= $data[5]*1;
	$exagero	= $data[6]*1;
	foreach( $consultas as $x=>$consulta){
		if($consultas[$x]['n'] > $minimo || $consultas[$x]['cont']<0)
		{
			$sql = "select block, query, ultimo, frequencia, ultima_execucao from queries where id = {$consulta['id']}";
			$data = $con->query($sql);
			if (!$data[0])
			{
				$con->query("update queries set block = 1 where id = {$consulta['id']}");
				$consultas[$data[1]]['ultimo']=$data[2];
				$consultas[$data[1]]['frequencia']=$data[3]*1;
				$consultas[$data[1]]['ultima_execucao']=$data[4]*1;
				$start = microtime(true);
				if ($consulta['ultimo']){
					$url = $host."?since_id=".$consultas[$x]['ultimo']."&include_entities=true&q=".$consultas[$x]['pesquisa'];
				}
				else {
					$url = $host.$parametros.$consultas[$x]['pesquisa'];
				}
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

				$output = curl_exec($ch);

				curl_close($ch);

				$output = json_decode( $output, true );
				if (isset($output['error'])) {
					echo "ops... exagerei!\n";
					sleep($exagero);
				}
				$consultas[$x]['refresh'] = $output['refresh_url'];
				$i = -1;
				if (isset($output['results']))
				{
					foreach($output['results'] as $i=>$tweet){
						$json = str_replace("'","\'",json_encode($tweet));
						$con->query("insert into tweet values (null,'$json','{$tweet['id_str']}')",false);
					}					
				}
				$i++;
				$n = (pow($i-$maximo,2)/100);
				if ($i - $maximo > 0) {
					$consultas[$x]['frequencia'] += ($consultas[$x]['frequencia']*($n>0.9?0.9:$n));
				}else {
					$consultas[$x]['frequencia'] -= ($consultas[$x]['frequencia']*($n>0.9?0.9:$n))*1.5;
				}
				if ($consultas[$x]['frequencia'] < $frequencia_minima) {
					$consultas[$x]['frequencia'] = $retorno_frequencia;
				}
				$ultimo = ($i)?$tweet['id_str']:$consultas[$x]['ultimo'];
				$agora = microtime(true);
				$consultas[$x]['cont'] = $i;
				$consultas[$x]['n'] = 0;
				$con->query("UPDATE queries set block = 0, ultimo = '$ultimo', frequencia = '{$consultas[$x]['frequencia']}', ultima_execucao = $agora WHERE id = '{$consultas[$x]['id']}'");
				$data = date('d/m/Y H:i:s');
				$separador = (strlen($consulta['pesquisa']) < 8)?"\t\t-":"\t-";
				print_r("Depois de \033[0;34m" . number_format( $agora - $consultas[$x]['ultima_execucao'], 3) . "\033[0;0m s\tMais \033[0;32m$i\033[0;0m\tresultados para \033[0;31m{$consultas[$x]['pesquisa']}\033[0;0m$separador em \033[0;35m" . number_format( $agora - $start, 3) . "\033[0;0m s \033[0;33m$data\033[0;0m\n");
				$consultas[$x]['ultima_execucao'] = $agora;
				
			}else{
				//echo "{$consulta['pesquisa']} Bloqueado... :(\n";
				$consultas[$x]['n'] /= 2;
			}
		}else {
			$consultas[$x]['n']+=($wait/$fator)*($consultas[$x]['frequencia']/100);
		}
	}
	if ($wait) {
		usleep($wait);
	}
}
?>

