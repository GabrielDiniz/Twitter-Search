<?php
$host = "http://search.twitter.com/search.json";
$parametros = "?rpp=100&include_entities=true&result_type=recent&q=";
mysql_pconnect("localhost","root","ak47") or die("conectou?");
mysql_selectdb('t')or die("selecionou?");
$todas = array();
$consultas = array();
while(1==1){
	$result = mysql_query("SELECT * from queries order by frequencia DESC");
	while($data = mysql_fetch_row($result)){
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
		$consultas[$data[1]]['ultimo']=$data[2];
		$consultas[$data[1]]['frequencia']=$data[3]*1;
		$consultas[$data[1]]['ultima_execucao']=$data[4]*1;
	}
	$result = mysql_query("SELECT * from config");
	$data = mysql_fetch_row($result);
	$wait   = $data[0]*1;
	$fator  = $data[1]*1;
	$maximo = $data[2]*1;
	$minimo = $data[3]*1;
	foreach( $consultas as $x=>$consulta){
		if($consultas[$x]['n'] > $minimo || $consultas[$x]['cont']<0)
		{
			$sql = "select block from queries where id = {$consulta['id']}";
			$block = mysql_query($sql) or die(mysql_error());
			$block = mysql_fetch_row($block)
			$start = microtime(true);
			if($consulta['refresh']){
				$url = $host.$consulta['refresh'];
			}elseif ($consulta['ultimo']){
				$url = $host."?since_id=".$consulta['ultimo']."&include_entities=true&q=".$consulta['pesquisa'];
			}
			else {
				$url = $host.$parametros.$consulta['pesquisa'];
			}
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
			$output = curl_exec($ch);
		
			curl_close($ch);
	
			$output = json_decode( $output, true );
			$consultas[$x]['refresh'] = $output['refresh_url'];
			$i = -1;
			foreach($output['results'] as $i=>$tweet){
				$json = str_replace("'","\'",json_encode($tweet));
				$sql = "insert into tweet values (null,'$json')";
				mysql_query($sql)or die(mysql_error());
				$ultimo = $tweet['id_str'];
			}
			$i++;
			$n = (pow($i-$maximo,2)/100);
			if ($i - $maximo > 0) {
				$consultas[$x]['frequencia'] += ($consultas[$x]['frequencia']*($n>0.9?0.9:$n));
			}else {
				$consultas[$x]['frequencia'] -= ($consultas[$x]['frequencia']*($n>0.9?0.9:$n));
			}
			if ($consultas[$x]['frequencia'] < 1) {
				$consultas[$x]['frequencia'] = 2;
			}
			$ultimo = ($i)?$ultimo:$consultas[$x]['ultimo'];
			$agora = microtime(true);
			$sql = "UPDATE queries set ultimo = '$ultimo',frequencia = '{$consultas[$x]['frequencia']}', ultima_execucao = $agora WHERE id = '{$consulta['id']}'";
			mysql_query($sql)or die(mysql_error());
			$consultas[$x]['cont'] = $i;
			$consultas[$x]['n'] = 0;
			$data = date('d/m/Y H:i:s');
			$separador = (strlen($consulta['pesquisa']) < 8)?"\t\t-":"\t-";
			print_r("Depois de \033[0;34m" . number_format( $agora - $consultas[$x]['ultima_execucao'], 3) . "\033[0;0m s\tMais \033[0;32m$i\033[0;0m\tresultados para \033[0;31m{$consulta['pesquisa']}\033[0;0m$separador em \033[0;35m" . number_format( $agora - $start, 3) . "\033[0;0m s \033[0;33m$data\033[0;0m\n");
			$consultas[$x]['ultima_execucao'] = $agora;
		}else {
			$consultas[$x]['n']+=($wait/$fator)*($consulta['frequencia']/100);
		}
	}
}
?>

