<?php
	require_once "/usr/local/lib/php/vendor/autoload.php";
	require_once "../model/DBAccess.php";
	require_once "../model/Cientifico.php";
	require_once "../model/Utils.php";
	require_once "./error.php";

	$loader = new \Twig\Loader\FilesystemLoader("../templates");
	$twig = new \Twig\Environment($loader);

	session_start();

	// Comprobar los permisos del usuario
	$db = new DBAccess();

	if (!isset($_SESSION['usuario']) ||
		!$db->Auth_CheckPermissionList($_SESSION['usuario'], ["Gestor", "Superusuario"]))
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

				if (!Form_IsStringFieldSet($_GET['busqueda']) || !Form_IsStringFieldSet($_GET['tipo_busqueda']))
				{
					header("Location: /gestion/cientificos");
					exit();
				}
				else
				{
					if ($_GET['tipo_busqueda'] === "nombre")
						$resultados_busqueda = $db->BuscarCientificosNombreApellidos($_GET['busqueda'], true);
					else if ($_GET['tipo_busqueda'] === "biografia")
						$resultados_busqueda = $db->BuscarCientificosBiografia($_GET['busqueda'], true);
				}

				echo $twig->render("gestion_cientificos.html", [
					"titulo_pagina" => "Gestión Científicos",
					"usuario" => $infoUsuario,
					"cientificos" => $resultados_busqueda,
					"redireccion" => "/gestion/cientificos"
				]);
			}
		}
		else if ($accion === "publicar")
		{
			if ($_SERVER['REQUEST_METHOD'] === 'GET')
			{
				$cientifico = new Cientifico();
				$cientifico->id = $_GET['id'];
				$cientifico->publicado = $_GET['estado'];

				$db->ActualizarCientificoPublicado($cientifico);
				header("Location: " . $redireccion);
				exit();
			}
		}
		else if ($accion === "editar")
		{
			// Es el submit del formulario de edición
			if ($_SERVER['REQUEST_METHOD'] === 'POST')
			{
				// Aplicar la edición de los datos principales
				$cientifico = new Cientifico();

				$cientifico->id = $_GET['id'];
				$cientifico->nombre = $_POST['nombre'];
				$cientifico->apellidos = $_POST['apellidos'];
				$cientifico->fecha_nacimiento = $_POST['fecha_nacimiento'];
				$cientifico->fecha_muerte = $_POST['fecha_muerte'];
				$cientifico->pais_origen = $_POST['pais_origen'];
				$cientifico->lugar_nacimiento = $_POST['lugar_nacimiento'];
				$cientifico->biografia = $_POST['biografia'];

				$db->ActualizarInformacionCientifico($cientifico);

				$hashtags_nuevos = [];

				// Actualizar los hashtags
				if (isset($_POST['hashtags']))
					$hashtags_nuevos = array_values(preg_split("/[\s,]+/", $_POST['hashtags']));

				$db->ActualizarHashtagsCientifico($cientifico, $hashtags_nuevos, $_POST['hashtags_eliminados'] ?? []);

				// Actualizar las imágenes
				$db->ActualizarImagenesCientifico($cientifico,
					isset($_FILES['imagenes_nuevas']) ? $_FILES['imagenes_nuevas'] : null,
					$_POST['imagenes_eliminadas'] ?? []);

				header("Location: " . $redireccion);
				exit();
			}

			// TODO Obtener la información del científico
			$cientifico = $db->GetCientificoCompleto($_GET['id']);

			// Si no existe el científico hacemos la redirección
			if (is_null($cientifico))
			{
				header("Location: " . $redireccion);
				exit();
			}

			$imagenes = $cientifico->GetGaleria();

			// Pasar la redirección como parámetro
			echo $twig->render("editar_cientifico.html", [
				"titulo_pagina" => "Editando Científico",
				"usuario" => $infoUsuario,
				"cientifico" => $cientifico,
				"imagenes" => $imagenes,
				"redireccion" => $redireccion
			]);
		}
		else if ($accion == "crear")
		{
			// Es el submit del formulario de edición
			if ($_SERVER['REQUEST_METHOD'] === 'POST')
			{
				$cientifico = new Cientifico();

				$cientifico->nombre = $_POST['nombre'];

				if (Form_IsStringFieldSet($_POST['apellidos']))
					$cientifico->apellidos = $_POST['apellidos'];
				else
					$cientifico->apellidos = "";

				if (Form_IsStringFieldSet($_POST['fecha_nacimiento']))
					$cientifico->fecha_nacimiento = $_POST['fecha_nacimiento'];
				else
					$cientifico->fecha_nacimiento = null;

				if (Form_IsStringFieldSet($_POST['fecha_muerte']))
					$cientifico->fecha_muerte = $_POST['fecha_muerte'];
				else
					$cientifico->fecha_muerte = null;

				if (Form_IsStringFieldSet($_POST['pais_origen']))
					$cientifico->pais_origen = $_POST['pais_origen'];
				else
					$cientifico->pais_origen = null;

				if (Form_IsStringFieldSet($_POST['lugar_nacimiento']))
					$cientifico->lugar_nacimiento = $_POST['lugar_nacimiento'];
				else
					$cientifico->lugar_nacimiento = null;

				if (Form_IsStringFieldSet($_POST['biografia']))
					$cientifico->biografia = $_POST['biografia'];
				else
					$cientifico->biografia = null;

				$db->CrearCientifico($cientifico);

				$hashtags_nuevos = [];

				// Actualizar los hashtags (Añadir)
				if (isset($_POST['hashtags']))
					$hashtags_nuevos = array_values(preg_split("/[\s,]+/", $_POST['hashtags']));

				$db->ActualizarHashtagsCientifico($cientifico, $hashtags_nuevos, []);

				// Actualizar las imágenes (Añadir)
				$db->ActualizarImagenesCientifico($cientifico,
					isset($_FILES['imagenes_nuevas']) ? $_FILES['imagenes_nuevas'] : null, []);

				header("Location: " . $redireccion);
				exit();
			}

			// Pasar la redirección como parámetro
			echo $twig->render("crear_cientifico.html", [
				"titulo_pagina" => "Creando Científico",
				"usuario" => $infoUsuario,
				"redireccion" => $redireccion
			]);
		}
		else if ($accion === "eliminar")
		{
			// Confirmación de eliminación
			if ($_SERVER['REQUEST_METHOD'] === 'POST')
			{
				if ($_POST['confirmacion'])
					$db->EliminarCientifico($_GET['id']);

				header("Location: " . $redireccion);
				exit();
			}

			$cientifico = $db->GetCientificoCompleto($_GET['id']);

			// Mostrar la página de confirmación
			echo $twig->render("eliminar_cientifico.html", [
				"titulo_pagina" => "Eliminando Científico",
				"usuario" => $infoUsuario,
				"cientifico" => $cientifico,
				"redireccion" => $redireccion
			]);

			// Hacer el borrado
			/*$db->EliminarCientifico($_GET['id']);

			// Preparar la redirección
			header("Location: " . $redireccion);*/
		}

		exit();
	}

	$cientificos = $db->GetTarjetasCientificos();

	echo $twig->render("gestion_cientificos.html", [
		"titulo_pagina" => "Gestión Científicos",
		"usuario" => $infoUsuario,
		"cientificos" => $cientificos,
		"redireccion" => "/gestion/cientificos"
	]);
