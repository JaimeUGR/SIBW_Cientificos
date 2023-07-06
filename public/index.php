<?php
	$parsed_uri = parse_url($_SERVER['REQUEST_URI']);
	$uri_path = $parsed_uri['path'];

	# Forma 2 - URL Sucia
	# NOTA: Si se utiliza la directiva HEADER, se pueden hacer URL Limpias desde aquí
	# NOTA: es posible que no funcione correctamente. Solo se deja como método alternativo
	if (strcmp($uri_path, "/") == 0)
	{
		include("portada.php");
	}
	elseif (strcmp($uri_path, "/cientificos") == 0)
	{
		include("cientifico.php");
	}
	elseif (strcmp($uri_path, "/login") == 0)
	{
		include("login.php");
	}
	elseif (strcmp($uri_path, "/signup") == 0)
	{
		include("signup.php");
	}
	else
	{
		require_once "error.php";
		GeneralErrorPage("No existe la página que has buscado", 404);
	}
