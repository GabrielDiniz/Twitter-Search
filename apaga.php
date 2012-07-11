<?php
include 'mysql.php';
$con =  new Mysql();
$cont=1;
while ($cont>0) {
	echo "Buscando resultados, aguarde....\n";
	$result = $con->query('SELECT id,count(*) c FROM `tweet` group by id_tweeter having c>1 order by c desc');
	$cont = count($result);
	echo "vo apagar ".$cont." itens\n";
	$p = 0;
	if ($cont){
		foreach ($result as $i=>$item) {
			$con->query("delete from tweet where id = {$item[0]}");
			if (!($i%(int)($cont/10000))) {
				$p += 0.01;
				echo number_format($p,2)."% completados\n";
			}
		}
	}
}
