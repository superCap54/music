<?php
namespace Core\Facebook_pages\Models;
use CodeIgniter\Model;

class Facebook_pagesModel extends Model
{
	public function __construct(){
        $this->config = include realpath( __DIR__."/../Config.php" );
    }
    
	public function block_accounts($path = ""){
        $team_id = get_team("id");
        $accounts = db_fetch("*", TB_ACCOUNTS, "social_network = 'facebook' AND category = 'page' AND team_id = '{$team_id}'");
        $user_proxy = db_fetch("id,team_id", TB_ACCOUNTS, "social_network = 'facebook' AND category = 'page' AND login_type != 1");

        return [
            "can_use_proxy" => $user_proxy,
        	"button" => view( 'Core\Facebook_pages\Views\button', [ 'config' => $this->config ] ),
        	"content" => view( 'Core\Facebook_pages\Views\content', [ 'config' => $this->config, 'accounts' => $accounts ] )
        ];
    }

    public function block_social_settings($path = ""){
        return [
            "menu" => view( 'Core\Facebook_pages\Views\settings\menu', [ 'config' => $this->config ] ),
            "content" => view( 'Core\Facebook_pages\Views\settings\content', [ 'config' => $this->config ] )
        ];
    }
}
