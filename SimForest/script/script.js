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




// Variable Modelos

let M;
let N;
let P;
let Q;

// Constantes del modelo
const INFERTIL = 0;
const FERTIL = 1
const ARBOL = 2;
const FUEGO = 3;

const CLASES = ["infertil", "fertil", "arbol", "fuego"];

let bosque;

// Primer arranque
panelConfigurador.style.display = "";
panelSimulador.style.display = "none";

// funcion donde depende de lo q ponga el cliente se hace la tabla
function pintarTablero(){
    // Limpiamos el tablero por si se pulsa "Iniciar" más de una vez
    tableroDIV.innerHTML = "";

    let tablaDOM = document.createElement("div");
    tablaDOM.className = "tablero";

    for(let i = 0; i < N; i++){
        let filaDOM = document.createElement("div");
        filaDOM.className = "fila";
        for(let j = 0; j < M; j++){
            let celdaDOM = document.createElement("div");
            celdaDOM.className = "celda " + CLASES[bosque[i][j]];
            filaDOM.appendChild(celdaDOM);

            // if(bosuqe[i][j] == 0)
            //     celda
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
        for(let j= 0; j < N; j++){

            // si el estado = fertil
            if(bosque[i][j] == j)
                // calculo la probabilidad (p) de nacimiento de arbol
                if(Math.random(0,1) <= P)
                {
                    bosque_futuro[i][j] = 2;
                }
                else{
                    bosque_futuro[i][j] = 1;

                }
            if(bosque[i][j] == 2){
                bosque_futuro[i][j] = ARBOL;
                // propagcion del fuego
                if (i +1 < M && bosque[i+1][j] == 3);
                    bosque_futuro[i][j] = 3;
                if (i +1 < N &&bosque[i+1][j] == 3);
                    bosque_futuro[i][j] = 3;                
                if (i +1 < M &&bosque[i+1][j] == 3);
                    bosque_futuro[i][j] = 3;   
                if (i +1 < N && bosque[i][j+1] == 3)
                    bosque_futuro[i][j] = 3;             

                // calcular probabildad Q de combustible
                if(Math.random() <= Q)
                    bosque_futuro[i][j] = 3
            }
            if(bosque[i][j] == 0)
                bosque_futuro[i][j] = 1;
            if(bosque[i][j] == 3)
                bosque_futuro[i][j] = 0;
        }
    }

    for(let i = 0; i < M; j++){
        for(let j = 0;j < N; j++){
            bosque[i][j] = bosque_futuro[i][j];
        }
    }
    t++;


    tableroDIV.removeChild(tableroDOM);
    pintarTablero();

}

btn_iniciar.addEventListener("click", prepararTablero);
btn_adelantar.addEventListener("click", avanzar1);