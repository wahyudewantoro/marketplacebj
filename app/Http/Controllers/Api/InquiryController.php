<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Pajak;
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
            'digits' => ':attribute harus :digits digits.',
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
            $tahun = trim($request->MasaPajak);
            $DateTime = $request->DateTime;
            $nop = splitnop($request->Nop);
            $objek = Pajak::dataObjek($nop, $tahun);
            $data = [];
            if ($objek) {
                $nomor_op = $objek->kd_propinsi . $objek->kd_dati2 . $objek->kd_kecamatan . $objek->kd_kelurahan . $objek->kd_blok . $objek->no_urut . $objek->kd_jns_op;
                $data = array(
                    "Nop" => trim($nomor_op),
                    "Nama" => $objek->nm_wp,
                    "Kelurahan" => $objek->nm_kelurahan ?? '',
                    "KodeKp" => "0000",
                    "KodeInstitusi" => $request->KodeInstitusi ?? '',
                    "NoHp" => $request->NoHp ?? '',
                    "Email" => $request->Email ?? '',
                );

                $restagihan = [];
                $sb = [0, 2];
                // return json_encode($objek->status_pembayaran);

                if (in_array($objek->status_pembayaran, $sb)) {

                    if ($objek->data_billing_id <> '') {
                        $msg = "Data Objek masuk dalam kode billing pembayaran kolektif";
                        $error = "True";
                        $kode = '10';
                    } else {
                        // data di temukan
                        $sppt = Pajak::Tagihan($nop, $tahun, $DateTime);
                        // return response()->json($sppt);
                        foreach ($sppt as $row) {
                            // if ($row->status_pembayaran_sppt == '0') {
                            if (in_array($row->status_pembayaran_sppt, $sb)) {
                                // $belumlunas += 1;
                                $restagihan[] = [
                                    'Tahun' => $row->tahun,
                                    'Pokok' => (int)$row->pokok,
                                    'Denda' => (int)$row->denda,
                                    'Total' => (int)$row->denda + $row->pokok
                                ];
                            }
                        }

                        $msg = "Success";
                        $error = "False";
                        $kode = '00';
                    }
                } else if ($objek->status_pembayaran == '3') {
                    $msg = "Data objek dengan NOP tersebut tidak ditemukan";
                    $error = "True";
                    $kode = '10';
                } else {
                    // tagihan lunas
                    $msg = "Tagihan SPPT dengan Tahun pajak dimaksud telah dibayar";
                    $error = "True";
                    $kode = '13';
                }

                $data['Tagihan'] = $restagihan;
            } else {
                $msg = "Data objek dengan NOP tersebut tidak ditemukan";
                $error = "True";
                $kode = '10';
            }

            /* $status = array(
                "Status" => [
                    'IsError' => $error,
                    'ResponseCode' => $kode,
                    'ErrorDesc' => $msg
                ]
            ); */

            /* $response = \array_merge($data, $status);

            return response()->json($response); */
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
