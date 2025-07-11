<?php
/*
* Sidebar
*/
$menu_top = [];
$menu_bottom = [];
$top_menu_groups = [];
$bottom_menu_groups = [];

$module_paths = get_module_paths();
$settings_data = array();
$configs = [];

if (!empty($module_paths)) {
    if (!empty($module_paths)) {
        foreach ($module_paths as $key => $module_path) {
            $config_path = $module_path . "/Config.php";
            $config = include $config_path;
            // 关键修改：检查是否包含隐藏参数
            if (isset($config['hidden']) && $config['hidden'] === true) {
                continue;
            }
            $configs[] = $config;
        }
    }
}

if (!empty($configs)) {

    $sidebar_icon_color = get_option("sidebar_icon_color", 0);
    $site_icon_color = get_option("site_icon_color", "#006dff");

    $menus = [];
    foreach ($configs as $config) {
        if (isset($config['menu']) && $config['menu']) {
            if (isset($config['platform']) && $config['platform'] > 0) {
                if ($config['platform'] == $platform) {
                    $config['menu']['platform'] = isset($config['platform']) ? $config['platform'] : false;
                    $config['menu']['id'] = isset($config['id']) ? $config['id'] : false;
                    $config['menu']['icon'] = isset($config['icon']) ? $config['icon'] : false;
                    $config['menu']['parent'] = isset($config['parent']) ? $config['parent'] : false;
                    $config['menu']['parent'] = isset($config['parent']) ? $config['parent'] : false;

                    if ($sidebar_icon_color && $site_icon_color != "") {
                        $config['menu']['color'] = $site_icon_color;
                    } else {
                        $config['menu']['color'] = isset($config['color']) ? $config['color'] : "#006dff";
                    }

                    $config['menu']['role'] = isset($config['role']) ? $config['role'] : false;
                    $menus[] = $config['menu'];
                }
            } else {
                $config['menu']['platform'] = isset($config['platform']) ? $config['platform'] : false;
                $config['menu']['id'] = isset($config['id']) ? $config['id'] : false;
                $config['menu']['icon'] = isset($config['icon']) ? $config['icon'] : false;
                $config['menu']['parent'] = isset($config['parent']) ? $config['parent'] : false;

                if ($sidebar_icon_color && $site_icon_color != "") {
                    $config['menu']['color'] = $site_icon_color;
                } else {
                    $config['menu']['color'] = isset($config['color']) ? $config['color'] : "#006dff";
                }

                $config['menu']['role'] = isset($config['role']) ? $config['role'] : false;
                $menus[] = $config['menu'];
            }

        }
    }

    if (!empty ($menus)) {

        foreach ($menus as $menu) {

            if (isset($menu['type']) && $menu['type'] == 'bottom') {
                $menu_bottom[] = $menu;
            } else if (isset($menu['type']) && $menu['type'] == 'top') {
                $menu_top[] = $menu;
            }

        }

    }

    if (count($menu_top) > 2) {
        usort($menu_top, function ($a, $b) {
            return $a['tab'] <=> $b['tab'];
        });
    }

    if (count($menu_bottom) > 2) {
        usort($menu_bottom, function ($a, $b) {
            return $a['tab'] <=> $b['tab'];
        });
    }

    //TOP TAB
    $top_tabs = [];
    foreach ($menu_top as $row) {
        $tab = $row['tab'];
        unset($row['tab']);
        $top_tabs[$tab][] = $row;
    }

    $top_tab_groups = [];
    foreach ($top_tabs as $key => $tab) {

        usort($tab, function ($a, $b) {
            return $a['position'] <=> $b['position'];
        });

        $tab = array_reverse($tab);
        $group = [];
        foreach ($tab as $menu) {

            $id = $menu['id'];
            if (permission($id) && (!isset($menu['role']) || (isset($menu['role']) && !$menu['role']))) {
                $group[$id]['id'] = $menu['id'];
                $group[$id]['name'] = $menu['name'];
                $group[$id]['icon'] = $menu['icon'];
                $group[$id]['color'] = $menu['color'];
                $group[$id]['custom'] = isset($menu['custom']) ? $menu['custom'] : false;
                if (isset($menu['parent'])) {
                    $group[$id]['parent'] = $menu['parent'];
                }


                if (isset($menu['sub_menu'])) {
                    if (permission($menu['sub_menu']['id']) && (!isset($menu['role']) || (isset($menu['role']) && !$menu['role']))) {
                        $group[$id]['sub_menu'][] = $menu['sub_menu'];
                    }
                }
            }

            if (role($id) && isset($menu['role']) && $menu['role']) {
                $group[$id]['id'] = $menu['id'];
                $group[$id]['name'] = $menu['name'];
                $group[$id]['icon'] = $menu['icon'];
                $group[$id]['color'] = $menu['color'];
                $group[$id]['custom'] = isset($menu['custom']) ? $menu['custom'] : false;

                if (isset($menu['sub_menu'])) {
                    if (role($menu['sub_menu']['id']) && isset($menu['role']) && $menu['role']) {
                        $group[$id]['sub_menu'][] = $menu['sub_menu'];
                    }
                }
            }

        }

        $top_tab_groups[$key] = $group;
    }

    foreach ($top_tab_groups as $tab => $data) {

        foreach ($data as $main => $row) {

            if (isset($row['sub_menu'])) {
                usort($row['sub_menu'], function ($a, $b) {
                    return $a['position'] <=> $b['position'];
                });

                $row['sub_menu'] = array_reverse($row['sub_menu']);
                $top_menu_groups[$tab][$main] = $row;
            } else {
                $top_menu_groups[$tab][$main] = $row;
            }

        }

    }

    //BOTTOM TAB
    $bottom_tabs = [];
    foreach ($menu_bottom as $row) {
        $tab = $row['tab'];
        unset($row['tab']);
        $bottom_tabs[$tab][] = $row;
    }

    $bottom_tab_groups = [];
    foreach ($bottom_tabs as $key => $tab) {

        usort($tab, function ($a, $b) {
            return $a['position'] <=> $b['position'];
        });

        $tab = array_reverse($tab);

        $group = [];
        foreach ($tab as $menu) {

            $id = $menu['id'];
            if (permission($id) && (!isset($menu['role']) || (isset($menu['role']) && $menu['role']))) {
                $group[$id]['id'] = $menu['id'];
                $group[$id]['name'] = $menu['name'];
                $group[$id]['icon'] = $menu['icon'];
                $group[$id]['color'] = $menu['color'];

                if (isset($menu['sub_menu'])) {
                    if (permission($menu['sub_menu']['id']) && (!isset($menu['role']) || (isset($menu['role']) && !$menu['role']))) {
                        $group[$id]['sub_menu'][] = $menu['sub_menu'];
                    }
                }
            }

            if (role($id) && isset($menu['role']) && $menu['role']) {
                $group[$id]['id'] = $menu['id'];
                $group[$id]['name'] = $menu['name'];
                $group[$id]['icon'] = $menu['icon'];
                $group[$id]['color'] = $menu['color'];

                if (isset($menu['sub_menu'])) {
                    if (role($menu['sub_menu']['id']) && isset($menu['role']) && $menu['role']) {
                        $group[$id]['sub_menu'][] = $menu['sub_menu'];
                    }
                }
            }
        }

        $bottom_tab_groups[$key] = $group;
    }

    foreach ($bottom_tab_groups as $tab => $data) {

        foreach ($data as $main => $row) {

            if (isset($row['sub_menu'])) {
                usort($row['sub_menu'], function ($a, $b) {
                    return $a['position'] <= $b['position'];
                });

                $bottom_menu_groups[$tab][$main] = $row;
            } else {
                $bottom_menu_groups[$tab][$main] = $row;
            }

        }

    }

}


$request->top_sidebar = $top_menu_groups;
$request->bottom_sidebar = $bottom_menu_groups;

if (post("set_theme")) {
    $module_paths = get_module_paths();
    $list_items = [];
    if (!empty($module_paths)) {
        if (!empty($module_paths)) {
            foreach ($module_paths as $key => $module_path) {

                if (stripos($module_path, "inc/themes/frontend/")) {
                    $config_path = $module_path . "/Config.php";
                    $config_item = include $config_path;

                    if (strtolower($config_item["id"]) == strtolower(post("set_theme"))) {
                        set_session(["frontend_template" => post("set_theme")]);
                    }
                }
            }
        }
    }
}

if (post("remove_theme")) {
    remove_session(["frontend_template"]);
}