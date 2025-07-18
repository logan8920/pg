@extends('layouts.main')
@section('title')
    - User List
@endsection
@section('css')
    <link href="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/virtual-select.min.css') }}">
    <style>
        .validate {
            border - radius: 20 px;
            height: 40 px;
            background - color: red;
            border: 1 px solid red;
            width: 140 px
        }

        .inputs input {
            width: 40 px;
            height: 40 px
        }

        input[type=number]::-webkit - inner - spin - button,
        input[type=number]::-webkit - outer - spin - button {
            -webkit - appearance: none;
            - moz - appearance: none;
            appearance: none;
            margin: 0
        }

        .card - 2 {
            background - color: #fff;
            padding: 10 px;
            width: 350 px;
            height: 100 px;
            bottom: -50 px;
            left: 20 px;
            position: absolute;
            border - radius: 5 px
        }

        .card - 2 .content {
            margin - top: 50 px
        }

        .card - 2 .content a {
            color: red
        }

        .form-control:disabled,
        .form-control[readonly] {
            background-color: #fff !important;
        }
    </style>
@endsection
@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <x-pageheading :heading="'Api Partner List'" :navigation="['Api Partner', 'List']" :description="$description ?? null" />

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header d-flex float-right justify-content-between py-3 w-100">
                <h6 class="align-content-around font-weight-bold justify-content-lg-between m-0 text-primary">List of user
                </h6>
                @can('api-partner-create')
                    <button class="btn btn-primary" data-href="{{ route('api-partner.store') }}" type="button"
                        id="openOffcanvas"><i class="fas fa-fw fa-plus"></i> &nbsp;
                        Add New Partner</button>
                @endcan
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center nowrap" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Sr No.</th>
                                <th>Name</th>
                                <th>Firm Name</th>
                                <th>Business Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Phone</th>
                                @if (auth()->user()->api_partner === 0)
                                    <th>Status</th>
                                    <th>Added Date</th>
                                    <th>Datetime</th>
                                    <th>Created By</th>
                                @endif
                                <th>Api Configuration</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    @include('partials.otp-modal')
    @canany(['api-partner-create', 'api-partner-edit'])
        <!-- /.container-fluid -->
        <div class="offcanvas w-25 overflow-auto" id="myOffcanvas">
            <div class="offcanvas-header">
                <h4 class="text-black-50">Add New User</h4>
                <button id="closeOffcanvas"
                    style="background:none; border:none; color:blak; font-size:20px; cursor:pointer;">&times;</button>
            </div>
            <hr>
            <div class="offcanvas-body">
                <form autocomplete="off" action="" id="regForm" method="post" validate>
                    @csrf
                    <div class="form-group">
                        <label class="text-black-50" for="name">Partner Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <input type="text" name="name" class="form-control after-parent" id="name"
                                placeholder="Enter Name..." required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="text-black-50" for="firmname">Firm Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-building"></i>
                                </div>
                            </div>
                            <input type="text" name="firmname" class="form-control after-parent" id="firmname"
                                placeholder="Enter Firm Name..." required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="text-black-50" for="business_name">Business Name <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-industry"></i>
                                </div>
                            </div>
                            <input type="text" name="business_name" class="form-control after-parent" id="business_name"
                                placeholder="Enter Business Name..." required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="text-black-50" for="username"> Username <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                            </div>
                            <input type="text" name="username" class="form-control after-parent" id="username"
                                placeholder="Generate User Name..." required readonly>
                            <div class="input-group-append">
                                <label class="input-group-text" style="cursor: pointer" title="Generate Username">
                                    <i class="fas fa-cog" id="generate-username" data-bs-toggle="tooltip" title="Generate Username"></i>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="text-black-50" for="email">Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </div>
                            </div>
                            <input type="email" name="email" class="form-control after-parent" id="email"
                                placeholder="Enter Email..." required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="text-black-50" for="phone">Phone <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-phone-alt"></i>
                                </div>
                            </div>
                            <input type="number" name="phone" maxlength="10" class="form-control after-parent num"
                                id="phone" placeholder="0123456789" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="text-black-50" for="payment_gateway">Select Payment Gateway
                            <svg xmlns="http://www.w3.org/2000/svg" 
                                viewBox="0 0 50 50" 
                                width="15px" 
                                height="15px"
                                data-bs-toggle="tooltip" 
                                data-bs-placement="top"
                                style="cursor: pointer;"
                                title="Select PG to set Default settings to api partner">
                                <path
                                    d="M 25 2 C 12.309295 2 2 12.309295 2 25 C 2 37.690705 12.309295 48 25 48 C 37.690705 48 48 37.690705 48 25 C 48 12.309295 37.690705 2 25 2 z M 25 4 C 36.609824 4 46 13.390176 46 25 C 46 36.609824 36.609824 46 25 46 C 13.390176 46 4 36.609824 4 25 C 4 13.390176 13.390176 4 25 4 z M 25 11 A 3 3 0 0 0 22 14 A 3 3 0 0 0 25 17 A 3 3 0 0 0 28 14 A 3 3 0 0 0 25 11 z M 21 21 L 21 23 L 22 23 L 23 23 L 23 36 L 22 36 L 21 36 L 21 38 L 22 38 L 23 38 L 27 38 L 28 38 L 29 38 L 29 36 L 28 36 L 27 36 L 27 21 L 26 21 L 22 21 L 21 21 z" />
                            </svg>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <select name="payment_gateway" class="after-parent" id="payment_gateway"
                                placeholder="Select Payment Gateway..." multiple></select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="text-black-50" for="status">Select Status <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <select name="status" class="form-control after-parent" id="role"
                                placeholder="Enter Status..." required>
                                <option value="">Select Status...</option>
                                <option value="0">In-active</option>
                                <option value="1">Active</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mt-2">
                        <button class="btn btn-danger" type="submit"><i class="fas fa-paper-plane"></i>
                            &nbsp;Submit</button>
                        <button class="btn btn-warning" type="reset"><i class="fas fa-redo"></i> &nbsp;Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="backdrop" id="backdrop"></div>

        <script>
            const openBtn = document.getElementById('openOffcanvas');
            const closeBtn = document.getElementById('closeOffcanvas');
            const offcanvas = document.getElementById('myOffcanvas');
            const backdrop = document.getElementById('backdrop');

            openBtn && openBtn.addEventListener('click', () => {
                if ($('[name="_method"]', offcanvas.querySelector('form')).length) {
                    $('[name="_method"]', offcanvas.querySelector('form')).remove();
                }
                offcanvas.querySelector('form').action = openBtn.dataset.href;
                offcanvas.querySelector('form').reset();
                offcanvas.querySelector('.offcanvas-header h4').textContent = "Add New Partner";
                offcanvas.classList.add('show');
                backdrop.classList.add('show');
                paymentGateway.$ele.setValue([]);
                paymentGateway.$ele.enable();
            });

            closeBtn && closeBtn.addEventListener('click', () => {
                offcanvas.classList.remove('show');
                backdrop.classList.remove('show');
            });

            backdrop && backdrop.addEventListener('click', () => {
                offcanvas.classList.remove('show');
                backdrop.classList.remove('show');
            });
        </script>
    @endcanany
