<?php
namespace Collections\Controller;

class RestApi extends \LimeExtra\Controller {

    public function get($collection=null) {

        if (!$collection) {
            return false;
        }

        if (!$this->app->module('collections')->exists($collection)) {
            return false;
        }

        $options = [];

        if ($filter   = $this->param("filter", null))   $options["filter"] = $filter;
        if ($limit    = $this->param("limit", null))    $options["limit"] = intval($limit);
        if ($sort     = $this->param("sort", null))     $options["sort"] = $sort;
        if ($skip     = $this->param("skip", null))     $options["skip"] = intval($skip);
        if ($populate = $this->param("populate", null)) $options["populate"] = $populate;

        if (isset($options["sort"])) {
            foreach ($sort as $key => &$value) {
                $options["sort"][$key]= intval($value);
            }
        }

        $entries = $this->app->module('collections')->find($collection, $options);

        // return only entries array - due to legacy
        if ((boolean) $this->param("simple", false)) {
            return $entries;
        }

        $collection = $this->app->module('collections')->collection($collection);

        $fields = [];

        foreach ($collection["fields"] as $field) {

            $fields[$field["name"]] = [
                "name" => $field["name"],
                "type" => $field["type"],
                "localize" => $field["localize"],
                "options" => $field["options"],
            ];
        }

        return [
            "fields"   => $fields,
            "entries"  => $entries,
            "total"    => count($entries)
        ];

        return $entries;
    }
}
