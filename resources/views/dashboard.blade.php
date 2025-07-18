@extends('layouts.main')
@section('title')
    - Dashboard
@endsection
@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <x-pageheading :heading="'Dashboard'" :navigation="['Dashboard']" :description="$description ?? null" />

        <!-- Content Row -->
        <div class="row">

            <!-- Earnings (Monthly) Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Transaction</div>
                                <div id="totalTransaction" class="h5 mb-0 font-weight-bold text-gray-800">₹ 0</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Earnings (Monthly) Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Successfull Transaction</div>
                                <div id="totalSTransaction" class="h5 mb-0 font-weight-bold text-gray-800">₹ 0</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Earnings (Monthly) Card Example -->
            @if (auth()->user()->api_partner === 1 && false)
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Todays Limit
                                    </div>
                                    <div class="row no-gutters align-items-center">
                                        <div class="col-auto">
                                            <div id="todaysLimit" class="h5 mb-0 mr-3 font-weight-bold text-gray-800">50%</div>
                                        </div>
                                        <div class="col">
                                            <div class="progress progress-sm mr-2">
                                                <div class="progress-bar bg-info" role="progressbar" style="width: 50%"
                                                    aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Today's Transaction</div>
                                    <div id="todayTransaction" class="h5 mb-0 font-weight-bold text-gray-800">₹ 0</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Pending Requests Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Today's Successful Transaction</div>
                                <div id="todaySTransaction" class="h5 mb-0 font-weight-bold text-gray-800">₹ 0</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-comments fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Row -->

        <div class="row">

            <!-- Area Chart -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <!-- Card Header - Dropdown -->
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Transactions - {{ date('Y') }}</h6>
                        <div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                aria-labelledby="dropdownMenuLink">
                                <div class="dropdown-header">Dropdown Header:</div>
                                <a class="dropdown-item" href="#">Action</a>
                                <a class="dropdown-item" href="#">Another action</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#">Something else here</a>
                            </div>
                        </div>
                    </div>
                    <!-- Card Body -->
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="myAreaChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pie Chart -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <!-- Card Header - Dropdown -->
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Transaction Till</h6>
                        <div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                aria-labelledby="dropdownMenuLink">
                                <div class="dropdown-header">Dropdown Header:</div>
                                <a class="dropdown-item" href="#">Action</a>
                                <a class="dropdown-item" href="#">Another action</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#">Something else here</a>
                            </div>
                        </div>
                    </div>
                    <!-- Card Body -->
                    <div class="card-body">
                        <div class="chart-pie pt-4 pb-2">
                            <canvas id="myPieChart"></canvas>
                        </div>
                        <div class="mt-4 text-center small">
                            <span class="mr-2">
                                <i class="fas fa-circle text-success"></i> Failed
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-circle text-primary"></i> Success
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-circle text-info"></i> Initiated
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-circle text-primary"></i> Completed
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-circle text-gray-300"></i> Refunded
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- /.container-fluid -->
@endsection
@section('js')
    <script src="{{ asset('assets/js/demo/chart-area-demo.js') }}"></script>
    <script src="{{ asset('assets/js/demo/chart-pie-demo.js') }}"></script>
    <script>
        let totalTransaction = document.querySelector('#totalTransaction'),
        totalSTransaction = document.querySelector('#totalSTransaction'),
        todaysLimit = document.querySelector('#todaysLimit'),
        todaySTransaction = document.querySelector('#todaySTransaction'),
        todayTransaction = document.querySelector('#todayTransaction');

        $(document).ready(async function() {
            toggleLoader();
            try {

                const url = '{{route("dashboard.transaction.data")}}';
                const data = new FormData();
                data.append('token__',Math.floor(Math.random() * 100000));
                
                const res = await makeHttpRequest(url, 'post', data, true);

                if(res.status) {
                    totalTransaction && (totalTransaction.textContent = res.data.totalTransaction ? formatINR(res.data.totalTransaction) : '₹ 0.00');
                    totalSTransaction && (totalSTransaction.textContent = res.data.totalSTransaction ? formatINR(res.data.totalSTransaction) : '₹ 0.00');
                    todaysLimit && (todaysLimit.textContent = res.data.todaysLimit ? formatINR(res.data.todaysLimit) : '₹ 0.00');
                    todaySTransaction && (todaySTransaction.textContent = res.data.todaySTransaction ? formatINR(res.data.todaySTransaction) : '₹ 0.00');
                    todayTransaction && (todayTransaction.textContent = res.data.todayTransaction ? formatINR(res.data.todayTransaction) : '₹ 0.00');
                    myLineChart.data.datasets[0].data = res.data.yearlyData;
                    myLineChart.update();
                    myPieChart.data.datasets[0].data = res.data.transactionTill ?? [0,0,0,0];
                    myPieChart.update();
                }else if(!res.status){
                    toastr.error(res.message)
                }else {
                    toastr.error('Failed to fetch Dashboard data!');
                }
                
            } catch (error) {
                toastr.error(error);
            }
            toggleLoader();
        })
    </script>
@endsection
