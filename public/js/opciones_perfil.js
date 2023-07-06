
const botonPerfil = document.getElementById("botonPerfil")
const menuPerfil = document.getElementById("menuPerfil")

// Agregar evento de clic al botón
botonPerfil.addEventListener("click", function(event) {
    event.stopPropagation() // Evitar que el evento se propague
    menuPerfil.style.display = menuPerfil.style.display === "block" ? "none" : "block"
})

// Agregar evento de click al documento
document.addEventListener("click", function(event) {
    let targetElement = event.target

    // Verificar si el clic ocurrió fuera del menú
    if (targetElement !== botonPerfil && !menuPerfil.contains(targetElement)) {
        menuPerfil.style.display = "none"
    }
})
