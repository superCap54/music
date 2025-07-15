<?php
namespace Core\Musicdata\Controllers;

class Musicdata extends \CodeIgniter\Controller
{
    protected $db;

    public function __construct(){
        $this->config = parse_config( include realpath( __DIR__."/../Config.php" ) );
        $this->class_name = get_class_name($this);
        $this->model = new \Core\Musicdata\Models\DistrokidModel();
        $this->db = \Config\Database::connect();
    }






    public function ajax_list(){
        $total_items = $this->model->get_list(false);
        $result = $this->model->get_list(true);
        $data = [
            "result" => $result,
            "config" => $this->config
        ];
        ms( [
            "total_items" => $total_items,
            "data" => view('Core\Musicdata\Views\ajax_list', $data)
        ] );
    }

}