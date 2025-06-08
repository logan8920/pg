@extends('layouts.main')
@section('title')
    - User List
@endsection
@section('css')
    <link href="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endsection
@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <x-pageheading :heading="'user List'" :navigation="['User Managment', 'User']" :description="$description ?? null" />

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header d-flex float-right justify-content-between py-3 w-100">
                <h6 class="align-content-around font-weight-bold justify-content-lg-between m-0 text-primary">List of user
                </h6>
                <button class="btn btn-primary" data-href="{{ route('user.store') }}" type="button" id="openOffcanvas"><i
                        class="fas fa-fw fa-plus"></i> &nbsp;Add
                    New user</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Sr No.</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Created Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    <!-- /.container-fluid -->
    <div class="offcanvas w-25" id="myOffcanvas">
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
                    <label class="text-black-50" for="user_title">User Title <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <input type="text" name="name" class="form-control after-parent" id="user_title"
                            placeholder="Enter title..." required>
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
                    <label class="text-black-50" for="role">Select Role <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <select name="role" class="form-control after-parent" id="role"
                            placeholder="Enter Role..." required>
                            <option value="">Select Role...</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}">{{ ucwords($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="text-black-50" for="password">Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <i class="fas fa-key"></i>
                            </div>
                        </div>
                        <input type="password" name="password" class="form-control after-parent" id="password"
                            placeholder="Enter Password..." required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="text-black-50" for="password_confirmation">Confirmation Password<span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <i class="fas fa-key"></i>
                            </div>
                        </div>
                        <input type="password" name="password_confirmation" class="form-control after-parent" id="password_confirmation"
                            placeholder="Enter Confirmation Password..." required>
                    </div>
                </div>
                <div class="form-group">
                    <button class="btn btn-danger" type="submit"><i class="fas fa-paper-plane"></i> &nbsp;Submit</button>
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
            if($('[name="_method"]',offcanvas.querySelector('form')).length){
                $('[name="_method"]',offcanvas.querySelector('form')).remove();
            }
            offcanvas.querySelector('form').password.setAttribute('required',true);
            offcanvas.querySelector('form').password_confirmation.setAttribute('required',true);
            offcanvas.querySelector('form').action = openBtn.dataset.href;
            offcanvas.querySelector('form').reset();
            offcanvas.querySelector('.offcanvas-header h4').textContent = "Add New User";
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
                data: "email"
            },
            {
                data: "created_at"
            },
            {
                data: "created_at"
            }
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
                targets: 2,
                render: function(e, t, a, s) {
                    tableData[a.id] = a;
                    console.log(e, t, a, s);
                    let $aHref = $('<a>').addClass('w-50').attr('title', a.email).attr("href", `mailto:${a.email}`).attr(
                        "data-bs-toggle", "tooltip").text(a.email);
                    return $aHref.html();
                }
            },
            {
                targets: 3,
                render: function(e, t, a, s) {

                    return a.created_at ? a.created_at.toConvertDatetime('D M, Y') : '-';
                }
            },
            {
                targets: -1,
                title: "Actions",
                orderable: !1,
                searchable: !1,
                render: function(e, t, a, s) {

                    return `<div style="white-space: nowrap;" class="main-edit-btn text-center">
                                @can('user-edit')
                                 <a href="${window.baseUrl}user-management/user/${a.id}" data-bs-toggle="tooltip" onclick="openEditModal(${a.id},this,event)" title="edit">
                                    <i class="fas fa-edit btn btn-primary btn-sm"></i>
                                </a>
                                @endcan
                                @can('user-destroy')
                                <a href="${window.baseUrl}user-management/user/${a.id}/destroy" data-bs-toggle="tooltip" data-id="${a.id}" onclick="deleteConfirmation(this,event)" title="Delete">
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
    <script>
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


            //Form Submit Handle
            $('#user-form').on("submit",function(e){
                e.preventDefault();
            })

        });

        function openEditModal(id, ele, event) {
            event.preventDefault();
            offcanvas.classList.add('show');
            backdrop.classList.add('show');
            const h4 = offcanvas.querySelector('.offcanvas-header h4');
            h4.textContent = `Edit user | ${tableData[id].name}`;
            
            const form = offcanvas.querySelector('form');
            form.action = ele.href;
            if(!$('[name="_method"]').length){
                let $put = $('<input type="hidden" name="_method" value="PUT">')
                $(form).append($put);
            }
            form.password.removeAttribute('required');
            form.password_confirmation.removeAttribute('required');
            form.name.value = tableData[id].name;
            form.email.value = tableData[id].email;
            form.role.value = tableData[id].roles[0].name;
        }
    </script>
    @include('scripts.datatable')
@endsection
