<?
require_once('JS.php');

// TODO: implement link_to function

class Tags {

    public static function check_box($name, $value = 1, $checked = false, $options = array()) {
        $html_options = array_replace(array("type" => "checkbox", "name" => $name, "id" => self::sanitize_to_id($name), "value" => $value), $options);
        if ($checked) $html_options["checked"] = "checked";
        return self::tag('input', $html_options);
    }

    public static function field_set($legend = null, $content=null, $options = null) {
        return self::content_tag('fieldset', $legend ? self::content_tag('legend', $legend).$content : $content, $options, false);
    }

    public static function file_field($name, $options = array()) {
        return self::text_field($name, null, array_replace(array("type" => "file"), $options));
    }

    public static function form($url = null, $options = array()) {
        if($options["multipart"]) {
            $options["enctype"] = "multipart/form-data";
            unset($options["multipart"]);
        }
        return self::tag('form', array_replace(array("action" => $url, "method" => "post"), $options), true);
    }

    public static function hidden_field($name, $value = null, $options = array()) {
        return self::text_field($name, $value, array_replace(array("type" => "hidden"), $options));
    }

    public static function image_submit($source, $options = array()) {
        if($options["confirm"]) {
            $options["onclick"].= 'return '.JS::confirm_function($options["confirm"]);
            unset($options["confirm"]);
        }
        return self::tag('input', array_replace(array("type" => "image", "src" => $source), $options));
    }

    public static function javascript_include($source) {
        return self::content_tag('script', null, array("type" => "text/javascript", "src" => $source));
    }

    public static function javascript_include_all($customJavascripts = array()) {
        $str = '';
        if (!is_array($customJavascripts)) {
            $customJavascripts = $customJavascripts ? array($customJavascripts) : array();
        }
        array_unshift($customJavascripts, "/skin/js/jquery.js", "/skin/js/main.js");
        foreach (array_unique($customJavascripts) as $js) {
            $str .= self::javascript_include($js)."\n";
        }
        return $str;
    }

    public static function label($name, $text = null, $options = array()){
        return self::content_tag('label', $text ? $text : self::humanize($name), array_replace(array("for" => self::sanitize_to_id($name)), $options));
    }

    public static function link_to($name, $url = "" , $options = array()) {
        if($options["confirm"]) {
            $options["onclick"].= 'return '.JS::confirm_function($options["confirm"]);
            unset($options["confirm"]);
        }
        return self::content_tag('a', $name, array_replace(array("href" => $url), $options));
    }

    public static function link_to_if($name, $url = "" , $condition = true, $options = array()) {
        if(!$condition) {
            return "<span>$name</span>";
        }
        return self::link_to($name, $url, $options);
    }

    public static function password_field($name = "password", $value = null, $options = array()) {
        return self::text_field($name, $value, array_replace(array("type" => "password"), $options));
    }

    public static function radio_button($name, $value, $checked = false, $options = array()) {
        $pretty_tag_value = preg_replace("/\s/", "_", $value);
        $pretty_tag_value = strtolower(preg_replace("/(?!-)\W/", "", $pretty_tag_value));
        $html_options = array("type" => "radio", "name" => $name, "id" => self::sanitize_to_id($name)."_".$pretty_tag_value, "value" => $value);
        $html_options = array_replace($html_options, $options);
        if ($checked) $html_options["checked"] = "checked";
        return self::tag('input', $html_options);
    }

    public static function select($name, $option_tags = null, $options = array()) {
        $html_name = ($options['multiple'] and substr($name, -2, 2) <> "[]") ? $name."[]" : $name;
        return self::content_tag('select', $option_tags, array_replace(array("name" => $html_name, "id" => self::sanitize_to_id($name)), $options), false);
    }

    public static function stylesheet_include($source) {
        return self::tag('link', array("rel" => "stylesheet", "type" => "text/css", "href" => $source));
    }

    public static function stylesheet_include_all($customStylesheets = array()) {
        $str = '';
        if (!is_array($customStylesheets)) {
            $customStylesheets = $customStylesheets ? array($customStylesheets) : array();
        }
        array_unshift($customStylesheets, "/skin/css/main.css");
        foreach (array_unique($customStylesheets) as $css) {
            $str .= self::stylesheet_include($css)."\n";
        }
        return $str;
    }

    public static function submit($value = "Save changes", $options = array()) {
        if($options["confirm"]) {
            $options["onclick"].= 'return '.JS::confirm_function($options["confirm"]);
            unset($options["confirm"]);
        }
        return self::tag('input', array_replace(array("type" => "submit", "name" => "commit", "value" => $value), $options));
    }

    public static function text_area($name, $content = null, $options = array()) {
        $options = array_replace(array("name" => $name, "id" => self::sanitize_to_id($name)), $options);
        if(!defined($options['spellcheck']))  $options['spellcheck'] = "false";
        return self::content_tag('textarea', $content, $options);
    }

    public static function text_field($name, $value = null, $options = array()) {
        return self::tag('input', array_replace(array("type" => "text", "name" => $name, "id" => self::sanitize_to_id($name), "value" => $value, "autocomplete" => "off"), $options));
    }

    public static function tag($name, $options = null, $open = false, $escape = true) {
        $str = '<'.$name;
        if($options) $str.= self::tag_options($options, $escape);
        if(!$open) {$str.= '/';}
        $str.= '>';
        return $str;
    }

    public static function content_tag($name, $content = null, $options = null, $escape = true) {
        $str = '<'.$name;
        if($options) $str.= self::tag_options($options, $escape);
        if ($escape && $content) $content = htmlspecialchars($content);
        $str.= '>'.$content.'</'.$name.'>'."\n";
        return $str;
    }

    public static function tag_options($options, $escape = true) {
        $str = '';
        if (is_array($options)) {
            foreach ($options as $key => $value) {
                if ($escape) $value = htmlspecialchars($value);
                $str.= ' '.$key.'="'.$value.'"';
            }
        }
        return $str;
    }

    public static function sanitize_to_id($name) {
        return preg_replace("/[^-a-zA-Z0-9:.]/", "_", str_replace(']','',$name));
    }

    public static function humanize($name) {
        return ucfirst(preg_replace("/ id$/", '', preg_replace("/[^-a-zA-Z0-9:.]/", " ", str_replace(']','',$name))));
    }

}
?>

