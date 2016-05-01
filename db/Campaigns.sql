use Aristarchus;
drop table if exists Campaigns;

create table Campaigns (
       campaign varchar(100),
       phenomenon varchar(1000),
       date varchar(100),
       primary key (campaign)       
);
