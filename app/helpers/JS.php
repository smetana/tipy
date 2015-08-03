<?
class JS {
    public static function confirm_function($confirm = 'O, really?') {
        return "confirm('".self::escape_javascript($confirm)."');";
    }

    public static function escape_javascript($txt) {
        return preg_replace("/\r?\n/", "\\n", addslashes($txt));
    }
}
?>
