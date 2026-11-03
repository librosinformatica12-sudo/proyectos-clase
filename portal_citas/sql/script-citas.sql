-- SCRIPT DE GENERACION DE BBDD EN MYSQL
create database if not exists Clinica;
use Clinica;
-- drops
drop table if exists citasMedicas;

-- tablas
create table citasMedias(
	id_cita int primary key auto_increment,
    nombre_paciente varchar(50) not null,
    especialidad enum("Medicina General","Pediatría","Dermatología","Traumatología"),
	fecha datetime

);

-- Procedure borrar
drop procedure AltaCita;

-- Procedure crear
delimiter //
create procedure AltaCita (in _nombre_paciente  varchar(50), in _especialidad varchar(20), in _fecha datetime, on _res int)

begin

	-- caso 1 - Nombre vacio
    if(_nombre_paciente like "" or _nombre_paciente is null) then
		set _res = -1
    -- caso 2 - Especialidad vacia
    elseif(_especialidad like "" or _especialidad is null) then
		set _res = -2
    -- caso 3 - Fecha vacia
    elseif(_fecha is null or _fecha like "") then
		set _res = -3
    -- caso 4 - Fecha anterior al presente
	elseif(_fecha <= now()) then
		set _res = -4
    else
    -- caso 0
    -- instruccio nuclear
    insert into citasMedias(nombre_paciente,especialidad,fecha)
		values (_nombre_paciente, _especialidad, _fecha);
end $$
delimiter ;
