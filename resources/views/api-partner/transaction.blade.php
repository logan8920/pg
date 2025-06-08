@extends('layouts.main')
@section('title')
    - Api Partner Transaction List
@endsection
@section('css')
    <link href="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/vendor/datatables/buttons.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.css') }}">
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

        .export-to button {
            font-weight: 700;
        }
    </style>
@endsection
@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <x-pageheading :heading="'Api Partner Transaction List'" :navigation="['Api Partner Transaction', 'List']" :description="$description ?? null" />
        <!-- Filters -->
        <div class="card shadow mb-2">
            <div class="card-header d-flex float-right justify-content-between py-3 w-100">
                <h6 class="align-content-around font-weight-bold justify-content-lg-between m-0 text-primary">
                    <i class="fas fa-filter fa-fw"></i>
                    Filters
                </h6>
            </div>
            <div class="card-body">
                <form action='' method='get' id='filterform'>
                    <div class='row'>
                        <div class='col-12 col-lg-2'>
                            <div class=" form-group">
                                <label>
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    <span>Date</span>
                                </label>
                                <input id='daterange' name='date' class='form-control'>
                                <input type="text" hidden id="hiddenRange">
                            </div>
                        </div>
                        <div class='col-12 col-lg-2'>
                            <div class=" form-group">
                                <label for="partner_id">
                                    <i class="fas fa-users-cog mr-2"></i>
                                    Partner Id
                                </label>
                                <select name="partner_id" id="partner_id" class="form-control select2"
                                    data-placeholder="Select Company">
                                    <option value="" Selected>All Partners</option>
                                    @foreach ($partnerIds as $partner)
                                        <option value="{{ $partner->username }}">{{ $partner->username }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class='col-12 col-lg-2'>
                            <div class=" form-group">
                                <label for="transaction_no">
                                    <i class="fas fa-list-ul mr-2"></i>
                                    Transaction No.
                                </label>
                                <input type="text" id="transaction_no" name="transaction_no" class="form-control"
                                    placeholder="Enter Transaction No...">
                            </div>
                        </div>
                        <div class='col-12 col-lg-2'>
                            <div class=" form-group">
                                <label for="reference_no">
                                    <i class="fas fa-vote-yea mr-2"></i>
                                    Reference No.
                                </label>
                                <input type="text" id="reference_no" name="reference_no" class="form-control"
                                    placeholder="Enter Reference No...">
                            </div>
                        </div>
                        <div class='col-12 col-lg-2'>
                            <div class=" form-group">
                                <label for="status">
                                    <i class="fas fa-clock mr-2"></i>
                                    Status
                                </label>
                                <select name="status" id="status" class="form-control select2">
                                    <option selected value="">All</option>
                                    <option value="0">Failed</option>
                                    <option value="1">Success</option>
                                    <option value="2">Initiated</option>
                                    <option value="3">Completed</option>
                                </select>
                            </div>
                        </div>

                        <div class="align-content-end col-sm-12 col-lg-2 mb-3">
                            <div class='d-flex export-to'>
                                <button class="btn btn-warning" style="font-size: 14px;" type="button"
                                    onclick="resetFilter(event)">
                                    <i class="fas fa-sync mr-1"></i>
                                    Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- ./Filters -->
        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            {{-- <div class="card-header d-flex float-right justify-content-between py-3 w-100">
                <h6 class="align-content-around font-weight-bold justify-content-lg-between m-0 text-primary">List of
                    Transaction
                </h6>
            </div> --}}
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center nowrap" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Username</th>
                                <th>Txn No</th>
                                <th>Ref. No</th>
                                <th>Charge</th>
                                <th>Amount</th>
                                <th>Gst</th>
                                <th>Mobile</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Mode PG</th>
                                <th>Remarks</th>
                                <th>Date</th>
                                @canany(['api-partner-query','api-partner-refund'])
                                <th>Action</th>
                                @endcanany
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('js')
    <script>
        // Data Table Config
        let tableData = {};
        let buttons, status, reference_no, transaction_no, partner_id, maxDate, minDate, startdate, enddate;
        let cols = [{
                data: "s_no"
            },
            {
                data: "user.username"
            },
            {
                data: "txnno"
            },
            {
                data: "refid"
            },
            {
                data: "charge"
            },
            {
                data: "amt_after_deduction"
            },
            {
                data: "gst"
            },
            {
                data: "mobile"
            },
            {
                data: "status"
            },
            {
                data: "email"
            },
            {
                data: "mode_pg"
            },
            {
                data: "remarks"
            },
            {
                data: "dateadded"
            },
            @canany(['api-partner-query','api-partner-refund'])
            {
                data: "dateadded"
            }
             @endcanany
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
                targets: 4,
                render: function(e, t, a, s) {
                    tableData[a.id] = a;
                    return a?.charge ? `₹ ${a.charge}` : '-';
                }
            },
            {
                targets: 5,
                render: function(e, t, a, s) {
                    return a?.amt_after_deduction ? `₹ ${a.amt_after_deduction}` : '-';
                }
            },
            {
                targets: 6,
                render: function(e, t, a, s) {
                    return a?.gst ? `₹ ${a.gst}` : '-';
                }
            },
            {
                targets: 7,
                render: function(e, t, a, s) {
                    return a?.mobile ? `+91 ${a.mobile}` : '-';
                }
            },
            {
                targets: 8,
                render: function(e, t, a, s) {
                    let $aHref = $('<a>').addClass('w-50').attr('title', a.email).attr("href", `mailto:${a.email}`)
                        .attr(
                            "data-bs-toggle", "tooltip").text(a.email.limitCharacter(15));
                    return $aHref[0].outerHTML;
                }
            },
            {
                targets: 9,
                render: function(e, t, a, s) {
                    let StatusArray = {
                        "0": `<h6><span class="badge badge-danger">Failed <i class="fas fa-exclamation-triangle"></span></h6>`,
                        "1": `<h6><span class="badge badge-success">Success <i class="fas fa-check-circle"></span></h6>`,
                        "2": `<h6><span class="badge badge-warning">Initiated  <i class="fas fa-star-of-david"></span></h6>`,
                        "3": `<h6><span class="badge badge-primary">Completed <i class="fas fa-check-double"></span></h6>`,
                    };
                    return StatusArray[`${a.status}`];
                }
            },
            {
                targets: 11,
                render: function(e, t, a, s) {
                    return a.status == 0 ? a.errormsg : a.remarks;
                }
            },
            {
                targets: 12,
                render: function(e, t, a, s) {
                    return a.dateadded ? a.dateadded.toConvertDatetime('d M, Y') : '-';
                }
            },
            @canany(['api-partner-query','api-partner-refund'])
            {
                targets: -1,
                title: "Actions",
                orderable: !1,
                searchable: !1,
                render: function(e, t, a, s) {

                    return `<div style="white-space: nowrap;" class="main-edit-btn text-center">
                                @can('api-partner-query')
                                 <a href="/user-management/user/${a.id}" data-bs-toggle="tooltip" onclick="openEditModal(${a.id},this,event)" title="Query" class="btn btn-primary">
                                    <i class="fas fa-comments mr-1"></i>
                                    Query
                                </a>
                                @endcan
                                @can('api-partner-refund')
                                <a href="/user-management/user/${a.id}/destroy" data-bs-toggle="tooltip" data-id="${a.id}" onclick="deleteConfirmation(this,event)" title="Refund" class="btn btn-success">
                                    <i class="fas fa-reply-all mr-1"></i>
                                    Refund
                                </a>
                                @endcan
                           </div>`;
                },
            },
             @endcanany
        ];

        const configuration = {
            ajax: {
                url: '{{ $datatableUrl }}',
                type: 'POST',
                data: function(d) {
                    d._token = '{{ csrf_token() }}';
                    d.start_date = minDate;
                    d.end_date = maxDate;
                    d.partner_id = partner_id;
                    d.transaction_no = transaction_no;
                    d.reference_no = reference_no;
                    d.status = status;
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
            pageLength: 100,
            initComplete: function() {
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        }
    </script>
    <script src="{{ asset('assets/vendor/libs/moment/moment.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('assets/js/additional-methods.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/jszip.min.js') }}"></script>
    <!-- Page level custom scripts -->
    <script src="{{ asset('assets/js/demo/datatables-demo.js') }}"></script>
    <script src="{{ asset('assets/js/api-partner.js') }}"></script>
    <script>
        let selectedPartner;
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

        });
    </script>

    <script>
        $(document).ready(function() {

            // Export To Excel Code
            buttons = new $.fn.dataTable.Buttons(table, {
                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Export',
                        exportOptions: {
                            //columns: ':not(:eq(6))',
                            modifier: {
                                search: 'none', // Ignore filtering
                                length: -1 // Export all rows, no limit
                            }
                        }
                    },

                ]
            });
            $('.export-to').append(buttons.container());
            buttons.container().find('button').addClass('btn btn-danger ml-2')
                .removeClass('dt-button buttons-collection').css('font-size', '14px').prepend(
                    '<i class="fas fa-file-excel mr-2"></i>');

            //Date Range Code
            startdate = '{{ date("d-m-Y") }}';
            enddate = '{{ date("d-m-Y") }}';
            $('#daterange').daterangepicker({
                    locale: {
                        format: 'DD/MM/YYYY'
                    },
                    ranges: {
                        'All Time': [moment('{{ $allTimeDate }}'), moment()],
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                            'month').endOf(
                            'month')]
                    },
                    startDate: startdate == '' ? startdate : moment(),
                    endDate: enddate == '' ? enddate : moment()
                },
                function(start, end) {
                    $('#daterange').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
                    $('#hiddenRange').val(`${start.format('YYYY/MM/DD')}-${end.format('YYYY/MM/DD')}`).trigger(
                        'change');
                }
            );

            /*---filter option handler---*/

            //date filter
            $(document).on('change', '#hiddenRange', function() {
                let [min, max] = (this.value).split('-');
                minDate = min;
                maxDate = max;
                //console.log(minDate,maxDate);
                if (
                    (min !== null || max !== null) &&
                    ((new Date(min)) <= (new Date(max)))
                ) {
                    table.draw();
                }
            });

            //partner_id filter
            $("#partner_id").on("change", function(e) {
                partner_id = this.value.trim();
                table.ajax.reload();
            });

            //status filter
            $("#status").on("change", function(e) {
                status = this.value.trim();
                table.ajax.reload();
            });

            //transaction_no and reference_no filter
            let typingTimer;
            const doneTypingInterval = 500;

            $('#transaction_no, #reference_no').on('keyup', function() {
                clearTimeout(typingTimer);
                const input = this; // store input reference
                typingTimer = setTimeout(() => doneTyping(input), doneTypingInterval);
            });

            $('#transaction_no, #reference_no').on('keydown', function() {
                clearTimeout(typingTimer);
            });

            function doneTyping(ele) {
                const number = $(ele).val().trim();

                const inputName = ele.name; // or $(ele).attr('name')

                if (inputName === 'reference_no') {
                    reference_no = number;
                } else {
                    transaction_no = number;
                }

                table.ajax.reload(); // Reload the datatable
            }

        });

        //reset filter
        function resetFilter(e) {
            e.preventDefault();
            const form = e.target.closest('form');
            form && form.reset();
            transaction_no = '';
            reference_no = '';
            status = '';
            partner_id = '';
            minDate = startdate;
            maxDate = enddate;
            $('#daterange').data('daterangepicker').setStartDate(startdate);
            $('#daterange').data('daterangepicker').setEndDate(enddate);
            table.ajax.reload();
        }
    </script>
@endsection
