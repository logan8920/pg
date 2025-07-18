@extends('layouts.main')
@section('title')
    - Set Congif | {{ ucfirst($user->firmname) }}
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

        ul.list-group {
            flex-direction: unset;
        }

        .json-object {
            background: #fff;
            padding: 1rem 2rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            display: inline-block;
            min-width: 300px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .brace {
            font-weight: bold;
            font-size: 1.5rem;
        }

        .json-line {
            margin-left: 30px;
            margin-bottom: 8px;
        }

        .json-key {
            color: #b22222;
        }

        .json-input {
            width: 250px;
            padding: 2px 5px;
            font-family: monospace;
            font-size: 1rem;
        }
    </style>
@endsection
@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">
        @php
            $firm = ucfirst($user->firmname);
            $heading = "Configuration Setting of {$firm}";
        @endphp
        <x-pageheading :heading="'Set Configuration'" :navigation="['Api Partner', 'Set Configuration']" :description="$description ?? null" />

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header d-flex float-right justify-content-between py-3 w-100">
                <h6 class="align-content-around font-weight-bold justify-content-lg-between m-0 text-primary p-1">
                    {{ $heading }}
                </h6>
            </div>
            <div class="card-body pt-0 pb-0">
                <div class="row">
                    <!-- Sidebar -->
                    <div class="col col-md-2 pl-3 pr-3 pt-4 sidebar">
                        <button data-target="#contact-details"
                            class="btn btn-outline-primary border-left-primary btn-sm w-100 mb-3 font-weight-bolder text-left active">
                            CONTACT DETAILS
                            <i class="fas fa-check-circle float-right mt-1 text-success"></i>
                        </button>
                        <button data-target="#api-credentials"
                            class="btn btn-outline-primary border-left-primary btn-sm w-100 mb-3 font-weight-bolder text-left">
                            API CREDENTIALS
                            {!! !$user->apiCredentials()->exists()
                                ? '<i class="fas fa-exclamation-circle float-right mt-1 text-warning"></i>'
                                : ' <i class="fas fa-check-circle float-right mt-1 text-success"></i>' !!}

                        </button>
                        <button data-target="#api-config"
                            class="btn btn-outline-primary border-left-primary btn-sm w-100 mb-3 font-weight-bolder text-left">
                            API CONFIG
                            {!! !$user->apiConfig()->exists()
                                ? '<i class="fas fa-exclamation-circle float-right mt-1 text-warning"></i>'
                                : ' <i class="fas fa-check-circle float-right mt-1 text-success"></i>' !!}
                        </button>
                    </div>

                    <!-- Main Content -->
                    <div class="col-md-10">
                        <div class="content">

                            <!-- Header Section -->
                            <div class="d-flex mb-4">
                                <div class="profile-box">
                                    <img src="https://projects.ciphersquare.in/kyc/resourses/assets/img/profile.png"
                                        alt="relimoney">
                                </div>
                                <div class="align-content-around ml-3">
                                    <h6 class="font-weight-bolder mb-0">{{ ucwords($user->firmname) }}</h6>
                                    <span class="text-muted">{{ ucwords($user->username) }}</span>
                                    <i class="fas fa-check-circle text-success ml-2"></i>
                                </div>
                            </div>

                            <!-- Form -->
                            <div class="card p-4 custom-box" id="contact-details">
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Name</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" value="{{ ucwords($user->name) }}"
                                            readonly>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Phone Number</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" value="{{ ucwords($user->phone) }}"
                                            readonly>
                                    </div>
                                </div>

                                <div class="form-group row align-items-center">
                                    <label class="col-sm-2 col-form-label">Email ID</label>
                                    <div class="col-sm-10 d-flex align-items-center">
                                        <input type="email" class="form-control" value="{{ ucwords($user->email) }}"
                                            readonly>
                                    </div>
                                </div>

                                <div class="form-group row align-items-center">
                                    <label class="col-sm-2 col-form-label">Business Name</label>
                                    <div class="col-sm-10 d-flex align-items-center">
                                        <input type="email" class="form-control"
                                            value="{{ ucwords($user->business_name) }}" readonly>
                                    </div>
                                </div>

                                <div class="form-group row align-items-center">
                                    <label class="col-sm-2 col-form-label">Username</label>
                                    <div class="col-sm-10 d-flex align-items-center">
                                        <input type="email" class="form-control" value="{{ ucwords($user->username) }}"
                                            readonly>
                                    </div>
                                </div>

                                <div class="form-group row align-items-center">
                                    <label class="col-sm-2 col-form-label">Firm Name</label>
                                    <div class="col-sm-10 d-flex align-items-center">
                                        <input type="email" class="form-control" value="{{ ucwords($user->firmname) }}"
                                            readonly>
                                    </div>
                                </div>

                                <div class="form-group row d-none">
                                    <label class="col-sm-2 col-form-label">API Permission</label>
                                    <div class="col-sm-10">
                                        <div class="permissions">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" checked disabled>
                                                <label class="form-check-label">Connected Banking</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" checked disabled>
                                                <label class="form-check-label">Payout</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" checked disabled>
                                                <label class="form-check-label">Pennydrop</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" checked disabled>
                                                <label class="form-check-label">Upi Collect</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row justify-content-end d-none">
                                    <div class="col-sm-10 text-right">
                                        <button type="submit" class="btn btn-submit">Submit</button>
                                    </div>
                                </div>

                            </div>

                            <div class="card p-4 d-none custom-box" id="api-credentials">
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label"> Ip Address</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="ip-address"
                                            value="{{ ucwords(implode(',', json_decode($user->apiCredentials?->ipaddress ?? '{}', true))) }}"
                                            readonly>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Status</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control"
                                            value="{{ $user->apiCredentials?->status ? 'Active' : 'In-active' }}"
                                            readonly>
                                    </div>
                                </div>

                                <div class="form-group row align-items-center">
                                    <label class="col-sm-2 col-form-label">Key</label>
                                    <div class="col-sm-10 d-flex align-items-center">
                                        <input type="password" class="form-control"
                                            value="{{ $user->apiCredentials?->key ? 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX' : '' }}"
                                            readonly>
                                    </div>
                                </div>

                                <div class="form-group row align-items-center">
                                    <label class="col-sm-2 col-form-label">Iv</label>
                                    <div class="col-sm-10 d-flex align-items-center">
                                        <input type="password" class="form-control"
                                            value="{{ $user->apiCredentials?->iv ? 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX' : '' }}"
                                            readonly>
                                    </div>
                                </div>

                                <div class="form-group row align-items-center">
                                    <label class="col-sm-2 col-form-label">Payment Gateway Permission</label>
                                    <div class="col-sm-10 d-flex align-items-center">
                                        <input type="text" class="form-control"
                                            value="{{ $user->apiCredentials?->pg ? 'Active' : 'In-active' }}" readonly>
                                    </div>
                                </div>

                                <div class="form-group row align-items-center">
                                    <label class="col-sm-2 col-form-label">Date Added</label>
                                    <div class="col-sm-10 d-flex align-items-center">
                                        <input type="text" class="form-control"
                                            value="{{ $user->apiCredentials?->date_added ? date('d M, Y', strtotime($user->apiCredentials->date_added)) : '' }}"
                                            readonly>
                                    </div>
                                </div>

                            </div>

                            <div class="card p-4 d-none custom-box" id="api-config">
                                <form action="{{ route('api-partner-config.post', $user->id) }}" validate id="regForm"
                                    callbackFn="checkModeSelection" method="POST">
                                    @csrf

                                    @if (count($pgCompanies->toArray()))
                                        @php $cKey = 0; @endphp
                                        @foreach ($pgCompanies as $pgCompany)
                                            <div
                                                class="form-group row form-group p-2 rounded row m-2 bg-gray-200 overflow-auto">
                                                <div
                                                    class="col-sm-12 form-check-inline mr-0 pl-3 text-nowrap justify-content-between pt-1 pb-2">
                                                    <div class="align-items-center d-flex font-weight-bold">
                                                        <input class="form-check-input"
                                                            name="pg[{{ $pgCompany->name }}][id]"
                                                            data-validation-target="{{ $pgCompany->name }}"
                                                            value="{{ $pgCompany->id }}" type="checkbox"
                                                            onchange="this.nextElementSibling.required = this.checked"
                                                            {{ isset($apiPartnerModeCompanies[$pgCompany->id]['pg_company_id']) ? 'checked' : '' }}>
                                                        {{ strtoupper($pgCompany->name) }}&nbsp;&nbsp;|&nbsp;&nbsp;
                                                        <input type="number"
                                                            name="pg[{{ $pgCompany->name }}][c_per_day_limit]"
                                                            value="{{ $apiPartnerModeCompanies[$pgCompany->id]['c_per_day_limit'] ?? '' }}"
                                                            class="form-control h-75 num"
                                                            validation="{{ $pgCompany->name }}" placeholder="₹ Limit"
                                                            style="width:120px">&nbsp; / Per Day
                                                    </div>
                                                    @can("api-partner-pg-credentials")
                                                    <div class="mr-2">
                                                        <a href="#pgCredentialModal"
                                                            data-pg-cred-modal="#{{ $pgCompany->name }}"
                                                            data-href="{{ route('api-partner.pg.credentials', ["pgCompany" => $pgCompany->id, "user" => $user->id]) }}"
                                                            data-pg-column="{{ base64_encode(json_encode(array_keys($pgCompany->pg_config ?? []))) }}"
                                                            class="font-weight-bold" style="text-decoration: none">
                                                            <i class="fas fa-key"></i>&nbsp;
                                                            {{ strtoupper($pgCompany->name) }} CREDENTIALS
                                                        </a>
                                                    </div>
                                                    @endcan
                                                </div>
                                                <hr class="p-0 m-0">
                                                <div class="col-sm-12">
                                                    <div class="permissions" style="background: none">
                                                        @if (count($modes->toArray()))
                                                            @foreach ($modes as $mode)
                                                                <ul class="form-check list-group">
                                                                    <li class="list-group-item w-100">
                                                                        <label
                                                                            for="{{ $pgCompany->name . $mode->id . $mode->name }}"
                                                                            class="form-check-inline ml-3 text-nowrap">
                                                                            <input class="form-check-input"
                                                                                type="checkbox"
                                                                                validation="{{ $pgCompany->name }}"
                                                                                value="{{ $mode->id }}"
                                                                                name="pg[{{ $pgCompany->name }}][mode][{{ $mode->name }}][id]"
                                                                                id="{{ $pgCompany->name . $mode->id . $mode->name }}"
                                                                                {{ isset($apiPartnerModeCompanies[$pgCompany->id]['config'][$mode->id]) ? 'checked' : '' }}>
                                                                            {{ ucwords($mode->name) }}
                                                                            Mode&nbsp;&nbsp;-&nbsp;&nbsp;
                                                                            <input type="number"
                                                                                class="form-control h-75 num"
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
                                        @endforeach
                                    @else
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Compnay Not Found<label>

                                        </div>
                                    @endif
                                    <div class="row justify-content-end">
                                        <div class="col-sm-10 text-right">
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
    @can("api-partner-pg-credentials")
    {{-- Pg CREDENTIALS Modal --}}
    <!-- Modal -->
    <div class="modal fade" data-backdrop="static" id="pgCredentialModal" tabindex="-1"
        aria-labelledby="pgCredentialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 662px;">
            <div class="modal-content">
                <form action="" class="any-form" method="POST" postCallbackFn="closePgCredModal">
                    <div class="modal-header bg-">
                        <h5 class="modal-title" id="pgCredentialModalLabel">Modal title</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <div>
                            <h4>Default PG Keys &nbsp;<small style="font-size: 12px"><em>(Default Keys are
                                        mandetory)</em></small></h4>
                            <div class="d-flex pb-3 w-100">
                                <input type="text" placeholder="Enter New Key to Credentials"
                                    class="form-control w-100">
                                <button class="btn btn-success float-right column-plus">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="json-object w-100">
                            <div class="brace">{</div>

                            <div class="form-group row pl-4" id="column-area">
                                <label class="json-key col-sm-2 col-form-label">"name":</label>
                                <div class="col-sm-10 d-flex">
                                    <input type="text" class="form-control" id="inputEmail3">
                                    <span class="fa-2x font-weight-bolder align-content-end">,</span>
                                </div>
                                <label class="json-key col-sm-2 col-form-label">"name":</label>
                                <div class="col-sm-10 d-flex">
                                    <input type="text" class="form-control" id="inputEmail3">
                                    <span class="fa-2x font-weight-bolder align-content-end">,</span>
                                </div>
                                <label class="json-key col-sm-2 col-form-label">"name":</label>
                                <div class="col-sm-10 d-flex">
                                    <input type="text" class="form-control" id="inputEmail3">
                                    <span class="fa-2x font-weight-bolder align-content-end">,</span>
                                </div>
                            </div>

                            <div class="brace">}</div>
                        </div>


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- ./Pg CREDENTIALS Modal --}}
    @endcan
