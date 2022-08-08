<?php

namespace App\Helpers;

// use Illuminate\Support\Facades\DB;
use App\SpptOltp;
use App\PembayaranTahun;
use Illuminate\Support\Facades\DB;

// use DB;

class Sppt
{

    public static function Tagihan($nop, $tahun, $tanggal)
    {
        if ($tanggal == '') {
            $tanggal = date('Ymd');
        } else {
            $tanggal = date('Ymd', strtotime($tanggal));
        }
        $sppt = DB::select(DB::raw("SELECT * from (
                                        select 
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
                                            get_denda (kd_dati2,
                                                    kd_kecamatan,
                                                    kd_kelurahan,
                                                    kd_blok,
                                                    no_urut,
                                                    kd_jns_op,
                                                    thn_pajak_sppt,
                                                    PBB_YG_HARUS_DIBAYAR_SPPT,
                                                    tgl_jatuh_tempo_sppt,
                                                    to_date('" . $tanggal . "','yyyymmdd'))  end   Denda,
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
                                            get_denda (kd_dati2,
                                                    kd_kecamatan,
                                                    kd_kelurahan,
                                                    kd_blok,
                                                    no_urut,
                                                    kd_jns_op,
                                                    thn_pajak_sppt,
                                                    PBB_YG_HARUS_DIBAYAR_SPPT,
                                                    tgl_jatuh_tempo_sppt,
                                                    to_date('" . $tanggal . "','yyyymmdd'))  end   as total
                                                                            from sppt_oltp
                                                                            where thn_pajak_sppt <=$tahun and kd_propinsi =substr('$nop',1,2) 
                                                                            and kd_dati2=substr('$nop',3,2)
                                                                            and kd_kecamatan=substr('$nop',5,3)
                                                                            and kd_kelurahan=substr('$nop',8,3)
                                                                            and kd_blok=substr('$nop',11,3) 
                                                                            and no_urut=substr('$nop',14,4) 
                                                                            and kd_jns_op=substr('$nop',18,1) 
                                                                            and data_billing_id is null
                                                                            order by thn_pajak_sppt desc) where rownum <=11
                                                                            "));


        if (count($sppt) == 0) {
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
                                        SUM (TOTAL) TOTAL
                                    FROM DATA_BILLING DATA_BILLING
                                        JOIN BILLING_KOLEKTIF BILLING_KOLEKTIF
                                            ON BILLING_KOLEKTIF.DATA_BILLING_ID =
                                                    DATA_BILLING.DATA_BILLING_ID
                                    WHERE     DATA_BILLING.TAHUN_PAJAK = '$tahun'
                                        AND kobil = '$nop'
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

    public static function TagihanTahun($nop, $tahun, $tanggal = '')
    {

        if ($tanggal == '') {
            $tanggal = date('Ymd');
        } else {
            $tanggal = date('Ymd', strtotime($tanggal));
        }



        $sppt = SpptOltp::select(DB::raw("nm_wp_sppt,(select nm_kelurahan 
                                        from pbb.ref_kelurahan a
                                        where kd_kelurahan=sppt_oltp.kd_kelurahan and kd_kecamatan=sppt_oltp.kd_kecamatan) kelurahan_op,
                                        (select nm_kecamatan
                                        from pbb.ref_kecamatan a
                                        where kd_kecamatan=sppt_oltp.kd_kecamatan) kecamatan_op,
                                        kelurahan_wp_sppt,thn_pajak_sppt tahun,pbb_yg_harus_dibayar_sppt pokok,
                                        CASE
                                            WHEN (SELECT COUNT (1)
                                                    FROM pemutihan_pajak
                                                    WHERE     status = '1'
                                                        AND tgl_mulai <= TRUNC (to_date('" . $tanggal . "','yyyymmdd'))
                                                        AND tgl_selesai >= TRUNC (to_date('" . $tanggal . "','yyyymmdd'))) > 0
                                            THEN
                                                0
                                            ELSE
                                        get_denda (kd_dati2,
                                                kd_kecamatan,
                                                kd_kelurahan,
                                                kd_blok,
                                                no_urut,
                                                kd_jns_op,
                                                thn_pajak_sppt,
                                                PBB_YG_HARUS_DIBAYAR_SPPT,
                                                tgl_jatuh_tempo_sppt,
                                                to_date('" . $tanggal . "','yyyymmdd'))  end  Denda,
                                        pbb_yg_harus_dibayar_sppt +  CASE
                                        WHEN (SELECT COUNT (1)
                                                FROM pemutihan_pajak
                                            WHERE     status = '1'
                                                    AND tgl_mulai <= TRUNC (to_date('" . $tanggal . "','yyyymmdd'))
                                                    AND tgl_selesai >= TRUNC (to_date('" . $tanggal . "','yyyymmdd'))) > 0
                                        THEN
                                        0
                                        ELSE
                                    get_denda (kd_dati2,
                                            kd_kecamatan,
                                            kd_kelurahan,
                                            kd_blok,
                                            no_urut,
                                            kd_jns_op,
                                            thn_pajak_sppt,
                                            PBB_YG_HARUS_DIBAYAR_SPPT,
                                            tgl_jatuh_tempo_sppt,
                                            to_date('" . $tanggal . "','yyyymmdd'))  end
                                        total,status_pembayaran_sppt"))
            ->whereraw("kd_propinsi =substr('$nop',1,2) 
                                        and kd_dati2=substr('$nop',3,2)
                                        and kd_kecamatan=substr('$nop',5,3)
                                        and kd_kelurahan=substr('$nop',8,3)
                                    and kd_blok=substr('$nop',11,3) 
                                    and no_urut=substr('$nop',14,4) 
                                    and kd_jns_op=substr('$nop',18,1) 
                                    and data_billing_id is null
                                    and status_pembayaran_sppt in (0,1) and thn_pajak_sppt in ($tahun)")->orderby('thn_pajak_sppt', 'desc')->get();
        if (count($sppt) == 0) {
            // and DATA_BILLING.expired_at>=sysdate
            $sppt = DB::select(DB::raw("SELECT *
            FROM (  SELECT KD_STATUS STATUS_PEMBAYARAN_SPPT,
                           NAMA_WP NM_WP_SPPT,
                           NAMA_KELURAHAN KELURAHAN_WP_SPPT,
                           DATA_BILLING.TAHUN_PAJAK TAHUN,
                           SUM (POKOK) POKOK,
                           CASE
                                            WHEN (SELECT COUNT (1)
                                                    FROM pemutihan_pajak
                                                    WHERE     status = '1'
                                                        AND tgl_mulai <= TRUNC (to_date('" . $tanggal . "','yyyymmdd'))
                                                        AND tgl_selesai >= TRUNC (to_date('" . $tanggal . "','yyyymmdd'))) > 0
                                            THEN
                                                0
                                            ELSE
                           SUM (DENDA) 
                           end
                           DENDA,
                           SUM (TOTAL) TOTAL,
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
                              show
                      FROM DATA_BILLING DATA_BILLING
                           JOIN BILLING_KOLEKTIF BILLING_KOLEKTIF
                              ON BILLING_KOLEKTIF.DATA_BILLING_ID =
                                    DATA_BILLING.DATA_BILLING_ID
                     WHERE     DATA_BILLING.TAHUN_PAJAK = '$tahun'
                           AND kobil = '$nop'
                           AND DATA_BILLING.deleted_at IS NULL
                  GROUP BY kd_status,
                           nama_wp,
                           nama_kelurahan,
                           DATA_BILLING.tahun_pajak,data_billing.expired_at)
           WHERE show=1"));
        }

        return $sppt;
    }

    public static function cekNtpd($ntpd)
    {
        $res = PembayaranTahun::where('KODEPENGESAHAN', $ntpd)->get();
        return $res;
    }

    public static function KodePengesahan()
    {
        $digits = 12;
        $i = 0; //counter
        $pin = ""; //our default pin is blank.
        while ($i < $digits) {
            //generate a random number between 0 and 9.
            $pin .= mt_rand(0, 9);
            $i++;
        }
        return '3504' . $pin;
    }
}
