// Variables Vista
const panelConfigurador = document.getElementById("panel-configurador")
const panelSimulador = document.getElementById("panel-simulador")

const tableroDOM = document.getElementById("tablero")
const tableroDIV = document.getElementById("tablero")

const anchura_bosque = document.getElementById("spin_anchura_bosque");
const altura_bosque = document.getElementById("spin_altura_bosque");
const probabilidad_arbol = document.getElementById("spin_probabilidad_arbol");
const probabilidad_indencio = document.getElementById("spin_probabilidad_incendio");

const btn_iniciar = document.getElementById("btn_tabla");

const btn_atrasar = document.getElementById("btn-atrasar");
const btn_parar = document.getElementById("btn-parar");
const btn_seguir = document.getElementById("btn-seguir");
const btn_adelantar = document.getElementById("btn-adelantar");


// Variable Modelo

let M;
let N;
let P;
let Q;

// Constantes del modelo
const INFERTIL = 0;
const ARBOL = 1;
const FUEGO = 2;

const CLASES = ["infertil", "arbol", "fuego"];

let bosque;

// Primer arranque
panelConfigurador.style.display = "";
panelSimulador.style.display = "none";

// funcion donde depende de lo q ponga el cliente se hace la tabla
function pintarTablero(){
    // Limpiamos el tablero por si se pulsa "Iniciar" más de una vez
    tableroDIV.innerHTML = "";

    let tablaDOM = document.createElement("table");
    tablaDOM.setAttribute("class", "tablero");

    for(let i = 0; i < N; i++){
        let filaDOM = document.createElement("tr");
        for(let j = 0; j < M; j++){
            let celdaDOM = document.createElement("td");
            celdaDOM.setAttribute("class", CLASES[bosque[i][j]]);
            filaDOM.appendChild(celdaDOM);
        }
        tablaDOM.appendChild(filaDOM);
    }

    tableroDIV.appendChild(tablaDOM);
}

function prepararTablero() {
    // Variables modelo
    M = parseInt(anchura_bosque.value);
    N = parseInt(altura_bosque.value);
    P = parseFloat(probabilidad_arbol.value);
    Q = parseFloat(probabilidad_indencio.value);

    // construccion tablero
    bosque = new Array(N);
    for(let i = 0; i < N; i++){
        bosque[i] = new Array(M);
    }

    // Inicializamos el bosque con todas las casillas
    for (let i = 0; i < N; i++) {
        for (let j = 0; j < M; j++){
            bosque[i][j] = ARBOL;
        }
    }

    panelConfigurador.style.display = "none";
    panelSimulador.style.display = "";

    pintarTablero();
}

function avanzar1(){
    for(let i; i < M; i++){
        for(let j; j < N; j++){

            // si el estado es fertil
                // calculo la probabilidad (p) de nacimiento de arbol
                // bosque[i][j] =
                rand(0,1)
            // sino si el estado = arbol
                // calcular probabilidad (Q) de combustion
                // bosque[i][j] = 
            // sino si el estado = interfil
                //bosque[i][j]= fertil
            // sino si el estado = fuego
                // bosque[i][j]= infertil
        }
    }
    // machacar el bosque actual con los resultados del bosque futuro
    // bosque actual <---- bosque_futuro
    t++;


    tableroDIV.removeChild(tableroDOM);
    // pintarTablero()

}
btn_iniciar.addEventListener("click", prepararTablero);