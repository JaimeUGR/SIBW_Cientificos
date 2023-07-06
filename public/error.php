<?php

function GeneralErrorPage($errorMessage, $errorNumber)
{
	require_once "/usr/local/lib/php/vendor/autoload.php";

	$loader = new \Twig\Loader\FilesystemLoader("../templates");
	$twig = new \Twig\Environment($loader);

	echo $twig->render("error.html", [
		"titulo_pagina" => "Error",
		"errorNumber" => $errorNumber,
		"mensaje" => $errorMessage
	]);
}
