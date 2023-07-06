<?php
	require_once "/usr/local/lib/php/vendor/autoload.php";
	require_once('../model/DBAccess.php');

	$loader = new \Twig\Loader\FilesystemLoader("../templates");
	$twig = new \Twig\Environment($loader);

	$errores = [];

	if ($_SERVER['REQUEST_METHOD'] === 'POST')
	{

		$db = new DBAccess();

		$usuario = $_POST['usuario'] ?? null;
		$password = $_POST['password'] ?? null;
		$email = $_POST['email'] ?? null;
		$nombre = $_POST['nombre'] ?? null;
		$apellidos = $_POST['apellidos'] ?? "";

		if (is_null($usuario) || is_null($password) || is_null($email) || is_null($nombre))
		{
			$errores[] = "Un campo obligatorio es nulo";
		}
		else
		{
			$password = password_hash($password, PASSWORD_DEFAULT);
			$result = $db->Auth_SignUp($nombre, $apellidos, $email, $usuario, $password);

			if ($result === "")
			{
				// Iniciamos sesión y redirigimos
				header("Location: /");
				exit();
			}

			$errores[] = $result;
		}
	}

	echo $twig->render("signup.html", [
		"titulo_pagina" => "Crear Cuenta",
		"errores" => $errores
	]);
