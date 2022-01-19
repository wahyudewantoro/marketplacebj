<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Sppt;
use App\Http\Controllers\Controller;
use App\Pbbminimal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use SpptHelp;
use App\Pembayaran;
use App\PembayaranTahun;
use DB;
use Carbon\Carbon;

class Paymentcontroller extends Controller
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
            "Merchant" => 'required|numeric',
            "DateTime" => 'required|date_format:Y-m-d H:i:s',
            "Reference" => 'required',
            // "KodeInstitusi" => 'required',
            //"NoHp" => 'required|numeric',
            //"Email" => 'required|email',
            "TotalBayar" => 'required|numeric',
            "Tagihan" => "required|array|min:1|max:11",
            "Tagihan.*.Tahun" => 'required|numeric|distinct|digits:4'

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

            $tahun = [];
            foreach ($request->Tagihan as $rr) {
                $tahun[] = implode(',', $rr);
            }
            $nop = $request->Nop;
            $tahun = implode(',', $tahun);
            $sppt = SpptHelp::TagihanTahun($nop, $tahun);

            if (count($sppt) > 0) {
                // cek jumlah tagihan
                $totalBayar = $request->TotalBayar;
                $tagihanDb = 0;
                foreach ($sppt as $ct) {
                    if ($ct->status_pembayaran_sppt == '0') {
                        $tagihanDb += $ct->total;
                    } else {
                        $tagihanDb = null;
                        $msg = "Data tagihan telah lunas";
                        $code = '13';
                        $error = "True";
                        break;
                    }


                    // $min = Pbbminimal::where('thn_pbb_minimal', $ct->tahun)->first();
                    // if ($min->nilai_pbb_minimal <= $ct->total) {
                    // $tagihanDb += $ct->total;
                    // }else{
                    // $tagihanDb = $ct->total;
                    // }
                }

                if ($totalBayar == $tagihanDb) {
                    DB::beginTransaction();
                    try {

                        $bayar = Pembayaran::create([
                            'NOP' => $nop,
                            'KODEKP' => '0000',
                            'KODEPENGESAHAN' => SpptHelp::KodePengesahan(),
                            'MERCHANT' => $request->Merchant,
                            'DATETIME' => new Carbon($request->DateTime),
                            'TOTALBAYAR' => $request->TotalBayar,
                        ]);

                        foreach ($sppt as $spt) {
                            PembayaranTahun::create([
                                'WS_PEMBAYARAN_ID' => $bayar->id,
                                'NOP' => $bayar->NOP,
                                'KODEPENGESAHAN' => $bayar->KODEPENGESAHAN,
                                'KODEKP' => $bayar->KODEKP,
                                'TAHUN_PAJAK' => $spt->tahun,
                                'POKOK' => $spt->pokok,
                                'DENDA' => $spt->denda,
                                'TOTAL' => $spt->total,
                                'DATETIME' => $bayar->DATETIME
                            ]);
                        }

                        DB::commit();
                        $data['Nop'] = $bayar->NOP;
                        $data['KodePengesahan'] = $bayar->KODEPENGESAHAN;
                        $data['KodeKp'] = $bayar->KODEKP;

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
                    if ($totalBayar <> $tagihanDb) {
                        $error = "True";
                        $code = "14";
                        $msg = "Jumlah tagihan yang dibayarkan tidak sesuai "; //.$totalBayar.' = '.$tagihanDb;
                    }
                }
            } else {
                $error = "True";
                $code = "10";
                $msg = "Data tagihan tidak ditemukan";
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
