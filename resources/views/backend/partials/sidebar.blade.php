<div id="kt_aside" class="aside aside-default aside-hoverable " data-kt-drawer="true" data-kt-drawer-name="aside"
    data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true"
    data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start"
    data-kt-drawer-toggle="#kt_aside_toggle">

    <!--begin::Brand-->
    <div class="px-10 aside-logo flex-column-auto pt-9 bg-primary" style="padding-bottom: 1.2rem" id="kt_aside_logo">
        <!--begin::Logo-->
        <a href="{{ route('admin.dashboard') }}">
            <img alt="Logo" src="{{ asset($systemSetting->logo ?? 'backend/media/logos/logo-default.svg') }}"
                class="max-h-50px logo-default theme-light-show" style="width: 45px;height: 45px;" />
            <img alt="Logo" src="{{ asset($systemSetting->logo ?? 'backend/media/logos/logo-default.svg') }}"
                class="max-h-50px logo-minimize" style="width: 45px;height: 45px;"/>
        </a>
        <!--end::Logo-->
    </div>
    <!--end::Brand-->

    <!--begin::Aside menu-->
    <div class="aside-menu flex-column-fluid ps-3 pe-1">
        <!--begin::Aside Menu-->

        <!--begin::Menu-->
        <div class="my-5 menu menu-sub-indention menu-column menu-rounded menu-title-gray-600 menu-icon-gray-400 menu-active-bg menu-state-primary menu-arrow-gray-500 fw-semibold fs-6 mt-lg-2 mb-lg-0"
            id="kt_aside_menu" data-kt-menu="true">

            <div class="mx-4 hover-scroll-y" id="kt_aside_menu_wrapper" data-kt-scroll="true"
                data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto"
                data-kt-scroll-wrappers="#kt_aside_menu" data-kt-scroll-offset="20px"
                data-kt-scroll-dependencies="#kt_aside_logo, #kt_aside_footer">

                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                        href="{{ route('admin.dashboard') }}">
                        <span class="menu-icon">
                            <i class="ki-duotone ki-element-11 fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                                <span class="path4"></span>
                            </i>
                        </span>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </div>

                <div class="menu-item">
                    <div class="menu-content">
                        <div class="mx-1 my-2 separator"></div>
                    </div>
                </div>

                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('admin.shops.*') ? 'active' : '' }}"
                        href="{{ route('admin.shops.index') }}">
                        <span class="menu-icon">

                            <svg width="32" height="32" viewBox="0 0 25 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M15.5 2H9.5C8.94772 2 8.5 2.44772 8.5 3V5C8.5 5.55228 8.94772 6 9.5 6H15.5C16.0523 6 16.5 5.55228 16.5 5V3C16.5 2.44772 16.0523 2 15.5 2Z"
                                    stroke="#274F45" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M16.5 4H18.5C19.0304 4 19.5391 4.21071 19.9142 4.58579C20.2893 4.96086 20.5 5.46957 20.5 6V20C20.5 20.5304 20.2893 21.0391 19.9142 21.4142C19.5391 21.7893 19.0304 22 18.5 22H6.5C5.96957 22 5.46086 21.7893 5.08579 21.4142C4.71071 21.0391 4.5 20.5304 4.5 20V6C4.5 5.46957 4.71071 4.96086 5.08579 4.58579C5.46086 4.21071 5.96957 4 6.5 4H8.5"
                                    stroke="#274F45" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M12.5 11H16.5" stroke="#274F45" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M12.5 16H16.5" stroke="#274F45" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M8.5 11H8.51" stroke="#274F45" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M8.5 16H8.51" stroke="#274F45" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>

                        </span>
                        <span class="menu-title">Shops</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('admin.listing_requests.*') ? 'active' : '' }}"
                        href="{{ route('admin.listing_requests.index') }}">
                        <span class="menu-icon">

                            <svg width="32" height="32" viewBox="0 0 25 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M9.5 3H4.5C3.94772 3 3.5 3.44772 3.5 4V9C3.5 9.55228 3.94772 10 4.5 10H9.5C10.0523 10 10.5 9.55228 10.5 9V4C10.5 3.44772 10.0523 3 9.5 3Z"
                                    stroke="#77978F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M9.5 14H4.5C3.94772 14 3.5 14.4477 3.5 15V20C3.5 20.5523 3.94772 21 4.5 21H9.5C10.0523 21 10.5 20.5523 10.5 20V15C10.5 14.4477 10.0523 14 9.5 14Z"
                                    stroke="#77978F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M14.5 4H21.5" stroke="#77978F" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M14.5 9H21.5" stroke="#77978F" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M14.5 15H21.5" stroke="#77978F" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M14.5 20H21.5" stroke="#77978F" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>

                        </span>
                        <span class="menu-title">Listing Requests</span>
                    </a>
                </div>

                <div data-kt-menu-trigger="click"
                    class="menu-item {{ request()->routeIs(['admin.categories.*', 'admin.sub_categories.*']) ? 'active show' : '' }} menu-accordion">
                    <span class="menu-link">
                        <span class="menu-icon">

                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="1"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 4h6v6h-6z" />
                                <path d="M4 14h6v6h-6z" />
                                <path d="M17 17m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                                <path d="M7 7m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                            </svg>

                        </span>
                        <span class="menu-title">Categories & Sub Categories</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a href="{{ route('admin.categories.index') }}"
                                class="menu-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Categories</span>
                            </a>
                        </div>
                    </div>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a href="{{ route('admin.sub_categories.index') }}"
                                class="menu-link {{ request()->routeIs('admin.sub_categories.*') ? 'active' : '' }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Sub Categories</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div data-kt-menu-trigger="click"
                    class="menu-item {{ request()->routeIs(['admin.pro_members.*', 'admin.basic_members.*', 'admin.sustainable_shoppers.*']) ? 'active show' : '' }} menu-accordion">
                    <span class="menu-link">
                        <span class="menu-icon">

                            <svg width="32" height="32" viewBox="0 0 25 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M21.3701 17.25L18.6601 12.57C19.2146 11.5515 19.5035 10.4097 19.5001 9.25C19.5001 7.39348 18.7626 5.61301 17.4498 4.30025C16.1371 2.9875 14.3566 2.25 12.5001 2.25C10.6436 2.25 8.86308 2.9875 7.55033 4.30025C6.23757 5.61301 5.50007 7.39348 5.50007 9.25C5.49663 10.4097 5.78553 11.5515 6.34007 12.57L3.63007 17.25C3.54213 17.4023 3.49592 17.5751 3.49609 17.751C3.49627 17.9269 3.54282 18.0996 3.63106 18.2517C3.7193 18.4039 3.84611 18.53 3.99868 18.6175C4.15125 18.705 4.3242 18.7507 4.50007 18.75H7.37007L8.83007 21.21C8.87929 21.2915 8.9399 21.3656 9.01007 21.43C9.19545 21.6087 9.4426 21.709 9.70007 21.71H9.84007C9.99104 21.6893 10.1353 21.6345 10.2618 21.5495C10.3883 21.4646 10.4938 21.3519 10.5701 21.22L12.5001 17.9L14.4301 21.25C14.5075 21.38 14.6134 21.4908 14.7399 21.5739C14.8663 21.6571 15.01 21.7104 15.1601 21.73H15.3001C15.561 21.7316 15.8122 21.6311 16.0001 21.45C16.0673 21.3893 16.1247 21.3184 16.1701 21.24L17.6301 18.78H20.5001C20.6763 18.7807 20.8495 18.7348 21.0023 18.647C21.1551 18.5592 21.282 18.4326 21.3701 18.28C21.4635 18.1245 21.5129 17.9464 21.5129 17.765C21.5129 17.5836 21.4635 17.4055 21.3701 17.25ZM9.69007 18.78L8.80007 17.29C8.71244 17.1422 8.58818 17.0194 8.4393 16.9336C8.29041 16.8478 8.12192 16.8018 7.95007 16.8H6.22007L7.65007 14.32C8.6348 15.2689 9.87589 15.9085 11.2201 16.16L9.69007 18.78ZM12.5001 14.25C11.5112 14.25 10.5445 13.9568 9.72222 13.4073C8.89998 12.8579 8.25911 12.077 7.88068 11.1634C7.50224 10.2498 7.40322 9.24445 7.59615 8.27455C7.78907 7.30464 8.26528 6.41373 8.96454 5.71447C9.6638 5.0152 10.5547 4.539 11.5246 4.34607C12.4945 4.15315 13.4999 4.25216 14.4135 4.6306C15.3271 5.00904 16.108 5.6499 16.6574 6.47215C17.2068 7.29439 17.5001 8.26109 17.5001 9.25C17.5001 10.5761 16.9733 11.8479 16.0356 12.7855C15.0979 13.7232 13.8262 14.25 12.5001 14.25ZM17.0501 16.8C16.8782 16.8018 16.7097 16.8478 16.5609 16.9336C16.412 17.0194 16.2877 17.1422 16.2001 17.29L15.3101 18.78L13.7901 16.13C15.1296 15.8734 16.366 15.2343 17.3501 14.29L18.7801 16.77L17.0501 16.8Z"
                                    fill="#274F45" />
                            </svg>

                        </span>
                        <span class="menu-title">Members</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a href="{{ route('admin.pro_members.index') }}"
                                class="menu-link {{ request()->routeIs('admin.pro_members.*') ? 'active' : '' }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Pro Members</span>
                            </a>
                        </div>
                    </div>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a href="{{ route('admin.basic_members.index') }}"
                                class="menu-link {{ request()->routeIs('admin.basic_members.*') ? 'active' : '' }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Basic Members</span>
                            </a>
                        </div>
                    </div>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a href="{{ route('admin.sustainable_shoppers.index') }}"
                                class="menu-link {{ request()->routeIs('admin.sustainable_shoppers.*') ? 'active' : '' }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Sustainable Shoppers</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('admin.members_spotlight.*') ? 'active' : '' }}"
                        href="{{ route('admin.members_spotlight.index') }}">
                        <span class="menu-icon">

                            <svg width="32" height="32" viewBox="0 0 21 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M12.5019 8.99994H11.5019V7.99994C11.5019 7.73472 11.3965 7.48037 11.209 7.29283C11.0215 7.1053 10.7671 6.99994 10.5019 6.99994C10.2367 6.99994 9.98232 7.1053 9.79479 7.29283C9.60725 7.48037 9.50189 7.73472 9.50189 7.99994V8.99994H8.50189C8.23668 8.99994 7.98232 9.1053 7.79479 9.29283C7.60725 9.48037 7.50189 9.73472 7.50189 9.99994C7.50189 10.2652 7.60725 10.5195 7.79479 10.707C7.98232 10.8946 8.23668 10.9999 8.50189 10.9999H9.50189V11.9999C9.50189 12.2652 9.60725 12.5195 9.79479 12.707C9.98232 12.8946 10.2367 12.9999 10.5019 12.9999C10.7671 12.9999 11.0215 12.8946 11.209 12.707C11.3965 12.5195 11.5019 12.2652 11.5019 11.9999V10.9999H12.5019C12.7671 10.9999 13.0215 10.8946 13.209 10.707C13.3965 10.5195 13.5019 10.2652 13.5019 9.99994C13.5019 9.73472 13.3965 9.48037 13.209 9.29283C13.0215 9.1053 12.7671 8.99994 12.5019 8.99994ZM20.2119 9.28994L17.8619 6.99994V3.63994C17.8619 3.37472 17.7565 3.12037 17.569 2.93283C17.3815 2.7453 17.1271 2.63994 16.8619 2.63994H13.5519L11.2119 0.289939C11.1189 0.196211 11.0083 0.121816 10.8865 0.0710475C10.7646 0.0202789 10.6339 -0.00585938 10.5019 -0.00585938C10.3699 -0.00585938 10.2392 0.0202789 10.1173 0.0710475C9.99546 0.121816 9.88486 0.196211 9.79189 0.289939L7.50189 2.63994H4.14189C3.87668 2.63994 3.62232 2.7453 3.43479 2.93283C3.24725 3.12037 3.14189 3.37472 3.14189 3.63994V6.99994L0.791892 9.28994C0.698164 9.3829 0.623769 9.4935 0.573001 9.61536C0.522232 9.73722 0.496094 9.86793 0.496094 9.99994C0.496094 10.132 0.522232 10.2627 0.573001 10.3845C0.623769 10.5064 0.698164 10.617 0.791892 10.7099L3.14189 13.0499V16.3599C3.14189 16.6252 3.24725 16.8795 3.43479 17.067C3.62232 17.2546 3.87668 17.3599 4.14189 17.3599H7.50189L9.84189 19.7099C9.93486 19.8037 10.0455 19.8781 10.1673 19.9288C10.2892 19.9796 10.4199 20.0057 10.5519 20.0057C10.6839 20.0057 10.8146 19.9796 10.9365 19.9288C11.0583 19.8781 11.1689 19.8037 11.2619 19.7099L13.6019 17.3599H16.9119C17.1771 17.3599 17.4315 17.2546 17.619 17.067C17.8065 16.8795 17.9119 16.6252 17.9119 16.3599V13.0499L20.2619 10.7099C20.3524 10.6137 20.423 10.5005 20.4695 10.3769C20.516 10.2532 20.5376 10.1215 20.533 9.98951C20.5283 9.85748 20.4975 9.72767 20.4424 9.6076C20.3873 9.48752 20.309 9.37956 20.2119 9.28994ZM16.1619 11.9299C16.0674 12.0226 15.9922 12.133 15.9407 12.2549C15.8892 12.3767 15.8624 12.5076 15.8619 12.6399V15.3599H13.1419C13.0096 15.3605 12.8787 15.3873 12.7568 15.4388C12.635 15.4903 12.5245 15.5655 12.4319 15.6599L10.5019 17.5899L8.57189 15.6599C8.47928 15.5655 8.36883 15.4903 8.24696 15.4388C8.12508 15.3873 7.9942 15.3605 7.86189 15.3599H5.14189V12.6399C5.14134 12.5076 5.11454 12.3767 5.06305 12.2549C5.01155 12.133 4.93638 12.0226 4.84189 11.9299L2.91189 9.99994L4.84189 8.06994C4.93638 7.97732 5.01155 7.86688 5.06305 7.745C5.11454 7.62313 5.14134 7.49225 5.14189 7.35994V4.63994H7.86189C7.9942 4.63939 8.12508 4.61259 8.24696 4.56109C8.36883 4.5096 8.47928 4.43443 8.57189 4.33994L10.5019 2.40994L12.4319 4.33994C12.5245 4.43443 12.635 4.5096 12.7568 4.56109C12.8787 4.61259 13.0096 4.63939 13.1419 4.63994H15.8619V7.35994C15.8624 7.49225 15.8892 7.62313 15.9407 7.745C15.9922 7.86688 16.0674 7.97732 16.1619 8.06994L18.0919 9.99994L16.1619 11.9299Z"
                                    fill="#274F45" />
                            </svg>

                        </span>
                        <span class="menu-title">Member Spotlight</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('admin.subscription.*') ? 'active' : '' }}"
                        href="{{ route('admin.subscription.index') }}">
                        <span class="menu-icon">

                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="1"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 9a10 10 0 1 0 20 0" />
                                <path d="M12 19a10 10 0 0 1 10 -10" />
                                <path d="M2 9a10 10 0 0 1 10 10" />
                                <path d="M12 4a9.7 9.7 0 0 1 2.99 7.5" />
                                <path d="M9.01 11.5a9.7 9.7 0 0 1 2.99 -7.5" />
                            </svg>


                        </span>
                        <span class="menu-title">Subscription Plans</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('admin.members_spotlight.*') ? 'active' : '' }}"
                        href="#">
                        <span class="menu-icon">

                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="1"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 8a5 5 0 0 1 0 8" />
                                <path d="M17.7 5a9 9 0 0 1 0 14" />
                                <path
                                    d="M6 15h-2a1 1 0 0 1 -1 -1v-4a1 1 0 0 1 1 -1h2l3.5 -4.5a.8 .8 0 0 1 1.5 .5v14a.8 .8 0 0 1 -1.5 .5l-3.5 -4.5" />
                            </svg>


                        </span>
                        <span class="menu-title">Discounts</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('admin.members_spotlight.*') ? 'active' : '' }}"
                        href="#">
                        <span class="menu-icon">

                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="1"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                <path d="M9 7l1 0" />
                                <path d="M9 13l6 0" />
                                <path d="M13 17l2 0" />
                            </svg>


                        </span>
                        <span class="menu-title">Accounting</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('admin.our_missions.*') ? 'active' : '' }}"
                        href="{{ route('admin.our_missions.index') }}">
                        <span class="menu-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="1"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                <path d="M12 7a5 5 0 1 0 5 5" />
                                <path d="M13 3.055a9 9 0 1 0 7.941 7.945" />
                                <path d="M15 6v3h3l3 -3h-3v-3z" />
                                <path d="M15 9l-3 3" />
                            </svg>


                        </span>
                        <span class="menu-title">Our Mission</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('admin.tutorials.*') ? 'active' : '' }}"
                        href="{{ route('admin.tutorials.index') }}">
                        <span class="menu-icon">

                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="1"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M15 10l4.553 -2.276a1 1 0 0 1 1.447 .894v6.764a1 1 0 0 1 -1.447 .894l-4.553 -2.276v-4z" />
                                <path
                                    d="M3 6m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z" />
                            </svg>

                        </span>
                        <span class="menu-title">Tutorials</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}"
                        href="{{ route('admin.faqs.index') }}">
                        <span class="menu-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24"
                                height="24" stroke-width="2">
                                <path
                                    d="M19.875 6.27c.7 .398 1.13 1.143 1.125 1.948v7.284c0 .809 -.443 1.555 -1.158 1.948l-6.75 4.27a2.269 2.269 0 0 1 -2.184 0l-6.75 -4.27a2.225 2.225 0 0 1 -1.158 -1.948v-7.285c0 -.809 .443 -1.554 1.158 -1.947l6.75 -3.98a2.33 2.33 0 0 1 2.25 0l6.75 3.98h-.033z">
                                </path>
                                <path d="M12 16v.01"></path>
                                <path d="M12 13a2 2 0 0 0 .914 -3.782a1.98 1.98 0 0 0 -2.414 .483"></path>
                            </svg>
                        </span>
                        <span class="menu-title">FAQ</span>
                    </a>
                </div>

                <div data-kt-menu-trigger="click"
                    class="menu-item {{ request()->routeIs(['admin.banners.*', 'admin.how_it_works.*']) ? 'active show' : '' }} menu-accordion">
                    <span class="menu-link">
                        <span class="menu-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="1"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" />
                                <path d="M12 4v7l2 -2l2 2v-7" />
                            </svg>
                        </span>
                        <span class="menu-title">CMS</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a href="{{ route('admin.banners.index') }}"
                                class="menu-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Banner</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a href="{{ route('admin.how_it_works.index') }}"
                                class="menu-link {{ request()->routeIs('admin.how_it_works.*') ? 'active' : '' }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">How It Works</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div data-kt-menu-trigger="click"
                    class="menu-item {{ request()->routeIs(['profile.setting', 'stripe.setting', 'paypal.setting', 'dynamic_page.*', 'system.index', 'mail.setting', 'social.index']) ? 'active show' : '' }} menu-accordion">
                    <span class="menu-link">
                        <span class="menu-icon">
                            <i class="fa-solid fa-gear fs-2"></i>
                        </span>
                        <span class="menu-title">Setting</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a href="{{ route('profile.setting') }}"
                                class="menu-link {{ request()->routeIs('profile.setting') ? 'active' : '' }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Profile Setting</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a href="{{ route('system.index') }}"
                                class="menu-link {{ request()->routeIs('system.index') ? 'active' : '' }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">System Setting</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a href="{{ route('dynamic_page.index') }}"
                                class="menu-link {{ request()->routeIs(['dynamic_page.index', 'dynamic_page.create', 'dynamic_page.update']) ? 'active show' : '' }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Dynamic Page</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a href="{{ route('mail.setting') }}"
                                class="menu-link {{ request()->routeIs('mail.setting') ? 'active' : '' }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Mail Setting</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a href="{{ route('social.index') }}"
                                class="menu-link {{ request()->routeIs('social.index') ? 'active' : '' }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Social Media</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
