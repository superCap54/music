<?php
namespace Core\Facebook_post\Controllers;

class Facebook_post extends \CodeIgniter\Controller
{
    public function __construct(){
        $this->config = parse_config( include realpath( __DIR__."/../Config.php" ) );
    }

    public function save_facebook_story_configure(){
        $fb_story_bg = post("fb_story_bg");
        $fb_story_title_bg = post("fb_story_title_bg");
        $fb_story_bg_opacity = (int)post("fb_story_bg_opacity");
        $fb_story_title_color = post("fb_story_title_color");
        $fb_story_title_top = (int)post("fb_story_title_top");
        $fb_story_title_left = (int)post("fb_story_title_left");
        $fb_story_title_width = (int)post("fb_story_title_width");
        $fb_story_title_font_size = (int)post("fb_story_title_font_size");
        $fb_story_title_font_family = post("fb_story_title_font_family");
        $fb_story_title_text_direction = (int)post("fb_story_title_text_direction");

        update_team_data("fb_story_bg", $fb_story_bg);
        update_team_data("fb_story_title_bg", $fb_story_title_bg);
        update_team_data("fb_story_bg_opacity", $fb_story_bg_opacity);
        update_team_data("fb_story_bg_opacity", $fb_story_bg_opacity);
        update_team_data("fb_story_title_color", $fb_story_title_color);
        update_team_data("fb_story_title_top", $fb_story_title_top);
        update_team_data("fb_story_title_left", $fb_story_title_left);
        update_team_data("fb_story_title_width", $fb_story_title_width);
        update_team_data("fb_story_title_font_size", $fb_story_title_font_size);
        update_team_data("fb_story_title_font_family", $fb_story_title_font_family);
        update_team_data("fb_story_title_text_direction", $fb_story_title_text_direction);

        ms([
            "status" => "success",
            "message" => __("Success")
        ]);
    }
}