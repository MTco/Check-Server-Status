<?php
	if(isset($_POST['name']) && isset($_POST['host']))
	{
		$port = 80;

		if(!empty($_POST['port'])) $port = $_POST['port'];

		addServer($_POST['name'], $_POST['host'], $port);
	}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta name="robots" content="noindex">
        <meta charset="utf-8">
        <title>Server Status</title>
        <link href="css/bootstrap.css" rel="stylesheet">
        <link href="css/bootstrap-theme.css" rel="stylesheet">
    </head>
    <body>
    	<div class="container">
    		<h3>Server status</h3>
    		<table class="table table-bordered">
				<tr>
					<th class="text-center">Name</th>
					<th class="text-center">Domain / IP</th>
					<th class="text-center">Port</th>
					<th class="text-center">Status</th>
				</tr>
                <?php parser(); ?>
			</table>
			<form class="form-inline" role="form" action="index.php" method="post">
				<div class="form-group">
					<input type="text" class="form-control" id="name" name="name" placeholder="Name">
				</div>
				<div class="form-group">
					<input type="text" class="form-control" id="host" name="host" placeholder="Domain / IP">
				</div>
				<div class="form-group">
					<input type="text" size="4" class="form-control" id="port" name="port" placeholder="Port">
				</div>
				<button type="submit" class="btn btn-default">Ajouter</button>
			</form>
			<br>
			<footer>
    			<a href="https://twitter.com/p1rox">@p1rox</a>
    		</footer> 
    	</div>   	
    </body>
</html>
<?php

function getStatus($ip, $port) {
	$socket = @fsockopen($ip, $port, $errorNo, $errorStr, 2);
	if (!$socket) return false;
	else return true;
}

function addServer($name, $host, $port) {
	$filename = 'servers.xml';
	$servers = simplexml_load_file($filename);
	$server = $servers->addChild('server');
	$server->addChild('name', (string)$name);
	$server->addChild('ip', (string)$host);
	$server->addChild('port', (string)$port);
	$servers->asXML($filename);
}

function parser() {
	$servers = simplexml_load_file("servers.xml");
	foreach ($servers as $server) {
		echo "<tr>";
		echo "<td>".$server->name."</td>";
		echo "<td>".$server->ip."</td>";
		echo "<td class=\"text-center\">".$server->port."</td>";
		if (getStatus((string)$server->ip, (string)$server->port)) {
			echo "<td class=\"text-center\"><span class=\"label label-success\">Online</span></td>";
		}
		else {
			echo "<td class=\"text-center\"><span class=\"label label-danger\">Offline</span></td>";
		}
		echo "</tr>";
	}
}

?>
