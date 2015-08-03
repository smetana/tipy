<?
class Helper {
    public static $stripeFlag = true;

    // ---------------------------------------------------------------
    // table striper
    // ---------------------------------------------------------------
    public static function stripe($class1, $class2) {
        self::$stripeFlag = !self::$stripeFlag;
        if (self::$stripeFlag) {
            return $class1;
        }
        return $class2;
    }

    public static function stripeReload() {
        self::$stripeFlag = true;
    }

    public static function paginator($page, $total, $per_page, $linkPrefix = '', $arrowPrefix = '') {
        $prevDotsSetted = false;
        $nextDotsSetted = false;
        $total_pages = ceil($total/$per_page);
        $arrowPrefix = $arrowPrefix ? $arrowPrefix.'_' : '';
        $html = '';
        if ($total_pages == 1) {
            return $html;
        }
        $html .= '<small class="paginator" title="'.String::s('enter_desired_page_number').'">';
        for( $i = 1; $i <= $total_pages; $i++) {
            if ($i < $page) {
                if ($page < 6) {
                    $html .= '<span><a href="#' . $linkPrefix . $i . '" class="page">' . $i . '</a>,</span>';
                } else if($i == 1 or $i == ($page-1) or $i == ($page-2)) {
                    $html .= '<span><a href="#' . $linkPrefix . $i . '" class="page">' . $i . '</a>,</span>';
                } else if ($total_pages <= 10) {
                    $html .= '<span><a href="#' . $linkPrefix . $i . '" class="page">' . $i . '</a>,</span>';
                } else if(!$prevDotsSetted) {
                    $html .= '<span>...,</span>';
                    $prevDotsSetted = true;
                }
            } else if ($i > $page) {
                if ($page > $total_pages - 5) {
                    $html .= '<span><a href="#' . $linkPrefix . $i . '" class="page">' . $i . '</a>,</span>';
                } else if($i == $total_pages or $i == ($page+1) or $i == ($page+2)) {
                    $html .= '<span><a href="#' . $linkPrefix . $i . '" class="page">' . $i . '</a>,</span>';
                } else if ($total_pages <=10) {
                    $html .= '<span><a href="#' . $linkPrefix . $i . '" class="page">' . $i . '</a>,</span>';
                } else if(!$nextDotsSetted) {
                    $html .= '<span>...,</span>';
                    $nextDotsSetted = true;
                }
            } else {
                $html .= '<input type="text" id="current_page" value="' . $i . '"/>,';
            }
        }
        $html = preg_replace('/,(<\/span>)?$/', '$1', $html);
        $html .= '</small>';
        if ($total_pages <= 10) return $html;
        $prefix = "";
        $postfix = "";
        if ($page < 5) {
            for($i = 0; $i < 5 - $page; $i++) {
                $prefix .= '<span></span>';
            }
        } else if ($page > $total_pages - 4) {
            for($i = 0; $i < 4 - $total_pages + $page; $i++) {
                $postfix .= '<span></span>';
            }
        }
        return $prefix.$html.$postfix;
    }

    public static function browser() {
        $browser = null;
        $ua = getenv('HTTP_USER_AGENT');
        if (!$ua) return $browser;
        if (preg_match("/Symbian/", $ua)) {
            $browser = "Mobile";
        } elseif (preg_match("/Opera/", $ua)) {
            $browser = "Opera";
        } elseif (preg_match("/KHTML/", $ua)) {
            $browser = "KHTML";
        } elseif (preg_match("/Gecko/", $ua)) {
            $browser = "Gecko";
        } elseif (preg_match("/MSIE/", $ua)) {
            $browser = "MSIE";
        }
        return $browser;
    }

    public static function detoxify($text) {
        $text=stripslashes($text);
        $text=preg_replace( "/'/", "&#8217;", $text);
        $text=preg_replace( "/<br>/", "\n", $text);
        $text=preg_replace( "/<br\/>/", "\n", $text);
        $text=preg_replace( "/</", "&lt;", $text);
        $text=preg_replace( "/>/", "&gt;", $text);
        $text=preg_replace( "/\r/", "", $text);
        return $text;
    }

    // sort by obj->compareCallback() property
    public static function order($objects) {
        usort($objects, array("Helper", "compareObjects"));
        return $objects;
    }

    // compare two objects by model defined sort method value
    public static function compareObjects($a, $b) {
        $a = String::s($a->compareCallback());
        $b = String::s($b->compareCallback());
        // move words starting with with .. to the end
        if (substr($a, 0, 2) == '..') return 1000;
        if (substr($b, 0, 2) == '..') return -1000;
        if ($a == $b) {
            return 0;
        }
        return strcmp($a, $b);
    }

    public static function plural($n, $one = 'число', $couple = 'числа', $many = 'чисел') {
        return $n%10==1&&$n%100!=11?$one:($n%10>=2&&$n%10<=4&&($n%100<10||$n%100>=20)?$couple:$many);
    }

    public static function jsNewline($txt) {
        return str_replace(array("\n", "\r", '"'), array("\\n", "\\r", '\"'), $txt);
    }

}
?>
