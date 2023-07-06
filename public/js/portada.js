
const inputBusqueda = document.getElementById("inputBuscador")
const cientificosSugeridos = document.getElementById("cientificosSugeridos")

let timeoutBusquedaId

inputBusqueda.addEventListener("input", function() {
    let query = inputBusqueda.value.trim()

    cientificosSugeridos.style.display = "block"
    clearTimeout(timeoutBusquedaId) // Reinicia el temporizador antes de hacer la petición

    if (query === "")
    {
        cientificosSugeridos.style.display = "none"
        cientificosSugeridos.innerHTML = ""
        return
    }

    timeoutBusquedaId = setTimeout(function() {
        const xhr = new XMLHttpRequest()

        xhr.open("GET", "/api/buscarCientificos?query=" + encodeURIComponent(query), true)

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200)
            {
                cientificosSugeridos.innerHTML = ""

                const response = JSON.parse(xhr.responseText)
                const regexHighlight = new RegExp(query, 'gi');

                for (let i = 0; i < response.length; i++)
                {
                    const infoCientifico = response[i]

                    let divSugerido = document.createElement("div")
                    divSugerido.setAttribute("class", "cientifico-sugerido")

                    let aCientifico = document.createElement("a")
                    aCientifico.innerHTML = infoCientifico['nombre'] + " " + infoCientifico['apellidos']
                    aCientifico.setAttribute("href", "/cientificos/" + infoCientifico['id'])

                    // Highlight
                    aCientifico.innerHTML = aCientifico.innerHTML.replace(regexHighlight, '<span class="busqueda-highlight">$&</span>')

                    divSugerido.appendChild(aCientifico)
                    cientificosSugeridos.appendChild(divSugerido)
                }
            }
        }

        let formData = new FormData()
        formData.append("query", query)

        xhr.send(formData)
    }, 200)
})
