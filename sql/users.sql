CREATE TABLE IF NOT EXISTS users
(
    user_id serial CONSTRAINT pk_users PRIMARY KEY,
    first_name varchar(50) NOT NULL,
    last_name varchar(50),
    email varchar(100),
    password varchar(50),
    is_active boolean NOT NULL DEFAULT true,
    date_joined timestamp DEFAULT current_timestamp,
    date_modified timestamp,
    last_login timestamp
);

insert into users(first_name) values('Margus');
insert into users(first_name) values('Fuji');