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
use App\UserService;
// use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
            $DateTime = $request->DateTime;
            $sppt = SpptHelp::TagihanTahun($nop, $tahun, $DateTime);

            if (count($sppt) > 0) {
                // cek jumlah tagihan
                $totalBayar = $request->TotalBayar;
                $tagihanDb = 0;

                $lunas = 0;
                foreach ($sppt as $ct) {
                    if ($ct->status_pembayaran_sppt == '0') {
                        $tagihanDb += $ct->total;
                    } else {
                        $lunas = 1;
                        $tagihanDb = null;
                        $msg = "Data tagihan telah lunas";
                        $code = '13';
                        $error = "True";
                        break;
                    }
                }
                /* 
                return [
                    'bayar' => $totalBayar,
                    'tagihan' => $tagihanDb
                ]; */
                if ($totalBayar == $tagihanDb) {
                    DB::beginTransaction();
                    try {
                        // proses payment
                        $username = $_SERVER['PHP_AUTH_USER'];
                        $pass = $_SERVER['PHP_AUTH_PW'];
                        $user = UserService::where('username', $username)->where('password_md5', $pass)->first();
                        // return trim($user->kode_bank);
                        $kode_bank = trim($user->kode_bank);
                        $databayar = [
                            'NOP' => $nop,
                            'KODEKP' => '0000',
                            'KODEPENGESAHAN' => SpptHelp::KodePengesahan(),
                            'MERCHANT' => $request->Merchant,
                            'DATETIME' => new Carbon($request->DateTime),
                            'TOTALBAYAR' => $request->TotalBayar,
                            'KODE_BANK' => $kode_bank
                        ];

                        // return $databayar;

                        $bayar = Pembayaran::create($databayar);

                        foreach ($sppt as $spt) {
                            $detailbayar = [
                                'WS_PEMBAYARAN_ID' => $bayar->id,
                                'NOP' => $bayar->NOP,
                                'KODEPENGESAHAN' => $bayar->KODEPENGESAHAN,
                                'KODEKP' => $bayar->KODEKP,
                                'TAHUN_PAJAK' => $spt->tahun,
                                'POKOK' => $spt->pokok,
                                'DENDA' => $spt->denda,
                                'TOTAL' => $spt->total,
                                'DATETIME' => $bayar->DATETIME,
                                'KODE_BANK' => $kode_bank
                            ];
                            // return $detailbayar;
                            PembayaranTahun::create($detailbayar);
                        }
                        $data['Nop'] = $bayar->NOP;
                        $data['KodePengesahan'] = $bayar->KODEPENGESAHAN;
                        $data['KodeKp'] = $bayar->KODEKP;

                        $msg = "Sukses";
                        $code = '00';
                        $error = "False";

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollback();
                        $error = "True";
                        $msg = $e->getMessage();
                        $code = "99";
                    }
                } else {
                    if ($totalBayar <> $tagihanDb) {
                        if ($lunas == 1) {
                            $msg = "Data tagihan telah lunas";
                            $code = '13';
                            $error = "True";
                        } else {
                            $error = "True";
                            $code = "14";
                            $msg = "Jumlah tagihan yang dibayarkan tidak sesuai ";
                        }
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
