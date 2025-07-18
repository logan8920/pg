@extends('layouts.main')
@section('title')
    - User List
@endsection
@section('css')
    <link href="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
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
    </style>
@endsection
@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <x-pageheading :heading="'PG Company List'" :navigation="['PG Company', 'List']" :description="$description ?? null" />

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header d-flex float-right justify-content-between py-3 w-100">
                <h6 class="align-content-around font-weight-bold justify-content-lg-between m-0 text-primary">List of user
                </h6>
                @can('pg-company-create')
                    <button class="btn btn-primary" data-href="{{ route('pg-company.store') }}" type="button" id="openOffcanvas"><i
                            class="fas fa-fw fa-plus"></i> &nbsp;
                        Add New PG Company</button>
                @endcan
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Sr No.</th>
                                <th>Name</th>
                                <th>Service Class Name</th>
                                <th>Status</th>
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
                    <label class="text-black-50" for="name">Company Name <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <input type="text" name="name" class="form-control after-parent" id="name"
                            placeholder="Enter Company Name..." required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="text-black-50" for="keys">Upload Keys <span class="text-danger"></span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text" id="inputGroupFileAddon01">
                                <i class="fas fa-building"></i>
                            </div>
                        </div>
                        <div class="custom-file">
                            <input type="file" aria-describedby="inputGroupFileAddon01" accept=".pem,.crt,.key,.cer,.der"
                                name="keys[]" class="custom-file-input after-parent ignore" id="keys"
                                oninput="this.nextElementSibling.textContent = (this.files.length > 1) ? this.files.length+' Files' : this.files[0].name"
                                multiple />
                            <label class="custom-file-label" for="keys">Choose file</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="text-black-50" for="pg_config">Payment Gateway Configuration<span
                            class="text-danger">*</span></label>
                    <textarea name="pg_config" rows="10" id="pg_config" class="form-control w-100"
                        placeholder='Please Enter Configuration in JSON Formate ({ "mid": "XXXXXXX", etc.. })' required></textarea>
                </div>

                <div class="form-group">
                    <label class="text-black-50" for="service_class_name">Service Class Name <span
                            class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <i class="fas fa-industry"></i>
                            </div>
                        </div>
                        <input type="text" name="service_class_name" class="form-control after-parent"
                            id="service_class_name" placeholder="Enter Service Class Name..." required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="text-black-50" for="status">Status <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <i class="fas fa-industry"></i>
                            </div>
                        </div>
                        <select name="status" class="form-control after-parent" id="status"
                            placeholder="Select Status..." required>
                            <option value="">Select Status...</option>
                            <option value="1" selected>Active</option>
                            <option value="0">In-active</option>
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
            offcanvas.querySelector('form').service_class_name.removeAttribute('disabled');
            offcanvas.querySelector('form')['keys[]'].nextElementSibling.textContent = 'Choose Files';
            offcanvas.querySelector('form').pg_config.innerHTML = '';
            offcanvas.querySelector('form').action = openBtn.dataset.href;
            offcanvas.querySelector('form').reset();
            offcanvas.querySelector('.offcanvas-header h4').textContent = "Add New PG Company";
            offcanvas.classList.add('show');
            backdrop.classList.add('show');
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
@endsection

@section('js')
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
                data: "service_class_name"
            },
            {
                data: "status"
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
                targets: 3,
                orderable: !1,
                searchable: !1,
                render: function(e, t, a, s) {
                    tableData[a.id] = a;
                    return a.status == 1 ? 'Active' : 'in-active';
                }
            },
            {
                targets: -1,
                title: "Actions",
                orderable: !1,
                searchable: !1,
                render: function(e, t, a, s) {

                    return `<div style="white-space: nowrap;" class="main-edit-btn text-center">
                                @can('pg-company-update')
                                 <a href="/pg-company/${a.id}" data-bs-toggle="tooltip" onclick="openEditModal(${a.id},this,event)" title="Edit ${a.name}">
                                    <i class="fas fa-edit btn btn-primary btn-sm"></i>
                                </a>
                                @endcan
                                @can('pg-company-destroy')
                                <a href="/pg-company/${a.id}/destroy" data-bs-toggle="tooltip" data-id="${a.id}" onclick="deleteConfirmation(this,event)" title="Delete ${a.name}">
                                    <i class="fas fa-trash-alt btn btn-danger btn-sm"></i>
                                </a>
                                @endcan
                                @can('pg-company-default-config')
                                <a href="/pg-company/${a.id}/default-config" data-bs-toggle="tooltip" data-id="${a.id}" title="Set Default Configuration of ${a.name}" class="btn btn-success btn-sm">
                                    <i class="fas fa-tools"></i>
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
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        }
    </script>
    <script src="{{ asset('assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('assets/js/additional-methods.min.js') }}"></script>
    <!-- Page level custom scripts -->
    <script src="{{ asset('assets/js/demo/datatables-demo.js') }}"></script>
    {{-- <script src="{{ asset('assets/js/pg-company.js') }}"></script> --}}
    <script>
        let selectedPartner;

        $.validator.addMethod("fileExtension", function(value, element, param) {
            if (element.files.length === 0) return true;
            var extension = value.split('.').pop().toLowerCase();
            return param.split(',').includes(extension);
        }, "Invalid file type.");

        $(document).ready(function() {
            // Form Validation
            $('form[validate]').validate({
                ignore: ".ignore",
                rules: {
                    keys: {
                        required: false,
                        fileExtension: "pem,crt,key,cer,der"
                    }
                },
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

        });
    </script>
    @can('pg-company-edit')
        <script>
            function openEditModal(id, ele, event) {
                event.preventDefault();
                offcanvas.classList.add('show');
                backdrop.classList.add('show');
                const h4 = offcanvas.querySelector('.offcanvas-header h4');
                h4.textContent = `Edit | ${tableData[id].name}`;

                const form = offcanvas.querySelector('form');
                form.action = ele.href;
                if (!$('[name="_method"]').length) {
                    let $put = $('<input type="hidden" name="_method" value="PUT">')
                    $(form).append($put);
                }
                form.service_class_name.setAttribute('disabled', true);
                form.name.value = tableData[id].name;
                form.pg_config.innerHTML = JSON.stringify(tableData[id].pg_config);
                form.service_class_name.value = tableData[id].service_class_name;
                form.status.value = tableData[id].status;
                form['keys[]'].nextElementSibling.textContent = 'Choose Files';
            }
        </script>
    @endcan

    @include('scripts.datatable')
@endsection
