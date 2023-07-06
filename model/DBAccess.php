<?php

require_once('Cientifico.php');
require_once('Usuario.php');
require_once('Comentario.php');
require_once('Utils.php');

class DBAccess
{
	private mysqli $mysqli;

	public function __construct()
	{
		$this->mysqli = new mysqli("database",
			apache_getenv("SIBW_DB_ADMIN_USER"),
			apache_getenv("SIBW_DB_ADMIN_PASS"),
			apache_getenv("SIBW_DB"));
	}

	public function BuscarCientificosNombreApellidos($buscado, $incluirNoVisibles = false)
	{
		$encontrados = [];

		$buscado = "%" . $buscado . "%";

		$query = "SELECT ID,NOMBRE,APELLIDOS,GALERIA_PATH,PUBLICADO FROM CIENTIFICOS 
			WHERE CONCAT(NOMBRE, ' ', APELLIDOS) LIKE ?";

		if (!$incluirNoVisibles)
			$query .= " AND PUBLICADO=1";

		$pstm = $this->mysqli->prepare($query);
		$pstm->bind_param("s", $buscado);
		$pstm->execute();

		$result = $pstm->get_result();

		if (!is_null($result))
		{
			while ($row = $result->fetch_assoc())
			{
				$pathGaleria = $row['GALERIA_PATH'];
				$fileSystemIterator = new FilesystemIterator(JoinPaths([apache_getenv("SIBW_PUBLIC_PATH"), $pathGaleria]));
				$imagenTarjeta = "";

				if (iterator_count($fileSystemIterator) > 0)
				{
					foreach ($fileSystemIterator as $fileInfo)
					{
						if (in_array($fileInfo->getExtension(), Cientifico::$EXTENSIONES_IMG))
						{
							$imagenTarjeta = JoinPaths(["/", $pathGaleria, $fileInfo->getFilename()]);
							break;
						}
					}
				}

				$encontrados[] = [
					'id' => $row['ID'],
					'nombre' => $row['NOMBRE'],
					'apellidos' => $row['APELLIDOS'],
					'imagen' => $imagenTarjeta,
					'publicado' => $row['PUBLICADO']
				];
			}
		}

		return $encontrados;
	}

	public function BuscarCientificosBiografia($biografia, $incluirNoVisibles = false)
	{
		$encontrados = [];

		$buscado = "%" . $biografia . "%";

		$query = "SELECT ID,NOMBRE,APELLIDOS,GALERIA_PATH,PUBLICADO FROM CIENTIFICOS 
			WHERE BIOGRAFIA LIKE ?";

		if (!$incluirNoVisibles)
			$query .= " AND PUBLICADO=1";

		$pstm = $this->mysqli->prepare($query);
		$pstm->bind_param("s", $buscado);
		$pstm->execute();

		$result = $pstm->get_result();

		if (!is_null($result))
		{
			while ($row = $result->fetch_assoc())
			{
				$pathGaleria = $row['GALERIA_PATH'];
				$fileSystemIterator = new FilesystemIterator(JoinPaths([apache_getenv("SIBW_PUBLIC_PATH"), $pathGaleria]));
				$imagenTarjeta = "";

				if (iterator_count($fileSystemIterator) > 0)
				{
					foreach ($fileSystemIterator as $fileInfo)
					{
						if (in_array($fileInfo->getExtension(), Cientifico::$EXTENSIONES_IMG))
						{
							$imagenTarjeta = JoinPaths(["/", $pathGaleria, $fileInfo->getFilename()]);
							break;
						}
					}
				}

				$encontrados[] = [
					'id' => $row['ID'],
					'nombre' => $row['NOMBRE'],
					'apellidos' => $row['APELLIDOS'],
					'imagen' => $imagenTarjeta,
					'publicado' => $row['PUBLICADO']
				];
			}
		}

		return $encontrados;
	}

