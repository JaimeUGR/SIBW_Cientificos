<?php
	require_once "/usr/local/lib/php/vendor/autoload.php";
	require_once('../model/DBAccess.php');

	$loader = new \Twig\Loader\FilesystemLoader("../templates");
	$twig = new \Twig\Environment($loader);

	$bd = new DBAccess();
	$nombrePagina = "SIBW_2023 @Jaime";
	$cientificos = $bd->GetTarjetasCientificos();

	//
	// Gestión sesión
	//

	session_start();

	$infoUsuario = null;

	if (isset($_SESSION['usuario']))
	{
		$db = new DBAccess();
		$infoUsuario = $db->GetUsuario($_SESSION['usuario']);
	}

	//
	// Render
	//

	echo $twig->render("portada.html", [
		"titulo_pagina" => $nombrePagina,
		"cientificos" => $cientificos,
		"usuario" => $infoUsuario,
		"redireccion" => "/"
	]);
