<?php

namespace App\Http\Controllers\Api;

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

                    /* UPDATE sim_pbb.data_billing
         SET kd_status = 0, tgl_bayar = NULL, pengesahan = NULL
       WHERE kobil = :new.nop AND tahun_pajak = :new.tahun_pajak; */
                }
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




            /* return response()->json($nop);
            die(); */

            /*        $tahun = [];
            foreach ($request->Tagihan as $rr) {
                $tahun[] = implode(',', $rr);
            }
            $ntpd = $request->KodePengesahan;
            $tahun = implode(',', $tahun);

            $nop = $request->Nop; */



            /* $sppt = PembayaranTahun::where('nop', $nop)->whereraw("tahun_pajak in (" . $tahun . ")")->get();
            if ($sppt->count() > 0) {
                // return $sppt;
                DB::beginTransaction();
                try {
                    $total = 0;
                    $kp = "";
                    $kode_bank = "";
                    foreach ($sppt as $rst) {
                        $total += $rst->total;
                        $kp .= $rst->kodepengesahan . ',';
                        $kode_bank = $rst->kode_bank;
                    }
                    $kp = substr($kp, 0, 1);
                    // insert ke reversal

                    // return $kode_bank;
                    $reversal = PembayaranReversal::create([
                        'NOP' => $nop,
                        'MERCHANT' => '000',
                        'DATETIME' => new Carbon($request->DateTime),
                        'TOTALBAYAR' => $total,
                        'KODEPENGESAHAN' => $kp,
                        'KODEKP' => '0000',
                        'KODE_BANK' => $kode_bank,
                    ]);

                    // insert ke reversal detail
                    // $pbda = PembayaranTahun::where('kodepengesahan', $pb->kodepengesahan)->get();

                    foreach ($sppt as $pbd) {
                        $detailrev = [
                            'WS_REVERSAL_ID' => $reversal->id,
                            'NOP' => $pbd->nop,
                            'TAHUN_PAJAK' => $pbd->tahun_pajak,
                            'KODEPENGESAHAN' => $pbd->kodepengesahan,
                            'KODEKP' => $pbd->kodekp,
                            'POKOK' => $pbd->pokok,
                            'DENDA' => $pbd->denda,
                            'TOTAL' => $pbd->total,
                            'DATETIME' => new Carbon($request->DateTime),
                            'KODE_BANK' => $kode_bank,
                        ];
                        PembayaranReversalTahun::create($detailrev);
                    }

                    DB::commit();
                    $data = $request->only(['Nop', 'Reference']);
                    $error = "False";
                    $msg = "sukses";
                    $code = "00";
                } catch (\Exception $e) {
                    DB::rollback();

                    $error = "True";
                    $msg = $e->getMessage();
                    // $msg = $sppt;
                    $code = "99";
                }
            } else {

                // DB
                $cekdua = DB::table('sppt_oltp')->whereraw("kd_propinsi =substr('$nop',1,2)
                and kd_dati2=substr('$nop',3,2)
                and kd_kecamatan=substr('$nop',5,3)
                and kd_kelurahan=substr('$nop',8,3)
                and kd_blok=substr('$nop',11,3)
                and no_urut=substr('$nop',14,4)
                and kd_jns_op=substr('$nop',18,1) and status_pembayaran_sppt='1' and thn_pajak_sppt ='" . $tahun . "'")->first();

                if ($cekdua) {

                    try {
                        //code...

                        DB::connection('oracle_satutujuh')->statement(DB::raw("
                    begin
                    delete from pembayaran_sppt where kd_propinsi =substr('$nop',1,2)
                    and kd_dati2=substr('$nop',3,2)
                    and kd_kecamatan=substr('$nop',5,3)
                    and kd_kelurahan=substr('$nop',8,3)
                    and kd_blok=substr('$nop',11,3)
                    and no_urut=substr('$nop',14,4)
                    and kd_jns_op=substr('$nop',18,1)  and thn_pajak_sppt ='" . $tahun . "';


                    update sppt set status_pembayaran_sppt='0'
                    where kd_propinsi =substr('$nop',1,2)
                    and kd_dati2=substr('$nop',3,2)
                    and kd_kecamatan=substr('$nop',5,3)
                    and kd_kelurahan=substr('$nop',8,3)
                    and kd_blok=substr('$nop',11,3)
                    and no_urut=substr('$nop',14,4)
                    and kd_jns_op=substr('$nop',18,1)  and thn_pajak_sppt ='" . $tahun . "';
                    end;
                    "));
                        $data = $request->only(['Nop', 'Reference']);
                        $error = "False";
                        $msg = "sukses";
                        $code = "00";
                    } catch (\Throwable $e) {
                        //throw $th;
                        $error = "True";
                        $msg = $e->getMessage();
                        // $msg = $sppt;
                        $code = "99";
                    }
                } else {
                    $error = "True";
                    $code = "34";
                    $msg = " Data reversal tidak ditemukan";
                }
            } */
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