	public function BuscarComentariosUsuario($buscado)
	{
		$encontrados = [];

		$buscado = "%" . $buscado . "%";

		$pstm = $this->mysqli->prepare("SELECT * FROM COMENTARIOS WHERE AUTOR LIKE ?");
		$pstm->bind_param("s", $buscado);
		$pstm->execute();

		$result = $pstm->get_result();

		if (!is_null($result))
			while ($row = $result->fetch_assoc())
			{
				$comentario = new Comentario();
				$comentario->SetFromSQL($row);

				$encontrados[] = $comentario;
			}

		return $encontrados;
	}

	public function GetTarjetasCientificos()
	{
		$cientificos = array();
		$result = $this->mysqli->query("SELECT ID,NOMBRE,APELLIDOS,GALERIA_PATH,PUBLICADO FROM CIENTIFICOS");

		// Procesamos los científicos y registramos su información
		if (!is_null($result))
		{
			while ($row = $result->fetch_assoc())
			{
				$pathGaleria = $row['GALERIA_PATH'];
				$fileSystemIterator = new FilesystemIterator(JoinPaths([apache_getenv("SIBW_PUBLIC_PATH"), $pathGaleria]));
				$imagenTarjeta = "";

				if (iterator_count($fileSystemIterator) > 0)
				{
					foreach ($fileSystemIterator as $fileInfo)
					{
						if (in_array($fileInfo->getExtension(), Cientifico::$EXTENSIONES_IMG))
						{
							$imagenTarjeta = JoinPaths(["/", $pathGaleria, $fileInfo->getFilename()]);
							break;
						}
					}
				}

				$cientificos[] = [
					'id' => $row['ID'],
					'nombre' => $row['NOMBRE'],
					'apellidos' => $row['APELLIDOS'],
					'publicado' => $row['PUBLICADO'],
					'imagen' => $imagenTarjeta
				];
			}
		}

		return $cientificos;
	}

	public function GetCientificoCompleto($id)
	{
		$cientifico = null;

		$pstm = $this->mysqli->prepare("SELECT * FROM CIENTIFICOS WHERE ID=?");
		$pstm->bind_param("i", $id);
		$pstm->execute();

		$result = $pstm->get_result();

		if (!is_null($result) && $result->num_rows > 0)
		{
			$row = $result->fetch_assoc();
			$cientifico = new Cientifico();

			$cientifico->id = $row['ID'];
			$cientifico->nombre = $row['NOMBRE'];
			$cientifico->apellidos = $row['APELLIDOS'];

			if (!is_null($row['FECHA_NACIMIENTO']))
				$cientifico->fecha_nacimiento = FormatSQLDate('d/m/Y', $row['FECHA_NACIMIENTO']);
			else
				$cientifico->fecha_nacimiento = null;

			if (!is_null($row['FECHA_MUERTE']))
				$cientifico->fecha_muerte = FormatSQLDate('d/m/Y', $row['FECHA_MUERTE']);
			else
				$cientifico->fecha_muerte = null;

			$cientifico->lugar_nacimiento = $row['LUGAR_NACIMIENTO'] ?? null;
			$cientifico->pais_origen = $row['PAIS_ORIGEN'] ?? null;
			$cientifico->biografia = $row['BIOGRAFIA'];
			$cientifico->galeria_path = $row['GALERIA_PATH'];
			$cientifico->publicado = $row['PUBLICADO'];

			// Obtener los hashtags
			$cientifico->hashtags = $this->GetHashtagsCientifico($id);
		}

		return $cientifico;
	}

	public function GetGaleriaPathCientifico($id)
	{
		$path = null;

		$pstm = $this->mysqli->prepare("SELECT GALERIA_PATH FROM CIENTIFICOS WHERE ID=?");
		$pstm->bind_param("i", $id);
		$pstm->execute();

		$result = $pstm->get_result();

		if (!is_null($result) && $row = $result->fetch_assoc())
			$path = $row['GALERIA_PATH'];

		return $path;
	}

