<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\PembayaranReversal;
use App\PembayaranReversalTahun;
use App\PembayaranTahun;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReversalOtherController extends Controller
{
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
            // "Reference" => 'required',
            "DateTime" => 'required|date_format:Y-m-d H:i:s',
            "Tahun" => 'required|numeric|distinct|digits:4'
            // "Tagihan" => "required|array|min:1|max:11",
            // "Tagihan.*.Tahun" => 'required|numeric|distinct|digits:4'

        ], $messages);
        if ($validator->fails()) {
            $msg = "";
            foreach ($validator->errors()->all() as $rk) {
                $msg .= $rk . ', ';
            }
            $msg = \substr($msg, '0', '-2');
            $error = "True";
            $code = "99";
        } else {

            /* $tahun = [];
            foreach ($request->Tagihan as $rr) {
                $tahun[] = implode(',', $rr);
            } */
            // $ntpd = $request->KodePengesahan;
            $tahun =$request->Tahun;

            $nop = $request->Nop;
            $sppt = PembayaranTahun::where('nop', $nop)->whereraw("tahun_pajak in (" . $tahun . ")")->get();
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
                        'DATETIME' =>  new Carbon($request->DateTime),
                        'TOTALBAYAR' => $total,
                        'KODEPENGESAHAN' => $kp,
                        'KODEKP' => '0000',
                        'KODE_BANK' => $kode_bank
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
                            'KODE_BANK' => $kode_bank
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
                $error = "True";
                $code = "34";
                $msg = " Data reversal tidak ditemukan";
            }
        }

        $status = array(
            "Status" => [
                'IsError' => $error,
                'ResponseCode' => $code,
                'ErrorDesc' => $msg
            ]
        );

        $response = \array_merge($data, $status);


        return response()->json($response);
    }
}
