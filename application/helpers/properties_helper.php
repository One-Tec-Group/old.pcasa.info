<?php

defined('BASEPATH') or exit('No direct script access allowed');

function get_listing_view($property) {
    $point = 0;
    if (strlen($property->Property_Title) > 30 && strlen($property->Property_Title) <= 60) {
        $point++;
    }
    if (strlen($property->Property_Description) > 100 && strlen($property->Property_Description) <= 160) {
        $point++;
    }
    if ($property->Price != null && $property->Price != "") {
        $point++;
    }
    $precentage = (int) (($point * 100) / 3);
    $degree = (int) (($precentage * 360) / 100);
    $color = 'rgb(162, 236, 251)';
    if ($degree >= 0 && $degree <= 180) {
        $color = 'rgb(162, 236, 251)';
        $degree = $degree + 90;
    } else if ($degree > 180 && $degree <= 360) {
        $color = 'rgb(57, 180, 204)';
        $degree = $degree - 90;
    }

    return '<div align = "center" id = "activeBorder" class = "active-border" style = "
            background-image:
                linear-gradient(' . $degree . 'deg, transparent 50%, ' . $color . ' 50%),
                linear-gradient(90deg, rgb(162, 236, 251) 50%, transparent 50%);
                margin: 10px auto;">
            <div id = "circle" class = "circle">
                <span class = "prec" id = "prec"><b>' . $precentage . '</b>%</span>
            </div>
        </div>';
}
