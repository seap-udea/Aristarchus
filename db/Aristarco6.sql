use Aristarchus;
drop table if exists Aristarco6;

create table Aristarco6 (
       obsid varchar(10),

       /* Location */
       email varchar(255),
       name varchar(255),
       code varchar(255),

       /* Location */
       sitename varchar(255),  
       latitude varchar(10),
       longitude varchar(10),
       altitude varchar(10),
       timezone varchar(2),

       /* Images */
       calimage varchar(100),
       images varchar(1000),
       times varchar(1000),       

       /* Images */
       instrument text,

       primary key (obsid)       
);
