<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <div class="sidebar-brand-wrapper d-none d-lg-flex align-items-center justify-content-center fixed-top">
        <a class="sidebar-brand brand-logo" href="{{ url('/home') }}">{{$app->app_name ?? 'Noclick'}}</a>
        <a class="sidebar-brand brand-logo-mini" href="{{ url('/home') }}">{{$app->short_name ?? 'NC'}}</a>
    </div>
    <ul class="nav">
        <li class="nav-item profile">
            <div class="profile-desc">
                <div class="profile-pic">
                    <div class="count-indicator">
                        <img class="img-xs rounded-circle " src="{{ asset('assets/images/auth/' . Auth::user()->image) }}" alt="{{ __('Missing profile image of ') . Auth::user()->name }}">
                        <span class="count bg-success"></span>
                    </div>
                    <div class="profile-name">
                        <h5 class="mb-0 font-weight-normal" title="{{ Auth::user()->name }}">{{ Auth::user()->name }}</h5>
                        <span>{{ ['Super admin', 'Admin', 'User'] [Auth::user()->user_type] }}</span>
                    </div>
                </div>
                <a href="#" id="profile-dropdown" data-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></a>
                <div class="dropdown-menu dropdown-menu-right sidebar-dropdown preview-list"
                     aria-labelledby="profile-dropdown">
                    <a href="{{ url('setting/users/' . Auth::user()->id . '/edit') }}" class="dropdown-item preview-item">
                        <div class="preview-thumbnail">
                            <div class="preview-icon bg-dark rounded-circle">
                                <i class="mdi mdi-settings text-primary"></i>
                            </div>
                        </div>
                        <div class="preview-item-content">
                            <p class="preview-subject ellipsis mb-1 text-small">Account settings</p>
                        </div>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="{{ url('setting/users/' . Auth::user()->id . '/edit') }}" class="dropdown-item preview-item">
                        <div class="preview-thumbnail">
                            <div class="preview-icon bg-dark rounded-circle">
                                <i class="mdi mdi-onepassword  text-info"></i>
                            </div>
                        </div>
                        <div class="preview-item-content">
                            <p class="preview-subject ellipsis mb-1 text-small">Change Password</p>
                        </div>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item preview-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <div class="preview-thumbnail">
                            <div class="preview-icon bg-dark rounded-circle">
                                <i class="mdi mdi-calendar-today text-success"></i>
                            </div>
                        </div>
                        <div class="preview-item-content">
                            <p class="preview-subject ellipsis mb-1 text-small">Logout</p>
                        </div>
                    </a>
                </div>
            </div>
        </li>

        <li class="nav-item nav-category">
            <span class="nav-link">Navigation</span>
        </li>

        <!-- Dashboard -->
        <li class="nav-item menu-items {{ (request()->is('home') OR request()->is('home/*')) ? 'active' : '' }}">
            <a class="nav-link" href="{{ url('/home') }}">
            <span class="menu-icon">
              <i class="mdi mdi-speedometer"></i>
            </span>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>
        <!-- #Dashboard -->

        <!-- Server -->
        <li class="nav-item menu-items {{ (request()->is('server') OR request()->is('server/*')) ? 'active' : '' }}">
            <a class="nav-link" data-toggle="collapse" href="#server" aria-expanded="false" aria-controls="server">
            <span class="menu-icon">
              <i class="mdi mdi-server"></i>
            </span>
                <span class="menu-title">Server</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse {{ (request()->is('server') OR request()->is('server/*')) ? 'show' : '' }}" id="server">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('server/info') ? 'active' : '' }}" href="{{ url('server/info') }}">Billing server</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('server/disk/space') ? 'active' : '' }}" href="{{ url('server/disk/space') }}">Logical disk space</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('server/database/status/space') ? 'active' : '' }}" href="{{ url('server/database/status/space') }}">Database status</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('server/connectivity/status') ? 'active' : '' }}" href="{{ url('server/connectivity/status') }}">Connectivity test</a>
                    </li>
                </ul>
            </div>
        </li>
        <!-- #Server -->

        <!-- IGW Platform -->
        <li class="nav-item menu-items {{ (request()->is('platform/igw') OR request()->is('platform/igw/*')) ? 'active' : '' }}">
            <a class="nav-link" data-toggle="collapse" href="#igw-platform" aria-expanded="false" aria-controls="igw-platform">
            <span class="menu-icon">
              <i class="mdi mdi-laptop"></i>
            </span>
                <span class="menu-title">IGW Platform</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse {{ (request()->is('platform/igw') OR request()->is('platform/igw/*')) ? 'show' : '' }}" id="igw-platform">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->is('platform/igw/report/callsummary') ? 'active' : '' }}"
                            href="{{ url('platform/igw/report/callsummary') }}">
                            IGW call summary report
                        </a>
                    </li>
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->is('platform/igw/report/ioswise') ? 'active' : '' }}"
                            href="{{ url('platform/igw/report/ioswise') }}">
                            IOS wise report
                        </a>
                    </li>
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->is('platform/igw/report/oswise') ? 'active' : '' }}"
                            href="{{ url('platform/igw/report/oswise') }}">
                            OS wise Report
                        </a>
                    </li>
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->is('platform/igw/report/btrc') ? 'active' : '' }}"
                            href="{{ url('platform/igw/report/btrc') }}">
                            BTRC daily report
                        </a>
                    </li>
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->is('platform/igw/report/crosscheck') ? 'active' : '' }}"
                            href="{{ url('platform/igw/report/crosscheck') }}">
                            Main and Summary check
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        <!-- #IGW Platform -->

        <!-- IOS Platform -->
        <li class="nav-item menu-items {{ (request()->is('platform/ios') OR request()->is('platform/ios/*')) ? 'active' : '' }}">
            <a class="nav-link" data-toggle="collapse" href="#ios-platform" aria-expanded="false" aria-controls="ios-platform">
            <span class="menu-icon">
              <i class="mdi mdi-laptop"></i>
            </span>
                <span class="menu-title">IOS Platform</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse {{ (request()->is('platform/ios') OR request()->is('platform/ios/*')) ? 'show' : '' }}" id="ios-platform">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->is('platform/ios/report/callsummary') ? 'active' : '' }}"
                            href="{{ url('platform/ios/report/callsummary') }}">
                            IOS daily report
                        </a>
                    </li>
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->is('platform/ios/report/btrc') ? 'active' : '' }}"
                            href="{{ url('platform/ios/report/btrc') }}">
                            IOS report for (BTRC)
                        </a>
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->is('platform/ios/report/crosscheck') ? 'active' : '' }}"
                            href="{{ url('platform/ios/report/crosscheck') }}">
                            Main and Summary check
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        <!-- #IOS Platform -->

        <!-- IGW and IOS Platform -->
        <li class="nav-item menu-items {{ (request()->is('platform/igwandios') OR request()->is('platform/igwandios/*')) ? 'active' : '' }}">
            <a class="nav-link" data-toggle="collapse" href="#igw_and_ios" aria-expanded="false" aria-controls="igw_and_ios">
            <span class="menu-icon">
              <i class="mdi mdi-laptop"></i>
            </span>
                <span class="menu-title">IGW and IOS reports</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse {{ (request()->is('platform/igwandios') OR request()->is('platform/igwandios/*')) ? 'show' : '' }}" id="igw_and_ios">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->is('platform/igwandios/report/iof/inoutbound') ? 'active' : '' }}"
                            href="{{ url('platform/igwandios/report/iof/inoutbound') }}">
                            IOF (In-Out) bound report
                        </a>
                    </li>
                    <li class="nav-item">
                        <a
                            class="nav-link
                            {{
                                (request()->is('platform/igwandios/report/iof/daily/call/summary/report') OR
                                request()->is('platform/igwandios/report/iof/company') OR
                                request()->is('platform/igwandios/report/iof/company/*')) ? 'active' : ''
                            }}"
                            href="{{ url('platform/igwandios/report/iof/daily/call/summary/report') }}">
                            IOF daily report
                        </a>
                    </li>
                    {{--                    <li class="nav-item">--}}
                    {{--                        <a--}}
                    {{--                            class="nav-link {{ request()->is('platform/igwandios/report/iof/callsummary/old') ? 'active' : '' }}"--}}
                    {{--                            href="{{ url('platform/igwandios/report/iof/callsummary/old') }}">--}}
                    {{--                            <span class="text-red">IOF Daily Report</span><span class="label pull-right bg-red">Old</span>--}}
                    {{--                        </a>--}}
                    {{--                    </li>--}}
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->is('platform/igwandios/report/comparison') ? 'active' : '' }}"
                            href="{{ url('platform/igwandios/report/comparison') }}">
                            IGW and IOS comparison
                        </a>
                    </li>

                </ul>
            </div>
        </li>
        <!-- #IGW and IOS Platform -->

        <!-- BanglaICX Platform -->
        <li class="nav-item menu-items {{ (request()->is('platform/banglaicx') OR request()->is('platform/banglaicx/*')) ? 'active' : '' }}">
            <a class="nav-link" data-toggle="collapse" href="#bicx-platform" aria-expanded="false" aria-controls="bicx-platform">
            <span class="menu-icon">
              <i class="mdi mdi-laptop"></i>
            </span>
                <span class="menu-title">BanglaICX Platform</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse {{ (request()->is('platform/banglaicx') OR request()->is('platform/banglaicx/*')) ? 'show' : '' }}" id="bicx-platform">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->is('platform/banglaicx/cdr-file/status') ? 'active' : '' }}"
                            href="{{ url('platform/banglaicx/cdr-file/status') }}">
                            CDR File Status
                        </a>
                    </li>
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->is('platform/banglaicx/report/callsummary') ? 'active' : '' }}"
                            href="{{ url('platform/banglaicx/report/callsummary') }}">
                            BanglaICX daily report
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        <!-- #BanglaICX Platform -->

        @if(Auth::user()->user_type != 2)
            <!-- Noclick -->
            <li class="nav-item menu-items {{ (request()->is('noclick') OR request()->is('noclick/*')) ? 'active' : '' }}">
                <a class="nav-link" data-toggle="collapse" href="#noclick" aria-expanded="false" aria-controls="noclick">
            <span class="menu-icon">
              <i class="mdi mdi-alarm"></i>
            </span>
                    <span class="menu-title">Noclick dashboard</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse {{ (request()->is('noclick') OR request()->is('noclick/*')) ? 'show' : '' }}" id="noclick">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a class="nav-link
                            {{ (request()->is('noclick/schedules') OR request()->is('noclick/schedules/*')) ? 'active' : '' }}"
                               href="{{ url('noclick/schedules/dashboard') }}">
                                Noclick schedules
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link
                            {{ (request()->is('noclick/commands') OR request()->is('noclick/commands/*')) ? 'active' : '' }}"
                               href="{{ url('noclick/commands') }}">
                                Noclick commands
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link
                        {{ (request()->is('noclick/mail/templates') OR request()->is('noclick/mail/templates/*')) ? 'active' : '' }}"
                               href="{{ url('noclick/mail/templates') }}">
                                Noclick mail templates
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <!-- #Noclick -->
        @endif

        <!-- Setting -->
        <li class="nav-item menu-items {{ (request()->is('setting') OR request()->is('setting/*')) ? 'active' : '' }}">
            <a class="nav-link" data-toggle="collapse" href="#setting" aria-expanded="false" aria-controls="setting">
            <span class="menu-icon">
              <i class="mdi mdi-settings"></i>
            </span>
                <span class="menu-title">Setting</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse {{ (request()->is('setting') OR request()->is('setting/*')) ? 'show' : '' }}" id="setting">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ (request()->is('setting/apps') OR request()->is('setting/apps/*')) ? 'active' : '' }}"
                           href="{{ url('setting/apps') }}"> App setting</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ (request()->is('setting/users') OR request()->is('setting/users/*')) ? 'active' : '' }}"
                           href="{{ url('setting/users') }}"> Users</a>
                    </li>
                    @if(Auth::user()->user_type != 2)
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->is('setting/themes') OR request()->is('setting/themes/*')) ? 'active' : '' }}"
                               href="{{ url('setting/themes') }}"> Themes</a>
                        </li>
                    @endif
                </ul>
            </div>
        </li>
        <!-- #Setting -->

    </ul>
</nav>
