<?php
	require_once "/usr/local/lib/php/vendor/autoload.php";
	require_once "../model/DBAccess.php";
	require_once "../model/Comentario.php";
	require_once "./error.php";

	$loader = new \Twig\Loader\FilesystemLoader("../templates");
	$twig = new \Twig\Environment($loader);

	session_start();

	$db = new DBAccess();

	// NOTE: Esto no permite que un usuario pueda editar su propio comentario.
	// NOTE: Se hace así por diseño, pero se podría cambiar fácilmente.
	if (!isset($_SESSION['usuario']) ||
		!$db->Auth_CheckPermissionList($_SESSION['usuario'], ["Moderador", "Superusuario"]))
	{
		GeneralErrorPage("No existe la página buscada", 404);
		exit();
	}

	$infoUsuario = $db->GetUsuario($_SESSION['usuario']);

	// Comprobar si es una operación de gestión
	if (isset($_GET['accion']))
	{
		$accion = $_GET['accion'];
		$redireccion = $_GET['redireccion'] ?? "/";

		if ($redireccion === "")
			$redireccion = "/";

		if ($accion === "Buscar")
		{
			// Comprobar los parámetros de búsqueda
			if ($_SERVER['REQUEST_METHOD'] === 'GET')
			{
				$resultados_busqueda = [];

				if (!Form_IsStringFieldSet($_GET['usuario']))
				{
					header("Location: /gestion/comentarios");
					exit();
				}
				else
					$resultados_busqueda = $db->BuscarComentariosUsuario($_GET['usuario']);

				echo $twig->render("gestion_comentarios.html", [
					"titulo_pagina" => "Gestión Comentarios",
					"usuario" => $infoUsuario,
					"comentarios" => $resultados_busqueda,
					"redireccion" => "/gestion/comentarios"
				]);
			}
		}
		else if ($accion === "editar")
		{
			// Es el submit del formulario de edición
			if ($_SERVER['REQUEST_METHOD'] === 'POST')
			{
				// Hacer la edición
				$db->EditarComentario($_GET['autor'], $_GET['id'], $_POST['nuevo_comentario']);

				header("Location: " . $redireccion);
				exit();
			}

			// Obtener la información del comentario
			$comentario = $db->GetComentario($_GET['autor'], $_GET['id']);

			// Si no existe el comentario hacemos la redirección
			if (is_null($comentario))
			{
				header("Location: " . $redireccion);
				exit();
			}

			// Pasar la redirección como parámetro
			echo $twig->render("editar_comentario.html", [
				"titulo_pagina" => "Editar Comentario",
				"usuario" => $infoUsuario,
				"redireccion" => $redireccion,
				"comentario" => $comentario
			]);
		}
		else if ($accion === "eliminar")
		{
			// Hacer el borrado
			$db->EliminarComentario($_GET['autor'], $_GET['id']);

			// Preparar la redirección
			header("Location: " . $redireccion);
		}

		exit();
	}

	// Mostrar el listado de comentarios
	$comentarios = $db->GetComentarios();

	// Agrupar los comentarios por científico


	echo $twig->render("gestion_comentarios.html", [
		"titulo_pagina" => "Gestión Comentarios",
		"usuario" => $infoUsuario,
		"comentarios" => $comentarios,
		"redireccion" => "/gestion/comentarios"
	]);