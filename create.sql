/*
Author: Min Xu <xukmin@gmail.com>

SQL script to create the bookstore database and tables, insert example rows for
testing.

Usage:
    mysql --user root --verbose < create.sql
*/

DROP DATABASE IF EXISTS bookstore;
CREATE DATABASE IF NOT EXISTS bookstore;

USE bookstore;

CREATE TABLE books (
  isbn CHAR(13),
  title VARCHAR(255),
  author VARCHAR(255),
  publisher VARCHAR(255),
  price NUMERIC(10,2),
  topic integer,
  image MEDIUMBLOB,
  PRIMARY KEY (isbn)
);

INSERT INTO books VALUES
(
  '9780073523323',
  'Database System Concepts',
  'Abraham Silberschatz',
  'McGraw-Hill',
  189.82,
  1,
  LOAD_FILE('/tmp/images/databases.jpg')
),
(
  '9780124077263',
  'Computer Organization and Design',
  'David A. Patterson',
  'Morgan Kaufmann',
  80.94,
  2,
  LOAD_FILE('/tmp/images/architecture.jpg')
),
(
  '9780262033848',
  'Introduction to Algorithms',
  'Thomas H. Cormen',
  'The MIT Press',
  79.13,
  1,
  LOAD_FILE('/tmp/images/algorithms.jpg')
),
(
  '9780321486813',
  'Compilers: Principles, Techniques, and Tools',
  'Alfred V. Aho',
  'Addison Wesley',
  144.77,
  2,
  LOAD_FILE('/tmp/images/compilers.jpg')
),
(
  '9780133591620',
  'Modern Operating Systems',
  'Andrew S. Tanenbaum',
  'Prentice Hall',
  153.09,
  1,
  LOAD_FILE('/tmp/images/operatingsystems.jpg')
),
(
  '9780132126953',
  'Computer Networks',
  'Andrew S. Tanenbaum',
  'Prentice Hall',
  132.58,
  2,
  LOAD_FILE('/tmp/images/networks.jpg')
);

CREATE TABLE customers (
  customer_id INTEGER AUTO_INCREMENT,
  username VARCHAR(255),
  password CHAR(40),  /* SHA1 hash of the password */
  first_name VARCHAR(255),
  last_name VARCHAR(255),
  email VARCHAR(255),
  is_prime_member boolean,
  PRIMARY KEY (customer_id)
);

INSERT INTO customers VALUES
(100, 'maria', SHA1('123456'), 'Maria', 'Pantoja', 'mpantoja@scu.edu', TRUE),
(101, 'mxu', SHA1('123456'), 'Min', 'Xu', 'mxu@scu.edu', TRUE),
(102, 'mike', SHA1('123456'), 'Michael', 'Santoro', 'msantoro@scu.edu', TRUE),
(103, 'wang', SHA1('123456'), 'Ming-Hwa', 'Wang', 'm1wang@scu.edu', FALSE),
(104, 'ed', SHA1('123456'), 'Edward', 'Karrels', 'ekarrels@scu.edu', FALSE);

CREATE TABLE employees (
  employee_id INTEGER,
  username VARCHAR(255),
  password CHAR(40),  /* SHA1 hash of the password */
  first_name VARCHAR(255),
  last_name VARCHAR(255),
  email VARCHAR(255),
  department VARCHAR(255),
  salary NUMERIC(10, 2)
);

INSERT INTO employees VALUES
(
  200, 'larry', SHA1('123456'), 'Larry', 'Ellison', 'larry@oracle.com',
  'Sales', 5000.00
),
(
  201, 'mickos', SHA1('123456'), 'Marten', 'Mickos', 'mickos@mysql.com',
  'Eng', 6000.00
);

-- TODO: Specify PRIMIARY KEY for all tables.
CREATE TABLE orders (
  order_id INTEGER AUTO_INCREMENT,  -- Automatically assign order_id.
  customer_id INTEGER,
  shipping_method VARCHAR(255),
  card_number VARCHAR(255),
  order_date date, 
  PRIMARY KEY (order_id)
);

INSERT INTO orders VALUES
(60000, 100, 'express', '1111111111111111', '2015-02-12'),
(60001, 101, 'ground', '2222222222222222', '2015-02-20'),
(60002, 102, 'ground', '4444444444444444', '2015-03-01'),
(60003, 100, 'express', '1111111111111111', '2015-03-03'),
(60004, 101, 'ground', '2222222222222222', '2015-03-07'),
(60005, 103, 'ground', '4444444444444444', '2015-03-10');

CREATE TABLE order_book (
  order_id INTEGER,
  isbn CHAR(13),
  quantity INTEGER,
  CONSTRAINT order_book_id PRIMARY KEY (order_id, isbn)
);

