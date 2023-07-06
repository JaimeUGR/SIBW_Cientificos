<?php

class Usuario
{
	public string $usuario;
	public string $nombre;
	public string $apellidos;
	public string $email;
	public string $password;
	public string $permisos;

	public function ToTwig()
	{
		return [
			"usuario" => $this->usuario,
			"nombre" => $this->nombre,
			"apellidos" => $this->apellidos,
			"email" => $this->email,
			//"password" => "No deberías utilizar este campo",
			"permisos" => $this->permisos
		];
	}
}