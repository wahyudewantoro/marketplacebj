<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Pajak;
use App\Http\Controllers\Controller;
use App\PembayaranReversal;
use App\PembayaranReversalTahun;
use App\PembayaranTahun;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ReversalController extends Controller
{
    //
    public function index(Request $request)
    {
        $data = [];
        $error = "False";

        $messages = [
            'required' => ':attribute harus disertakan',
            'numeric' => ':attribute harus angka',
            'digits' => ':attribute harus :digits digits.',
            'date_format' => ':attribute tidak sesuai format, pastikan format :attribute adalah :format.',
            'array' => ':attribute harus array.',
            'min' => [
                'array' => ':attribute setidaknya harus memiliki :min items.',
            ],
            'max' => [
                'array' => ':attribute mungkin tidak lebih dari :max items.',
            ],
        ];
        $validator = Validator::make($request->all(), [
            "Nop" => 'required|numeric|digits:18',
            // "KodePengesahan" => 'required|numeric',
            "Reference" => 'required',
            "DateTime" => 'required|date_format:Y-m-d H:i:s',
            "Tagihan" => "required|array|min:1|max:11",
            "Tagihan.*.Tahun" => 'required|numeric|distinct|digits:4',

        ], $messages);
        if ($validator->fails()) {
            $msg = "";
            foreach ($validator->errors()->all() as $rk) {
                $msg .= $rk . ', ';
            }
            $msg = \substr($msg, '0', '-2');
            $error = "True";
            $code = "96";
        } else {
            $tahun = [];
            foreach ($request->Tagihan as $rr) {
                $tahun[] = implode(',', $rr);
            }
            // return response()->json($tahun);
            $nop = splitnop(trim($request->Nop));

            // return $tahun;

            $kd_propinsi = $nop['kd_propinsi'];
            $kd_dati2 = $nop['kd_dati2'];
            $kd_kecamatan = $nop['kd_kecamatan'];
            $kd_kelurahan = $nop['kd_kelurahan'];
            $kd_blok = $nop['kd_blok'];
            $no_urut = $nop['no_urut'];
            $kd_jns_op = $nop['kd_jns_op'];

            db::beginTransaction();
            $th = implode(',', $tahun);
            try {

                $chanel = Pajak::chanel($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
                $kode_bank = trim($chanel->kode_bank);
                /* if($kd_blok=='999'){
                    $cekb_billing=DB::table(db::raw("sim_pbb.data_billing"))
                    ->wherer('kd_propinsi', $kd_propinsi)
                        ->wherer('kd_dati2', $kd_dati2)
                        ->wherer('kd_kecamatan', $kd_kecamatan)
                        ->wherer('kd_kelurahan', $kd_kelurahan)
                        ->wherer('kd_blok', $kd_blok)
                        ->wherer('no_urut', $no_urut)
                        ->wherer('kd_jns_op', $kd_jns_op)
                        ->wherer('thn_pajak_sppt', $kd_jns_op);
                } */

                foreach ($tahun as $tahun) {
                    // cek data pembayaran dulu
                    if ($kd_blok != '999') {
                        $cek = DB::table(db::raw("pbb.pembayaran_sppt"))
                            ->select(db::raw("to_char(tgl_pembayaran_sppt,'yyyy-mm-dd') tanggal,pengesahan"))
                            ->where('kd_propinsi', $kd_propinsi)
                            ->where('kd_dati2', $kd_dati2)
                            ->where('kd_kecamatan', $kd_kecamatan)
                            ->where('kd_kelurahan', $kd_kelurahan)
                            ->where('kd_blok', $kd_blok)
                            ->where('no_urut', $no_urut)
                            ->where('kd_jns_op', $kd_jns_op)
                            ->where('thn_pajak_sppt', $tahun)
                            ->where('kode_bank', $kode_bank)->get();
                    } else {
                        $cek = DB::table(db::raw("sim_pbb.data_billing"))
                            ->select(db::raw("to_char(tgl_bayar,'yyyy-mm-dd') tanggal,pengesahan"))
                            ->where('kd_propinsi', $kd_propinsi)
                            ->where('kd_dati2', $kd_dati2)
                            ->where('kd_kecamatan', $kd_kecamatan)
                            ->where('kd_kelurahan', $kd_kelurahan)
                            ->where('kd_blok', $kd_blok)
                            ->where('no_urut', $no_urut)
                            ->where('kd_jns_op', $kd_jns_op)
                            ->where('tahun_pajak', $tahun)
                            ->where('kode_bank', $kode_bank)->get();
                    }

                    foreach ($cek as $item) {
                        DB::statement(db::raw("begin  proc_hapus_bayar('" . $kd_propinsi . "', '" . $kd_dati2 . "', '" . $kd_kecamatan . "', '" . $kd_kelurahan . "', '" . $kd_blok . "', '" . $no_urut . "', '" . $kd_jns_op . "', '" . $tahun . "',to_date('" . $item->tanggal . "','yyyy-mm-dd'),'" . $kode_bank . "','".$item->pengesahan."'); commit; end;"));
                    }
                }





                /* foreach ($tahun as $tahun_pajak) {
                    # code...
                } */

                /*
                if ($kd_blok != '999') {
                    // nop biasa

                    DB::statement(DB::raw("BEGIN 
                        DELETE from pbb.pembayaran_sppt where kd_kecamatan='$kd_kecamatan' and kd_kelurahan='$kd_kelurahan' and kd_blok='$kd_blok' and no_urut='$no_urut' and kd_jns_op='$kd_jns_op' and thn_pajak_sppt in ($th);
                        DELETE from spo.pembayaran_sppt where kd_kecamatan='$kd_kecamatan' and kd_kelurahan='$kd_kelurahan' and kd_blok='$kd_blok' and no_urut='$no_urut' and kd_jns_op='$kd_jns_op' and thn_pajak_sppt in ($th);
                        UPDATE PBB.SPPT set status_pembayaran_sppt='0' where kd_kecamatan='$kd_kecamatan' and kd_kelurahan='$kd_kelurahan' and kd_blok='$kd_blok' and no_urut='$no_urut' and kd_jns_op='$kd_jns_op' and thn_pajak_sppt in ($th);
                        commit;
                        END;
                    "));
                } else {
                    DB::statement(DB::raw("BEGIN 
                                                              DELETE FROM pbb.pembayaran_sppt
                                                              WHERE ROWID IN (SELECT b.ROWID
                                                        FROM sim_pbb.billing_kolektif a
                                                             JOIN pbb.pembayaran_sppt b
                                                                ON     a.tahun_pajak = b.thn_pajak_sppt
                                                                   AND a.kd_propinsi = b.kd_propinsi
                                                                   AND a.kd_dati2 = b.kd_dati2
                                                                   AND a.kd_kecamatan = b.kd_kecamatan
                                                                   AND a.kd_kelurahan = b.kd_kelurahan
                                                                   AND a.kd_blok = b.kd_blok
                                                                   AND a.no_urut = b.no_urut
                                                                   AND a.kd_jns_op = b.kd_jns_op
                                                       WHERE data_billing_id IN (SELECT data_billing_id
                                                                                   FROM sim_pbb.data_billing
                                                                                  WHERE  kobil ='" . $request->Nop . "'
                                                                                        AND tahun_pajak  in (" . $th . ") and deleted_at is null ));

                            DELETE FROM spo.pembayaran_sppt
                                    WHERE ROWID IN (SELECT b.ROWID
                              FROM sim_pbb.billing_kolektif a
                                   JOIN spo.pembayaran_sppt b
                                      ON     a.tahun_pajak = b.thn_pajak_sppt
                                         AND a.kd_propinsi = b.kd_propinsi
                                         AND a.kd_dati2 = b.kd_dati2
                                         AND a.kd_kecamatan = b.kd_kecamatan
                                         AND a.kd_kelurahan = b.kd_kelurahan
                                         AND a.kd_blok = b.kd_blok
                                         AND a.no_urut = b.no_urut
                                         AND a.kd_jns_op = b.kd_jns_op
                             WHERE data_billing_id IN (SELECT data_billing_id
                                                         FROM sim_pbb.data_billing
                                                        WHERE  kobil ='" . $request->Nop . "'
                                                              AND tahun_pajak  in (" . $th . ") and deleted_at is null ));

                    UPDATE pbb.sppt
                    SET status_pembayaran_sppt = '0'
                  WHERE ROWID IN (SELECT b.ROWID
                                    FROM sim_pbb.billing_kolektif a
                                         JOIN pbb.sppt b
                                            ON     a.tahun_pajak = b.thn_pajak_sppt
                                               AND a.kd_propinsi = b.kd_propinsi
                                               AND a.kd_dati2 = b.kd_dati2
                                               AND a.kd_kecamatan = b.kd_kecamatan
                                               AND a.kd_kelurahan = b.kd_kelurahan
                                               AND a.kd_blok = b.kd_blok
                                               AND a.no_urut = b.no_urut
                                               AND a.kd_jns_op = b.kd_jns_op
                                   WHERE data_billing_id IN (SELECT data_billing_id
                                                         FROM sim_pbb.data_billing
                                                        WHERE  kobil ='" . $request->Nop . "'
                                                              AND tahun_pajak  in (" . $th . ") and deleted_at is null ));


                                                              UPDATE sim_pbb.data_billing
                                                                        SET kd_status = 0, tgl_bayar = NULL, pengesahan = NULL
                                                                        WHERE  kobil ='" . $request->Nop . "'
                                                                        AND tahun_pajak  in (" . $th . ") and deleted_at is null;
                                                              commit;
                                                              END;
                                                          "));
                }*/
                //code...
                DB::commit();
                $data = $request->only(['Nop', 'Reference']);
                $error = "False";
                $msg = "success";
                $code = "00";
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollBack();
                Log::info($th);
                $msg = $th->getMessage();
                $error = "True";
                $code = "96";
            }
        }

        $status = array(
            "Status" => [
                'IsError' => $error,
                'ResponseCode' => $code,
                'ErrorDesc' => $msg,
            ],
        );

        $response = \array_merge($data, $status);

        return response()->json($response);
    }
}
