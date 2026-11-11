-- ============================================================
-- script-citas.sql (portal_citas - versión antigua)
--
-- OJO: este script es antiguo y tiene errores de nombres/sintaxis.
-- El script bueno y comentado está en:
--   portal_citasV2/generador_clinia.sql
--
-- ÍNDICE RÁPIDO (Ctrl+F):
--   TABLAS
--   PROCEDURE AltaDoctor
--   PROCEDURE ListaDoctores
--   PROCEDURE DameDoctor
--   PROCEDURE AltaCita
--   PROCEDURE ModificarCita
-- ============================================================

-- SCRIPT DE GENERACION DE BBDD EN MYSQL
create database if not exists Clinica;
use Clinica;

-- drops

drop table if exists citasMedicas;
drop table if exists doctores;

-- ===== TABLAS =====
-- Tabla padre: doctores.
create table doctores(
	n_licencia int primary key,
    nombre_doctor varchar(50) not null,
    apellido_doctor varchar(50) not null
    
);


-- Tabla hija: citas. Aquí está el error "citasMedias" (debería ser citasMedicas).
create table citasMedias(
	id_cita int primary key auto_increment,
    nombre_paciente varchar(50) not null,
    especialidad enum("Medicina General","Pediatría","Dermatología","Traumatología"),
	fecha datetime,
    fk_doctor int not null,
    foreign key (fk_doctor) references doctores(n_licencia)
);

-- alter table citasMedicas add column(fk_doctor int not null);
-- alter table citasMedicas add constraint foreign foreing key (fk_doctor)

-- Procedure borrar
drop procedure AltaCita;

-- ===== PROCEDURE AltaDoctor =====
-- Casos: -1 nombre vacío, -2 apellido vacío, -3 licencia duplicada, 0 correcto.
delimiter //

create procedure AltaDoctor (
    in n_licencia varchar(50),
    in _nombre_doctor varchar(50),
    in _apellido_doctor varchar(20),
    out _res int
)
begin
    -- Caso 1: Si no hay nombre de doctor
    if (_nombre_doctor = '' or _nombre_doctor is null) then
        set _res = -1;
        
    -- Caso 2: Si no hay apellido
    elseif (_apellido_doctor = '' or _apellido_doctor is null) then
        set _res = -2;
        
    -- Caso 3: Si la licencia ya existe en la base de datos
    elseif (exists (select * from doctores where doctores.n_licencia = n_licencia)) then
        set _res = -3;
        
    else
        -- Caso 0: Inserción correcta
        insert into doctores (n_licencia, nombre_doctor, apellidos_doctor)
        values (n_licencia, _nombre_doctor, _apellido_doctor);
        
        set _res = 0; -- Opcional: indicar éxito
        
    end if;
end //

delimiter ;



-- ===== PROCEDURE ListaDoctores =====
-- Devuelve todos los doctores. Ojo: más abajo hay otro listadoctores duplicado.
DELIMITER //

CREATE PROCEDURE ListaDoctores ()
BEGIN
    -- caso 0
    -- instruccion nuclear
    SELECT * FROM doctores;
END //

DELIMITER ;

delimiter //

create procedure listadoctores ()
begin
    -- caso 0
    -- instruccion nuclear
    select * from doctores;
end //

delimiter ;

-- ===== PROCEDURE DameDoctor =====
-- Busca un doctor por su licencia.
delimiter //

create procedure damedoctor (in _n_licencia int)
begin
    -- caso 0
    -- instruccion nuclear
    select * from doctores where n_licencia = _n_licencia;
end //

delimiter ;



-- ===== PROCEDURE AltaCita =====
-- Casos: -1 nombre, -2 especialidad, -3 fecha vacía, -4 fecha pasada, 0 correcto.
-- Ojo: la definición "in fk_doctor on _res int" es incorrecta; falta OUT _res.
delimiter //
create procedure AltaCita (in _nombre_paciente  varchar(50), in _especialidad varchar(20), in _fecha datetime, in fk_doctor on _res int)

begin

	-- caso 1 - Nombre vacio
    if(_nombre_paciente like "" or _nombre_paciente is null) then
		set _res = -1;
    -- caso 2 - Especialidad vacia
    elseif(_especialidad like "" or _especialidad is null) then
		set _res = -2;
    -- caso 3 - Fecha vacia
    elseif(_fecha is null or _fecha like "") then
		set _res = -3;
    -- caso 4 - Fecha anterior al presente
	elseif(_fecha <= now()) then
		set _res = -4;
    else
    -- caso 0
    -- instruccio nuclear
    insert into citasMedias(nombre_paciente,especialidad,fecha,fk_doctor)
		values (_nombre_paciente, _especialidad, _fecha,_fk_doctor);
        
		set _res = 0; -- Opcional: indicar éxito
        
	end if;
end $$
delimiter ;



-- ===== PROCEDURE ModificarCita =====
-- Actualiza una cita existente. Ojo: el WHERE usa id_cita = id_cita
-- (se compara consigo mismo); debería usar el parámetro de entrada.
delimiter //
create procedure ModificarCita (in id_cita int, _nombre_paciente  varchar(50), in _especialidad varchar(20), in _fecha datetime, in fk_doctor on _res int)

begin

	-- caso 1 - Nombre vacio
    if(_nombre_paciente like "" or _nombre_paciente is null) then
		set _res = -1;
    -- caso 2 - Especialidad vacia
    elseif(_especialidad like "" or _especialidad is null) then
		set _res = -2;
    -- caso 3 - Fecha vacia
    elseif(_fecha is null or _fecha like "") then
		set _res = -3;
    -- caso 4 - Fecha anterior al presente
	elseif(_fecha <= now()) then
		set _res = -4;
    else
    -- caso 0
    -- instruccio nuclear
    update citasmedicas set nombre_paciente = _nombre_paciente, especialidad = _especialidad , fecha = _fecha, fk_doctor = fk_doctor
		where id_cita = id_cita;
        
		set _res = 0; -- Opcional: indicar éxito
        
	end if;
end $$
delimiter ;