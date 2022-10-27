@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Service Pembayaran</div>

                <div class="card-body">
                    <nav>
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                            <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home"
                                role="tab" aria-controls="nav-home" aria-selected="true">Inquiry</a>
                            <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile"
                                role="tab" aria-controls="nav-profile" aria-selected="false">Payment</a>
                            <a class="nav-item nav-link" id="nav-contact-tab" data-toggle="tab" href="#nav-contact"
                                role="tab" aria-controls="nav-contact" aria-selected="false">Reversal</a>
                        </div>
                    </nav>
                    <div class="tab-content" id="nav-tabContent">
                        <div class="tab-pane fade show active" id="nav-home" role="tabpanel"
                            aria-labelledby="nav-home-tab">
                            <form id="FORM_INQURY">
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-2 col-form-label">NOP</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="Nop" name="Nop" placeholder="NOP">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-2 col-form-label">MASA PAJAK</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="MasaPajak" name="MasaPajak"
                                            placeholder="MASA PAJAK">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-2 col-form-label">MERCHANT</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="Merchant" name="Merchant" placeholder="MERCHANT">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-2 col-form-label">DATETIME</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="DateTime" name="DateTime" placeholder="DATETIME" value="{{ date('Y-m-d H:i:s') }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-10">
                                        <button class="btn btn-primary ">SUBMIT</button>
                                    </div>
                                </div>
                            </form>
                            <div id="resinquiry"></div>
                        </div>
                        <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">

                            <form id="FORM_PAYMENT">
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-2 col-form-label">NOP</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="Nop2" name="Nop" placeholder="NOP">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-2 col-form-label">MERCHANT</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="Merchant2" name="Merchant" placeholder="MERCHANT">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-2 col-form-label">DATETIME</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="DateTime2" name="DateTime" placeholder="DATETIME" value="{{ date('Y-m-d H:i:s') }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-2 col-form-label">TOTAL BAYAR</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="TotalBayar2" name="TotalBayar"
                                            placeholder="TOTAL BAYAR">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-2 col-form-label">TAHUN</label>
                                    <div class="input-group col-sm-10">
                                        <span class="input-group-text " id="addTagihan2"><i class="fa fa-fw fa-plus"></i></span>
                                        <input type="text" class="form-control Tagihan2" placeholder="TAHUN" name="Tagihan2[]" aria-label="Username" aria-describedby="basic-addon1">
                                      </div>
                                </div>
                                <div id="FormTagihan2">

                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-10">
                                        <button class="btn btn-primary ">SUBMIT</button>
                                    </div>
                                </div>
                            </form>
                            <div id="respayment"></div>
                        </div>
                        <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">

                            <form id="FORM_REVERSAL">
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-2 col-form-label">NOP</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="Nop3"  placeholder="NOP">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-2 col-form-label">KODE PENGESAHAN</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="KodePengesahan"  placeholder="KODE PENGESAHAN">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-2 col-form-label">KODE KP</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="KodeKp" placeholder="KODE KP">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-2 col-form-label">DATETIME</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="DateTime3"  placeholder="DATETIME" value="{{ date('Y-m-d H:i:s') }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-2 col-form-label">TAHUN</label>
                                    <div class="input-group col-sm-10">
                                        <span class="input-group-text " id="addTagihan3"><i class="fa fa-fw fa-plus"></i></span>
                                        <input type="text" class="form-control Tagihan3" placeholder="TAHUN" name="Tagihan3[]" aria-label="Username" aria-describedby="basic-addon1">
                                      </div>
                                </div>
                                <div id="FormTagihan3">

                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-10">
                                        <button class="btn btn-primary ">SUBMIT</button>
                                    </div>
                                </div>
                            </form>
                            <div id="resreversal"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('#FORM_INQURY').on('submit', function(e){
      e.preventDefault();
      $('#resinquiry').html('');
    var url = "{{route('inquiry')}}";

    $.ajax({
      type: "GET",
      url: url,
      data: {Nop: $('#Nop').val(),MasaPajak: $('#MasaPajak').val(),Merchant: $('#Merchant').val(),DateTime: $('#DateTime').val(),},
      success: function (data) {
        //   alert(JSON.stringify(data));
        $('#resinquiry').html(JSON.stringify(data));
      }
    });
  });
    $('#FORM_PAYMENT').on('submit', function(e){
      e.preventDefault();
      $('#respayment').html('');
    var url = "{{route('payment')}}";
    $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var Tagihan2 = [];
      $('input[name^="Tagihan2"]').each(function() {
          var tahun = {}
          tahun.Tahun = $(this).val();
            Tagihan2.push(tahun);
        });
    $.ajax({
      type: "POST",
      url: url,
      data: {Nop: $('#Nop2').val(),Merchant: $('#Merchant2').val(),DateTime: $('#DateTime2').val(),Tagihan: Tagihan2,TotalBayar: $('#TotalBayar2').val()},
      success: function (data) {
        //   alert(JSON.stringify(data));
        $('#respayment').html(JSON.stringify(data));
      }
    });
  });

  var counter = 1;
  $(document).on('click', '#addTagihan2', function (e) {
        var newTextBoxDiv = $(document.createElement('div')).attr("id", 'TextBoxDiv' + counter);
        newTextBoxDiv.after().html('<div class="form-group row hapus">\
                                    <label for="inputEmail3" class="col-sm-2 col-form-label">TAHUN</label>\
                                    <div class="input-group col-sm-10">\
                                        <span class="input-group-text hapusTagihan2" id="basic-addon1"><i class="fa fa-fw fa-minus hapusTagihan2"></i></span>\
                                        <input type="text" class="form-control Tagihan2" placeholder="TAHUN" name="Tagihan2[]" aria-label="Username" aria-describedby="basic-addon1">\
                                      </div>\
                                </div>');
        newTextBoxDiv.appendTo("#FormTagihan2");
            counter++;
    })
        $(document).on('click', '.hapusTagihan2', function (e) {
            $(this).parents(".hapus").remove();
         });
    $('#FORM_REVERSAL').on('submit', function(e){
      e.preventDefault();
      $('#resreversal').html('');
    var url = "{{route('reversal')}}";
    $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var Tagihan3 = [];
      $('input[name^="Tagihan3"]').each(function() {
          var tahun = {}
          tahun.Tahun = $(this).val();
            Tagihan3.push(tahun);
        });
    $.ajax({
      type: "POST",
      url: url,
      data: {Nop: $('#Nop3').val(),KodePengesahan: $('#KodePengesahan').val(),DateTime: $('#DateTime3').val(),Tagihan: Tagihan3,KodeKp: $('#KodeKp').val()},
      success: function (data) {
        //   alert(JSON.stringify(data));
        $('#resreversal').html(JSON.stringify(data));
      }
    });
  });

  var counters = 1;
  $(document).on('click', '#addTagihan3', function (e) {
        var newTextBoxDiv = $(document.createElement('div')).attr("id", 'TextBoxDiv' + counters);
        newTextBoxDiv.after().html('<div class="form-group row hapus">\
                                    <label for="inputEmail3" class="col-sm-2 col-form-label">TAHUN</label>\
                                    <div class="input-group col-sm-10">\
                                        <span class="input-group-text hapusTagihan3" id="basic-addon1"><i class="fa fa-fw fa-minus hapusTagihan2"></i></span>\
                                        <input type="text" class="form-control Tagihan3" placeholder="TAHUN" name="Tagihan3[]" aria-label="Username" aria-describedby="basic-addon1">\
                                      </div>\
                                </div>');
        newTextBoxDiv.appendTo("#FormTagihan3");
            counters++;
    })
        $(document).on('click', '.hapusTagihan3', function (e) {
            $(this).parents(".hapus").remove();
         });
</script>
@endsection
