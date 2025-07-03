<?php 
//Get Settings
if(!function_exists("get_user_data")){
    function get_user_data($key, $value = "", $uid = 0){
        if( USER_INFO ){
            $uid = USER_IDS;
            $user = unserialize(USER_INFO);
            $data = $user->data;
        }else{
            if($uid != 0){
                $user = db_get("data", TB_USERS, "id = '".$uid."' ");
                if($user){
                    $data = $user->data;
                }else{
                    $data = false;
                }
            }else{
                $data = false;
            }
        }

        if($data){
            try {
                $option = json_decode($data);
            } catch (\Exception $e) {
                $option = [];
            }
        }else{
            $option = [];
        }
        
        if(is_array($option) || is_object($option)){
            $option = (array)$option;

            if( isset($option[$key]) ){
                return $option[$key];
            }else{
                $option[$key] = $value;
                db_update(TB_USERS, ["data" => json_encode($option)], [ "id" => $uid ] );
                return $value;
            }
        }else{ 
            $option = json_encode(array($key => $value));
            db_update(TB_USERS, ["data" => $option ], [ "id" => $uid ] );
            return $value;
        }
    }
}

//Update Settingz
if(!function_exists("update_user_data")){
    function update_user_data($key, $value, $uid = 0){
        if( get_session("uid")){
            $uid = USER_ID;
        }

        if($uid != 0){
            $data = db_get("data", TB_USERS, "id = '".$uid."' ")->data;
            $option = json_decode($data);
            if(is_array($option) || is_object($option)){
                $option = (array)$option;
                if( isset($option[$key]) ){
                    $option[$key] = $value;
                    db_update(TB_USERS, [ "data" => json_encode($option) ], [ "id" => $uid ] );
                    return true;
                }
            }
        }
        return false;
    }
}

//Get Team Settings
if(!function_exists("get_team_data")){
    function get_team_data($key, $value = "", $team_id = 0){
        if( TEAM_IDS ){
            $team_id = TEAM_ID;
            if(!TEAM_INFO ){
                return false;
            }

            $team = unserialize(TEAM_INFO);
            $data = $team->data;
        }else{
            if($team_id){
                $data = db_get("data", "sp_team", "id = '".$team_id."' ")->data;
            }
        }

        if($team_id != 0){
            if(!empty($data)){
                $option = json_decode($data);
            }else{
                $option = false;
            }

            if(is_array($option) || is_object($option)){
                $option = (array)$option;

                if( isset($option[$key]) ){
                    return $option[$key];
                }else{
                    $option[$key] = $value;
                    db_update("sp_team", ["data" => json_encode($option)], [ "id" => $team_id ] );
                    return $value;
                }
            }else{ 
                $option = json_encode(array($key => $value));
                db_update("sp_team", ["data" => $option ], [ "id" => $team_id ] );
                return $value;
            }
        }
    }
}

//Update Team Setting
if(!function_exists("update_team_data")){
    function update_team_data($key, $value, $team_id = 0){
        if( get_session("team_id") && $team_id == 0){
            $team_id = get_team("id");
        }

        if($team_id != 0){
            $data = db_get("data", "sp_team", "id = '".$team_id."' ")->data;
            if($data != ""){
                try {
                    $option = json_decode($data);
                } catch (\Exception $e) {
                    $option = [];
                }
            }else{
                $option = [];
            }
            if(is_array($option) || is_object($option)){
                $option = (array)$option;
                if( isset($option[$key]) ){
                    $option[$key] = $value;
                    db_update("sp_team", [ "data" => json_encode($option) ], [ "id" => $team_id ] );
                    return true;
                }
            }
        }
        return false;
    }
}

if(!function_exists("get_user")){
    function get_user( $field = "ids", $uid = 0){
        if($uid == 0){
            if(USER_INFO){
                $user = unserialize(USER_INFO);
            }else{
                $user = false;
            }

        }else{
            $uid = db_get("ids", TB_USERS, "id = '{$uid}'")->ids;
            $user = db_get("*", TB_USERS, "ids = '".$uid."'");
        }

        

        if($user && isset($user->$field)){
            return $user->$field;
        }

        return false;
    }
}

if(!function_exists("get_team")){
    function get_team( $field = "ids", $tid = 0){
        if($tid == 0){
            if(USER_INFO){
                $team = unserialize(TEAM_INFO);
            }else{
                $team = false;
            }
            
        }else{
            $team = db_get("*", TB_TEAM, ['id' => $tid]);
        }
        
        if($team && isset($team->$field)){
            return $team->$field;
        }

        return false;
    }
}

/*Permissions*/
if(!function_exists('user_roles')){
    function user_roles($type, $field){
        $request = \Config\Services::request();
        $permissions =  $request->user_roles;

        if( !$permissions ) return false;

        switch ($type) {
            case 'checkbox':
                
                if( isset($permissions->$field) ) return $permissions->$field;

                break;

            case 'radio':
                
                if( isset($permissions->$field) ) return $permissions->$field;

                break;

            case 'selected':
                
                if( isset($permissions->$field) ) return $permissions->$field;

                break;
            
            default:
                
                if( isset($permissions->$field) ) return $permissions->$field;

                break;
        }

        return false;
    }
}

if(!function_exists('role')){
    function role($name, $uid=""){
        if($uid==""){
            $uid = get_user('id');
            $role = get_user("role");
            $is_admin = get_user("is_admin");
            if(!get_session("team_id") || !get_session("uid")){
                return false;
            }
            $roles = false;
            if($role){
                $roles = db_get("*", TB_ROLES, ["id" => $role]);
            }
            
            if(!USER_INFO){
                return false;
            }

            $user = unserialize(USER_INFO);
        }else{
            $role = 0;
            $is_admin = 0;
            $roles = false;
            $user = db_get("expiration_date", TB_USERS, ["id" => $uid]);
        }

        $expiration_date = $user->expiration_date;
        if( $user->expiration_date < time() && !$is_admin ){
            return false;
        }

        $permissions = false;
        if($roles){
            $permissions = json_decode($roles->permissions, true);
        }


        if($permissions){
            if( isset( $permissions[$name] ) ){
                return $permissions[$name];
            }
        }

        if($is_admin){
            return true;
        }

        return false;
    }
}