	public function GetHashtagsCientifico($id) : string
	{
		$hashtags = "";

		$pstm = $this->mysqli->prepare("SELECT HASHTAG FROM HASHTAGS_CIENTIFICO WHERE ID_CIENTIFICO=?");
		$pstm->bind_param("i", $id);
		$pstm->execute();

		$result = $pstm->get_result();

		if (!is_null($result))
			while ($row = $result->fetch_assoc())
			{
				$hashtags .= ucfirst(strtolower($row['HASHTAG'])) . ",";
			}

		if ($hashtags !== "")
			$hashtags = rtrim($hashtags, ",");

		return $hashtags;
	}

	// TODO: Iniciar una transacción hasta haber creado el directorio de galería
	public function CrearCientifico($cientifico)
	{
		$fechaNacSQL = null;
		$fechaMueSQL = null;

		// Hacer la inserción de los datos básicos
		if (Form_IsStringFieldSet($cientifico->fecha_nacimiento))
			$fechaNacSQL = date('Y-m-d', strtotime(str_replace('/', '-', $cientifico->fecha_nacimiento)));

		if (Form_IsStringFieldSet($cientifico->fecha_muerte))
			$fechaMueSQL = date('Y-m-d', strtotime(str_replace('/', '-', $cientifico->fecha_muerte)));

		// Hacer un update de la información básica
		$pstm = $this->mysqli->prepare("INSERT INTO CIENTIFICOS (NOMBRE, APELLIDOS, FECHA_NACIMIENTO,
                         FECHA_MUERTE, LUGAR_NACIMIENTO, PAIS_ORIGEN, BIOGRAFIA) VALUES(?, ?, ?, ?, ?, ?, ?)");
		$pstm->bind_param("sssssss", $cientifico->nombre, $cientifico->apellidos,
			$fechaNacSQL, $fechaMueSQL, $cientifico->lugar_nacimiento, $cientifico->pais_origen,
			$cientifico->biografia);

		if (!$pstm->execute())
			return -1;

		// Coger el id y crear el directorio de galería
		$cientifico->id = $pstm->insert_id;

		$rel_path = JoinPaths(["resources/cientificos", $cientifico->id]);

		if (!mkdir(JoinPaths([apache_getenv("SIBW_PUBLIC_PATH"), $rel_path])))
			return -1;

		$pstm = $this->mysqli->prepare("UPDATE CIENTIFICOS SET GALERIA_PATH=? WHERE ID=?");
		$pstm->bind_param("si", $rel_path, $cientifico->id);

		if (!$pstm->execute())
			return -1;

		$cientifico->galeria_path = $rel_path;
		return $cientifico->id;
	}

	public function EliminarCientifico($id)
	{
		$path_galeria = $this->GetGaleriaPathCientifico($id);

		$TABLAS_EXTERNAS = ["REDES_CIENTIFICO", "HASHTAGS_CIENTIFICO", "COMENTARIOS"];

		foreach ($TABLAS_EXTERNAS as $tabla)
		{
			$pstm = $this->mysqli->prepare("DELETE FROM " . $tabla . " WHERE ID_CIENTIFICO=?");
			$pstm->bind_param("i", $id);
			$pstm->execute();
		}

		$pstm = $this->mysqli->prepare("DELETE FROM CIENTIFICOS WHERE ID=?");
		$pstm->bind_param("i", $id);

		$path_galeria_abs = JoinPaths([apache_getenv("SIBW_PUBLIC_PATH"), $path_galeria]);

		array_map('unlink', glob("$path_galeria_abs/*.*"));
		rmdir($path_galeria_abs);

		return $pstm->execute();
	}

	public function ActualizarCientificoPublicado($cientifico)
	{
		$pstm = $this->mysqli->prepare("UPDATE CIENTIFICOS SET PUBLICADO=? WHERE ID=?");
		$pstm->bind_param("ii", $cientifico->publicado, $cientifico->id);

		return $pstm->execute();
	}

