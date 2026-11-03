-- SCRIPT DE GENERACION DE BBDD EN MYSQL
create database if not exists Clinica;
use Clinica;

-- drops

drop table if exists citasMedicas;
drop table if exists doctores;

-- tablas
create table doctores(
	n_licencia int primary key,
    nombre_doctor varchar(50) not null,
    apellido_doctor varchar(50) not null
    
);


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

-- Procedure crear
delimiter //
create procedure AltaCita (
	in n_licencia varchar(50),
    in _nombre_doctor varchar(50),
    in _apellido_doctor varchar(20),
    on _res int
)

begin

	-- caso 1 - si no hay nombre doctor
    if(_nombre_doctor like "" or _nombre_doctor is null) then
		set _res = -1;
    -- caso 2 - si no hay apellido
    elseif(_apellido_doctor like "" or _apellido_doctor is null) then
		set _res = -2;
    -- caso 3 - licencia igual error
    elseif(select n_licencia from where n_licencia like _n_licencia is not null then
		set _res = -3;
	else
    -- caso 0
    -- instruccio nuclear
    insert into doctores(n_licencia,nombre_doctor,apellidos_doctor)
		values ();
end $$
delimiter ;





-- Procedure crear
delimiter //
create procedure AltaCita (in _nombre_paciente  varchar(50), in _especialidad varchar(20), in _fecha datetime, on _res int)

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
    insert into citasMedias(nombre_paciente,especialidad,fecha)
		values (_nombre_paciente, _especialidad, _fecha);
end $$
delimiter ;
