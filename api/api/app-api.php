<?php
require_once 'apiException.Class.php';
require_once 'API.Class.php';

class APP extends API
{
    public $data = "";

    /**
     * ORM EntityManager
     */
    public $em = NULL;

    public function __construct($request, $origin) {
        parent::__construct($request);
        $this->dbConnect(); // Initiate Database connection
    }

     /*
     * Connect to Database
     */
    private function dbConnect() {
        if( !(defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS') && defined('DB_HOST')) ) {
            return;
        }
        $this->em = new ORM\EntityManager([
            ORM\EntityManager::OPT_CONNECTION => ['mysql', DB_NAME, DB_USER, DB_PASS, DB_HOST]
        ]);
    }

    protected function GET_courses($args){
        $courses = $this->em->fetch(Course::class)->all();
        return array_map(['Course', 'format'], $courses );
    }

    protected function GET_applications() {
        $apps = $this->em->fetch(Application::class)->all();
        return array_map(['Application', 'format'], $apps );
    }

    protected function POST_applications() {
        return Application::add_new( $this->request );
    }

}