@endsection

@section('js')
    <script src="{{ asset('assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('assets/js/additional-methods.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
    <script src="{{ asset('assets/js/bootstrap4-toggle.min.js') }}"></script>
    <script src="{{ asset('assets/js/form-submit.js')}}"></script>
    <script>
        let selectedPartner, callbackErrorMessage;
        const josnColumn = (cName,minus=false) => `<label for="${cName}" class="json-key col-sm-2 col-form-label">"${cName}":</label>
        <div class="col-sm-10 d-flex mb-0">
            <input type="text" class="form-control after-parent" id="${cName}" name="${cName}" required autocomplete="off">
            <span class="fa-2x font-weight-bolder align-content-end">,</span>
            ${minus ? '<button class="btn btn-danger column-mins h-75">-</button>' : ''}
        </div>`;

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

            @if (!$apiPartnerModeCompanies)
                $('.collapse').each(function() {
                    $(this).collapse('toggle');
                });
            @endif

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
            @can("api-partner-pg-credentials")
            const $columnArea = $("#column-area");
            //pg credentials modal #
            $("a[data-pg-cred-modal]").on("click", function(e) {
                e.preventDefault();

                $columnArea.html('');
                const column = JSON.parse(atob(this.dataset.pgColumn));
                const partnerId = '{{ $user->username }}';
                const userId = '{{ $user->id }}';
                const $pgCred = $(this.attributes.href.textContent);
                $pgCred.find('.modal-title').html(`${this.innerHTML} | ${partnerId}`);
                $pgCred.modal("show");
                const action = this.dataset.href;
                $columnArea.closest('form').attr('action',action);
                column.forEach(key => {
                    $columnArea.append(josnColumn(key));
                });
                $columnArea.closest('form').validate(validationConfig);

            });

            //plus button
            $(".column-plus").on("click",function(e){
                e.preventDefault();
                let key = $(this).prev('input').val();
                if(!key) {
                    toastr.error("Please Enter Key Name !");
                    return;
                }
                let alreadExist = $columnArea.find(`[name="${key}"]`);
                if(alreadExist.length) {
                    toastr.error("Enter Key Name Already Exists, Please Enter Unique Name !");
                    return;
                }
                $columnArea.append(josnColumn(key,true));
                $(this).prev('input').val('');
                 $columnArea.closest('form').validate(validationConfig);
            });

            //minus 
            $(document).on("click",".column-mins",function(e){
                e.preventDefault();
                const labelFor = $(this).prev('input').attr("id");
                // console.log($(this).parent('div').prev('label[for="${labelFor}"]'));
                $(this).parent('div').prev(`label`).remove();
                $(this).parent('div').remove();
                $columnArea.closest('form').validate(validationConfig);
            });
            @endcan
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
        @can("api-partner-pg-credentials")
        function closePgCredModal() {
            $('#pgCredentialModal').modal("hide");
            return true;
        }
        @endcan
    </script>
@endsection
