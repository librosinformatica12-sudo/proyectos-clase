/* ============================================================
   script.js (SimForest)
   ÍNDICE RÁPIDO (Ctrl+F):
     REFERENCIAS DOM
     VARIABLES MODELO
     CONSTANTES DEL MODELO
     ESTADO DEL BOSQUE
     VELOCIDAD
     ARRANQUE
     pintarTablero
     prepararTablero
     avanzar1
     continuarSimulacion
     pararSimulacion
     reiniciarTimer
     acelerarSimulacion
     frenarSimulacion
     EVENTOS
   ============================================================ */

// ===== REFERENCIAS DOM =====
// Variables Vista
const panelConfigurador = document.getElementById("panel-configurador")
const panelSimulador = document.getElementById("panel-simulador")

const tableroDOM = document.getElementById("tablero") // no se usa en el resto del archivo
const tableroDIV = document.getElementById("tablero") // este es el que se pinta

const anchura_bosque = document.getElementById("spin_anchura_bosque");
const altura_bosque = document.getElementById("spin_altura_bosque");
const probabilidad_arbol = document.getElementById("spin_probabilidad_arbol");
const probabilidad_indencio = document.getElementById("spin_probabilidad_incendio");

const btn_iniciar = document.getElementById("btn_tabla");

const btn_atrasar = document.getElementById("btn-atrasar");
const btn_parar = document.getElementById("btn-parar");
const btn_seguir = document.getElementById("btn-seguir");
const btn_adelantar = document.getElementById("btn-adelantar");

// ===== VARIABLES MODELO =====
// M = columnas, N = filas, P = probabilidad de árbol, Q = probabilidad de fuego.
let M;
let N;
let P;
let Q;

// ===== CONSTANTES DEL MODELO =====
// Cada número representa un estado; CLASES lo traduce a la clase CSS.
const INFERTIL = 0;
const FERTIL = 1
const ARBOL = 2;
const FUEGO = 3;

const CLASES = ["infertil", "fertil", "arbol", "fuego"];

// ===== ESTADO DEL BOSQUE =====
// bosque = matriz actual; bosque_futuro = matriz donde se calcula el siguiente paso.
let bosque;
let bosque_futuro;

// ===== VELOCIDAD =====
// variables de velocidad (temporizador)
let timer = null;
let velocidad = 1000; // 1000 ms son 1 segundos

// ===== ARRANQUE =====
// Primer arranque: se ve la configuración y se oculta el simulador.
panelConfigurador.style.display = "";
panelSimulador.style.display = "none";

// ===== pintarTablero =====
// Convierte la matriz "bosque" en divs. Cada celda recibe la clase del estado.
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
        }
        tablaDOM.appendChild(filaDOM);
    }

    tableroDIV.appendChild(tablaDOM);
}

// ===== prepararTablero =====
// Lee los inputs, crea las matrices N x M y muestra el simulador.
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

    // Inicializamos el bosque con todas las casillas en FERTIL (1).
    for (let i = 0; i < N; i++) {
        for (let j = 0; j < M; j++){
            bosque[i][j] = 1;
        }
    }

    // bosque futuro con la misma forma N x M
    bosque_futuro = new Array(N);
    for (let i = 0; i < N; i++){
        bosque_futuro[i] = new Array(M);
    }

    panelConfigurador.style.display = "none";
    panelSimulador.style.display = "";

    pintarTablero();
}

let t = 0; // contador de pasos (declararlo arriba junto a las demás variables)

// ===== avanzar1 =====
// Calcula UN paso de la simulación.
// 1) Recorre el bosque actual y rellena bosque_futuro.
// 2) Copia bosque_futuro sobre bosque.
// 3) Vuelve a pintar.
function avanzar1() {
    if (!bosque) return;

    for (let i = 0; i < N; i++) {
        for (let j = 0; j < M; j++) {
            const estado = bosque[i][j];

            if (estado === 0) {
                // infértil -> fértil
                bosque_futuro[i][j] = 1;

            } else if (estado === 1) {
                // fértil: con probabilidad P nace un árbol
                if (Math.random() <= P) {
                    bosque_futuro[i][j] = 2;
                } else {
                    bosque_futuro[i][j] = 1;
                }

            } else if (estado === 2) {
                // árbol: ¿algún vecino (4 direcciones) está en fuego?
                let vecinoEnFuego =
                    (i > 0     && bosque[i - 1][j] === 3) ||
                    (i < N - 1 && bosque[i + 1][j] === 3) ||
                    (j > 0     && bosque[i][j - 1] === 3) ||
                    (j < M - 1 && bosque[i][j + 1] === 3);

                if (vecinoEnFuego) {
                    bosque_futuro[i][j] = 3;
                } else if (Math.random() <= Q) {
                    // incendio espontáneo con probabilidad Q
                    bosque_futuro[i][j] = 3;
                } else {
                    bosque_futuro[i][j] = 2;
                }

            } else if (estado === 3) {
                // fuego -> infértil (ceniza)
                bosque_futuro[i][j] = 0;
            }
        }
    }

    // Copiar el futuro al presente (si no, el siguiente paso partiría del estado viejo).
    for (let i = 0; i < N; i++) {
        for (let j = 0; j < M; j++) {
            bosque[i][j] = bosque_futuro[i][j];
        }
    }

    t++;
    pintarTablero();
}

// ===== continuarSimulacion =====
// Arranca el temporizador: cada "velocidad" ms se ejecuta avanzar1().
function continuarSimulacion(){
    if (!bosque) return;
    if (timer !== null) return;
    timer = setInterval(avanzar1, velocidad);
}

// ===== pararSimulacion =====
// Detiene el temporizador y lo deja listo para volver a arrancar.
function pararSimulacion(){
    clearInterval(timer);   // detiene al repeticion
    timer = null;
}


// ===== reiniciarTimer =====
// Vuelve a crear el intervalo para aplicar la nueva velocidad.
function reiniciarTimer(){
    if (timer !== null){
        clearInterval(timer);
        timer = setInterval(avanzar1, velocidad);
    }
}

// ===== acelerarSimulacion =====
// Menos milisegundos = más rápido. El mínimo es 100 ms.
function acelerarSimulacion(){
    velocidad = Math.max(100, velocidad - 200); // menos ms = mas rapido
    reiniciarTimer();
}

// ===== frenarSimulacion =====
// Más milisegundos = más lento. El máximo es 3000 ms.
function frenarSimulacion(){
    velocidad = Math.min(3000, velocidad + 200); // mas ms = mas lento
    reiniciarTimer();
}

// ===== EVENTOS =====
// Cada botón llama a una función distinta.
btn_iniciar.addEventListener("click", prepararTablero);

// Avanzar 1 (paso manual)
document.getElementById("btn-avanzar1").addEventListener("click", avanzar1);

// Play / Pausa
btn_seguir.addEventListener("click", continuarSimulacion); // ▶
btn_parar.addEventListener("click", pararSimulacion);      // ■

// Bonus: velocidad
btn_adelantar.addEventListener("click", acelerarSimulacion); // ⏩ mas rapido
btn_atrasar.addEventListener("click", frenarSimulacion);     // ⏪ mas lento