@endsection

@section('js')
    <script>
        window.Laravel = {
            phone: "{{ auth()->user()->phone }}",
            api_partner: {{ auth()->user()->api_partner }},
            routes: {
                generateOtp: "{{ route('generate.otp') }}",
                ipWhitelist: "{{ route('ip.whiteList') }}",
            }
        };
    </script>
    <script>
        // Data Table Config
        let tableData = {};
        let cols = [{
                data: "s_no"
            },
            {
                data: "name"
            },
            {
                data: "firmname"
            },
            {
                data: "business_name"
            },
            {
                data: "username"
            },
            {
                data: "email"
            },
            {
                data: "phone"
            },
            @if (auth()->user()->api_partner === 0)
                {
                    data: "status"
                }, {
                    data: "created_at"
                }, {
                    data: "datetime"
                }, {
                    data: "created_by.name"
                },
            @endif {
                data: 'api_credentials'
            },
            {
                data: ""
            },
        ];

        let colDefs = [{
                targets: 0,
                orderable: !1,
                searchable: !1,
                render: function(e, t, a, s) {
                    return (a.s_no) + '.';
                }
            },
            {
                targets: 5,
                render: function(e, t, a, s) {
                    tableData[a.id] = a;
                    // console.log(e, t, a, s);
                    let $aHref = $('<a>').addClass('w-50').attr('title', a.email).attr("href", `mailto:${a.email}`)
                        .attr(
                            "data-bs-toggle", "tooltip").text(a.email.limitCharacter(15));
                    return $aHref[0].outerHTML;
                }
            },
            @if (auth()->user()->api_partner === 0)
                {
                    targets: 7,
                    render: function(e, t, a, s) {

                        return a.status ? 'Active' : 'In-active';
                    }
                }, {
                    targets: 8,
                    render: function(e, t, a, s) {

                        return a.created_at ? a.created_at.toConvertDatetime('d M, Y') : '-';
                    }
                }, {
                    targets: 9,
                    orderable: !1,
                    searchable: !1,
                    render: function(e, t, a, s) {

                        return a.created_at ? a.created_at.toConvertDatetime('d M, Y') : '-';
                    }
                },
            @endif {
                targets: -2,
                orderable: !1,
                searchable: !1,
                render: function(e, t, a, s) {
                    let html = '';
                    @if (auth()->user()->api_partner)
                        if (a?.api_credentials && a.api_credentials.ipaddress) {
                            html = $(
                                    `<a href="javascript:;" data-bs-toggle="tooltip" title="Credentials" data-credentials data-id="{{ auth()->user()->id }}" data-type="view">`
                                )
                                .attr("type", "button")
                                .append('<i class="bg-gray-400 border fas fa-redo rounded-circle p-2"></i>')[0]
                                .outerHTML;
                        } else {
                            html = $('<a data-bs-toggle="tooltip">').attr('href', 'javascript:;').attr('title',
                                'First Time Key Genration Pending form vendor side.').prepend($('<i>').addClass(
                                'fas fa-clock bg-gray-400 border rounded-circle p-2'))[0].outerHTML;
                        }
                    @else
                        if (a?.api_credentials && a.api_credentials.ipaddress) {
                            html = $(
                                    `<a href="javascript:;" data-bs-toggle="tooltip" title="Regenerate Credentials"  data-credentials data-id="${a.id}" data-type="view">`
                                    )
                                .attr("type", "button")
                                .append('<i class="fas fa-redo bg-gray-400 border rounded-circle p-2"></i>')[0]
                                .outerHTML;

                            html = html + $(
                                `<a href="javascript:;" data-send-notification class="ml-2" data-bs-toggle="tooltip" title="Whitelist Ip Address" data-id="${a.id}">`
                                )
                            .attr("type", "button")
                            .append('<i class="fas fa-list bg-gray-400 border rounded-circle p-2"></i>')[0]
                            .outerHTML;
                        } else if (a?.api_credentials && !a.api_credentials.ipaddress) {
                            html = $(
                                    `<a href="javascript:;" data-send-notification data-bs-toggle="tooltip" title="Whitelist Ip Pending" data-id="${a.id}">`
                                    )
                                .attr("type", "button")
                                .append('<i class="fas fa-redo bg-gray-400 border rounded-circle p-2"></i>')[0]
                                .outerHTML;
                        } else {
                            html = $(
                                    `<a href="javascript:;" data-bs-toggle="tooltip" title="Generate Credentials"  data-credentials data-id="${a.id}" data-type="generate">`
                                )
                                .attr("type", "button")
                                .append('<i class="fas fa-key bg-gray-400 border rounded-circle p-2"></i>')[0]
                                .outerHTML;
                        }
                        @can('api-partner-config')
                            html = html + `<a class="ml-1" data-bs-toggle="tooltip" title="Set Configuration of Api Partner ${a.firmname}" href="${window.baseUrl}api-partner/set-config/${a.id}">
                                            <i class="bg-gray-400 border fa-wrench fas p-2 rounded-circle" ></i>
                                        </a>`;
                        @endcan
                    @endif
                    return html;

                }
            },
            {
                targets: -1,
                title: "Actions",
                orderable: !1,
                searchable: !1,
                render: function(e, t, a, s) {

                    return `<div style="white-space: nowrap;" class="main-edit-btn text-center">
                                @can('api-partner-update')
                                 <a href="/user-management/user/${a.id}" data-bs-toggle="tooltip" onclick="openEditModal(${a.id},this,event)" title="edit">
                                    <i class="fas fa-edit btn btn-primary btn-sm"></i>
                                </a>
                                @endcan
                                @can('api-partner-destroy')
                                <a href="/user-management/user/${a.id}/destroy" data-bs-toggle="tooltip" data-id="${a.id}" onclick="deleteConfirmation(this,event)" title="Delete">
                                    <i class="fas fa-trash-alt btn btn-danger btn-sm"></i>
                                </a>
                                @endcan
                           </div>`;
                },
            },
        ];

        const configuration = {
            ajax: {
                url: '{{ $datatableUrl }}',
                type: 'POST',
                data: function(d) {
                    d._token = '{{ csrf_token() }}';
                }
            },
            columns: cols || [],
            columnDefs: colDefs || [],
            lengthChange: true,
            ordering: true,
            order: [],
            info: true,
            processing: true,
            serverSide: true,
            initComplete: function() {
                $('[data-bs-toggle="tooltip"]',document).tooltip();
            }
        }
    </script>
    <script src="{{ asset('assets/js/virtual-select.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('assets/js/additional-methods.min.js') }}"></script>
    <!-- Page level custom scripts -->
    <script src="{{ asset('assets/js/demo/datatables-demo.js') }}"></script>
    <script src="{{ asset('assets/js/api-partner.js') }}"></script>
    <script>
        let selectedPartner, paymentGateway, options = {!! json_encode($pgCompanies) !!};
        $(document).ready(function() {
            // Form Validation
            $('form[validate]').validate({
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
            });

            @can('api-partner-create')
                //username Generate
                $('#generate-username').click(async function(e) {
                    e.preventDefault();
                    try {
                        toggleLoader();
                        const data = new FormData();
                        data.append('_token', '{{ csrf_token() }}');
                        const res = await makeHttpRequest(
                            `${window.baseUrl}api-partner/generate-username`,
                            'POST',
                            data
                        );
                        if (res.success) {
                            form.username.value = res.data.username;
                            this.closest('.input-group-append').setAttribute('disabled', true);
                        } else if (res.error) {
                            toastr.error(res.error);
                        } else if (res.message) {
                            toastr.error(res.message);
                        } else {
                            toastr.error("Something went wrong :(");
                        }

                    } catch (error) {
                        toastr.error(error)
                    }
                    toggleLoader();
                });
            @endcan

            //initialize VirtualSelect to group_name
            paymentGateway = VirtualSelect.init({
                ele: '#payment_gateway',
                //required: true,
                multiple: true
                // allowNewOption: true
            });
            options.length && paymentGateway.$ele.setOptions(options);

        });
    </script>
    @can('api-partner-update')
        <script>
            function openEditModal(id, ele, event) {
                event.preventDefault();
                offcanvas.classList.add('show');
                backdrop.classList.add('show');
                const h4 = offcanvas.querySelector('.offcanvas-header h4');
                h4.textContent = `Edit user | ${tableData[id].name.toUpperCase()}`;

                const form = offcanvas.querySelector('form');
                form.action = ele.href;
                if (!$('[name="_method"]').length) {
                    let $put = $('<input type="hidden" name="_method" value="PUT">')
                    $(form).append($put);
                }
                [...form.querySelectorAll('[name]')].forEach(input => input.value = tableData[id][input.name]);
                console.log(tableData[id].api_config.map(ele => String(ele.pg_company_id)).filter((item, index, self) => self.indexOf(item) === index));
                paymentGateway.$ele.setValue(String(tableData[id].api_config.map(ele => String(ele.pg_company_id)).filter((item, index, self) => self.indexOf(item) === index)));
                paymentGateway.$ele.disable();
            }
        </script>
    @endcan

    {{-- <script>
        (function() {
            document.addEventListener("DOMContentLoaded", function() {
                const otpModel = document.querySelector('#otpModel') || undefined;
                const resendAgain = document.querySelector('#resendAgain') || undefined;
                const maskedNumber = document.querySelector('#maskedNumber') || undefined;
                const validateBtn = document.getElementById('validateBtn');
                const phoneNo = '{{ auth()->user()->phone }}';

                let selectedPartner;
                let verifyCallback = undefined;

                function OTPInput() {
                    const inputs = document.querySelectorAll('#otp > input');
                    inputs.forEach((input, i) => {
                        input.addEventListener('input', function() {
                            if (this.value.length > 1) this.value = this.value[0];
                            if (this.value && i < inputs.length - 1) inputs[i + 1].focus();
                        });
                        input.addEventListener('keydown', function(event) {
                            if (event.key === 'Backspace') {
                                this.value = '';
                                if (i > 0) inputs[i - 1].focus();
                            }
                        });
                    });
                }

                OTPInput();

                resendAgain?.addEventListener('click', async function() {
                    toggleLoader();
                    if (!phoneNo) return toastr['warning']('Phone not updated in database!');

                    const data = new FormData();
                    data.append('phone', phoneNo);
                    data.append('otp_for', 'key_gen');

                    try {
                        const res = await makeHttpRequest('{{ route('generate.otp') }}', 'POST',
                            data, true);
                        if (res?.success) {
                            maskedNumber.textContent = phoneNo;
                            verifyCallback = 'createKeyGenDetails';
                            toastr.success(res.success);
                        } else if (res.validationError) {
                            Object.values(res.validationError).forEach(msg => toastr.warning(msg));
                        } else {
                            toastr.error(res?.error || res?.message || 'Something went wrong.');
                        }
                    } catch (error) {
                        toastr.error(error);
                    }
                    toggleLoader();
                });

                validateBtn?.addEventListener('click', async function() {
                    let otp = '';
                    const inputs = document.querySelectorAll('#otp > input');
                    inputs.forEach(input => otp += input.value);
                    if (otp.length < 6) {
                        inputs.forEach(input => input.classList.toggle('error', input.value ===
                            ''));
                        return;
                    }

                    toggleLoader();
                    const data = new FormData();
                    if (verifyCallback) data.append('verifyCallback', verifyCallback);
                    data.append('id', selectedPartner.id);
                    data.append('otp', otp);
                    data.append('phone', maskedNumber.textContent);

                    const verifyUrl = validateBtn.dataset.action;
                    if (!verifyUrl) return toastr.warning('OTP Verify URL is missing!');

                    try {
                        const res = await makeHttpRequest(verifyUrl, 'POST', data, true);
                        if (res.success) {
                            toastr.success(res.success);
                            res.callback && window[res.callback](selectedPartner.id);
                            res.tableReqload && table.ajax.reload();
                            $(otpModel).modal('hide');
                            $(otpModel).find('input').val('');
                        } else if (res.validateError) {
                            Object.values(res.validateError).forEach(msg => toastr.warning(msg));
                        } else {
                            toastr.error(res?.error || res?.message || 'Something went wrong.');
                        }
                    } catch (error) {
                        toastr.error(error);
                    }
                    toggleLoader();
                });

                // secure, not accessible from console
                async function credentialsModalShow(ele, event, id, type) {
                    event.preventDefault();
                    const data = tableData[id];
                    const $otp = $(otpModel);

                    const message = data?.api_credentials ?
                        `Want to regenerate API credentials of ${data?.firmname.toUpperCase()}?` :
                        `Want to generate API credentials of ${data?.firmname.toUpperCase()}?`;

                    const confirm = await confirmation({
                        redirectMessage: "Are You Sure",
                        redirectConfirmation: message,
                    });

                    if (!confirm) return;

                    toggleLoader();

                    try {
                        selectedPartner = data;
                        const payload = new FormData();
                        payload.append('phone', data.phone);
                        payload.append('otp_for', 'key_gen');

                        const res = await makeHttpRequest('{{ route('generate.otp') }}', 'POST', payload,
                            true);

                        if (res?.success) {
                            maskedNumber.textContent = data.phone;
                            verifyCallback = 'createKeyGenDetails';
                            $otp.modal('show');
                            toastr.success(res.success);
                        } else if (res.validationError) {
                            Object.values(res.validationError).forEach(msg => toastr.warning(msg));
                        } else {
                            toastr.error(res?.error || res?.message || 'Something went wrong.');
                        }
                    } catch (error) {
                        toastr.error(error);
                    }

                    toggleLoader();
                }

                // Delegated event for dynamically created elements
                document.addEventListener('click', async function(e) {
                    const target = e.target.closest('[data-credentials]');
                    if (target) {
                        const id = target.getAttribute('data-id');
                        const type = target.getAttribute('data-type');
                        await credentialsModalShow(target, e, id, type);
                    }
                });
            });
        })();
    </script> --}}

    @include('scripts.datatable')
@endsection
