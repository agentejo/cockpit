<?php

// API

$app->bind("/api/forms/submit/:form", function($params) use($app) {

    $form = $params["form"];

    // Security check

    if ($formhash = $this->param("__csrf", false)) {

        if ($formhash != $this->hash($form)) {
            return false;
        }

    } else {
        return false;
    }

    $frm = $this->db->findOne("common/forms", ["name"=>$form]);

    if (!$frm) {
        return false;
    }

    if ($formdata = $this->param("form", false)) {

        // custom form validation
        if ($this->path("custom:forms/{$form}.php") && false===include($this->path("custom:forms/{$form}.php"))) {
            return false;
        }

        if(isset($frm["email"])) {

            $emails          = array_map('trim', explode(',', $frm['email']));
            $filtered_emails = [];

            foreach($emails as $to){

                // Validate each email address individually, push if valid
                if(filter_var($to, FILTER_VALIDATE_EMAIL)){
                    $filtered_emails[] = $to;
                }
            }

            if (count($filtered_emails)) {

                $frm['email'] = implode(',', $filtered_emails);

                $body = [];

                foreach ($formdata as $key => $value) {
                    $body[] = "<b>{$key}:</b>\n<br>";
                    $body[] = (is_string($value) ? $value:json_encode($value))."\n<br>";
                }

                $this->mailer->mail($frm["email"], $this->param("__mailsubject", "New form data for: ".$form), implode("\n<br>", $body));
            }
        }

        if (isset($frm["entry"]) && $frm["entry"]) {

            $collection = "form".$frm["_id"];
            $entry      = ["data" => $formdata, "created"=>time()];
            $this->db->insert("forms/{$collection}", $entry);
        }

        return json_encode($formdata);

    } else {
        return "false";
    }

});

$this->module("forms")->extend([

    "form" => function($name, $options = []) use($app) {

        $options = array_merge(array(
            "id"    => uniqid("form"),
            "class" => "",
            "csrf"  => $app->hash($name)
        ), $options);

        $app->renderView("forms:views/api/form.php", compact('name', 'options'));
    },

    "collectionById" => function($formId) use($app) {

        $entrydb = "form{$formId}";

        return $app->db->getCollection("forms/{$entrydb}");
    },

    "entries" => function($name) use($app) {

        $frm = $app->db->findOne("common/forms", ["name"=>$name]);

        if (!$frm) {
            return false;
        }

        $entrydb = "form".$frm["_id"];

        return $app->db->getCollection("forms/{$entrydb}");
    }
]);


if (!function_exists('form')) {

    function form($name, $options = []) {
        cockpit("forms")->form($name, $options);
    }
}

// ADMIN

if(COCKPIT_ADMIN && !COCKPIT_REST) {

    $app->on("admin.init", function() {

        if(!$this->module("auth")->hasaccess("Forms", ['manage.forms', 'manage.entries'])) return;

        $this->bindClass("Forms\\Controller\\Forms", "forms");
        $this->bindClass("Forms\\Controller\\Api", "api/forms");

        $this("admin")->menu("top", [
            "url"    => $this->routeUrl("/forms"),
            "label"  => '<i class="uk-icon-inbox"></i>',
            "title"  => $this("i18n")->get("Forms"),
            "active" => (strpos($this["route"], '/forms') === 0)
        ], 5);

        // handle global search request
        $this->on("cockpit.globalsearch", function($search, $list) {

            foreach ($this->db->find("common/forms") as $f) {
                if(stripos($f["name"], $search)!==false){
                    $list[] = [
                        "title" => '<i class="uk-icon-inbox"></i> '.$f["name"],
                        "url"   => $this->routeUrl('/forms/form/'.$f["_id"])
                    ];
                }
            }
        });
    });

    $app->on("admin.dashboard.aside", function() {

        if(!$this->module("auth")->hasaccess("Forms", ['manage.forms', 'manage.entries'])) return;

        $title = $this("i18n")->get("Forms");
        $badge = $this->db->getCollection("common/forms")->count();
        $forms = $this->db->find("common/forms", ["limit"=> 3, "sort"=>["created"=>-1] ])->toArray();

        $this->renderView("forms:views/dashboard.php with cockpit:views/layouts/dashboard.widget.php", compact('title', 'badge', 'forms'));
    });

    // acl
    $app("acl")->addResource("Forms", ['manage.forms', 'manage.entries']);
}