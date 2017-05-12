create database yiishop;
use yiishop;
set names gbk;

drop table if exists shop_admin;
create table shop_admin
(
	adminid int unsigned not null auto_increment comment '用户Id',
	adminuser varchar(32) not null default '' comment '用户名',
	adminpass varchar(32) not null default '' comment '密码',
	adminemail varchar(50) not null default '' comment '邮件',
	logintime int unsigned not null default '0' comment '登录时间',
	loginip bigint not null default '0' comment '登录IP',
	createtime int unsigned not null default '0' comment '注册时间',
	primary key(adminid),
	key adminuser(adminuser),
	key adminpass9(adminpass),
	key adminemail(adminemail)
)engine = InnoDB default charset=utf8 comment "商城管理员表";


insert into shop_admin (adminid,adminuser,adminpass,adminemail,createtime) values('1','itarvin','be140e0fb8126d278d0d823ddd4fab2a','itarvin@163.com',UNIX_TIMESTAMP());