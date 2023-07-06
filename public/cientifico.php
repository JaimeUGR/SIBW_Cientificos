<?php
	require_once "/usr/local/lib/php/vendor/autoload.php";
	require_once "../model/DBAccess.php";

	$loader = new \Twig\Loader\FilesystemLoader("../templates");
	$twig = new \Twig\Environment($loader);

	$cientifico_id = -1;
	$modoImpresion = false;

	if (isset($_GET['id']))
		$cientifico_id = $_GET['id'];

	if (isset($_GET['modo_impresion']))
		$modoImpresion = true;

	$db = new DBAccess();

	//
	// Gestión sesión
	//

	session_start();

	$infoUsuario = null;

	if (isset($_SESSION['usuario']))
	{
		$infoUsuario = $db->GetUsuario($_SESSION['usuario']);

		// Solicitud del formulario de comentarios (por ahora solo hay este)
		if ($_SERVER['REQUEST_METHOD'] === 'POST')
		{
			$comentario = $_POST['comentario'];

			if (Form_IsStringFieldSet($comentario))
			{
				$db->HacerComentario($infoUsuario->usuario, $cientifico_id, $comentario);
			}

			header("Location: /cientificos/" . $cientifico_id);
			exit();
		}
	}

	//

	$cientifico = $db->GetCientificoCompleto($cientifico_id);

	if (is_null($cientifico) || (!$cientifico->publicado && !$db->Auth_CheckPermissionList($infoUsuario->usuario ?? null, ["Gestor", "Superusuario"])))
	{
		require_once "error.php";
		GeneralErrorPage("Científico desconocido", 404);
		exit();
	}

	$nombrePagina = "SIBW_2023 @Jaime - {$cientifico->nombre} {$cientifico->apellidos}";
	$redes = $db->GetRedesCientifico($cientifico->id);
	$comentarios = $db->GetComentariosCientifico($cientifico->id);

	echo $twig->render("cientifico.html", [
		"titulo_pagina" => $nombrePagina,
		"usuario" => $infoUsuario,
		"cientifico" => $cientifico->ToTwig(),
		"redes" => $redes,
		"comentarios" => $comentarios,
		"url_impresion" => "{$_SERVER['REQUEST_URI']}/imprimir",
		"modo_impresion" => $modoImpresion,
		"redireccion" => "/cientificos/" . $cientifico_id
	]);
