<!-- Sidebar -->
<style>
    .nav-item a.collapse-item {
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3">SB Admin <sup>2</sup></div>
    </a>
    @can('dashboard')
        <!-- Divider -->
        <hr class="sidebar-divider my-0">
        <!-- Nav Item - Dashboard -->
        <li class="nav-item active">
            <a class="nav-link" href="/">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span></a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">
    @endcan

    <!-- Heading -->
    <div class="sidebar-heading">
        Interface
    </div>

    @can('api-partner')
        <!-- Nav Item - Pages Collapse Menu -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseApiPartner"
                aria-expanded="true" aria-controls="collapseApiPartner">
                <i class="fas fa-fw fa-cog"></i>
                <span>Api Partner</span>
            </a>
            <div id="collapseApiPartner" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <!-- <h6 class="collapse-header"></h6> -->
                    @can('api-partner-list')
                        <a class="collapse-item" href="{{ route('api-partner.list') }}">
                            <i class="fas fa-fw fa-users-cog"></i>
                            <span>Api Partner List</span>
                        </a>
                    @endcan
                    @can('api-partner-transaction')
                        <a class="collapse-item" href="{{ route('api-partner.transaction') }}">
                            <i class="fas fa-fw fa-users-cog"></i>
                            <span>Api Partner Transaction</span>
                        </a>
                    @endcan
                    @can('api-partner-ledger')
                        <a class="collapse-item" href="{{ route('api-partner.ledger') }}">
                            <i class="fas fa-fw fa-users-cog"></i>
                            <span>Api Partner Ledger</span>
                        </a>
                    @endcan
                </div>
            </div>
        </li>
    @endcan

    @can('user-management')
        <!-- Nav Item - Pages Collapse Menu -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUMP"
                aria-expanded="true" aria-controls="collapseUMP">
                <i class="fas fa-fw fa-cog"></i>
                <span>User Management</span>
            </a>
            <div id="collapseUMP" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <!-- <h6 class="collapse-header"></h6> -->
                    @can('role-list')
                        <a class="collapse-item" href="{{ route('role.list') }}">
                            <i class="fas fa-fw fa-users-cog"></i>
                            <span>Role</span>
                        </a>
                    @endcan
                    @can('permission-list')
                        <a class="collapse-item" href="{{ route('permission.list') }}">
                            <i class="fas fa-fw fa-lock"></i>
                            <span>Permission</span>
                        </a>
                    @endcan
                    @can('user-list')
                        <a class="collapse-item" href="{{ route('user.list') }}">
                            <i class="fas fa-fw fa-user-cog"></i>
                            <span>User</span>
                        </a>
                    @endcan
                </div>
            </div>
        </li>
    @endcan

    @can('pg-company')
        <!-- Nav Item - Pages Collapse Menu -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapse-pg-company"
                aria-expanded="true" aria-controls="collapse-pg-company">
                <i class="fas fa-fw fa-cog"></i>
                <span>PG Company</span>
            </a>
            <div id="collapse-pg-company" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <!-- <h6 class="collapse-header"></h6> -->
                    @can('pg-company-list')
                        <a class="collapse-item" href="{{ route('pg-company.list') }}">
                            <i class="fas fa-fw fa-users-cog"></i>
                            <span>PG Company List</span>
                        </a>
                    @endcan
                </div>
            </div>
        </li>
    @endcan

    @can('mode')
        <!-- Nav Item - Pages Collapse Menu -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapse-mode"
                aria-expanded="true" aria-controls="collapse-mode">
                <i class="fas fa-fw fa-cog"></i>
                <span>Modes</span>
            </a>
            <div id="collapse-mode" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <!-- <h6 class="collapse-header"></h6> -->
                    @can('mode-list')
                        <a class="collapse-item" href="{{ route('mode.list') }}">
                            <i class="fas fa-fw fa-users-cog"></i>
                            <span>Mode List</span>
                        </a>
                    @endcan
                </div>
            </div>
        </li>
    @endcan
    <!-- Nav Item - Utilities Collapse Menu -->
    <!-- <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
            aria-expanded="true" aria-controls="collapseUtilities">
            <i class="fas fa-fw fa-wrench"></i>
            <span>Utilities</span>
        </a>
        <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Custom Utilities:</h6>
                <a class="collapse-item" href="utilities-color.html">Colors</a>
                <a class="collapse-item" href="utilities-border.html">Borders</a>
                <a class="collapse-item" href="utilities-animation.html">Animations</a>
                <a class="collapse-item" href="utilities-other.html">Other</a>
            </div>
        </div>
    </li> -->

    <!-- Divider -->
    <!-- <hr class="sidebar-divider"> -->

    <!-- Heading -->
    <!-- <div class="sidebar-heading">
        Addons
    </div> -->

    <!-- Nav Item - Pages Collapse Menu -->
    <!-- <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages" aria-expanded="true"
            aria-controls="collapsePages">
            <i class="fas fa-fw fa-folder"></i>
            <span>Pages</span>
        </a>
        <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Login Screens:</h6>
                <a class="collapse-item" href="login.html">Login</a>
                <a class="collapse-item" href="register.html">Register</a>
                <a class="collapse-item" href="forgot-password.html">Forgot Password</a>
                <div class="collapse-divider"></div>
                <h6 class="collapse-header">Other Pages:</h6>
                <a class="collapse-item" href="404.html">404 Page</a>
                <a class="collapse-item" href="blank.html">Blank Page</a>
            </div>
        </div>
    </li> -->

    <!-- Nav Item - Charts -->
    <!-- <li class="nav-item">
        <a class="nav-link" href="charts.html">
            <i class="fas fa-fw fa-chart-area"></i>
            <span>Charts</span></a>
    </li> -->

    <!-- Nav Item - Tables -->
    <!-- <li class="nav-item">
        <a class="nav-link" href="tables.html">
            <i class="fas fa-fw fa-table"></i>
            <span>Tables</span></a>
    </li> -->

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <!-- <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div> -->

    <!-- Sidebar Message -->
    <!-- <div class="sidebar-card d-none d-lg-flex">
        <img class="sidebar-card-illustration mb-2" src="img/undraw_rocket.svg" alt="...">
        <p class="text-center mb-2"><strong>SB Admin Pro</strong> is packed with premium features, components, and more!
        </p>
        <a class="btn btn-success btn-sm" href="https://startbootstrap.com/theme/sb-admin-pro">Upgrade to Pro!</a>
    </div> -->

</ul>
<!-- End of Sidebar -->

<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        let currentUrl = `${window.location.origin}${window.location.pathname}${window.location.search ?? ''}`;
        let ele = document.querySelector(`[href="${currentUrl}"]`) ?? document.querySelector(
            `[href="${window.location.origin}${window.location.pathname}"]`);

        ele && ele.classList.add("active");
        if(ele) {
            let closestA = ele.closest("li.nav-item").querySelector('a.nav-link');
            closestA?.dataset?.target && $(`${closestA?.dataset?.target}`).collapse('show');
        }

        [...document.querySelectorAll('.nav-item a.collapse-item')].forEach(a => a.setAttribute('title',a.textContent.trim()));

    });
</script>
