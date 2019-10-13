<?php
$flush = true;


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'entities/entities.php';


// $flush = false;
if( !(defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS') && defined('DB_HOST')) ) {
    die('db not set up');
}
$em = new ORM\EntityManager([
    ORM\EntityManager::OPT_CONNECTION => ['mysql', DB_NAME, DB_USER, DB_PASS, DB_HOST]
]);


if( $flush ) {

    // Courses
    $em->getConnection()->query("DROP TABLE IF EXISTS course");

    $em->getConnection()->query("CREATE TABLE course (
      id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
      course_name VARCHAR (64) NOT NULL
    )");
    
    $em->getConnection()->query("INSERT INTO course (course_name) VALUES
      ('Cutting trees, the ins and outs'),
      ('CSS and you - a love story'),
      ('Baking mud cakes using actual mud'),
      ('Christmas eve - myth or reality?'),
      ('LEGO colors through time')
    ");
    
    $em->getConnection()->query("CREATE UNIQUE INDEX course_course_name ON course (course_name)");

    // Course dates
    $em->getConnection()->query("DROP TABLE IF EXISTS course_date");

    $em->getConnection()->query("CREATE TABLE course_date (
      id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
      course_id INTEGER NOT NULL,
      course_date DATE NOT NULL
    )");

    
    $em->getConnection()->query("INSERT INTO course_date (course_id, course_date) VALUES
      (1, '2017-01-01'),
      (1, '2017-10-31'),
      (2, '2017-05-25'),
      (2, '2017-05-26'),
      (2, '2017-05-27'),
      (3, '2017-01-01'),
      (3, '2018-12-10'),
      (3, '2017-04-01'),
      (3, '2019-03-12'),
      (4, '2017-12-24'),
      (4, '2018-12-24'),
      (4, '2019-12-24'),
      (5, '2017-06-30')
    ");
    
    // Applications
    $em->getConnection()->query("DROP TABLE IF EXISTS application");

    $em->getConnection()->query("CREATE TABLE application (
      id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
      course_id INTEGER NOT NULL,
      date_id INTEGER NOT NULL,
      company_name VARCHAR (64) NOT NULL,
      company_phone VARCHAR (64) NOT NULL,
      company_email VARCHAR (64) NOT NULL
    )");

    // Employees
    $em->getConnection()->query("DROP TABLE IF EXISTS participant");

    $em->getConnection()->query("CREATE TABLE participant (
      id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
      application_id INTEGER NOT NULL,
      participant_name VARCHAR (64) NOT NULL,
      participant_phone VARCHAR (64) NOT NULL,
      participant_email VARCHAR (64) NOT NULL
    )");
    
  echo 'DB tables created!';
}