	public function ActualizarInformacionCientifico($cientifico)
	{
		$fechaNacSQL = null;
		$fechaMueSQL = null;

		// Hacer la inserción de los datos básicos
		if (Form_IsStringFieldSet($cientifico->fecha_nacimiento))
			$fechaNacSQL = date('Y-m-d', strtotime(str_replace('/', '-', $cientifico->fecha_nacimiento)));

		if (Form_IsStringFieldSet($cientifico->fecha_muerte))
			$fechaMueSQL = date('Y-m-d', strtotime(str_replace('/', '-', $cientifico->fecha_muerte)));


		// Hacer un update de la información básica
		$pstm = $this->mysqli->prepare("UPDATE CIENTIFICOS SET NOMBRE=?, 
		   APELLIDOS=?, FECHA_NACIMIENTO=?, FECHA_MUERTE=?, LUGAR_NACIMIENTO=?,
		   PAIS_ORIGEN=?, BIOGRAFIA=? WHERE ID=?");
		$pstm->bind_param("sssssssi", $cientifico->nombre, $cientifico->apellidos,
			$fechaNacSQL, $fechaMueSQL, $cientifico->lugar_nacimiento, $cientifico->pais_origen,
			$cientifico->biografia, $cientifico->id);

		return $pstm->execute();
	}

	public function ActualizarHashtagsCientifico($cientifico, $hashtags_nuevos = [], $hashtags_eliminados = [])
	{
		//
		// Eliminar hashtags
		//

		$pstm = $this->mysqli->prepare("DELETE FROM HASHTAGS_CIENTIFICO WHERE HASHTAG=? AND ID_CIENTIFICO=?");

		foreach($hashtags_eliminados as $hashtag)
		{
			$hashtag = strtoupper($hashtag);

			$pstm->bind_param("si", $hashtag, $cientifico->id);
			$pstm->execute();
		}

		//
		// Añadir los hashtags nuevos a la tabla
		//

		$pstm = $this->mysqli->prepare("INSERT INTO HASHTAGS_CIENTIFICO (HASHTAG, ID_CIENTIFICO) VALUES(?, ?)");

		foreach($hashtags_nuevos as $hashtag)
		{
			if ($hashtag !== "")
			{
				$hashtag = strtoupper($hashtag);
				$pstm->bind_param("si", $hashtag, $cientifico->id);
				$pstm->execute();
			}
		}
	}

	public function ActualizarImagenesCientifico($cientifico, $subidas = [], $imagenes_eliminadas = [])
	{
		$errores_elim = [];
		$errores_sub = [];

		// Obtener el path de la galería del científico
		$path_galeria = $this->GetGaleriaPathCientifico($cientifico->id);
		$abs_path_galeria = JoinPaths([apache_getenv("SIBW_PUBLIC_PATH"), $path_galeria]);

		foreach ($imagenes_eliminadas as $imagen)
		{
			$abs_path = JoinPaths([apache_getenv("SIBW_PUBLIC_PATH"), $imagen]);

			if (!file_exists($abs_path) ||
				!in_array(pathinfo($abs_path, PATHINFO_EXTENSION), Cientifico::$EXTENSIONES_IMG)
				|| !unlink($abs_path))
			{
				$errores_elim[pathinfo($abs_path, PATHINFO_FILENAME)] = "No se pudo eliminar " . $imagen;
			}
		}

		//
		// Procesar las imágenes subidas
		//

		if (!empty($subidas))
		{
			$numArchivosDirectorio = count(scandir($abs_path_galeria)) - 2 + 1;

			for ($i = 0; $i < count($subidas['name']); $i++) {
				$nombreArchivo = $subidas['name'][$i];
				$tipoArchivo = $subidas['type'][$i];
				$rutaTemporal = $subidas['tmp_name'][$i];
				$errorArchivo = $subidas['error'][$i];
				$tamArchivo = $subidas['size'][$i];

				//
				// Comprobar la información del archivo
				//
				$errores_sub[$nombreArchivo] = [];

				if ($errorArchivo !== UPLOAD_ERR_OK)
					$errores_sub[$nombreArchivo][] = "Error en la subida de la imagen " . $nombreArchivo;

				if ($tamArchivo > 5123456)
					$errores_sub[$nombreArchivo][] = "La imagen " . $nombreArchivo . " pesa demasiado. El límite son 5MB";

				if (strlen($nombreArchivo) > 150)
					$errores_sub[$nombreArchivo][] = "La imagen " . $nombreArchivo . " tiene un nombre demasiado largo";

				$extension_imagen = pathinfo($nombreArchivo, PATHINFO_EXTENSION);

				if (!in_array($extension_imagen, Cientifico::$EXTENSIONES_IMG))
					$errores_sub[$nombreArchivo][] = "La imagen no tiene un formato válido: " . print_r(Cientifico::$EXTENSIONES_IMG, true);

				if (empty($errores_sub[$nombreArchivo]))
				{
					// Cambiar el nombre del archivo
					$nombreArchivo = pathinfo($nombreArchivo, PATHINFO_FILENAME) . "_" . $numArchivosDirectorio . "." . $extension_imagen;

					$destino = JoinPaths([$abs_path_galeria, $nombreArchivo]);
					move_uploaded_file($rutaTemporal, $destino);
				}
			}
		}

		return array_merge($errores_elim, $errores_sub);
	}

	public function GetComentarios()
	{
		$comentarios = array();

		$result = $this->mysqli->query("SELECT * FROM COMENTARIOS ORDER BY ID_CIENTIFICO");

		if (!is_null($result))
			while ($row = $result->fetch_assoc())
			{
				$comentario = new Comentario();
				$comentario->SetFromSQL($row);

				$comentarios[] = $comentario;
			}

		return $comentarios;
	}

	public function GetComentariosCientifico($id)
	{
		$comentarios = array();

		$pstm = $this->mysqli->prepare("select AUTOR, ID_CIENTIFICO, COMENTARIO, FECHA_COMENTARIO, EDITADO FROM
                                            	(select * FROM COMENTARIOS WHERE ID_CIENTIFICO=?) C NATURAL JOIN 
												(select USUARIO AS AUTOR from USUARIOS) U");
		$pstm->bind_param("i", $id);
		$pstm->execute();
		$result = $pstm->get_result();

		if (!is_null($result))
		{
			while ($row = $result->fetch_assoc())
			{
				$comentario = new Comentario();

				$comentario->SetFromSQL($row);
				$comentario->idCientifico = $id;

				$comentarios[] = $comentario;
			}
		}

		return $comentarios;
	}

	public function GetComentario($autor, $id_cientifico, $incluir_cientifico = true)
	{
		$comentario = null;

		$pstm = $this->mysqli->prepare("SELECT * FROM COMENTARIOS WHERE AUTOR=? AND ID_CIENTIFICO=?");
		$pstm->bind_param("si", $autor, $id_cientifico);

		if ($pstm->execute())
		{
			$result = $pstm->get_result();

			if (!is_null($result) && $row = $result->fetch_assoc())
			{
				$comentario = new Comentario();
				$comentario->SetFromSQL($row);

				if ($incluir_cientifico)
				{
					$pstm = $this->mysqli->prepare("SELECT CONCAT(NOMBRE, \" \", APELLIDOS) AS NOMBRE_COMPLETO
					FROM CIENTIFICOS WHERE ID=?");
					$pstm->bind_param("i", $id_cientifico);

					if ($pstm->execute() && $result = $pstm->get_result())
						if ($row = $result->fetch_assoc())
							$comentario->cientifico = $row['NOMBRE_COMPLETO'];
				}
			}
		}

		return $comentario;
	}

	public function GetRedesCientifico($id)
	{
		$redes = array();

		$pstm = $this->mysqli->prepare("SELECT NOMBRE, ENLACE FROM REDES_CIENTIFICO WHERE ID_CIENTIFICO=? ORDER BY NUM");
		$pstm->bind_param("i", $id);
		$pstm->execute();
		$result = $pstm->get_result();

		if (!is_null($result))
		{
			while ($row = $result->fetch_assoc())
			{
				$redes[] = [
					'nombre' => $row['NOMBRE'],
					'enlace' => $row['ENLACE']
				];
			}
		}

		return $redes;
	}

	public function GetPalabrasProhibidas()
	{
		$palabras = array();

		$result = $this->mysqli->query("SELECT PALABRA FROM BAD_WORDS");

		if (!is_null($result))
			while ($row = $result->fetch_assoc())
				$palabras[] = $row['PALABRA'];

		return $palabras;
	}

	public function HacerComentario($usuario, $cientifico_id, $comentario)
	{
		// TODO: Aplicar la expresión regular de no palabras feas aquí

		$pstm = $this->mysqli->prepare("INSERT INTO COMENTARIOS (ID_CIENTIFICO, AUTOR, COMENTARIO, FECHA_COMENTARIO)
			VALUES(?, ?, ?, NOW())");

		if (!$pstm)
			return false;

		$pstm->bind_param("iss", $cientifico_id, $usuario, $comentario);

		return $pstm->execute();
	}

	// NOTE: Ahora mismo el parámetro de moderador se asume siempre como true porque no permitimos que un
	// usuario edite sus comentarios.
	public function EditarComentario($autor, $cientifico_id, $comentario_nuevo, $es_moderador = true)
	{
		$pstm = $this->mysqli->prepare("UPDATE COMENTARIOS SET COMENTARIO=?, EDITADO=?
                WHERE AUTOR=? AND ID_CIENTIFICO=?");

		if (!$pstm)
			return false;

		$pstm->bind_param("sisi", $comentario_nuevo, $es_moderador, $autor, $cientifico_id);

		return $pstm->execute();
	}

	public function EliminarComentario($autor, $cientifico_id)
	{
		$pstm = $this->mysqli->prepare("DELETE FROM COMENTARIOS WHERE ID_CIENTIFICO=? AND AUTOR=?");

		if (!$pstm)
			return false;

		$pstm->bind_param("is", $cientifico_id, $autor);

		return $pstm->execute();
	}

	public function GetListaPermisos()
	{
		$listaPermisos = [];

		$result = $this->mysqli->query("SELECT * FROM PERMISOS");

		if (!is_null($result))
			while ($row = $result->fetch_assoc())
				$listaPermisos[] = ucfirst($row['PERMISO']);

		return $listaPermisos;
	}

	public function GetUsuario($usuario)
	{
		$pstm = $this->mysqli->prepare("SELECT * FROM USUARIOS WHERE USUARIO=?");
		$pstm->bind_param("s", $usuario);
		$pstm->execute();

		$result = $pstm->get_result();

		if (!is_null($result) && $row = $result->fetch_assoc())
		{
			$infoUsuario = new Usuario();

			$infoUsuario->usuario = $row['USUARIO'];
			$infoUsuario->nombre = $row['NOMBRE'];
			$infoUsuario->apellidos = $row['APELLIDOS'];
			$infoUsuario->email = $row['EMAIL'];
			$infoUsuario->password = $row['PASSWORD'];
			$infoUsuario->permisos = $row['PERMISOS'];

			return $infoUsuario;
		}

		return null;
	}

	public function GetListaUsuarioPermiso()
	{
		$result = $this->mysqli->query("SELECT USUARIO, PERMISOS FROM USUARIOS ORDER BY PERMISOS");
		$listaUsuarios = [];

		if (!is_null($result))
			while ($row = $result->fetch_assoc())
			{
				$listaUsuarios[] = [
					"usuario" => $row['USUARIO'],
					"permisos" => $row['PERMISOS']
				];
			}

		return $listaUsuarios;
	}

	public function ContarUsuariosConPermiso($permiso)
	{
		$permiso = ucfirst($permiso);

		$pstm = $this->mysqli->prepare("SELECT COUNT(*) AS USUARIOS FROM USUARIOS WHERE PERMISOS=?");
		$pstm->bind_param("s", $permiso);
		$pstm->execute();

		$result = $pstm->get_result();

		if (!is_null($result) && $row = $result->fetch_assoc())
			return $row['USUARIOS'];

		return 0;
	}

	public function CambiarPermisos($usuario, $permisos)
	{
		$permisos = ucfirst($permisos);

		// Si este usuario era superusuario, va a dejar de serlo y es el último
		// TODO: Idealmente esto se haría en un trigger de la bd
		if ($this->Auth_CheckPermission($usuario, "Superusuario") && $permisos !== "Superusuario"
			&& $this->ContarUsuariosConPermiso("Superusuario") === 1)
		{
			return false;
		}

		$pstm = $this->mysqli->prepare("UPDATE USUARIOS SET PERMISOS=? WHERE USUARIO=?");
		$pstm->bind_param("ss", $permisos, $usuario);

		return $pstm->execute();
	}

	// PRE: Lista permisos está en Capital
	public function Auth_CheckPermissionList($usuario, $lista_permisos)
	{
		if ($usuario === null)
			return false;

		$pstm = $this->mysqli->prepare("SELECT PERMISOS FROM USUARIOS WHERE USUARIO=?");
		$pstm->bind_param("s", $usuario);
		$pstm->execute();

		$result = $pstm->get_result();

		if (!is_null($result) && $row = $result->fetch_assoc())
			return in_array(ucfirst($row['PERMISOS']), $lista_permisos);

		return false;
	}

	public function Auth_CheckPermission($usuario, $permisos)
	{
		if (is_null($usuario))
			return false;

		$pstm = $this->mysqli->prepare("SELECT PERMISOS FROM USUARIOS WHERE USUARIO=?");
		$pstm->bind_param("s", $usuario);
		$pstm->execute();

		$result = $pstm->get_result();

		if (!is_null($result) && $row = $result->fetch_assoc())
			return ucfirst($permisos) === ucfirst($row['PERMISOS']);

		return false;
	}

	public function Auth_CheckLogin($usuario, $password)
	{
		$pstm = $this->mysqli->prepare("SELECT PASSWORD FROM USUARIOS WHERE USUARIO=?");
		$pstm->bind_param("s", $usuario);
		$pstm->execute();

		$result = $pstm->get_result();

		if (is_null($result))
			return "Error en la validación";

		if ($row = $result->fetch_assoc())
		{
			if (password_verify($password, $row['PASSWORD']))
				return "";

			return "Contraseña incorrecta";
		}

		return "El usuario no existe / no es válido";
	}

	public function Auth_UpdateUser($usuario, $nuevoNombre, $nuevosApellidos, $nuevoEmail, $nuevaPassword)
	{
		$pstm = $this->mysqli->prepare("UPDATE USUARIOS SET NOMBRE=?, APELLIDOS=?, EMAIL=?, PASSWORD=?
                WHERE USUARIO=?");

		if (!$pstm)
			return false;

		$pstm->bind_param("sssss", $nuevoNombre, $nuevosApellidos, $nuevoEmail, $nuevaPassword, $usuario);

		return $pstm->execute();
	}

	public function Auth_SignUp($nombre, $apellidos, $email, $usuario, $password)
	{
		$pstm = $this->mysqli->prepare("INSERT INTO USUARIOS (NOMBRE, APELLIDOS, EMAIL, USUARIO, PASSWORD, PERMISOS)
			VALUES(?, ?, ?, ?, ?, \"Registrado\")");
		$pstm->bind_param("sssss", $nombre, $apellidos, $email, $usuario, $password);

		if (!$pstm->execute())
		{
			//echo var_dump($pstm->error_list);
			return "El usuario y/o correo ya existen";
		}

		return "";
	}
}
