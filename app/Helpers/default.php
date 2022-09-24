<?php
function grade($nilai)
{
    if ($nilai < 35)
        $g = "E";
    elseif ($nilai < 50)
        $g = "D";
    elseif ($nilai < 60)
        $g = "C";
    elseif ($nilai < 65)
        $g = "BC";
    elseif ($nilai < 75)
        $g = "B";
    elseif ($nilai < 80)
        $g = "AB";
    else
        $g = "A";
    return $g;
}

function onlyNumber($string)
{
    return preg_replace('/[^0-9]/', '', $string);
}

function splitnop($nop = 0)
{
    $res = onlyNumber($nop);
    // $res = str_replace('.', '', $nop);
    $result['kd_propinsi'] = substr($res, 0, 2) ?? '';
    $result['kd_dati2'] = substr($res, 2, 2) ?? '';
    $result['kd_kecamatan'] = substr($res, 4, 3) ?? '';
    $result['kd_kelurahan'] = substr($res, 7, 3) ?? '';
    $result['kd_blok'] = substr($res, 10, 3) ?? '';
    $result['no_urut'] = substr($res, 13, 4) ?? '';
    $result['kd_jns_op'] = substr($res, 17, 1) ?? '';
    return $result;
}
