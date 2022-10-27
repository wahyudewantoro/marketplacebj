<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Pajak;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use SpptHelp;

class InquiryOtherController extends Controller
{
    public function index(Request $request)
    {

        $error = "False";
        $kode = '00';
        $messages = [
            'required' => ':attribute harus disertakan',
            'numeric' => ':attribute harus angka',
            'digits' => ':attribute harus :digits digits.',
            'date_format' => ':attribute tidak sesuai format, pastikan format :attribute adalah :format.',
        ];
        $validator = Validator::make($request->all(), [
            "Nop" => 'required|numeric|digits:18',
            "MasaPajak" => 'required|numeric|digits:4',
            "DateTime" => 'required|date_format:Y-m-d H:i:s',
            // "Merchant" => 'required|numeric',
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
            // $nop = $request->Nop;
            $nop = splitnop($request->Nop);

            // TagihanTahun
            $DateTime = $request->DateTime;
            $tahun = $request->MasaPajak;
            // $ceksppt = SpptHelp::TagihanTahun($nop, $request->MasaPajak,$DateTime);
            $ceksppt = Pajak::Tagihan($nop, $tahun, $DateTime);
            // return $ceksppt;
            // status_pembayaran_sppt
            if (!empty($ceksppt)) {
                // return $ceksppt[0];
                if (count($ceksppt) > 0) {
                    $row = $ceksppt[0];
                    $restagihan = [];
                    $sp = [0, 2];
                    if (in_array($row->status_pembayaran_sppt, $sp)) {
                        $restagihan = [
                            'Tahun' => $row->tahun,
                            'Pokok' => (int)$row->pokok,
                            'Denda' => (int)$row->denda,
                            'Total' => (int)$row->denda + $row->pokok
                        ];

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

                $dataa = array(
                    "Nop" => implode('',$nop),
                    "Nama" => $ceksppt[0]->nm_wp_sppt ?? '',
                    "Kelurahan" => $ceksppt[0]->kelurahan_wp_sppt ?? '',
                    "Kecamatan" => $ceksppt[0]->kecamatan_op ?? '',
                    "Dati2" => "KAB MALANG",
                    "Propinsi" => "JAWA TIMUR"
                );

                $data = array_merge($dataa, $restagihan);
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
