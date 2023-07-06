<?php

class Comentario
{
	public string $autor;
	public int $idCientifico;
	public string $comentario;
	public string $fecha;
	public bool $editado;
	public ?string $cientifico = null;

	public function SetFromSQL($row)
	{
		$this->autor = $row['AUTOR'];
		$this->idCientifico = $row['ID_CIENTIFICO'];
		$this->comentario = $row['COMENTARIO'];
		$this->fecha = FormatSQLDate('d/m/Y H:i:s', $row['FECHA_COMENTARIO']);
		$this->editado = $row['EDITADO'] ?? 0;
	}
}