INSERT INTO order_book VALUES
(60000, '9780073523323', 5),
(60000, '9780124077263', 2),
(60001, '9780262033848', 3),
(60001, '9780321486813', 1),
(60001, '9780133591620', 2),
(60001, '9780132126953', 1),
(60002, '9780073523323', 2),
(60002, '9780262033848', 2),
(60002, '9780124077263', 3),
(60003, '9780321486813', 4),
(60003, '9780132126953', 1),
(60004, '9780124077263', 1),
(60005, '9780133591620', 6),
(60005, '9780132126953', 1);

-- TODO: Allow sharing credit cards among multiple users.
CREATE TABLE payments (
  card_number VARCHAR(255),
  customer_id INTEGER,
  name_on_card VARCHAR(255),
  card_company VARCHAR(255),
  expire_month INTEGER,
  expire_year INTEGER
);

INSERT INTO payments VALUES
('1111111111111111', 100, 'Maria Pantoja', 'Visa', 3, 2015),
('2222222222222222', 101, 'Min Xu', 'MasterCard', 4, 2015),
('3333333333333333', 101, 'Min Xu', 'Visa', 8, 2017),
('4444444444444444', 102, 'Michael Santoro', 'Discover', 5, 2015),
('5555555555555555', 103, 'Ming-Hwa Wang', 'Visa', 6, 2015),
('6666666666666666', 104, 'Edward Karrels', 'Visa', 7, 2015);

CREATE TABLE address (
  street_number INTEGER, 
  street_name VARCHAR(255),
  city VARCHAR(255),
  state VARCHAR(255),
  zip_code INTEGER
);

INSERT INTO address VALUES
(500, 'El Camino Real', 'Santa Clara', 'CA', 95053),
(447, 'Great Mall Dr', 'Milpitas', 'CA', 95035),
(500, 'Oracle Parkway', 'Redwood Shores', 'CA', 94065);

CREATE TABLE customer_address (
  customer_id INTEGER,
  street_number INTEGER, 
  street_name VARCHAR(255),
  city VARCHAR(255),
  state VARCHAR(255)
);

INSERT INTO customer_address VALUES
(100, 500, 'El Camino Real', 'Santa Clara', 'CA'),
(101, 447, 'Great Mall Dr', 'Milpitas', 'CA'),
(102, 500, 'El Camino Real', 'Santa Clara', 'CA'),
(103, 500, 'El Camino Real', 'Santa Clara', 'CA'),
(104, 500, 'El Camino Real', 'Santa Clara', 'CA'); 

CREATE TABLE employee_address (
  employee_id INTEGER,
  street_number INTEGER,
  street_name VARCHAR(255),
  city VARCHAR(255),
  state VARCHAR(255)
);

INSERT INTO employee_address VALUES
(200, 500, 'Oracle Parkway', 'Redwood Shores', 'CA'),
(201, 500, 'Oracle Parkway', 'Redwood Shores', 'CA');

-- TODO: Use TEXT type for content.
CREATE TABLE comments (
  order_id INTEGER,
  isbn CHAR(13),
  rating SMALLINT,
  content VARCHAR(255)
);

INSERT INTO comments VALUES
(
  60000, '9780073523323', 5,
  'This is a great book on relational databases.'
),
(
  60002, '9780073523323', 5,
  'The book is suitable for being a textbook.'
),  
(
  60000, '9780124077263', 5,
  'This is a great book on computer architecture.'
),
(
  60002, '9780124077263', 4,
  'This is a great book on computer architecture.'
),
(
  60002, '9780262033848', 3,
  'It is OK.'
),
(
  60005, '9780262033848', 4,
  'The book is good!'
),
(
  60003, '9780321486813', 4,
  'Good book!'
),
(
  60003, '9780321486813', 5,
  'Excellent book!'
),
(
  60005, '9780133591620', 5,
  'Great!'
),
(
  60003, '9780132126953', 3,
  'Just so so.'
),
(
  60005, '9780132126953', 5,
  'Excellent!'
);

CREATE TABLE inventory (
  isbn CHAR(13), 
  quantity INTEGER
);

INSERT INTO inventory VALUES
('9780073523323', 200),
('9780124077263', 50),
('9780262033848', 100),
('9780321486813', 60),
('9780133591620', 40),
('9780132126953', 80);

CREATE TABLE suppliers (
  supplier_id INTEGER,
  company VARCHAR(255),
  isbn CHAR(13)
);

INSERT INTO suppliers VALUES
(11101, 'McGraw-Hill', '9780073523323'),
(11102, 'Morgan Kaufmann', '9780124077263'), 
(11103, 'The MIT Press', '9780262033848'), 
(11101, 'Addison Wesley', '9780321486813'), 
(11101, 'Prentice Hall', '9780133591620'),
(11101, 'Prentice Hall', '9780132126953'); 

