<?php
	require_once "/usr/local/lib/php/vendor/autoload.php";
	require_once('../model/DBAccess.php');

	$loader = new \Twig\Loader\FilesystemLoader("../templates");
	$twig = new \Twig\Environment($loader);

	$errores = [];

	if ($_SERVER['REQUEST_METHOD'] === 'POST')
	{
		$usuario = $_POST['usuario'];
		$password = $_POST['password'];

		$db = new DBAccess();

		// TODO: Si falla, añadir los errores para mostrarlos
		$resultado = $db->Auth_CheckLogin($usuario, $password);

		if ($resultado === "")
		{
			session_start();
			$_SESSION['usuario'] = $usuario;

			header("Location: /");
			exit();
		}

		$errores[] = $resultado;
	}

	echo $twig->render("login.html", [
		"titulo_pagina" => "Inicia Sesión",
		"errores" => $errores
	]);
