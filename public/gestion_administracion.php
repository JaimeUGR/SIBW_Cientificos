<?php
	require_once "/usr/local/lib/php/vendor/autoload.php";
	require_once "../model/DBAccess.php";

	$loader = new \Twig\Loader\FilesystemLoader("../templates");
	$twig = new \Twig\Environment($loader);

	session_start();

	// Comprobaciones básicas

	$db = new DBAccess();

	if (!isset($_SESSION['usuario']) || !$db->Auth_CheckPermission($_SESSION['usuario'], "Superusuario"))
	{
		require_once('error.php');
		GeneralErrorPage("Página no encontrada", 404);
		exit();
	}

	if ($_SERVER['REQUEST_METHOD'] === 'POST')
	{
		// Formulario de gestión de usuario
		if ($_POST['gestion_usuario'])
		{
			// Obtener el usuario objetivo y los nuevos permisos
			$usuarioGestionado = $_GET['usuario'] ?? null;
			$nuevosPermisos = $_POST['nuevos_permisos'];

			// NOTE: Se ha decidido que por diseño, un superusuario no puede cambiar sus propios
			// permisos. En caso de que se quiera permitir, eliminar esta condición.
			if ($usuarioGestionado !== $_SESSION['usuario'])
				$db->CambiarPermisos($usuarioGestionado, $nuevosPermisos);
		}

		header("Location: /gestion/administracion");
		exit();
	}

	$infoUsuario = $db->GetUsuario($_SESSION['usuario']);
	$listaUsuarios = $db->GetListaUsuarioPermiso();
	$listaPermisos = $db->GetListaPermisos();

	echo $twig->render("gestion_administracion.html", [
		"titulo_pagina" => "Administración",
		"usuario" => $infoUsuario,
		"lista_usuarios" => $listaUsuarios,
		"lista_permisos" => $listaPermisos
	]);
