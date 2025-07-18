@extends('layouts.main')
@section('title')
    - Set Default Congif | {{ ucfirst($pgCompany->name) }}
@endsection
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap4-toggle.min.css') }}">
    <style>
        body {
            background-color: #f8f9fc;
            font-family: "Nunito", sans-serif;
        }

        .sidebar {
            background-color: #fff;
            min-height: 100%;
            border-right: 1px solid #e3e6f0;
        }

        .sidebar h6 {
            font-weight: 700;
            font-size: 14px;
            color: #4e73df;
            margin-bottom: 20px;
        }

        .sidebar .btns {
            width: 100%;
            text-align: left;
            font-weight: 600;
            color: #4e73df;
            background: none;
            border: 1px solid #4e73df;
            border-radius: 0.35rem;
            margin-bottom: 10px;
        }

        .content {
            padding: 30px;
        }

        .verified-icon {
            width: 20px;
            height: 20px;
            margin-left: 8px;
        }

        .form-group label {
            font-weight: 600;
            font-size: 14px;
        }

        .form-control[readonly] {
            background-color: #e9ecef;
        }

        .permissions {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 0.35rem;
            margin-top: -10px;
        }

        .form-check-label {
            font-size: 14px;
            color: #5a5c69;
        }

        .btn-submit {
            background-color: #4e73df;
            color: white;
            font-weight: 600;
            padding: 8px 24px;
            border-radius: 0.35rem;
        }

        .profile-box img {
            width: 75px;
        }

        .form-control:disabled,
        .form-control[readonly] {
            background: none;
        }

        .form-control[readonly] {
            background: none;
        }

        tag.tagify__tag {
            margin-block: 0;
            margin: 2.5px 1px 0px 2.5px;
        }

        .toggle-icon {
            float: right;
            transition: transform 0.3s;
        }

        .rotate {
            transform: rotate(180deg);
        }


        input.error {
            width: 100%;
        }

        label.error {
            width: 100%;
        }
    </style>
