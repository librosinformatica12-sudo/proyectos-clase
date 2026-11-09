-- ============================================================
-- script de generacion de la base de datos "clinica"
-- gestor de citas medicas - acceso a datos
-- ============================================================

create database if not exists clinica
    default character set utf8mb4
    collate utf8mb4_0900_ai_ci;

use clinica;

set names utf8mb4;

-- ------------------------------------------------------------
-- borrado previo (primero las citas por la clave foranea)
-- ------------------------------------------------------------
drop table if exists citasmedias;
drop table if exists doctores;

drop procedure if exists altadoctor;
drop procedure if exists listadoctores;
drop procedure if exists damedoctor;
drop procedure if exists altacita;
drop procedure if exists listacitas;
drop procedure if exists modificarcita;

-- ------------------------------------------------------------
-- tablas
-- ------------------------------------------------------------
create table doctores (
    n_licencia int primary key,
    nombre_doctor varchar(50) not null,
    apellido_doctor varchar(50) not null
) engine=innodb default charset=utf8mb4 collate=utf8mb4_0900_ai_ci;

create table citasmedias (
    id_cita int primary key auto_increment,
    nombre_paciente varchar(50) not null,
    especialidad enum('medicina general', 'pediatría', 'dermatología', 'traumatología')
        character set utf8mb4 collate utf8mb4_0900_ai_ci default null,
    fecha datetime default null,
    fk_doctor int not null,
    constraint fk_citas_doctor foreign key (fk_doctor) references doctores (n_licencia)
) engine=innodb default charset=utf8mb4 collate=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------
-- procedimientos de doctores
-- ------------------------------------------------------------
delimiter //

create procedure altadoctor (
    in _n_licencia varchar(50),
    in _nombre_doctor varchar(50),
    in _apellido_doctor varchar(50),
    out _res int
)
begin
    if (_nombre_doctor is null or trim(_nombre_doctor) = '') then
        set _res = -1;
    elseif (_apellido_doctor is null or trim(_apellido_doctor) = '') then
        set _res = -2;
    elseif (_n_licencia is null or trim(_n_licencia) = '') then
        set _res = -3;
    elseif exists (select 1 from doctores where n_licencia = _n_licencia) then
        set _res = -4;
    else
        insert into doctores (n_licencia, nombre_doctor, apellido_doctor)
        values (_n_licencia, _nombre_doctor, _apellido_doctor);
        set _res = 0;
    end if;
end //

create procedure listadoctores ()
begin
    select n_licencia, nombre_doctor, apellido_doctor
    from doctores
    order by n_licencia;
end //

create procedure damedoctor (in _n_licencia int)
begin
    select n_licencia, nombre_doctor, apellido_doctor
    from doctores
    where n_licencia = _n_licencia;
end //

-- ------------------------------------------------------------
-- procedimientos de citas
-- ------------------------------------------------------------
create procedure altacita (
    in _nombre_paciente varchar(50),
    in _especialidad varchar(20),
    in _fecha datetime,
    in _doctor_asignado int,
    out _res int
)
begin
    if (_nombre_paciente is null or trim(_nombre_paciente) = '') then
        set _res = -1;
    elseif (_especialidad is null or trim(_especialidad) = '') then
        set _res = -2;
    elseif (_fecha is null) then
        set _res = -3;
    elseif (_fecha <= now()) then
        set _res = -4;
    elseif not exists (select 1 from doctores where n_licencia = _doctor_asignado) then
        set _res = -5;
    else
        insert into citasmedias (nombre_paciente, especialidad, fecha, fk_doctor)
        values (_nombre_paciente, _especialidad, _fecha, _doctor_asignado);
        set _res = 0;
    end if;
end //

create procedure listacitas ()
begin
    select id_cita, nombre_paciente, especialidad, fecha, fk_doctor
    from citasmedias
    order by fecha;
end //

create procedure modificarcita (
    in _id_cita int,
    in _nombre_paciente varchar(50),
    in _especialidad varchar(20),
    in _fecha datetime,
    in _doctor_asignado int,
    out _res int
)
begin
    if (_nombre_paciente is null or trim(_nombre_paciente) = '') then
        set _res = -1;
    elseif (_especialidad is null or trim(_especialidad) = '') then
        set _res = -2;
    elseif (_fecha is null) then
        set _res = -3;
    elseif (_fecha <= now()) then
        set _res = -4;
    else
        update citasmedias
        set nombre_paciente = _nombre_paciente,
            especialidad = _especialidad,
            fecha = _fecha,
            fk_doctor = _doctor_asignado
        where id_cita = _id_cita;
        set _res = 0;
    end if;
end //

delimiter ;