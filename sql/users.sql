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

curl --data "first_name=Lucas&last_name=William" https://ml-php-rest-api-dt.herokuapp.com/users


URL                        HTTP Method  Operation
/api/users                 GET          Returns an array of users
/api/users/:id             GET          Returns the user with id of :id
/api/users                 POST         Adds a new user and return it with an id attribute added
/api/users/:id             POST         Partial Updates to the user with id of :id
/api/users/:id             DELETE       Deletes the user with id of :id
--/api/users/:id             PUT          Updates the user with id of :id
--/api/users/:id             PATCH        Partially update INSTRUCTIONS the user with id of :id