@endsection
@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">
        @php
            $firm = ucfirst($pgCompany->name);
            $heading = "Default Configuration Setting of {$firm}";
        @endphp
        <x-pageheading :heading="'Set Default Configuration'" :navigation="['Api Partner', 'Set Configuration']" :description="$description ?? null" />

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header d-flex float-right justify-content-between py-3 w-100">
                <h6 class="align-content-around font-weight-bold justify-content-lg-between m-0 text-primary p-1">
                    {{ $heading }}
                </h6>
            </div>
            <div class="card-body pt-0 pb-0">
                <div class="row">
                    <!-- Main Content -->
                    <div class="col-md-12">
                        <div class="">
                            <div class="custom-box" id="api-config">
                                <form action="{{ route('pg-company.default.config.post', $pgCompany->id) }}" validate
                                    callbackFn="checkModeSelection" id="regForm" method="POST">
                                    @csrf

                                    @if (count($pgCompany->toArray()))
                                        @php $cKey = 0; @endphp
                                        <div class="form-group row form-group p-2 rounded row m-2">
                                            <label class="col-sm-6 form-check-inline ml-3 text-nowrap">
                                                <input class="form-check-input" name="pg[{{ $pgCompany->name }}][id]"
                                                    data-validation-target="{{ $pgCompany->name }}"
                                                    value="{{ $pgCompany->id }}" type="checkbox"
                                                    onchange="this.nextElementSibling.required = this.checked"
                                                    {{ isset($apiPartnerModeCompanies[$pgCompany->id]['pg_company_id']) ? 'checked' : '' }}>
                                                {{ strtoupper($pgCompany->name) }}&nbsp;&nbsp;|&nbsp;&nbsp;
                                                <input type="number" name="pg[{{ $pgCompany->name }}][c_per_day_limit]"
                                                    value="{{ $apiPartnerModeCompanies[$pgCompany->id]['c_per_day_limit'] ?? '' }}"
                                                    class="form-control h-75 num" validation="{{ $pgCompany->name }}"
                                                    placeholder="₹ Limit" style="width:120px">&nbsp; / Per Day
                                            </label>
                                            <hr class="p-0 m-0">
                                            <div class="col-sm-12">
                                                <div class="permissions" style="background: none">
                                                    @if (count($modes->toArray()))
                                                        @foreach ($modes as $mode)
                                                            <ul class="form-check list-group">
                                                                <li class="list-group-item">
                                                                    <label
                                                                        for="{{ $pgCompany->name . $mode->id . $mode->name }}"
                                                                        class="form-check-inline ml-3 text-nowrap">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            validation="{{ $pgCompany->name }}"
                                                                            value="{{ $mode->id }}"
                                                                            name="pg[{{ $pgCompany->name }}][mode][{{ $mode->name }}][id]"
                                                                            id="{{ $pgCompany->name . $mode->id . $mode->name }}"
                                                                            {{ isset($apiPartnerModeCompanies[$pgCompany->id]['config'][$mode->id]) ? 'checked' : '' }}>
                                                                        {{ ucwords($mode->name) }}
                                                                        Mode&nbsp;&nbsp;-&nbsp;&nbsp;
                                                                        <input type="number" class="form-control h-75 num"
                                                                            style="width:110px"
                                                                            placeholder="{{ $mode->name }} Limit ₹"
                                                                            validation="{{ $pgCompany->name }}"
                                                                            name="pg[{{ $pgCompany->name }}][mode][{{ $mode->name }}][limit]"
                                                                            value="{{ $apiPartnerModeCompanies[$pgCompany->id]['config'][$mode->id]['mode_limit'] ?? '' }}">&nbsp;
                                                                        / Allowed
                                                                    </label>
                                                                    <a class="pl-3" data-toggle="collapse"
                                                                        href="#list-{{ $pgCompany->name . $mode->id . $mode->name }}"
                                                                        role="button" aria-expanded="false"
                                                                        aria-controls="list-{{ $pgCompany->name . $mode->id . $mode->name }}">

                                                                        <strong class="float-right">Set Charges <span
                                                                                class="toggle-icon">&#9660;</span></strong>
                                                                    </a>
                                                                    <div class="collapse mt-2 divs"
                                                                        id="list-{{ $pgCompany->name . $mode->id . $mode->name }}">
                                                                        @php $dataAray = isset($apiPartnerModeCompanies[$pgCompany->id]['config'][$mode->id]['charges']) ? $apiPartnerModeCompanies[$pgCompany->id]['config'][$mode->id]['charges'] : []; @endphp
                                                                        @if (count($dataAray))
                                                                            @foreach ($dataAray as $ii => $data)
                                                                                <div class="border p-1 rounded row">
                                                                                    <div class="col-sm-4">
                                                                                        <div class="form-group">
                                                                                            <label>Min
                                                                                                Slab <span
                                                                                                    class="text-danger">*</span>
                                                                                            </label>
                                                                                            <input type="number"
                                                                                                placeholder="0"
                                                                                                validation="{{ $pgCompany->name }}"
                                                                                                name="pg[{{ $pgCompany->name }}][mode][{{ $mode->name }}][charges][min][{{ $ii }}]"
                                                                                                class="form-control num min"
                                                                                                min="100"
                                                                                                value="{{ $data['min'] ?? '' }}">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-sm-4">
                                                                                        <div class="form-group">
                                                                                            <label>Max
                                                                                                Slab <span
                                                                                                    class="text-danger">*</span></label>
                                                                                            <input type="number"
                                                                                                name="pg[{{ $pgCompany->name }}][mode][{{ $mode->name }}][charges][max][{{ $ii }}]"
                                                                                                value="{{ $data['max'] ?? '' }}"
                                                                                                placeholder="2000"
                                                                                                class="form-control num max"
                                                                                                validation="{{ $pgCompany->name }}">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-sm-3">
                                                                                        <div class="form-group">
                                                                                            <label class="w-100"
                                                                                                for="Charges">Charges
                                                                                                <span
                                                                                                    class="text-danger">*</span>
                                                                                                <input type="hidden"
                                                                                                    name="pg[{{ $pgCompany->name }}][mode][{{ $mode->name }}][charges][charges_type][{{ $ii }}]"
                                                                                                    value="Percentage">
                                                                                                <input type="checkbox"
                                                                                                    class="charges-type"
                                                                                                    {{ $data['charges_type'] == 'Flat' ? 'checked' : ($data['charges_type'] == 'Percentage' ? '' : 'checked') }}
                                                                                                    name="pg[{{ $pgCompany->name }}][mode][{{ $mode->name }}][charges][charges_type][{{ $ii }}]"
                                                                                                    value="{{ $data['charges_type'] ?? 'Flat' }}"
                                                                                                    data-toggle="toggle"
                                                                                                    data-on="Flat"
                                                                                                    data-width="100"
                                                                                                    data-off="Percentage"
                                                                                                    data-size="xs"
                                                                                                    data-onstyle="outline-success"
                                                                                                    data-offstyle="outline-danger">
                                                                                            </label>
                                                                                            <input type="number"
                                                                                                placeholder="₹ 10.00"
                                                                                                class="form-control num amt"
                                                                                                name="pg[{{ $pgCompany->name }}][mode][{{ $mode->name }}][charges][amt][{{ $ii }}]"
                                                                                                value="{{ $data['amt'] ?? '' }}"
                                                                                                validation="{{ $pgCompany->name }}">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div
                                                                                        class="align-content-center col-sm-1 mt-2">
                                                                                        <button
                                                                                            class="btn btn-{{ $ii === 0 ? 'danger' : 'success' }} {{ $ii === 0 ? 'plus-slub' : 'minus-slab' }}"
                                                                                            type="button">
                                                                                            <i
                                                                                                class="fas {{ $ii === 0 ? 'fa-plus' : 'fa-minus' }}"></i>
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            @endforeach
                                                                        @else
                                                                            <div class="border p-1 rounded row">
                                                                                <div class="col-sm-4">
                                                                                    <div class="form-group">
                                                                                        <label>Min
                                                                                            Slab <span
                                                                                                class="text-danger">*</span>
                                                                                        </label>
                                                                                        <input type="number"
                                                                                            placeholder="0"
                                                                                            name="pg[{{ $pgCompany->name }}][mode][{{ $mode->name }}][charges][min][0]"
                                                                                            class="form-control num min"
                                                                                            min="100"
                                                                                            oninput="this.closest('.row').previousElementSibling ? ((this.min = parseInt(this.closest('.row').previousElementSibling.querySelectorAll('input')[1].value ?? 0) + 1),(this.max = parseInt(this.closest('.row').previousElementSibling.querySelectorAll('input')[1].value ?? 0) + 1)) : this.closest('.row').querySelectorAll('input')[1].min = parseInt(this.value) + 1"
                                                                                            validation="{{ $pgCompany->name }}">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-sm-4">
                                                                                    <div class="form-group">
                                                                                        <label>Max
                                                                                            Slab <span
                                                                                                class="text-danger">*</span></label>
                                                                                        <input type="number"
                                                                                            placeholder="2000"
                                                                                            name="pg[{{ $pgCompany->name }}][mode][{{ $mode->name }}][charges][max][0]"
                                                                                            class="form-control num max"
                                                                                            min=""
                                                                                            oninput="this.min = parseInt(this.closest('.row').querySelectorAll('input')[0].value ?? 0) + 1"
                                                                                            validation="{{ $pgCompany->name }}">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-sm-3">
                                                                                    <div class="form-group">
                                                                                        <label class="w-100"
                                                                                            for="Charges">Charges
                                                                                            <span
                                                                                                class="text-danger">*</span>
                                                                                            <input type="hidden"
                                                                                                name="pg[{{ $pgCompany->name }}][mode][{{ $mode->name }}][charges][charges_type][0]"
                                                                                                value="Percentage">
                                                                                            <input type="checkbox"
                                                                                                class="charges-type"
                                                                                                checked
                                                                                                name="pg[{{ $pgCompany->name }}][mode][{{ $mode->name }}][charges][charges_type][0]"
                                                                                                value="Flat"
                                                                                                data-toggle="toggle"
                                                                                                data-on="Flat"
                                                                                                data-width="100"
                                                                                                data-off="Percentage"
                                                                                                data-size="xs"
                                                                                                data-onstyle="outline-success"
                                                                                                data-offstyle="outline-danger">
                                                                                        </label>
                                                                                        <input type="number"
                                                                                            placeholder="₹ 10.00"
                                                                                            class="form-control num amt"
                                                                                            name="pg[{{ $pgCompany->name }}][mode][{{ $mode->name }}][charges][amt][0]"
                                                                                            validation="{{ $pgCompany->name }}">
                                                                                    </div>
                                                                                </div>
                                                                                <div
                                                                                    class="align-content-center col-sm-1 mt-2">
                                                                                    <button
                                                                                        class="btn btn-danger plus-slub"
                                                                                        type="button">
                                                                                        <i class="fas fa-plus"></i>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </li>

                                                            </ul>
                                                        @endforeach
                                                    @else
                                                        <div class="form-check">
                                                            <p>No Mode Found</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @php $cKey++; @endphp
                                    @else
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Compnay Not Found<label>

                                        </div>
                                    @endif
                                    <div class="row justify-content-end">
                                        <div class="col-sm-12 text-right p-3">
                                            <button type="submit" class="btn btn-primary">Submit</button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('assets/js/additional-methods.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
    <script src="{{ asset('assets/js/bootstrap4-toggle.min.js') }}"></script>
    <script>
        let selectedPartner, callbackErrorMessage;
        const validationConfig = {
            errorPlacement: function(error, element) {
                if (element.closest('.input-group')?.length) {
                    error.insertAfter(element.closest('.input-group'));
                } else {
                    if ($(element).hasClass("after-parent")) {
                        error.addClass("text-center w-100").insertAfter(element[0].parentElement)
                    } else {
                        error.insertAfter(element);
                    }
                }
            }
        };
        $(document).ready(function() {
            // Form Validation
            $('form[validate]').validate(validationConfig);

            //sidebar tabs
            $(".sidebar button").on("click", function(e) {
                e.preventDefault();
                $(".sidebar button").removeClass('active');
                $('.custom-box').addClass('d-none');
                const $target = $(this.dataset.target);
                $target.removeClass('d-none');
                $(this).addClass('active');
            })

            var input = document.querySelector('#ip-address');
            // initialize Tagify on the above input node reference
            new Tagify(input)

            //
            $('div.collapse.mt-2.divs').on('show.bs.collapse', function() {
                $(this).prev().find('.toggle-icon').html('&#9650;'); // ▲
            });

            $('div.collapse.mt-2.divs').on('hide.bs.collapse', function() {
                $(this).prev().find('.toggle-icon').html('&#9660;'); // ▼
            });

            //#
            $("button.plus-slub").on("click", function(e) {
                e.preventDefault();
                const $row = $(this).closest('.row');
                const $newRow = $row.clone()
                const $checkbox = $newRow.find('input[type=checkbox]').clone();
                const name = $checkbox.attr("name");
                const $rows = $(this).closest('.divs').find(".row");
                const lastRow = $rows[$rows.length - 1];
                let updatedName = name.replace(/\[\d+\]$/, `[${$rows.length}]`);
                console.log($newRow.find('input'));
                let updatedMinName = $newRow.find('input.min').attr('name').replace(/\[\d+\]$/,
                    `[${$rows.length}]`);
                let updatedMaxName = $newRow.find('input.max').attr('name').replace(/\[\d+\]$/,
                    `[${$rows.length}]`);
                let updatedAmtName = $newRow.find('input.amt').attr('name').replace(/\[\d+\]$/,
                    `[${$rows.length}]`);
                $checkbox.attr("name", updatedName);
                $newRow.find('input[type=hidden]').attr("name", updatedName);
                $newRow.find('input.min').attr("name", updatedMinName);
                $newRow.find('input.max').attr("name", updatedMaxName);
                $newRow.find('input.amt').attr("name", updatedAmtName);
                $newRow.find('input:not([type="hidden"])').val('');
                //console.log($newRow.html())
                $newRow.find('[data-toggle="toggle"]').remove();
                $newRow.find('button.plus-slub').removeClass('btn-danger plus-slub').addClass(
                    'minus-slab btn-success').html("<i class='fas fa-minus'></i>");
                $newRow.insertAfter(lastRow);
                $newRow.find('[for="Charges"]').append($checkbox);
                $newRow.find('input[type=checkbox]').bootstrapToggle();
                $('form[validate]').validate(validationConfig);
            });

            $(document).on("click", ".minus-slab", function(e) {
                e.preventDefault();
                $(this).closest('.row').remove();
            });

            $('#toggle-demo').bootstrapToggle();

        });

        $(document).ready(function() {
            $(document).off('change', 'input[data-toggle="toggle"]')
                .on('change', 'input[data-toggle="toggle"]', function() {
                    const $input = $(this);
                    const $cInput = $(this).closest('label').next('input');
                    const val = $input.prop('checked') ? $input.attr('data-on') : $input.attr('data-off');
                    const searchValue = val === "Percentage" ? "Flat" : "Percentage";
                    $cInput[0].name = $cInput[0].name.replace(searchValue, val);
                    $cInput[0].placeholder = val === "Percentage" ? "10.00%" : "₹ 10.00";
                    this.value = val === "Percentage" ? "Percentage" : "Flat";

                });

            $('.collapse').each(function() {
                $(this).collapse('toggle');
            });

            $("input[validation]").on("change", function() {
                if ($(this).is(":checked")) {
                    $(this).closest('ul.form-check').find('input:not([type=checkbox])').prop('required',
                        true);
                    $('form[validate]').validate(validationConfig);
                } else {
                    $(this).closest('ul.form-check').find('input:not([type=checkbox])').prop('required',
                        false);
                    $('form[validate]').validate(validationConfig);
                }
            });

            $('input[data-validation-target]').on("change", function() {
                const limitEle = this?.nextElementSibling?.nextElementSibling;
                limitEle && (limitEle.required = this.checked);
                $('form[validate]').validate(validationConfig);
            });
        });

        function checkModeSelection() {

            const $pg = $('input[data-validation-target]:checked');

            if (!$pg.length) {
                callbackErrorMessage = 'Please select any Payment Gateway';
                return false;
            }

            let ok = true;
            $pg.each(function() {
                const pgName = this.dataset.validationTarget.trim() || 'Gateway';
                const $modes = $('input[validation]', this.closest('.row'));

                if (!$modes.filter(':checked').length) {
                    callbackErrorMessage = `Please select any mode of Payment Gateway – ${pgName}`;
                    ok = false;
                    return false;
                }
            });

            return ok;
        }
    </script>
@endsection
