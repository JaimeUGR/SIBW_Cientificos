//
// Inicialización
//

const menuComentarios = document.getElementById("menu_comentarios")
const textoComentario = document.getElementById("campo_comentario")
const botonFormulario = document.getElementById("campo_boton")

// Inicializar con display None el menú
menuComentarios.style.display = "none"

//
// Gestión palabras prohibidas
//
function cargarPalabrasProhibidas()
{
	fetch('/api/getPalabrasProhibidas')
		.then(response => response.json())
		.then(data => {
			for(const palabra of data.palabras)
				palabrasProhibidas.push(palabra)

			console.log("Palabras Cargadas: "  + palabrasProhibidas)
		})
		.catch(error => console.error(error))
}

const palabrasProhibidas = []

cargarPalabrasProhibidas()


//
// Funciones
//
function mostrarMenuComentarios()
{
	if (menuComentarios.style.display === "none")
	{
		menuComentarios.style.display = "flex"
	}
	else
	{
		menuComentarios.style.display = "none"
	}

	console.log("Click Mostrar/Esconder MenúComentarios")
}

function compruebaPalabras(elemento)
{
	palabrasProhibidas.forEach((palabra) => {
		const regExp = new RegExp(palabra, "gi")
		elemento.value = elemento.value.replace(regExp, "*".repeat(palabra.length))
	})
}

function crearComentario(autor, fecha, texto)
{
	let comentario = document.createElement("div")
	comentario.setAttribute("class", "comentario")

	let comAutor = document.createElement("span")
	let comFecha = document.createElement("span")
	let comTexto = document.createElement("p")

	comAutor.setAttribute("class", "comentario-autor")
	comFecha.setAttribute("class", "comentario-fecha")
	comTexto.setAttribute("class", "comentario-texto")

	comAutor.textContent = autor
	comFecha.textContent = fecha
	comTexto.textContent = texto

	comentario.appendChild(comAutor)
	comentario.appendChild(comFecha)
	comentario.appendChild(comTexto)

	document.getElementById("cajon_comentarios").appendChild(comentario)
}

function enviarComentario()
{
	console.log("Intentando enviar comentario")

	// Coger la información del formulario
	let formNombre = document.getElementById("campo_nombre")
	let formEmail = document.getElementById("campo_email")
	let formText = document.getElementById("campo_comentario")

	// Las comprobaciones de contenido no son necesarias si utilizamos el
	// botón tipo submit con required. Sin embargo, no es "correcto"
	// ya que no estamos enviando nada a una base de datos
	if (formNombre.value.length === 0)
	{
		window.alert("Debes añadir un nombre")
		return
	}

	if (formEmail.value.length === 0)
	{
		window.alert("Debes añadir un email")
		return
	}

	if (!/^([a-zA-Z0-9]+@[a-zA-Z]+\.[a-zA-Z]+)$/.test(formEmail.value))
	{
		window.alert("El email no parece válido")
		return
	}

	if (formText.value.length === 0)
	{
		window.alert("Debes añadir un comentario")
		return
	}

	let now = new Date()

	// Comprobar los campos
	crearComentario(formNombre.value, now.toLocaleDateString() + " " + now.toLocaleTimeString(), formText.value)

	formNombre.value = ""
	formEmail.value = ""
	formText.value = ""
}

//
// "Main"
//

// Por ahora ambos botones hacen lo mismo
try
{
	document.getElementById("boton_com").onclick = mostrarMenuComentarios
	document.getElementById("boton_cierre").onclick = mostrarMenuComentarios

	textoComentario.oninput = () => {
		compruebaPalabras(textoComentario)
	};

// Nota: si hubiera base de datos sería correcto utilizar submit
	//botonFormulario.onclick = enviarComentario

// Hacer una comprobación inicial
	compruebaPalabras(textoComentario)
}
catch (er)
{
}
