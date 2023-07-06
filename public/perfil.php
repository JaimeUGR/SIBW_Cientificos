<?php
	require_once "/usr/local/lib/php/vendor/autoload.php";
	require_once('../model/DBAccess.php');
	require_once('../model/Usuario.php');
	require_once('../model/Utils.php');

	$loader = new \Twig\Loader\FilesystemLoader("../templates");
	$twig = new \Twig\Environment($loader);

	//
	// Gestión Sesión
	//

	session_start();

	if (!isset($_SESSION['usuario']))
	{
		// Como alternativa se podría enviar a una página de error
		header("Location: /");
		exit();
	}

	// Obtener los datos del usuario
	$db = new DBAccess();
	$infoUsuario = $db->GetUsuario($_SESSION['usuario']);

	if (is_null($infoUsuario))
	{
		GeneralErrorPage("El usuario no existe / no tienes acceso", 404);
		exit();
	}

	// Formulario de edición
	$errores = [];

	if ($_SERVER['REQUEST_METHOD'] === 'POST')
	{
		$password = $_POST['password'];

		// Comprobar la contraseña antes de hacer cambios
		if (Form_IsStringFieldSet($password) && password_verify($password, $infoUsuario->password))
		{
			// Procesar los cambios
			$nuevoNombre = $_POST['nuevoNombre'];
			$nuevosApellidos = $_POST['nuevosApellidos'];
			$nuevoEmail = $_POST['nuevoEmail'];
			$nuevaPassword = $_POST['nuevaPassword'];

			if (!Form_IsStringFieldSet($nuevoNombre))
				$nuevoNombre = $infoUsuario->nombre;

			if (!Form_IsStringFieldSet($nuevosApellidos))
				$nuevosApellidos = $infoUsuario->apellidos;

			if (!Form_IsStringFieldSet($nuevoEmail))
				$nuevoEmail = $infoUsuario->email;

			if (!Form_IsStringFieldSet($nuevaPassword))
				$nuevaPassword = $infoUsuario->password;
			else
				$nuevaPassword = password_hash($nuevaPassword, PASSWORD_DEFAULT);

			// Petición a la BD para actualizar los datos de este usuario
			$result = $db->Auth_UpdateUser($infoUsuario->usuario, $nuevoNombre, $nuevosApellidos, $nuevoEmail, $nuevaPassword);

			if (!$result)
			{
				require_once("error.php");
				GeneralErrorPage("Error al actualizar los datos", 400);
				exit();
			}

			header("Location: /perfil");
			exit();
		}

		// La contraseña no era correcta / no estaba
		$errores[] = "Debes proporcionar tu contraseña actual para hacer cambios";
	}

	//
	// Render
	//

	echo $twig->render("perfil.html", [
		"titulo_pagina" => "Tu Perfil",
		"usuario" => $infoUsuario->usuario,
		"nombre" => $infoUsuario->nombre,
		"apellidos" => $infoUsuario->apellidos,
		"email" => $infoUsuario->email,
		"permisos" => $infoUsuario->permisos,
		"errores" => $errores
	]);
