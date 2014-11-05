<?php
	if(isset($_POST['name']) && isset($_POST['host']))
	{
		$port = 80;

		if(!empty($_POST['port'])) $port = $_POST['port'];

		addServer($_POST['name'], $_POST['host'], $port);

		header('Location: index.php');
	}
	else if(isset($_GET['del']))
	{
		$index = (int) $_GET['del'];
		if($index >= 0) deleteServer($index);
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
					<th class="text-center" style="width:80px">Delete</th>
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

function getStatus($ip, $port)
{
	$socket = @fsockopen($ip, $port, $errorNo, $errorStr, 2);
	if (!$socket) return false;
	else return true;
}

function addServer($name, $host, $port)
{
	// TODO : rewrite the opening part correctly (better errors management)
	$i = 0;
	$filename = 'servers.xml';

	$servers = file_get_contents("servers.xml");
	if (trim($servers) == '')
	{
		exit();
	}
	else
	{
		$servers = simplexml_load_file("servers.xml");
		foreach ($servers as $server) $i++;
	}

	$servers = simplexml_load_file($filename);
	$server = $servers->addChild('server');

	$server->addAttribute('id', (string) $i);
	//$server->addChild('id', (string)$id++);
	if(strlen($name) == 0) $name = $host;
	$server->addChild('name', (string)$name);
	$server->addChild('ip', (string)$host);
	$server->addChild('port', (string)$port);
	$servers->asXML($filename);
}

function parser()
{
	//TODO : Fix errors when no valid XML content inside file.
	$file = "servers.xml";
	if(file_exists($file))
	{
		$servers = file_get_contents("servers.xml");
		if (trim($servers) == '') //File exists but empty
		{
			
			$content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><servers></servers>";
			file_put_contents($file, $content, FILE_APPEND | LOCK_EX);
		}
		else
		{
			$servers = simplexml_load_file("servers.xml");
			$i = 0;
			foreach ($servers as $server)
			{
				echo "<tr>";
				echo "<td>".$server->name."</td>";
				echo "<td>".$server->ip."</td>";
				echo "<td class=\"text-center\">".$server->port."</td>";

				if (getStatus((string)$server->ip, (string)$server->port))
				{
					echo "<td class=\"text-center\"><span class=\"label label-success\">Online</span></td>";
				}
				else 
				{
					echo "<td class=\"text-center\"><span class=\"label label-danger\">Offline</span></td>";
				}
				echo "<td class=\"text-center\"><a href=\"index.php?del=".$server->attributes()."\">X</a></td>";
				echo "</tr>";
				$i++;
			}
		}
	}
	else
	{
		// TODO : detect creation errors (ex : permissions)
		$content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><servers></servers>";
		file_put_contents($file, $content, FILE_APPEND | LOCK_EX);
	}
}

function deleteServer($index)
{
	$file = "servers.xml";

	$serverFile = new DOMDocument; 
	$serverFile->load($file);
	$servers = $serverFile->documentElement;
	$list = $servers->getElementsByTagName('server');
	$nodeToRemove = null;

	foreach ($list as $server)
	{
		$attrValue = $server->getAttribute('id');
		if ((int)$attrValue == $index) $nodeToRemove = $server;
	}

	//Now remove it.
	if ($nodeToRemove != null) $servers->removeChild($nodeToRemove);

	$serverFile->save($file); 
}