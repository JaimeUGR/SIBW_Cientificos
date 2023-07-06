<?php

function getPalabrasProhibidas()
{
	require_once "../model/DBAccess.php";
	$db = new DBAccess();

	return ["palabras" => $db->GetPalabrasProhibidas()];
}

function buscarCientificos()
{
	if (!isset($_GET['query']))
		return null;

	// Comprobar los permisos de la sesión para incluir los que son invisibles también
	session_start();

	$query = $_GET['query'];

	// Devolver una lista con cada elemento nombre apellidos y enlace.
	require_once "../model/DBAccess.php";
	$db = new DBAccess();

	$incluirNoVisibles = isset($_SESSION['usuario'])
		&& $db->Auth_CheckPermissionList($_SESSION['usuario'], ["Gestor", "Superusuario"]);

	$encontrados = $db->BuscarCientificosNombreApellidos($query, $incluirNoVisibles);

	return $encontrados;
}


// Comprobamos
// 1) El parámetro está definido
// 2) El parámetro tiene un valor
// 3) La función se ha definido en este script

//
// API GET
//
if(isset($_GET['function']) && !empty($_GET['function']) && function_exists($_GET['function']))
{
	$result = $_GET['function']();
	header('Content-Type: application/json');
	echo json_encode($result);
}
else
{
	require_once "error.php";
	GeneralErrorPage("Función de la api desconocida", 404);
}
