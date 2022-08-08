<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Sppt;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use SpptHelp;
// use DB;
use Validator;
use App\LogService;
use App\UserService;
use Debugbar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InquiryController extends Controller
{

    public function index(Request $request)
    {

        $error = "False";
        $kode = '00';
        $messages = [
            'required' => ':attribute harus disertakan',
            'numeric' => ':attribute harus angka',
            'digits' => ':attribute kudu :digits digits.',
            'date_format' => ':attribute tidak sesuai format, pastikan format :attribute adalah :format.',
        ];
        $validator = Validator::make($request->all(), [
            "Nop" => 'required|numeric|digits:18',
            "MasaPajak" => 'required|numeric|digits:4',
            "DateTime" => 'required|date_format:Y-m-d H:i:s',
            "Merchant" => 'required|numeric',
        ], $messages);
        $data = [];
        if ($validator->fails()) {
            $msg = "";
            foreach ($validator->errors()->all() as $rk) {
                $msg .= $rk . ', ';
            }
            $msg = \substr($msg, '0', '-2');
            $error = "True";
            $kode = '99';
        } else {
            $nop = $request->Nop;

            // TagihanTahun
            $DateTime = $request->DateTime;
            $ceksppt = SpptHelp::TagihanTahun($nop, $request->MasaPajak, $DateTime);
            if (!empty($ceksppt)) {

                if (count($ceksppt) > 0) {
                    $restagihan = [];
                    if ($ceksppt[0]->status_pembayaran_sppt == '0') {
                        $sppt = SpptHelp::Tagihan($nop, $request->MasaPajak, $DateTime);
                        $belumlunas = 0;

                        foreach ($sppt as $row) {
                            if ($row->status_pembayaran_sppt == '0') {
                                $belumlunas += 1;
                                $restagihan[] = [
                                    'Tahun' => $row->tahun,
                                    'Pokok' => (int)$row->pokok,
                                    'Denda' => (int)$row->denda,
                                    'Total' => (int)$row->denda + $row->pokok
                                ];
                            }
                        }
                        $msg = "sukses ";
                        $kode = '00';
                    } else {
                        $msg = "Data tagihan telah lunas";
                        $kode = '13';
                    }
                } else {
                    $msg = "Data tidak ditemukan";
                    $kode = '10';
                }

                $data = array(
                    "Nop" => $nop,
                    "Nama" => $ceksppt[0]->nm_wp_sppt ?? '',
                    "Kelurahan" => $ceksppt[0]->kelurahan_wp_sppt ?? '',
                    "KodeKp" => "0000",
                    "KodeInstitusi" => $request->KodeInstitusi ?? '',
                    "NoHp" => $request->NoHp ?? '',
                    "Email" => $request->Email ?? '',
                    "Tagihan" => $restagihan,
                );
            } else {
                $msg = "Data tidak ditemukan";
                $error = "True";
                $kode = '10';
            }
        }

        $status = array(
            "Status" => [
                'IsError' => $error,
                'ResponseCode' => $kode,
                'ErrorDesc' => $msg
            ]
        );

        $response = \array_merge($data, $status);

        return response()->json($response);
    }
}
