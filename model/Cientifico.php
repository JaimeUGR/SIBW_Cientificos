<?php

require_once('Utils.php');

class Cientifico
{
	public static array $EXTENSIONES_IMG = ["png", "jpg", "jpeg"];

	public int $id;
	public string $nombre;
	public ?string $apellidos;
	public ?string $fecha_nacimiento;
	public ?string $fecha_muerte;
	public ?string $lugar_nacimiento;
	public ?string $pais_origen;
	public ?string $biografia;
	public ?string $galeria_path;
	public ?bool $publicado;
	public ?string $hashtags;

	public function GetBiografia() : string
	{
		return $this->biografia ?? "";
	}

	public function GetImagenTarjeta()
	{
		if (is_null($this->galeria_path) or $this->galeria_path === "")
			return null;

		$pathGaleria = $this->galeria_path;
		$fileSystemIterator = new FilesystemIterator(JoinPaths([apache_getenv("SIBW_PUBLIC_PATH"), $pathGaleria]));
		$imagenTarjeta = "";

		if (iterator_count($fileSystemIterator) > 0)
		{
			foreach ($fileSystemIterator as $fileInfo)
			{
				if (in_array($fileInfo->getExtension(), Cientifico::$EXTENSIONES_IMG))
				{
					$imagenTarjeta = JoinPaths([$pathGaleria, $fileInfo->getFilename()]);
					break;
				}
			}
		}

		if ($imagenTarjeta === "")
			return null;

		return JoinPaths(["/", $imagenTarjeta]);
	}

	public function GetGaleria() : array
	{
		$imagenes = array();

		if (is_null($this->galeria_path) or $this->galeria_path === "")
			return $imagenes;

		$relativePath = JoinPaths([$this->galeria_path]);
		$fileSystemIterator = new FilesystemIterator(JoinPaths([apache_getenv("SIBW_PUBLIC_PATH"), $relativePath]));

		foreach ($fileSystemIterator as $fileInfo)
		{
			// Solo se permiten ciertas extensiones de imagen
			if(in_array($fileInfo->getExtension(), Cientifico::$EXTENSIONES_IMG))
			{
				// Convertimos el Path Relativo a absoluto
				$imagenes[] = JoinPaths(["/", $relativePath, $fileInfo->getFilename()]);
			}
		}

		// Para que estén ordenadas según el número / nombre de la foto
		return array_reverse($imagenes);
	}

	public function ToTwig() : array
	{
		return [
			"id" => $this->id,
			"nombre" => $this->nombre,
			"apellidos" => $this->apellidos,
			"fechaNacimiento" => $this->fecha_nacimiento,
			"fechaMuerte" => $this->fecha_muerte,
			"lugarNacimiento" => $this->lugar_nacimiento,
			"paisOrigen" => $this->pais_origen,
			"descripcion" => $this->GetBiografia(),
			"tarjeta" => $this->GetImagenTarjeta(),
			"galeria" => $this->GetGaleria(),
			"publicado" => $this->publicado,
			"hashtags" => $this->hashtags
			];
	}
}