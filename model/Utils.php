<?php
function JoinPaths($paths) : string
{
	return preg_replace('#/+#','/',join('/', $paths));
}

function Form_IsStringFieldSet($field)
{
	return isset($field) && $field !== "";
}

function FormatSQLDate($format, $sql_date) : string
{
	return date($format, strtotime($sql_date));
}
