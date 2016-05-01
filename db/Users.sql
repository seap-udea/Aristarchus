use Aristarchus;
drop table if exists Users;

create table Users (
       email varchar(255),
       name varchar(255),
       code varchar(255),
       sitename varchar(255),

       campaign_s varchar(1000),
       obsid_s varchar(1000),

       primary key (email)       
);
