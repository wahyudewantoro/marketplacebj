<?php

namespace App\Helpers;

use App\UserService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Pajak
{

    public static function  tagihan($nop, $tahun, $tanggal)
    {
        if ($tanggal == '') {
            $tanggal = date('Ymd');
        } else {
            $tanggal = date('Ymd', strtotime($tanggal));
        }

        $kd_propinsi = $nop['kd_propinsi'];
        $kd_dati2 = $nop['kd_dati2'];
        $kd_kecamatan = $nop['kd_kecamatan'];
        $kd_kelurahan = $nop['kd_kelurahan'];
        $kd_blok = $nop['kd_blok'];
        $no_urut = $nop['no_urut'];
        $kd_jns_op = $nop['kd_jns_op'];

        if ($kd_blok <> '999') {
            // nop biasa
            $sppt = DB::select(DB::raw("SELECT * from (select 
            status_pembayaran_sppt,nm_wp_sppt,kelurahan_wp_sppt,thn_pajak_sppt tahun,pbb_yg_harus_dibayar_sppt pokok,
                CASE
                    WHEN (SELECT COUNT (1)
                            FROM pemutihan_pajak
                            WHERE     status = '1'
                                AND tgl_mulai <= TRUNC (to_date('" . $tanggal . "','yyyymmdd'))
                                AND tgl_selesai >= TRUNC (to_date('" . $tanggal . "','yyyymmdd'))) > 0
                    THEN
                        0
                    ELSE
                        case when status_pembayaran_sppt<>2 then 
                        get_denda (
                        PBB_YG_HARUS_DIBAYAR_SPPT,
                        tgl_jatuh_tempo_sppt,
                        to_date('" . $tanggal . "','yyyymmdd'))  
                        else
                            denda
                        end 
                    end  
                        
                        as  Denda,
                                                    pbb_yg_harus_dibayar_sppt + 
                                                    CASE
                                                    WHEN (SELECT COUNT (1)
                                                            FROM pemutihan_pajak
                                                            WHERE     status = '1'
                                                                AND tgl_mulai <= TRUNC (to_date('" . $tanggal . "','yyyymmdd'))
                                                                AND tgl_selesai >= TRUNC (to_date('" . $tanggal . "','yyyymmdd'))) > 0
                                                    THEN
                                                        0
                                                    ELSE
                                                        case when status_pembayaran_sppt<>2 then 
                                                        get_denda (
                                                        PBB_YG_HARUS_DIBAYAR_SPPT,
                                                        tgl_jatuh_tempo_sppt,
                                                        to_date('" . $tanggal . "','yyyymmdd'))  
                                                        else
                                                            denda
                                                        end 
                                                    end    as total
                                                from sppt_oltp
                                                where thn_pajak_sppt <=$tahun and kd_propinsi ='$kd_propinsi'
                                                and kd_dati2='$kd_dati2'
                                                and kd_kecamatan='$kd_kecamatan'
                                                and kd_kelurahan='$kd_kelurahan'
                                                and kd_blok='$kd_blok'
                                                and no_urut='$no_urut'
                                                and kd_jns_op='$kd_jns_op'
                                                and data_billing_id is null
                                                order by thn_pajak_sppt desc) where rownum <=11
                "));
        } else {
            // kobil
            $sppt = DB::select(DB::raw("SELECT *
            FROM (  SELECT KD_STATUS STATUS_PEMBAYARAN_SPPT,
                           NAMA_WP NM_WP_SPPT,
                           NAMA_KELURAHAN KELURAHAN_WP_SPPT,
                           DATA_BILLING.TAHUN_PAJAK TAHUN,
                           CASE
                              WHEN KD_STATUS = 1
                                            THEN
                                                1
                                            ELSE
                                                CASE
                                                    WHEN DATA_BILLING.expired_at >= SYSDATE THEN 1
                                                    ELSE 0
                                                END
                                        END
                                            show,
                                        SUM (POKOK) POKOK,
                                        CASE
                                            WHEN (SELECT COUNT (1)
                                                    FROM pemutihan_pajak
                                                    WHERE     status = '1'
                                                        AND tgl_mulai <= TRUNC (to_date('" . $tanggal . "','yyyymmdd'))
                                                        AND tgl_selesai >= TRUNC (to_date('" . $tanggal . "','yyyymmdd'))) > 0
                                            THEN
                                                0
                                            ELSE SUM (DENDA) end  DENDA,
                                        
                                            CASE
                                            WHEN (SELECT COUNT (1)
                                                    FROM pemutihan_pajak
                                                    WHERE     status = '1'
                                                        AND tgl_mulai <= TRUNC (to_date('" . $tanggal . "','yyyymmdd'))
                                                        AND tgl_selesai >= TRUNC (to_date('" . $tanggal . "','yyyymmdd'))) > 0
                                            THEN
                                                sum(pokok)
                                            ELSE
                                            SUM (TOTAL)
                           end
                                            TOTAL
                                    FROM DATA_BILLING DATA_BILLING
                                        JOIN BILLING_KOLEKTIF BILLING_KOLEKTIF
                                            ON BILLING_KOLEKTIF.DATA_BILLING_ID =
                                                    DATA_BILLING.DATA_BILLING_ID
                                    WHERE     DATA_BILLING.TAHUN_PAJAK = '$tahun'
                                    and DATA_BILLING.kd_propinsi ='$kd_propinsi'
                                    and DATA_BILLING.kd_dati2='$kd_dati2'
                                    and DATA_BILLING.kd_kecamatan='$kd_kecamatan'
                                    and DATA_BILLING.kd_kelurahan='$kd_kelurahan'
                                    and DATA_BILLING.kd_blok='$kd_blok'
                                    and DATA_BILLING.no_urut='$no_urut'
                                    and DATA_BILLING.kd_jns_op='$kd_jns_op'
                                        AND DATA_BILLING.deleted_at IS NULL
                                GROUP BY kd_status,
                                        nama_wp,
                                        nama_kelurahan,
                                        DATA_BILLING.tahun_pajak,
                                        DATA_BILLING.expired_at)
                        WHERE show = 1"));
        }
        return $sppt;
    }

    public static function  tagihanTotalSingle($nop, $tahun, $tanggal)
    {
        if ($tanggal == '') {
            $tanggal = date('Ymd');
        } else {
            $tanggal = date('Ymd', strtotime($tanggal));
        }

        $kd_propinsi = $nop['kd_propinsi'];
        $kd_dati2 = $nop['kd_dati2'];
        $kd_kecamatan = $nop['kd_kecamatan'];
        $kd_kelurahan = $nop['kd_kelurahan'];
        $kd_blok = $nop['kd_blok'];
        $no_urut = $nop['no_urut'];
        $kd_jns_op = $nop['kd_jns_op'];

        if ($kd_blok <> '999') {
            // nop biasa
            $sppt = DB::select(DB::raw("SELECT 
            status_pembayaran_sppt,nm_wp_sppt,kelurahan_wp_sppt,thn_pajak_sppt tahun,pbb_yg_harus_dibayar_sppt pokok,
            CASE
                    WHEN (SELECT COUNT (1)
                            FROM pemutihan_pajak
                            WHERE     status = '1'
                                AND tgl_mulai <= TRUNC (to_date('" . $tanggal . "','yyyymmdd'))
                                AND tgl_selesai >= TRUNC (to_date('" . $tanggal . "','yyyymmdd'))) > 0
                    THEN
                        0
                    ELSE
                        case when status_pembayaran_sppt<>2 then 
                        get_denda (
                        PBB_YG_HARUS_DIBAYAR_SPPT,
                        tgl_jatuh_tempo_sppt,
                        to_date('" . $tanggal . "','yyyymmdd'))  
                        else
                            denda
                        end 
                    end  
             as Denda,
                                                    pbb_yg_harus_dibayar_sppt + 
                                                    CASE
                    WHEN (SELECT COUNT (1)
                            FROM pemutihan_pajak
                            WHERE     status = '1'
                                AND tgl_mulai <= TRUNC (to_date('" . $tanggal . "','yyyymmdd'))
                                AND tgl_selesai >= TRUNC (to_date('" . $tanggal . "','yyyymmdd'))) > 0
                    THEN
                        0
                    ELSE
                        case when status_pembayaran_sppt<>2 then 
                        get_denda (
                        PBB_YG_HARUS_DIBAYAR_SPPT,
                        tgl_jatuh_tempo_sppt,
                        to_date('" . $tanggal . "','yyyymmdd'))  
                        else
                            denda
                        end 
                    end  
                                                    as total
                                                from sppt_oltp
                                                where thn_pajak_sppt =$tahun and kd_propinsi ='$kd_propinsi'
                                                and kd_dati2='$kd_dati2'
                                                and kd_kecamatan='$kd_kecamatan'
                                                and kd_kelurahan='$kd_kelurahan'
                                                and kd_blok='$kd_blok'
                                                and no_urut='$no_urut'
                                                and kd_jns_op='$kd_jns_op'
                                                and status_pembayaran_sppt in (0,2)
                                                and data_billing_id is null
                                                order by thn_pajak_sppt desc
                "));
        } else {
            // kobil
            $sppt = DB::select(DB::raw("SELECT *
            FROM (  SELECT KD_STATUS STATUS_PEMBAYARAN_SPPT,
                           NAMA_WP NM_WP_SPPT,
                           NAMA_KELURAHAN KELURAHAN_WP_SPPT,
                           DATA_BILLING.TAHUN_PAJAK TAHUN,
                           CASE
                              WHEN KD_STATUS = 1
                                            THEN
                                                1
                                            ELSE
                                                CASE
                                                    WHEN DATA_BILLING.expired_at >= SYSDATE THEN 1
                                                    ELSE 0
                                                END
                                        END
                                            show,
                                        SUM (POKOK) POKOK,
                                        CASE
                                            WHEN (SELECT COUNT (1)
                                                    FROM pemutihan_pajak
                                                    WHERE     status = '1'
                                                        AND tgl_mulai <= TRUNC (to_date('" . $tanggal . "','yyyymmdd'))
                                                        AND tgl_selesai >= TRUNC (to_date('" . $tanggal . "','yyyymmdd'))) > 0
                                            THEN
                                                0
                                            ELSE SUM (DENDA) end  DENDA,
                                        
                                            CASE
                                            WHEN (SELECT COUNT (1)
                                                    FROM pemutihan_pajak
                                                    WHERE     status = '1'
                                                        AND tgl_mulai <= TRUNC (to_date('" . $tanggal . "','yyyymmdd'))
                                                        AND tgl_selesai >= TRUNC (to_date('" . $tanggal . "','yyyymmdd'))) > 0
                                            THEN
                                                sum(pokok)
                                            ELSE
                                            SUM (TOTAL)
                           end
                                            TOTAL
                                    FROM DATA_BILLING DATA_BILLING
                                        JOIN BILLING_KOLEKTIF BILLING_KOLEKTIF
                                            ON BILLING_KOLEKTIF.DATA_BILLING_ID =
                                                    DATA_BILLING.DATA_BILLING_ID
                                    WHERE     DATA_BILLING.TAHUN_PAJAK = '$tahun'
                                    and DATA_BILLING.kd_propinsi ='$kd_propinsi'
                                    and DATA_BILLING.kd_dati2='$kd_dati2'
                                    and DATA_BILLING.kd_kecamatan='$kd_kecamatan'
                                    and DATA_BILLING.kd_kelurahan='$kd_kelurahan'
                                    and DATA_BILLING.kd_blok='$kd_blok'
                                    and DATA_BILLING.no_urut='$no_urut'
                                    and DATA_BILLING.kd_jns_op='$kd_jns_op'
                                    and data_billing.kd_status='0'
                                    AND DATA_BILLING.deleted_at IS NULL
                                GROUP BY kd_status,
                                        nama_wp,
                                        nama_kelurahan,
                                        DATA_BILLING.tahun_pajak,
                                        DATA_BILLING.expired_at)
                        WHERE show = 1"));
        }
        // return $sppt;

        $pokok = 0;
        $denda = 0;
        $total = 0;
        if (isset($sppt[0])) {
            $pokok = $sppt['0']->pokok;
            $denda = $sppt['0']->denda;
            $total = $sppt['0']->total;
        }

        return [
            'pokok' => (int)$pokok,
            'denda' => (int)$denda,
            'total' => (int)$total,
        ];
    }


    public static function  dataObjek($nop, $tahun)
    {
        // $nop = splitnop($nop);
        $kd_propinsi = $nop['kd_propinsi'];
        $kd_dati2 = $nop['kd_dati2'];
        $kd_kecamatan = $nop['kd_kecamatan'];
        $kd_kelurahan = $nop['kd_kelurahan'];
        $kd_blok = $nop['kd_blok'];
        $no_urut = $nop['no_urut'];
        $kd_jns_op = $nop['kd_jns_op'];

        if ($kd_blok == '999') {
            // kobil
            $data = DB::table(db::raw("sim_pbb.data_billing"))->select(DB::raw("kd_propinsi,kd_dati2,kd_kecamatan,kd_kelurahan,kd_blok,no_urut,kd_jns_op,nama_wp nm_wp,nama_kelurahan nm_kelurahan,nama_kecamatan nm_kecamatan,case when expired_at<sysdate then '3' else  kd_status end status_pembayaran,null data_billing_id"))
                ->whereraw("tahun_pajak='$tahun' and kd_propinsi='$kd_propinsi' and kd_dati2='$kd_dati2' and kd_kecamatan='$kd_kecamatan' and kd_kelurahan='$kd_kelurahan' and kd_blok='$kd_blok' and no_urut='$no_urut' and kd_jns_op='$kd_jns_op' and deleted_at is null")->first();
        } else {
            $data = DB::connection('oracle_satutujuh')->table('dat_objek_pajak')
                ->join('dat_subjek_pajak', 'dat_objek_pajak.subjek_pajak_id', '=', 'dat_subjek_pajak.subjek_pajak_id')
                ->join('ref_kecamatan', 'ref_kecamatan.kd_kecamatan', '=', 'dat_objek_pajak.kd_kecamatan')
                ->join('ref_kelurahan', function ($join) {
                    $join->on('ref_kelurahan.kd_kecamatan', '=', 'dat_objek_pajak.kd_kecamatan')
                        ->on('ref_kelurahan.kd_kelurahan', '=', 'dat_objek_pajak.kd_kelurahan');
                })
                ->selectraw("dat_objek_pajak.kd_propinsi, dat_objek_pajak.kd_dati2, dat_objek_pajak.kd_kecamatan, dat_objek_pajak.kd_kelurahan, dat_objek_pajak.kd_blok, dat_objek_pajak.no_urut, dat_objek_pajak.kd_jns_op,nm_wp,nm_kelurahan,nm_kecamatan,
                                        NVL (
                                            (SELECT 
                                                    status_pembayaran_sppt 
                                            FROM sppt a
                                            WHERE     kd_propinsi = dat_objek_pajak.kd_propinsi
                                                    AND kd_dati2 = dat_objek_pajak.kd_dati2
                                                    AND kd_kecamatan = dat_objek_pajak.kd_kecamatan
                                                    AND kd_kelurahan = dat_objek_pajak.kd_kelurahan
                                                    AND kd_blok = dat_objek_pajak.kd_blok
                                                    AND no_urut = dat_objek_pajak.no_urut
                                                    AND kd_jns_op = dat_objek_pajak.kd_jns_op
                                                    AND thn_pajak_sppt = '$tahun'),
                                            3)
                                            status_pembayaran,
                                            (SELECT bb.data_billing_id
                                                    FROM sim_pbb.billing_kolektif aa
                                                            JOIN sim_pbb.data_billing bb
                                                            ON aa.data_billing_id = bb.data_billing_id
                                                    WHERE aa.kd_kecamatan = dat_objek_pajak.kd_kecamatan
                                AND aa.kd_kelurahan = dat_objek_pajak.kd_kelurahan
                                AND aa.kd_blok = dat_objek_pajak.kd_blok
                                AND aa.no_urut = dat_objek_pajak.no_urut
                                AND aa.tahun_pajak = '$tahun'
                                and bb.deleted_at is null
                                and ((bb.kd_status=0 and  bb.expired_at > SYSDATE)
                                or (bb.kd_status=1 and  bb.expired_at < SYSDATE)
                                )) data_billing_id")
                ->whereraw(" dat_objek_pajak.kd_propinsi='$kd_propinsi' and dat_objek_pajak.kd_dati2='$kd_dati2' and dat_objek_pajak.kd_kecamatan='$kd_kecamatan' and dat_objek_pajak.kd_kelurahan='$kd_kelurahan' and dat_objek_pajak.kd_blok='$kd_blok' and dat_objek_pajak.no_urut='$no_urut' and kd_jns_op='$kd_jns_op'")->first();


                /* 
                CASE
                                                    WHEN (SELECT COUNT (1)
                                                            FROM pembayaran_sppt
                                                            WHERE     kd_propinsi = a.kd_propinsi
                                                                    AND kd_dati2 = a.kd_dati2
                                                                    AND kd_kecamatan = a.kd_kecamatan
                                                                    AND kd_kelurahan = a.kd_kelurahan
                                                                    AND kd_blok = a.kd_blok
                                                                    AND no_urut = a.no_urut
                                                                    AND kd_jns_op = a.kd_jns_op
                                                                    AND thn_pajak_sppt = a.thn_pajak_sppt) > 0
                                                    THEN
                                                        '1'
                                                    ELSE
                                                        A.STATUS_PEMBAYARAN_SPPT
                                                    END */
        }
        return $data;
    }


    public static function  chanel($username, $password)
    {
        $seconds = 6000;
        $res = Cache::remember(md5($username . $password), $seconds, function () use ($username, $password) {
            // return user()->roles()->pluck('name')->toarray();
            return UserService::where('username', $username)->where('password_md5', $password)->first();
        });
        return $res;
    }
}
